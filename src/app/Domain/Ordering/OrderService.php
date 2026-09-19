<?php

declare(strict_types=1);

namespace App\Domain\Ordering;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Domain\Cart\CartService;
use App\Domain\Delivery\DeliveryService;
use App\Domain\Ordering\DTO\CheckoutInput;
use App\Domain\Ordering\DTO\CheckoutReview;
use App\Domain\Ordering\DTO\PlaceResult;
use App\Domain\Inventory\InventoryService;
use App\Domain\Pricing\PricingService;
use App\Domain\Settings\SettingsService;
use App\Domain\Support\Enums\OrderStatus;
use App\Domain\Support\Money;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\DeliveryArea;
use App\Models\DeliverySlot;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Order review, placement, and lifecycle (prompt 42 §8–§10). Everything commercial is recomputed
 * server-side; placement is transactional with row-locked stock deduction, immutable snapshots,
 * duplicate-submit idempotency, and full rollback on failure. Cancellation restores stock exactly
 * once. Pricing/inventory/delivery rules are enforced by their own services — never bypassed.
 */
final class OrderService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly PricingService $pricing,
        private readonly DeliveryService $delivery,
        private readonly SettingsService $settings,
        private readonly InventoryService $inventory,
        private readonly AdminAuditService $audit,
    ) {
    }

    /** Build the server-authoritative checkout summary + changed-terms/blockers (no mutation). */
    public function review(Customer $customer, CheckoutInput $input): CheckoutReview
    {
        $cartView = $this->cart->view($customer);
        $address = CustomerAddress::query()->where('customer_id', $customer->id)->find($input->addressId);
        $area = $address?->delivery_area_id ? DeliveryArea::find($address->delivery_area_id) : null;
        $slot = DeliverySlot::find($input->slotId);
        $date = $this->parseDate($input->deliveryDate);

        $subtotal = $cartView->productSubtotal;
        $quote = $area !== null ? $this->delivery->quote($area, $subtotal) : null;
        $finalTotal = $subtotal->plus($quote?->finalFee ?? Money::zero());

        $blockers = [];
        if ($cartView->isEmpty()) {
            $blockers[] = 'checkout.errors.empty';
        }
        if (collect($cartView->lines)->contains(fn ($l) => ! $l->available)) {
            $blockers[] = 'checkout.errors.unavailable';
        }
        if (! $cartView->meetsMinimum) {
            $blockers[] = 'checkout.errors.below_minimum';
        }
        if ($area === null || ! $this->delivery->isAreaOrderable($area)) {
            $blockers[] = 'checkout.errors.area';
        }
        if ($slot === null || $date === null || ! $this->delivery->isSlotSelectable($slot, $date)) {
            $blockers[] = 'checkout.errors.slot';
        }

        $changes = [];
        foreach ($cartView->lines as $line) {
            $seen = $line->item->last_seen_unit_price;
            if ($seen !== null && (int) $seen !== $line->price->applied->minorUnits) {
                $changes[] = 'checkout.changes.price';
                break;
            }
        }

        return new CheckoutReview(
            lines: $cartView->lines,
            productSubtotal: $subtotal,
            deliveryQuote: $quote,
            finalTotal: $finalTotal,
            meetsMinimum: $cartView->meetsMinimum,
            minimumOrderAmount: $cartView->minimumOrderAmount,
            changes: array_values(array_unique($changes)),
            blockers: array_values(array_unique($blockers)),
            deliveryAreaName: $area?->localized('name'),
        );
    }

    /**
     * Place the order transactionally. Returns a `review` result (no order) when terms changed or
     * blockers exist; otherwise creates the order, deducts stock, clears the cart, and returns it.
     */
    public function place(Customer $customer, CheckoutInput $input): PlaceResult
    {
        $result = DB::transaction(function () use ($customer, $input): PlaceResult {
            // Duplicate-submit idempotency.
            if ($input->submissionToken !== null) {
                $existing = Order::query()->where('submission_token', $input->submissionToken)->first();
                if ($existing !== null) {
                    return PlaceResult::placed($existing);
                }
            }

            $review = $this->review($customer, $input);
            if (! $review->canPlace()) {
                return PlaceResult::reviewRequired($review);
            }

            $address = CustomerAddress::query()->where('customer_id', $customer->id)->findOrFail($input->addressId);
            $area = DeliveryArea::findOrFail($address->delivery_area_id);
            $slot = DeliverySlot::findOrFail($input->slotId);
            $quote = $review->deliveryQuote;

            $order = new Order();
            $order->order_number = 'TMP-'.Str::uuid();
            $order->submission_token = $input->submissionToken;
            $order->customer_id = $customer->id;
            $order->business_name = $customer->business_name;
            $order->contact_person_name = $customer->contact_person_name;
            $order->phone = $customer->phone;
            $order->whatsapp_phone = $customer->whatsapp_phone;
            $order->delivery_area_name = $area->localized('name');
            $order->address_line = $address->address_line;
            $order->building = $address->building;
            $order->floor = $address->floor;
            $order->unit = $address->unit;
            $order->landmark = $address->landmark;
            $order->delivery_notes = $address->delivery_notes;
            $order->delivery_date = $this->parseDate($input->deliveryDate);
            $order->delivery_slot_label = $slot->localized('label');
            $order->slot_start_time = (string) $slot->start_time;
            $order->slot_end_time = (string) $slot->end_time;
            $order->product_subtotal = $review->productSubtotal->minorUnits;
            $order->base_delivery_fee = $quote?->baseFee->minorUnits ?? 0;
            $order->delivery_discount = $quote?->discountAmount->minorUnits ?? 0;
            $order->final_delivery_fee = $quote?->finalFee->minorUnits ?? 0;
            $order->final_total = $review->finalTotal->minorUnits;
            $order->payment_method = 'cod';
            $order->status = OrderStatus::New->value;
            $order->placed_at = Carbon::now();
            $order->save();

            $order->order_number = OrderNumber::fromId($order->id);
            $order->save();

            // Snapshot lines + aggregate normalized sub-units per product.
            $perProduct = [];
            foreach ($review->lines as $line) {
                $unit = $line->unit;
                $conversion = max(1, (int) $unit->conversion_to_sub_unit);
                $subUnits = $line->item->quantity * $conversion;
                $perProduct[$unit->product_id] = ($perProduct[$unit->product_id] ?? 0) + $subUnits;

                $order->items()->create([
                    'product_id' => $unit->product_id,
                    'product_unit_id' => $unit->id,
                    'product_name' => $unit->product?->localized('name') ?? '',
                    'brand' => $unit->product?->brand,
                    'unit_name' => $unit->label(),
                    'unit_level' => $unit->level,
                    'package_description' => $unit->localized('package_description') ?: null,
                    'quantity' => $line->item->quantity,
                    'conversion_factor' => $conversion,
                    'sub_unit_quantity' => $subUnits,
                    'base_unit_price' => $line->price->base->minorUnits,
                    'applied_unit_price' => $line->price->applied->minorUnits,
                    'applied_source' => $line->price->appliedSource,
                    'unit_saving' => $line->price->unitSaving->minorUnits,
                    'line_total' => $line->price->lineTotal->minorUnits,
                ]);
            }

            // Deduct authoritative sub-unit stock per product (row-locked; throws → rollback).
            foreach ($perProduct as $productId => $subUnits) {
                $product = Product::query()->findOrFail($productId);
                $product->load('subUnit');
                $this->inventory->deductForOrder($product->subUnit, $subUnits, $order->id);
            }

            // Clear the cart on success.
            $this->cart->getOrCreate($customer)->items()->delete();

            return PlaceResult::placed($order->fresh('items'));
        });

        // Notify active admins after commit (database channel; surfaced via Filament polling).
        if ($result->isPlaced()) {
            $admins = \App\Models\User::query()->where('is_active', true)->get();
            if ($admins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NewOrderNotification($result->order));
            }
        }

        return $result;
    }

    /** Forward status change or cancellation, actor = 'admin'|'customer'. */
    public function transition(Order $order, OrderStatus $to, string $actor, ?string $reason = null): Order
    {
        if ($to === OrderStatus::Cancelled) {
            return $this->cancel($order, $actor, $reason);
        }

        $from = $order->status;
        if (! in_array($to, $from->forwardTransitions(), true)) {
            throw new InvalidStatusTransitionException($from, $to);
        }

        $order->status = $to->value;
        $order->save();

        $this->audit->record(AuditAction::ORDER_STATUS_CHANGED, $order,
            ['status' => $from->value], ['status' => $to->value], ['order_number' => $order->order_number]);

        return $order;
    }

    public function cancel(Order $order, string $actor, ?string $reason = null): Order
    {
        $from = $order->status;
        $allowed = $actor === 'customer' ? $from->isCustomerCancellable() : $from->isAdminCancellable();

        if (! $allowed) {
            throw new InvalidStatusTransitionException($from, OrderStatus::Cancelled);
        }

        return DB::transaction(function () use ($order, $from, $actor, $reason): Order {
            $order->status = OrderStatus::Cancelled->value;
            $order->cancelled_by = $actor;
            $order->cancellation_reason = $reason;
            $order->save();

            // Restore stock exactly once (idempotent).
            if (! $this->inventory->hasRestoreFor($order->id)) {
                foreach ($order->items()->whereNotNull('product_id')->get() as $item) {
                    $product = Product::query()->with('subUnit')->find($item->product_id);
                    if ($product?->subUnit !== null) {
                        $this->inventory->restoreForOrder($product->subUnit, (int) $item->sub_unit_quantity, $order->id);
                    }
                }
            }

            $this->audit->record(AuditAction::ORDER_CANCELLED, $order,
                ['status' => $from->value], ['status' => OrderStatus::Cancelled->value],
                ['order_number' => $order->order_number, 'cancelled_by' => $actor]);

            return $order;
        });
    }

    public function canCustomerCancel(Order $order): bool
    {
        return $order->status->isCustomerCancellable();
    }

    public function canAdminCancel(Order $order): bool
    {
        return $order->status->isAdminCancellable();
    }

    private function parseDate(string $date): ?Carbon
    {
        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
