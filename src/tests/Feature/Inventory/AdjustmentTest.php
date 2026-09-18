<?php

declare(strict_types=1);

use App\Domain\Inventory\AdjustInventoryAction;
use App\Domain\Inventory\InsufficientStockException;
use App\Domain\Inventory\InventoryAdjustmentType;
use App\Domain\Inventory\InventoryService;
use App\Models\ProductUnit;

beforeEach(function () {
    $this->service = app(InventoryService::class);
    $this->action = app(AdjustInventoryAction::class);
});

it('adds stock and records a manual_add adjustment', function () {
    $unit = ProductUnit::factory()->stock(10)->create();

    $this->action->add($unit, 20, 'توريد جديد', null);

    expect($unit->fresh()->stock_quantity)->toBe(30);
    $this->assertDatabaseHas('inventory_adjustments', [
        'product_unit_id' => $unit->id,
        'type' => InventoryAdjustmentType::ManualAdd->value,
        'quantity_delta' => 20,
        'quantity_before' => 10,
        'quantity_after' => 30,
    ]);
});

it('removes stock and records a manual_remove adjustment', function () {
    $unit = ProductUnit::factory()->stock(30)->create();

    $this->action->remove($unit, 12, null, null);

    expect($unit->fresh()->stock_quantity)->toBe(18);
    $this->assertDatabaseHas('inventory_adjustments', [
        'product_unit_id' => $unit->id,
        'type' => InventoryAdjustmentType::ManualRemove->value,
        'quantity_delta' => -12,
        'quantity_after' => 18,
    ]);
});

it('never allows stock to go below zero', function () {
    $unit = ProductUnit::factory()->stock(5)->create();

    expect(fn () => $this->action->remove($unit, 10, null, null))
        ->toThrow(InsufficientStockException::class);

    // Balance unchanged and no adjustment written (transaction rolled back).
    expect($unit->fresh()->stock_quantity)->toBe(5);
    $this->assertDatabaseCount('inventory_adjustments', 0);
});

it('corrects stock to an exact target', function () {
    $unit = ProductUnit::factory()->stock(40)->create();

    $this->action->correctTo($unit, 25, 'جرد', null);

    expect($unit->fresh()->stock_quantity)->toBe(25);
    $this->assertDatabaseHas('inventory_adjustments', [
        'product_unit_id' => $unit->id,
        'type' => InventoryAdjustmentType::Correction->value,
        'quantity_delta' => -15,
        'quantity_after' => 25,
    ]);
});

it('exposes stock helpers and orderability', function () {
    $inStock = ProductUnit::factory()->stock(3)->create();
    $empty = ProductUnit::factory()->outOfStock()->create();
    $inactive = ProductUnit::factory()->stock(3)->create(['is_active' => false]);

    expect($this->service->hasStockFor($inStock, 3))->toBeTrue()
        ->and($this->service->hasStockFor($inStock, 4))->toBeFalse()
        ->and($inStock->isOrderable())->toBeTrue()
        ->and($empty->isOrderable())->toBeFalse()
        ->and($inactive->isOrderable())->toBeFalse();
});
