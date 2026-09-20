<?php

declare(strict_types=1);

use App\Domain\Cart\CartService;
use App\Domain\Settings\SettingsService;
use App\Models\Customer;
use App\Models\Product;

beforeEach(function () {
    app(SettingsService::class)->update(['minimum_order_amount' => '50000']); // 500 ج
});

it('blocks a 499 ج effective subtotal (below minimum, excluding delivery)', function () {
    $customer = Customer::factory()->onboarded()->create();
    $product = Product::factory()->withUnits(conversion: 12, subStock: 50, subPrice: 49900)->create();
    $svc = app(CartService::class);

    $svc->add($customer, $product->subUnit()->first(), 1);
    $view = $svc->view($customer);

    expect($view->productSubtotal->minorUnits)->toBe(49900)
        ->and($view->meetsMinimum)->toBeFalse()
        ->and($view->remainingToMinimum->minorUnits)->toBe(100);
});

it('allows a 500 ج effective subtotal (meets minimum)', function () {
    $customer = Customer::factory()->onboarded()->create();
    $product = Product::factory()->withUnits(conversion: 12, subStock: 50, subPrice: 50000)->create();
    $svc = app(CartService::class);

    $svc->add($customer, $product->subUnit()->first(), 1);
    $view = $svc->view($customer);

    expect($view->productSubtotal->minorUnits)->toBe(50000)
        ->and($view->meetsMinimum)->toBeTrue()
        ->and($view->remainingToMinimum->minorUnits)->toBe(0);
});
