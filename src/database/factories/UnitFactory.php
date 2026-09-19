<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->randomElement(['carton', 'piece', 'bag', 'bottle', 'pack'])
                .$this->faker->unique()->numberBetween(1, 99999),
            'name_ar' => 'وحدة',
            'name_en' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function code(string $code, string $nameAr): static
    {
        return $this->state(fn (): array => ['code' => $code, 'name_ar' => $nameAr]);
    }
}
