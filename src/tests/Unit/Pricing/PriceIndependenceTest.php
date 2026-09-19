<?php

declare(strict_types=1);

use App\Domain\Pricing\PricingService;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('prices the primary and sub units independently (never derived via conversion)', function () {
    $product = Product::factory()->withUnits(conversion: 12, subStock: 120, primaryPrice: 120000, subPrice: 11000)->create();
    $carton = $product->primaryUnit()->first();
    $piece = $product->subUnit()->first();
    $svc = app(PricingService::class);

    expect($svc->priceFor($carton, 1)->applied->minorUnits)->toBe(120000)
        ->and($svc->priceFor($piece, 1)->applied->minorUnits)->toBe(11000);

    // Changing the carton price must not affect the piece price (no derivation).
    $carton->update(['base_price' => 130000]);

    expect($svc->priceFor($carton, 1)->applied->minorUnits)->toBe(130000)
        ->and($svc->priceFor($piece->fresh(), 1)->applied->minorUnits)->toBe(11000);
});
