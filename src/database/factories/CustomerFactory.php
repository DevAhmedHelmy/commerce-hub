<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'phone' => '+2010'.$this->faker->unique()->numerify('########'),
            'is_active' => true,
            'business_name' => 'مطعم '.$this->faker->numberBetween(1, 9999),
            'contact_person_name' => 'مسؤول '.$this->faker->numberBetween(1, 9999),
            'whatsapp_phone' => null,
            'onboarding_completed_at' => null,
        ];
    }

    public function onboarded(): static
    {
        return $this->state(fn (): array => ['onboarding_completed_at' => now()]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
