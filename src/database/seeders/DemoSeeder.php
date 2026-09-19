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
