<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeliveryArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryArea>
 */
class DeliveryAreaFactory extends Factory
{
    protected $model = DeliveryArea::class;

    public function definition(): array
    {
        return [
            'name_ar' => 'منطقة '.$this->faker->unique()->numberBetween(1, 99999),
            'name_en' => null,
            'base_fee' => 3000,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
