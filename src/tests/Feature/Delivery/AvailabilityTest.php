<?php

declare(strict_types=1);

use App\Domain\Delivery\DeliveryService;
use App\Models\DeliveryArea;
use App\Models\DeliverySlot;
use Illuminate\Support\Carbon;

it('treats an inactive area as not orderable', function () {
    $svc = app(DeliveryService::class);

    expect($svc->isAreaOrderable(DeliveryArea::factory()->create()))->toBeTrue()
        ->and($svc->isAreaOrderable(DeliveryArea::factory()->inactive()->create()))->toBeFalse();
});

it('selects a slot only when active, matching the weekday, and not in the past', function () {
    $svc = app(DeliveryService::class);
    $date = Carbon::now()->addDays(3)->startOfDay();
    $weekday = $date->isoWeekday();

    $good = DeliverySlot::factory()->create(['day_of_week' => $weekday]);
    $inactive = DeliverySlot::factory()->inactive()->create(['day_of_week' => $weekday]);
    $wrongDay = DeliverySlot::factory()->create(['day_of_week' => ($weekday % 7) + 1]);

    expect($svc->isSlotSelectable($good, $date))->toBeTrue()
        ->and($svc->isSlotSelectable($inactive, $date))->toBeFalse()
        ->and($svc->isSlotSelectable($wrongDay, $date))->toBeFalse()
        ->and($svc->isSlotSelectable($good, Carbon::now()->subDays(2)->startOfDay()))->toBeFalse();
});

it('lists active slots for a date weekday', function () {
    $date = Carbon::now()->addDays(2)->startOfDay();
    $weekday = $date->isoWeekday();
    DeliverySlot::factory()->create(['day_of_week' => $weekday, 'sort_order' => 1]);
    DeliverySlot::factory()->create(['day_of_week' => $weekday, 'sort_order' => 2]);
    DeliverySlot::factory()->inactive()->create(['day_of_week' => $weekday]);

    expect(app(DeliveryService::class)->availableSlots($date))->toHaveCount(2);
});
