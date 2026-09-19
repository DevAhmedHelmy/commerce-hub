<?php

declare(strict_types=1);

use App\Domain\Cart\CartService;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductOffer;

it('flags an insufficient-stock line and excludes it from the qualifying subtotal', function () {
    $customer = Customer::factory()->onboarded()->create();
    $product = Product::factory()->withUnits(conversion: 12, subStock: 3, subPrice: 11000)->create();
    $sub = $product->subUnit()->first();
    $svc = app(CartService::class);

    $svc->add($customer, $sub, 5); // needs 5 sub-units, only 3 in stock
    $view = $svc->view($customer);

    expect($view->lines[0]->available)->toBeFalse()
        ->and($view->productSubtotal->minorUnits)->toBe(0);
});

it('recomputes the estimate when an offer expires (server-authoritative)', function () {
    $customer = Customer::factory()->onboarded()->create();
    $product = Product::factory()->withUnits(conversion: 12, subStock: 50, subPrice: 11000)->create();
    $sub = $product->subUnit()->first();
    $offer = ProductOffer::factory()->create(['product_unit_id' => $sub->id, 'offer_price' => 8000]);
    $svc = app(CartService::class);

    $svc->add($customer, $sub, 1);
    expect($svc->view($customer)->lines[0]->price->applied->minorUnits)->toBe(8000);

    $offer->update(['ends_at' => now()->subDay()]);
    expect($svc->view($customer)->lines[0]->price->applied->minorUnits)->toBe(11000);
});
