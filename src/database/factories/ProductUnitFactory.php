<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductUnit>
 */
class ProductUnitFactory extends Factory
{
    protected $model = ProductUnit::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'code' => 'bag',
            'display_name_ar' => 'كيس',
            'display_name_en' => null,
            'base_price' => 10000, // 100 EGP in minor units
            'stock_quantity' => 100,
            'is_active' => true,
            'is_default' => true,
            'sort_order' => 0,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (): array => ['stock_quantity' => 0]);
    }

    public function stock(int $quantity): static
    {
        return $this->state(fn (): array => ['stock_quantity' => $quantity]);
    }
}
