<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
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
            'unit_id' => Unit::factory(),
            'level' => ProductUnit::LEVEL_SUB,
            'conversion_to_sub_unit' => 1,
            'is_sellable' => true,
            'display_name_ar' => null,
            'display_name_en' => null,
            'base_price' => 10000, // 100 EGP minor units
            'stock_quantity' => 100,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function primary(int $conversion = 12): static
    {
        return $this->state(fn (): array => [
            'level' => ProductUnit::LEVEL_PRIMARY,
            'conversion_to_sub_unit' => $conversion,
            'stock_quantity' => 0, // authoritative balance lives on the sub row
        ]);
    }

    public function sub(): static
    {
        return $this->state(fn (): array => [
            'level' => ProductUnit::LEVEL_SUB,
            'conversion_to_sub_unit' => 1,
        ]);
    }

    public function stock(int $quantity): static
    {
        return $this->state(fn (): array => ['stock_quantity' => $quantity]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (): array => ['stock_quantity' => 0]);
    }
}
