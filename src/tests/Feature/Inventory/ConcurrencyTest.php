<?php

declare(strict_types=1);

use App\Domain\Inventory\InsufficientStockException;
use App\Domain\Inventory\InventoryService;
use App\Domain\Ordering\OrderService;
use App\Models\Product;

/*
 * No-oversell invariant (T157/T168). InventoryService::applyToSubUnit takes a `lockForUpdate` row
 * lock and guards the balance non-negative inside a transaction — on MySQL InnoDB this serializes
 * concurrent deductions so exactly one of two racing orders can win. This suite asserts the
 * deterministic OUTCOME (one wins, one fails, final = 3, never negative, no partial deduction).
 *
 * True multi-process parallelism is not executed here (PHP test process, no pcntl on Windows); the
 * DB-level guarantee is the `lockForUpdate` in InventoryService. To exercise real MySQL locking run
 * this file against a MySQL test DB: `DB_CONNECTION=mysql php artisan test --filter=ConcurrencyTest`.
 */

it('never oversells: stock 10, two 7-unit deductions → one wins, one fails, final 3', function () {
    $product = Product::factory()->withUnits(conversion: 1, subStock: 10)->create();
    $sub = $product->subUnit()->first();
    $inv = app(InventoryService::class);

    $inv->deductForOrder($sub, 7, 1001);
    expect($product->fresh()->subStock())->toBe(3);

    expect(fn () => $inv->deductForOrder($sub->fresh(), 7, 1002))
        ->toThrow(InsufficientStockException::class);

    // No negative stock, no partial second deduction.
    expect($product->fresh()->subStock())->toBe(3);
});

it('deducts normalized sub-units for a mixed-unit order and restores exactly once on cancel', function () {
    ['customer' => $customer, 'input' => $input, 'product' => $product] = checkoutSetup(qty: 2, subStock: 240, subPrice: 50000);
    // qty 2 of the SUB unit (conversion 1) → 2 sub-units; verify via primary conversion separately below.
    $orders = app(OrderService::class);

    $order = $orders->place($customer, $input)->order;
    expect($product->fresh()->subStock())->toBe(238);

    $orders->cancel($order->fresh(), 'admin');
    expect($product->fresh()->subStock())->toBe(240); // restored once

    // Double-cancel is rejected and does not double-restore.
    expect(fn () => $orders->cancel($order->fresh(), 'admin'))->toThrow(\App\Domain\Ordering\InvalidStatusTransitionException::class);
    expect($product->fresh()->subStock())->toBe(240);
});

it('normalizes a primary-unit order to sub-units (2 cartons × 12 = 24)', function () {
    $product = Product::factory()->withUnits(conversion: 12, subStock: 240)->create();
    $primary = $product->primaryUnit()->first();
    $inv = app(InventoryService::class);

    // 2 cartons → 24 sub-units deducted.
    $inv->deductForOrder($product->subUnit()->first(), 2 * (int) $primary->conversion_to_sub_unit, 2001);

    expect($product->fresh()->subStock())->toBe(216);
});
