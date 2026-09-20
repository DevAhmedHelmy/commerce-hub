<?php

declare(strict_types=1);

use App\Domain\Pricing\PriceResult;
use App\Domain\Pricing\PricingService;
use App\Models\ProductPriceTier;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('applies base below the first tier and the deepest tier at/after each threshold', function () {
    $unit = pricingUnit(120000); // 1,200 ج
    ProductPriceTier::factory()->create(['product_unit_id' => $unit->id, 'min_quantity' => 5, 'unit_price' => 115000]);
    ProductPriceTier::factory()->create(['product_unit_id' => $unit->id, 'min_quantity' => 10, 'unit_price' => 110000]);

    $svc = app(PricingService::class);

    expect($svc->priceFor($unit, 1)->applied->minorUnits)->toBe(120000)
        ->and($svc->priceFor($unit, 4)->applied->minorUnits)->toBe(120000)
        ->and($svc->priceFor($unit, 4)->appliedSource)->toBe(PriceResult::SOURCE_NORMAL)
        ->and($svc->priceFor($unit, 5)->applied->minorUnits)->toBe(115000)
        ->and($svc->priceFor($unit, 5)->appliedSource)->toBe(PriceResult::SOURCE_TIER)
        ->and($svc->priceFor($unit, 9)->applied->minorUnits)->toBe(115000)
        ->and($svc->priceFor($unit, 10)->applied->minorUnits)->toBe(110000)
        ->and($svc->priceFor($unit, 20)->applied->minorUnits)->toBe(110000);
});

it('ignores an inactive tier', function () {
    $unit = pricingUnit(120000);
    ProductPriceTier::factory()->create(['product_unit_id' => $unit->id, 'min_quantity' => 5, 'unit_price' => 100000, 'is_active' => false]);

    expect(app(PricingService::class)->priceFor($unit, 10)->applied->minorUnits)->toBe(120000);
});
