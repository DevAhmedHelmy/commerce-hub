<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductOffer;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductOffer>
 */
class ProductOfferFactory extends Factory
{
    protected $model = ProductOffer::class;

    public function definition(): array
    {
        return [
            'product_unit_id' => ProductUnit::factory(),
            'offer_price' => 9500,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'is_active' => true,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDay(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
