<?php

declare(strict_types=1);

use App\Domain\Ordering\InvalidStatusTransitionException;
use App\Domain\Ordering\OrderService;
use App\Domain\Support\Enums\OrderStatus;

it('rejects an invalid forward transition and allows a valid one', function () {
    ['customer' => $customer, 'input' => $input] = checkoutSetup();
    $svc = app(OrderService::class);
    $order = $svc->place($customer, $input)->order;

    expect(fn () => $svc->transition($order, OrderStatus::Delivered, 'admin'))
        ->toThrow(InvalidStatusTransitionException::class);

    $svc->transition($order, OrderStatus::Confirmed, 'admin');
    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
});

it('lets a customer cancel only while new', function () {
    ['customer' => $customer, 'input' => $input] = checkoutSetup();
    $svc = app(OrderService::class);
    $order = $svc->place($customer, $input)->order;

    $svc->transition($order, OrderStatus::Confirmed, 'admin');

    expect(fn () => $svc->cancel($order->fresh(), 'customer'))
        ->toThrow(InvalidStatusTransitionException::class);
});

it('lets admin cancel a non-terminal order but not a delivered one', function () {
    ['customer' => $customer, 'input' => $input] = checkoutSetup();
    $svc = app(OrderService::class);
    $order = $svc->place($customer, $input)->order;

    $svc->transition($order, OrderStatus::Confirmed, 'admin');
    $svc->transition($order, OrderStatus::Preparing, 'admin');
    $svc->transition($order, OrderStatus::OutForDelivery, 'admin');
    $svc->transition($order, OrderStatus::Delivered, 'admin');

    expect(fn () => $svc->cancel($order->fresh(), 'admin'))
        ->toThrow(InvalidStatusTransitionException::class);
});

it('restores stock exactly once on cancellation (idempotent)', function () {
    ['customer' => $customer, 'input' => $input, 'product' => $product] = checkoutSetup(qty: 4, subStock: 100, subPrice: 50000);
    $svc = app(OrderService::class);

    $order = $svc->place($customer, $input)->order;
    expect($product->fresh()->subStock())->toBe(96); // 100 - 4

    $svc->cancel($order->fresh(), 'admin');
    expect($product->fresh()->subStock())->toBe(100); // restored

    // A second cancel attempt is rejected (already cancelled) → no double restore.
    expect(fn () => $svc->cancel($order->fresh(), 'admin'))
        ->toThrow(InvalidStatusTransitionException::class);
    expect($product->fresh()->subStock())->toBe(100);
});
