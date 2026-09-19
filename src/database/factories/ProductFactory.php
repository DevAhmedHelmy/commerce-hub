<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Support\Enums\AvailabilityStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
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

    /**
     * Attach a two-level unit pair (carton primary → piece sub) with a conversion and an
     * authoritative sub-unit stock balance. Generic units are shared/reused across products.
     */
    public function withUnits(int $conversion = 12, int $subStock = 120, int $primaryPrice = 120000, int $subPrice = 11000): static
    {
        return $this->afterCreating(function (Product $product) use ($conversion, $subStock, $primaryPrice, $subPrice): void {
            $carton = Unit::firstOrCreate(['code' => 'carton'], ['name_ar' => 'كرتونة', 'is_active' => true]);
            $piece = Unit::firstOrCreate(['code' => 'piece'], ['name_ar' => 'قطعة', 'is_active' => true]);

            ProductUnit::factory()->create([
                'product_id' => $product->id,
                'unit_id' => $carton->id,
                'level' => ProductUnit::LEVEL_PRIMARY,
                'conversion_to_sub_unit' => $conversion,
                'base_price' => $primaryPrice,
                'stock_quantity' => 0,
            ]);

            ProductUnit::factory()->create([
                'product_id' => $product->id,
                'unit_id' => $piece->id,
                'level' => ProductUnit::LEVEL_SUB,
                'conversion_to_sub_unit' => 1,
                'base_price' => $subPrice,
                'stock_quantity' => $subStock,
            ]);
        });
    }
}

