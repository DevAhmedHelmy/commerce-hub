<?php

declare(strict_types=1);

use App\Domain\Inventory\AdjustInventoryAction;
use App\Domain\Inventory\InsufficientStockException;
use App\Domain\Inventory\InventoryAdjustmentType;
use App\Domain\Inventory\InventoryService;
use App\Models\Product;

beforeEach(function () {
    $this->action = app(AdjustInventoryAction::class);
    $this->service = app(InventoryService::class);
});

it('adds stock entered in the primary unit, converted to sub-units', function () {
    $product = Product::factory()->withUnits(conversion: 12, subStock: 100)->create();

    $this->action->add($product, $product->primaryUnit, 10, 'توريد جديد', null); // 10 cartons = 120 pieces

    expect($this->service->currentStock($product))->toBe(220);
    $this->assertDatabaseHas('inventory_adjustments', [
        'type' => InventoryAdjustmentType::ManualAdd->value,
        'input_quantity' => 10,
        'quantity_delta' => 120,
        'quantity_before' => 100,
        'quantity_after' => 220,
    ]);
});

it('adds stock entered directly in the sub unit', function () {
    $product = Product::factory()->withUnits(conversion: 12, subStock: 100)->create();

    $this->action->add($product, $product->subUnit, 7, null, null);

    expect($this->service->currentStock($product))->toBe(107);
    $this->assertDatabaseHas('inventory_adjustments', ['quantity_delta' => 7, 'quantity_after' => 107]);
});

it('removes stock in the sub unit', function () {
    $product = Product::factory()->withUnits(conversion: 12, subStock: 50)->create();

    $this->action->remove($product, $product->subUnit, 12, null, null);

    expect($this->service->currentStock($product))->toBe(38);
});

it('never allows stock below zero', function () {
    $product = Product::factory()->withUnits(conversion: 12, subStock: 5)->create();

    expect(fn () => $this->action->remove($product, $product->subUnit, 10, null, null))
        ->toThrow(InsufficientStockException::class);

    expect($this->service->currentStock($product))->toBe(5);
    $this->assertDatabaseCount('inventory_adjustments', 0);
});

it('corrects the balance to an exact sub-unit target', function () {
    $product = Product::factory()->withUnits(conversion: 12, subStock: 100)->create();

    $this->action->correctTo($product, 25, 'جرد', null);

    expect($this->service->currentStock($product))->toBe(25);
    $this->assertDatabaseHas('inventory_adjustments', [
        'type' => InventoryAdjustmentType::Correction->value,
        'quantity_after' => 25,
    ]);
});
