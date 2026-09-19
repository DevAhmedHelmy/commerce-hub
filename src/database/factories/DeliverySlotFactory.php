<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeliverySlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliverySlot>
 */
class DeliverySlotFactory extends Factory
{
    protected $model = DeliverySlot::class;

    public function definition(): array
    {
        return [
            'label_ar' => '٤–٨ مساءً',
            'label_en' => null,
            'day_of_week' => 1,
            'start_time' => '16:00',
            'end_time' => '20:00',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
