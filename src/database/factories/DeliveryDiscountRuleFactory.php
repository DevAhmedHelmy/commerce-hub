<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Support\Enums\DeliveryDiscountType;
use App\Models\DeliveryDiscountRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryDiscountRule>
 */
class DeliveryDiscountRuleFactory extends Factory
{
    protected $model = DeliveryDiscountRule::class;

    public function definition(): array
    {
        return [
            'name' => 'خصم '.$this->faker->unique()->numberBetween(1, 99999),
            'type' => DeliveryDiscountType::Fixed->value,
            'value' => 1000,
            'min_subtotal' => 0,
            'is_active' => true,
        ];
    }
}
