<?php

declare(strict_types=1);

namespace App\Domain\Cart;

use App\Domain\Pricing\PricingService;
use App\Domain\Settings\SettingsService;
use App\Domain\Support\Enums\AvailabilityStatus;
use App\Domain\Support\Money;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\ProductUnit;
use InvalidArgumentException;

/**
 * The customer's cart operations (prompt 42 §6). All money is recomputed server-side via
 * {@see PricingService}; the cart never reserves stock and never shows delivery estimates. The
 * qualifying subtotal (for minimum-order) is the effective product subtotal of available lines.
 */
final class CartService
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly SettingsService $settings,
    ) {
    }

    public function getOrCreate(Customer $customer): Cart
    {
        return Cart::firstOrCreate(['customer_id' => $customer->id]);
    }

    /** Add (or increase) a sellable unit line. Returns the affected item. */
    public function add(Customer $customer, ProductUnit $unit, int $quantity = 1): CartItem
    {
        if (! $unit->is_active || ! $unit->is_sellable) {
            throw new InvalidArgumentException('Unit is not sellable.');
        }

        $quantity = max(1, $quantity);
        $cart = $this->getOrCreate($customer);

        $item = $cart->items()->firstOrNew(['product_unit_id' => $unit->id]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $item->last_seen_unit_price = $this->pricing->priceFor($unit, $item->quantity)->applied->minorUnits;
        $item->save();

        return $item;
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($item);

            return;
        }

        $item->quantity = $quantity;
        $item->last_seen_unit_price = $this->pricing->priceFor($item->productUnit, $quantity)->applied->minorUnits;
        $item->save();
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    /** Recompute the full cart for display: effective prices, availability, minimum-order progress. */
    public function view(Customer $customer): CartView
    {
        $cart = $this->getOrCreate($customer);
        $cart->load('items.productUnit.product');

        $lines = [];
        $subtotal = Money::zero();

        foreach ($cart->items as $item) {
            $unit = $item->productUnit;
            $price = $this->pricing->priceFor($unit, $item->quantity);
            [$available, $reason] = $this->availability($unit, $item->quantity);

            if ($available) {
                $subtotal = $subtotal->plus($price->lineTotal);
            }

            $lines[] = new CartLine($item, $unit, $price, $available, $reason);
        }

        $minimum = $this->settings->minimumOrderAmount();
        $meets = $subtotal->greaterThanOrEqualTo($minimum);
        $remaining = $meets ? Money::zero() : $minimum->minus($subtotal);

        return new CartView($lines, $subtotal, $meets, $minimum, $remaining);
    }

    /**
     * @return array{0: bool, 1: ?string} available flag + translation key when unavailable
     */
    private function availability(ProductUnit $unit, int $quantity): array
    {
        $product = $unit->product;

        if ($product === null || $product->availability !== AvailabilityStatus::Available) {
            return [false, 'inventory.out_of_stock'];
        }

        if (! $unit->is_active || ! $unit->is_sellable) {
            return [false, 'inventory.out_of_stock'];
        }

        $requiredSubUnits = $quantity * max(1, (int) $unit->conversion_to_sub_unit);

        if ($product->subStock() < $requiredSubUnits) {
            return [false, 'inventory.insufficient'];
        }

        return [true, null];
    }
}
