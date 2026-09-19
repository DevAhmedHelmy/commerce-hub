<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Inventory\InventoryAdjustmentType;
use App\Domain\Support\Enums\AvailabilityStatus;
use App\Domain\Support\Enums\DeliveryDiscountType;
use App\Models\Category;
use App\Models\DeliveryArea;
use App\Models\DeliveryDiscountRule;
use App\Models\DeliverySlot;
use App\Models\Product;
use App\Models\ProductOffer;
use App\Models\ProductPriceTier;
use App\Models\ProductUnit;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Idempotent demo data for staging/review (prompt 42 §O). Safe to re-run: everything uses
 * firstOrCreate/updateOrCreate keyed on stable identifiers. Creates a dev-only admin (never in
 * production, no committed production password). No real customer PII; OTP stays dev-only.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $units = $this->units();
        $this->settings();
        $this->delivery();
        $this->catalog($units);
        $this->adminUser();
        $this->sampleOrders();
    }

    /** A demo customer with a few orders across statuses, for dashboard/order-screen review. */
    private function sampleOrders(): void
    {
        if (\App\Models\Order::query()->exists()) {
            return; // idempotent — only seed once
        }

        $customer = \App\Models\Customer::firstOrCreate(
            ['phone' => '01055550000'],
            ['business_name' => 'مطعم العرض', 'contact_person_name' => 'أحمد', 'whatsapp_phone' => '01055550000', 'onboarding_completed_at' => Carbon::now()],
        );
        $area = DeliveryArea::query()->where('is_active', true)->first();
        $slot = DeliverySlot::query()->where('is_active', true)->first();
        $product = Product::query()->whereHas('units', fn ($q) => $q->where('level', ProductUnit::LEVEL_SUB)->where('stock_quantity', '>', 10))->first();

        if ($area === null || $slot === null || $product === null) {
            return;
        }

        $address = $customer->addresses()->firstOrCreate(
            ['is_default' => true],
            ['delivery_area_id' => $area->id, 'address_line' => 'شارع التحرير، المبنى ٣'],
        );

        $cart = app(\App\Domain\Cart\CartService::class);
        $orders = app(\App\Domain\Ordering\OrderService::class);
        $date = Carbon::now()->addDay();
        // Align the slot weekday with the delivery date so it is selectable.
        $slot->update(['day_of_week' => $date->isoWeekday()]);

        $statuses = [\App\Domain\Support\Enums\OrderStatus::New, \App\Domain\Support\Enums\OrderStatus::Confirmed, \App\Domain\Support\Enums\OrderStatus::Delivered];
        foreach ($statuses as $target) {
            // Carton (primary) qty keeps each order above the demo minimum-order amount.
            $cart->add($customer, $product->primaryUnit()->first(), 2);
            $input = new \App\Domain\Ordering\DTO\CheckoutInput(
                addressId: $address->id, deliveryDate: $date->toDateString(),
                slotId: $slot->id, submissionToken: (string) \Illuminate\Support\Str::uuid(),
            );
            $result = $orders->place($customer, $input);
            if (! $result->isPlaced()) {
                continue;
            }
            // Advance to the target status through the forward path.
            $order = $result->order;
            while ($order->status !== $target && $order->status->forwardTransitions() !== []) {
                $order = $orders->transition($order, $order->status->forwardTransitions()[0], 'admin');
                if ($order->status === $target) {
                    break;
                }
            }
        }
    }

    /** @return array<string, Unit> */
    private function units(): array
    {
        $defs = [
            'carton' => 'كرتونة', 'piece' => 'قطعة', 'bag' => 'كيس', 'bottle' => 'زجاجة', 'pack' => 'عبوة',
        ];
        $units = [];
        $sort = 0;
        foreach ($defs as $code => $nameAr) {
            $units[$code] = Unit::firstOrCreate(['code' => $code], ['name_ar' => $nameAr, 'is_active' => true, 'sort_order' => $sort++]);
        }

        return $units;
    }

    private function settings(): void
    {
        foreach ([
            'minimum_order_amount' => '50000',
            'business_name_ar' => 'إمداد لمستلزمات المطاعم',
            'business_phone' => '01000000000',
            'business_whatsapp' => '01000000000',
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function delivery(): void
    {
        foreach (['التجمع الخامس' => 4000, 'مدينة نصر' => 3000, 'المعادي' => 5000] as $name => $fee) {
            DeliveryArea::firstOrCreate(['name_ar' => $name], ['base_fee' => $fee, 'is_active' => true]);
        }

        // Weekday windows (ISO 1=Mon..7=Sun) — Sat–Thu, two windows.
        foreach ([6, 7, 1, 2, 3, 4] as $i => $day) {
            DeliverySlot::firstOrCreate(
                ['day_of_week' => $day, 'start_time' => '10:00', 'end_time' => '14:00'],
                ['label_ar' => '١٠ص – ٢م', 'is_active' => true, 'sort_order' => $i],
            );
            DeliverySlot::firstOrCreate(
                ['day_of_week' => $day, 'start_time' => '16:00', 'end_time' => '20:00'],
                ['label_ar' => '٤م – ٨م', 'is_active' => true, 'sort_order' => $i + 10],
            );
        }

        DeliveryDiscountRule::firstOrCreate(
            ['name' => 'توصيل مجاني للطلبات الكبيرة'],
            ['type' => DeliveryDiscountType::FreeDelivery->value, 'value' => 0, 'min_subtotal' => 150000, 'is_active' => true],
        );
    }

    /** @param array<string, Unit> $units */
    private function catalog(array $units): void
    {
        $categories = [
            'صلصات ومعلبات' => [
                ['كاتشب هاينز', 'Heinz', 12, 120000, 11000, 240],
                ['صلصة مايونيز', 'Almarai', 12, 96000, 9000, 120],
            ],
            'مجمدات' => [
                ['بطاطس مجمدة', 'Farm Frites', 4, 85000, 24000, 80],
                ['بانيه دجاج', 'Americana', 6, 150000, 27000, 60],
            ],
            'زيوت' => [
                ['زيت عباد الشمس', 'عافية', 12, 132000, 12000, 144],
            ],
        ];

        $catSort = 0;
        foreach ($categories as $catName => $products) {
            $category = Category::firstOrCreate(['name_ar' => $catName], ['is_active' => true, 'sort_order' => $catSort++]);

            foreach ($products as [$name, $brand, $conversion, $cartonPrice, $piecePrice, $subStock]) {
                $product = Product::firstOrCreate(
                    ['name_ar' => $name],
                    ['category_id' => $category->id, 'brand' => $brand, 'availability' => AvailabilityStatus::Available->value],
                );

                if ($product->units()->exists()) {
                    continue; // already seeded — keep idempotent
                }

                ProductUnit::create([
                    'product_id' => $product->id, 'unit_id' => $units['carton']->id,
                    'level' => ProductUnit::LEVEL_PRIMARY, 'conversion_to_sub_unit' => $conversion,
                    'is_sellable' => true, 'base_price' => $cartonPrice, 'stock_quantity' => 0, 'is_active' => true,
                ]);
                $sub = ProductUnit::create([
                    'product_id' => $product->id, 'unit_id' => $units['piece']->id,
                    'level' => ProductUnit::LEVEL_SUB, 'conversion_to_sub_unit' => 1,
                    'is_sellable' => true, 'base_price' => $piecePrice, 'stock_quantity' => $subStock, 'is_active' => true,
                ]);

                // A quantity tier + an active offer on the carton unit for demo pricing.
                $primary = $product->primaryUnit()->first();
                ProductPriceTier::create(['product_unit_id' => $primary->id, 'min_quantity' => 10, 'unit_price' => (int) ($cartonPrice * 0.92), 'is_active' => true]);
                ProductOffer::create([
                    'product_unit_id' => $primary->id, 'offer_price' => (int) ($cartonPrice * 0.9),
                    'starts_at' => Carbon::now()->subDay(), 'ends_at' => Carbon::now()->addDays(14), 'is_active' => true,
                ]);
            }
        }
    }

    private function adminUser(): void
    {
        if (app()->environment('production')) {
            return; // never auto-provision an admin in production
        }

        $user = User::updateOrCreate(
            ['email' => 'admin@emdad.test'],
            ['name' => 'مدير تجريبي', 'password' => Hash::make(env('DEMO_ADMIN_PASSWORD', 'password')), 'is_active' => true],
        );
        $user->syncRoles([RolesAndPermissionsSeeder::ROLE_SUPER_ADMIN]);
    }
}
