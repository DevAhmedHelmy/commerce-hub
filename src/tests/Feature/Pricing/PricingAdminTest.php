<?php

declare(strict_types=1);

use App\Models\AdminAuditLog;
use App\Models\ProductOffer;
use App\Models\ProductPriceTier;

it('audits creating a price tier', function () {
    $this->actingAs(superAdmin());
    $unit = pricingUnit(120000);

    ProductPriceTier::factory()->create(['product_unit_id' => $unit->id, 'min_quantity' => 5, 'unit_price' => 110000]);

    $log = AdminAuditLog::query()->where('auditable_type', ProductPriceTier::class)->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->new_values['unit_price'])->toBe(110000)
        ->and($log->metadata['product_unit_id'])->toBe($unit->id);
});

it('audits creating and repricing an offer', function () {
    $this->actingAs(superAdmin());
    $unit = pricingUnit(120000);
    $offer = ProductOffer::factory()->create(['product_unit_id' => $unit->id, 'offer_price' => 100000]);

    $offer->update(['offer_price' => 95000]);

    $log = AdminAuditLog::query()->where('auditable_type', ProductOffer::class)
        ->where('action', 'price_changed')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->old_values['offer_price'])->toBe(100000)
        ->and($log->new_values['offer_price'])->toBe(95000);
});

it('lets pricing_staff reach the offers screen but not inventory_staff', function () {
    $this->actingAs(adminWithRole('pricing_staff'))->get('/admin/offers')->assertOk();
    $this->actingAs(adminWithRole('inventory_staff'))->get('/admin/offers')->assertForbidden();
});

it('renders the offer create and edit forms', function () {
    $this->actingAs(superAdmin());
    $unit = pricingUnit(120000);
    $offer = ProductOffer::factory()->create(['product_unit_id' => $unit->id, 'offer_price' => 100000]);

    $this->get('/admin/offers/create')->assertOk();
    $this->get("/admin/offers/{$offer->id}/edit")->assertOk();
});

it('renders the product detail with effective pricing', function () {
    $customer = App\Models\Customer::factory()->onboarded()->create();
    $product = App\Models\Product::factory()->withUnits(conversion: 12, subStock: 120)->create();

    $this->actingAs($customer, 'customer')->get("/products/{$product->id}")->assertOk();
});
