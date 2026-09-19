<?php

declare(strict_types=1);

use App\Domain\Pricing\PriceResult;
use App\Domain\Pricing\PricingService;
use App\Models\ProductPriceTier;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('returns the base price for a flat-priced unit with no tier messaging', function () {
    $unit = pricingUnit(85000);

    $r = app(PricingService::class)->baselineFromPrice($unit);

    expect($r->applied->minorUnits)->toBe(85000)
        ->and($r->appliedSource)->toBe(PriceResult::SOURCE_NORMAL)
        ->and($r->tier)->toBeNull()
        ->and($r->hasDiscount())->toBeFalse();
});

it('ignores quantity tiers for the baseline (evaluated at quantity 1)', function () {
    $unit = pricingUnit(120000);
    ProductPriceTier::factory()->create(['product_unit_id' => $unit->id, 'min_quantity' => 5, 'unit_price' => 110000]);

    $r = app(PricingService::class)->baselineFromPrice($unit);

    expect($r->applied->minorUnits)->toBe(120000)
        ->and($r->appliedSource)->toBe(PriceResult::SOURCE_NORMAL);
});
