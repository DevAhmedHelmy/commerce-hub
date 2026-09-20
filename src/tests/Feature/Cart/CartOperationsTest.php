<?php

declare(strict_types=1);

use App\Domain\Cart\CartService;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductPriceTier;

it('adds, recalculates on quantity change (tier), and removes a line', function () {
    $customer = Customer::factory()->onboarded()->create();
    $product = Product::factory()->withUnits(conversion: 12, subStock: 100, subPrice: 11000)->create();
    $sub = $product->subUnit()->first();
    ProductPriceTier::factory()->create(['product_unit_id' => $sub->id, 'min_quantity' => 5, 'unit_price' => 9000]);
    $svc = app(CartService::class);

    $svc->add($customer, $sub, 1);
    $view = $svc->view($customer);

    expect($view->lines)->toHaveCount(1)
        ->and($view->lines[0]->price->applied->minorUnits)->toBe(11000);

    $svc->updateQuantity($view->lines[0]->item, 5);
    $view = $svc->view($customer);

    expect($view->lines[0]->price->applied->minorUnits)->toBe(9000)
        ->and($view->lines[0]->price->lineTotal->minorUnits)->toBe(45000)
        ->and($view->productSubtotal->minorUnits)->toBe(45000);

    $svc->remove($view->lines[0]->item);

    expect($svc->view($customer)->isEmpty())->toBeTrue();
});

it('adds via the endpoint and renders the cart page', function () {
    $customer = Customer::factory()->onboarded()->create();
    $product = Product::factory()->withUnits(conversion: 12, subStock: 50, subPrice: 11000)->create();
    $sub = $product->subUnit()->first();

    $this->actingAs($customer, 'customer')
        ->post('/cart/items', ['product_unit_id' => $sub->id, 'quantity' => 2])
        ->assertRedirect(route('cart.index'));

    $this->actingAs($customer, 'customer')->get('/cart')->assertOk();
});

it('keeps the same product under different units as separate lines', function () {
    $customer = Customer::factory()->onboarded()->create();
    $product = Product::factory()->withUnits(conversion: 12, subStock: 240)->create();
    $svc = app(CartService::class);

    $svc->add($customer, $product->primaryUnit()->first(), 1);
    $svc->add($customer, $product->subUnit()->first(), 3);

    expect($svc->view($customer)->lines)->toHaveCount(2);
});
