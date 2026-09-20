<?php

declare(strict_types=1);

use App\Domain\Ordering\OrderService;
use App\Domain\Support\Enums\OrderStatus;
use App\Models\Order;

it('creates an order with items and deducts normalized sub-unit stock', function () {
    ['customer' => $customer, 'input' => $input, 'product' => $product] = checkoutSetup(qty: 5, subStock: 100, subPrice: 50000);

    $result = app(OrderService::class)->place($customer, $input);

    expect($result->isPlaced())->toBeTrue();
    $order = $result->order;
    expect($order->status)->toBe(OrderStatus::New)
        ->and($order->order_number)->toStartWith('ORD-')
        ->and($order->items)->toHaveCount(1)
        ->and($order->final_total)->toBe(50000 * 5 + 3000); // subtotal + base delivery fee

    // Sub-unit stock deducted by quantity × conversion (5 × 1 for a sub unit = 5).
    expect($product->fresh()->subStock())->toBe(95);
});

it('rolls back completely when stock is insufficient (no header without items)', function () {
    ['customer' => $customer, 'input' => $input] = checkoutSetup(qty: 5, subStock: 100, subPrice: 50000);
    // Drain stock to below the required amount after carting but before placement.
    $setup2 = checkoutSetup();

    ['customer' => $c2, 'input' => $in2, 'product' => $p2] = checkoutSetup(qty: 10, subStock: 3, subPrice: 50000);

    $result = app(OrderService::class)->place($c2, $in2);

    // 10 needed, 3 in stock → line flagged unavailable → review required, no order.
    expect($result->isPlaced())->toBeFalse()
        ->and(Order::query()->where('customer_id', $c2->id)->count())->toBe(0);
});

it('clears the cart after a successful placement', function () {
    ['customer' => $customer, 'input' => $input] = checkoutSetup();

    app(OrderService::class)->place($customer, $input);

    expect(app(App\Domain\Cart\CartService::class)->view($customer)->isEmpty())->toBeTrue();
});
