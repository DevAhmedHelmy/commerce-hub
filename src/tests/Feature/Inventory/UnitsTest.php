<?php

declare(strict_types=1);

use App\Domain\Inventory\ConversionChangeBlockedException;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Database\QueryException;

it('creates a reusable unit and can deactivate it', function () {
    $unit = Unit::create(['code' => 'carton', 'name_ar' => 'كرتونة', 'is_active' => true]);

    expect($unit->is_active)->toBeTrue();

    $unit->update(['is_active' => false]);

    expect($unit->fresh()->is_active)->toBeFalse();
    $this->assertDatabaseHas('units', ['code' => 'carton', 'is_active' => false]);
});

it('enforces a unique code per unit', function () {
    Unit::create(['code' => 'piece', 'name_ar' => 'قطعة']);

    expect(fn () => Unit::create(['code' => 'piece', 'name_ar' => 'قطعة أخرى']))
        ->toThrow(QueryException::class);
});

it('allows exactly one unit per level per product', function () {
    $product = Product::factory()->create();
    $unitA = Unit::factory()->create();
    $unitB = Unit::factory()->create();

    ProductUnit::factory()->create([
        'product_id' => $product->id, 'unit_id' => $unitA->id, 'level' => ProductUnit::LEVEL_PRIMARY,
    ]);

    expect(fn () => ProductUnit::factory()->create([
        'product_id' => $product->id, 'unit_id' => $unitB->id, 'level' => ProductUnit::LEVEL_PRIMARY,
    ]))->toThrow(QueryException::class);
});

it('forbids the same generic unit for both levels of a product (primary != sub)', function () {
    $product = Product::factory()->create();
    $unit = Unit::factory()->create();

    ProductUnit::factory()->create([
        'product_id' => $product->id, 'unit_id' => $unit->id, 'level' => ProductUnit::LEVEL_PRIMARY,
    ]);

    expect(fn () => ProductUnit::factory()->create([
        'product_id' => $product->id, 'unit_id' => $unit->id, 'level' => ProductUnit::LEVEL_SUB,
    ]))->toThrow(QueryException::class);
});

it('requires a conversion factor greater than zero', function () {
    $product = Product::factory()->create();
    $unit = Unit::factory()->create();

    expect(fn () => ProductUnit::factory()->primary()->create([
        'product_id' => $product->id, 'unit_id' => $unit->id, 'conversion_to_sub_unit' => 0,
    ]))->toThrow(InvalidArgumentException::class);
});

it('blocks changing the conversion factor while the product has stock', function () {
    $product = Product::factory()->withUnits(conversion: 12, subStock: 60)->create();

    expect(fn () => $product->primaryUnit()->first()->update(['conversion_to_sub_unit' => 24]))
        ->toThrow(ConversionChangeBlockedException::class);

    expect($product->primaryUnit()->first()->conversion_to_sub_unit)->toBe(12);
});

it('allows changing the conversion factor once stock is reconciled to zero', function () {
    $product = Product::factory()->withUnits(conversion: 12, subStock: 0)->create();

    $product->primaryUnit()->first()->update(['conversion_to_sub_unit' => 24]);

    expect($product->primaryUnit()->first()->conversion_to_sub_unit)->toBe(24);
});

it('builds a valid two-level product (primary carton + sub piece)', function () {
    $product = Product::factory()->withUnits(conversion: 12, subStock: 120)->create()->load('units');

    expect($product->primaryUnit->conversion_to_sub_unit)->toBe(12)
        ->and($product->subUnit->conversion_to_sub_unit)->toBe(1)
        ->and($product->subStock())->toBe(120)
        ->and($product->primaryUnit->unit_id)->not->toBe($product->subUnit->unit_id);
});
