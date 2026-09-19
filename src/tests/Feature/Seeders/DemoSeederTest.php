<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\DeliveryArea;
use App\Models\Product;
use App\Models\Unit;
use Database\Seeders\DemoSeeder;

it('seeds demo data and is safe to re-run (idempotent)', function () {
    (new DemoSeeder())->run();

    $products = Product::query()->count();
    $units = Unit::query()->count();
    $areas = DeliveryArea::query()->count();
    $categories = Category::query()->count();

    expect($products)->toBeGreaterThan(0)
        ->and($units)->toBeGreaterThan(0)
        ->and($areas)->toBeGreaterThan(0)
        ->and($categories)->toBeGreaterThan(0);

    // Re-run: counts must not grow.
    (new DemoSeeder())->run();

    expect(Product::query()->count())->toBe($products)
        ->and(Unit::query()->count())->toBe($units)
        ->and(DeliveryArea::query()->count())->toBe($areas)
        ->and(Category::query()->count())->toBe($categories);
});
