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
        $this->landing();
        $this->adminUser();
        $this->sampleOrders();
    }

    /** Default landing settings + sections + marketing items (idempotent; never overwrites admin edits). */
    private function landing(): void
    {
        \App\Models\LandingPageSetting::firstOrCreate(['id' => 1], [
            'site_title_ar' => 'إمداد',
            'company_name_ar' => 'إمداد لمستلزمات المطاعم',
            'hero_title_ar' => 'مستلزمات مطعمك تصلك بسهولة',
            'hero_subtitle_ar' => 'اطلب بالجملة بأسعار مناسبة وتوصيل سريع — الدفع عند الاستلام.',
            'primary_cta_label_ar' => 'ابدأ الطلب',
            'phone' => '01000000000',
            'whatsapp_phone' => '01000000000',
            'is_active' => true,
        ]);

        $sections = [
            ['hero', 'ابدأ الطلب', 0, null],
            ['categories', 'التصنيفات', 1, ['limit' => 8]],
            ['featured_products', 'عروض مميزة', 2, ['limit' => 8]],
            ['features', 'لماذا إمداد؟', 3, null],
            ['contact', 'تواصل معنا', 4, null],
        ];
        foreach ($sections as [$key, $title, $order, $settings]) {
            \App\Models\LandingSection::firstOrCreate(['key' => $key], [
                'title_ar' => $title, 'is_active' => true, 'sort_order' => $order, 'settings' => $settings,
            ]);
        }

        $features = \App\Models\LandingSection::where('key', 'features')->first();
        if ($features !== null && $features->items()->doesntExist()) {
            foreach ([
                ['أسعار جملة تنافسية', '🏷️'],
                ['توصيل لمناطق مختارة', '🚚'],
                ['الدفع عند الاستلام', '💵'],
            ] as $i => [$title, $icon]) {
                $features->items()->create(['title_ar' => $title, 'icon' => $icon, 'is_active' => true, 'sort_order' => $i]);
            }
        }
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

    /**
     * Generate a polished generic product placeholder (brand-toned, per-product hue) and store it
     * on the public disk (prompt 48 §9.6 — no internet, no copyrighted packshots). Returns the path.
     */
    private function placeholderImage(string $seed): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $size = 600;
        $im = imagecreatetruecolor($size, $size);
        $palette = [[3, 72, 142], [2, 100, 177], [13, 122, 42], [9, 143, 220], [2, 44, 104]];
        $c = $palette[crc32($seed) % count($palette)];
        imagefilledrectangle($im, 0, 0, $size, $size, imagecolorallocate($im, $c[0], $c[1], $c[2]));

        $lighter = imagecolorallocate($im, min(255, $c[0] + 45), min(255, $c[1] + 45), min(255, $c[2] + 45));
        imagefilledellipse($im, (int) ($size / 2), (int) ($size * 0.42), (int) ($size * 0.6), (int) ($size * 0.6), $lighter);

        $white = imagecolorallocate($im, 255, 255, 255);
        $pad = (int) ($size * 0.32);
        imagefilledrectangle($im, $pad, (int) ($size * 0.30), $size - $pad, (int) ($size * 0.44), $white);
        imagefilledrectangle($im, $pad, (int) ($size * 0.50), $size - $pad, (int) ($size * 0.70), $white);

        ob_start();
        imagepng($im);
        $binary = (string) ob_get_clean();
        imagedestroy($im);

        $path = 'products/'.\Illuminate\Support\Str::ulid().'.png';
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $binary);

        return $path;
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

                // Generate a clean branded placeholder image when none exists (never overwrite an
                // admin-uploaded image; skip if GD is unavailable → Blade letter fallback).
                if (! $product->image_path && ($path = $this->placeholderImage($name)) !== null) {
                    $product->update(['image_path' => $path]);
                }

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
