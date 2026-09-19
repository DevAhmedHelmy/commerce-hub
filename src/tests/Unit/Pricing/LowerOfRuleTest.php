<?php

declare(strict_types=1);

use App\Domain\Pricing\PriceResult;
use App\Domain\Pricing\PricingService;
use App\Models\ProductOffer;
use App\Models\ProductPriceTier;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

function tierAndOffer(int $base, ?int $tier, ?int $offer): App\Models\ProductUnit
{
    $unit = pricingUnit($base);
    if ($tier !== null) {
        ProductPriceTier::factory()->create(['product_unit_id' => $unit->id, 'min_quantity' => 5, 'unit_price' => $tier]);
    }
    if ($offer !== null) {
        ProductOffer::factory()->create(['product_unit_id' => $unit->id, 'offer_price' => $offer]);
    }

    return $unit;
}

it('takes the offer when it is the lowest (200/170/160 → 160)', function () {
    $unit = tierAndOffer(20000, 17000, 16000);
    $r = app(PricingService::class)->priceFor($unit, 5);

    expect($r->applied->minorUnits)->toBe(16000)
        ->and($r->appliedSource)->toBe(PriceResult::SOURCE_OFFER);
});

it('takes the tier when it is the lowest (200/150/160 → 150)', function () {
    $unit = tierAndOffer(20000, 15000, 16000);
    $r = app(PricingService::class)->priceFor($unit, 5);

    expect($r->applied->minorUnits)->toBe(15000)
        ->and($r->appliedSource)->toBe(PriceResult::SOURCE_TIER);
});

it('never stacks — equal tier and offer resolve deterministically to offer', function () {
    $unit = tierAndOffer(20000, 16000, 16000);
    $r = app(PricingService::class)->priceFor($unit, 5);

    expect($r->applied->minorUnits)->toBe(16000)
        ->and($r->appliedSource)->toBe(PriceResult::SOURCE_OFFER);
});

it('returns normal when neither applies and reports full fields', function () {
    $unit = tierAndOffer(20000, null, null);
    $r = app(PricingService::class)->priceFor($unit, 3);

    expect($r->applied->minorUnits)->toBe(20000)
        ->and($r->appliedSource)->toBe(PriceResult::SOURCE_NORMAL)
        ->and($r->base->minorUnits)->toBe(20000)
        ->and($r->tier)->toBeNull()
        ->and($r->offer)->toBeNull()
        ->and($r->quantity)->toBe(3)
        ->and($r->lineTotal->minorUnits)->toBe(60000)
        ->and($r->unitSaving->minorUnits)->toBe(0);
});

it('computes line total and unit saving from the applied price', function () {
    $unit = tierAndOffer(20000, null, 16000);
    $r = app(PricingService::class)->priceFor($unit, 4);

    expect($r->applied->minorUnits)->toBe(16000)
        ->and($r->lineTotal->minorUnits)->toBe(64000)
        ->and($r->unitSaving->minorUnits)->toBe(4000)
        ->and($r->offerId)->not->toBeNull();
});
