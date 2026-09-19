<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductPriceTier;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductPriceTier>
 */
class ProductPriceTierFactory extends Factory
{
    protected $model = ProductPriceTier::class;

    public function definition(): array
    {
        return [
            'product_unit_id' => ProductUnit::factory(),
            'min_quantity' => 5,
            'unit_price' => 9000,
            'is_active' => true,
        ];
    }
}
