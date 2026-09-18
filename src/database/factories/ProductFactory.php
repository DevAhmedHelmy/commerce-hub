<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Support\Enums\AvailabilityStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name_ar' => 'منتج '.$this->faker->unique()->numberBetween(1, 99999),
            'name_en' => null,
            'brand' => $this->faker->randomElement(['Heinz', 'Farm Frites', 'Almarai']),
            'availability' => AvailabilityStatus::Available->value,
            'sort_order' => 0,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (): array => ['availability' => AvailabilityStatus::OutOfStock->value]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['availability' => AvailabilityStatus::Inactive->value]);
    }
}
