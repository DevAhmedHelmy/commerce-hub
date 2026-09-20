<?php

declare(strict_types=1);

use App\Domain\Pricing\PriceResult;
use App\Domain\Pricing\PricingService;
use App\Models\ProductOffer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('applies an active, in-range offer', function () {
    $unit = pricingUnit(120000);
    ProductOffer::factory()->create(['product_unit_id' => $unit->id, 'offer_price' => 100000]);

    $result = app(PricingService::class)->priceFor($unit, 1);

    expect($result->applied->minorUnits)->toBe(100000)
        ->and($result->appliedSource)->toBe(PriceResult::SOURCE_OFFER);
});

it('ignores an inactive offer', function () {
    $unit = pricingUnit(120000);
    ProductOffer::factory()->inactive()->create(['product_unit_id' => $unit->id, 'offer_price' => 100000]);

    expect(app(PricingService::class)->priceFor($unit, 1)->applied->minorUnits)->toBe(120000);
});

it('ignores an expired offer', function () {
    $unit = pricingUnit(120000);
    ProductOffer::factory()->expired()->create(['product_unit_id' => $unit->id, 'offer_price' => 100000]);

    expect(app(PricingService::class)->priceFor($unit, 1)->applied->minorUnits)->toBe(120000);
});

it('ignores a future offer', function () {
    $unit = pricingUnit(120000);
    ProductOffer::factory()->create([
        'product_unit_id' => $unit->id, 'offer_price' => 100000,
        'starts_at' => now()->addDay(), 'ends_at' => now()->addDays(3),
    ]);

    expect(app(PricingService::class)->priceFor($unit, 1)->applied->minorUnits)->toBe(120000);
});

it('applies an offer only to its own unit', function () {
    $unitA = pricingUnit(120000);
    $unitB = pricingUnit(120000);
    ProductOffer::factory()->create(['product_unit_id' => $unitA->id, 'offer_price' => 100000]);

    expect(app(PricingService::class)->priceFor($unitB, 1)->applied->minorUnits)->toBe(120000);
});
