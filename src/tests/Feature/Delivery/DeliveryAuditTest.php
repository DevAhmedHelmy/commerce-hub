<?php

declare(strict_types=1);

use App\Models\AdminAuditLog;
use App\Models\DeliveryArea;
use App\Models\DeliveryDiscountRule;
use App\Models\DeliverySlot;

beforeEach(fn () => $this->actingAs(superAdmin()));

it('audits a delivery-area fee change as price_changed', function () {
    $area = DeliveryArea::factory()->create(['base_fee' => 3000]);
    $area->update(['base_fee' => 4500]);

    $log = AdminAuditLog::query()->where('auditable_type', DeliveryArea::class)
        ->where('auditable_id', $area->id)->where('action', 'price_changed')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->old_values['base_fee'])->toBe(3000)
        ->and($log->new_values['base_fee'])->toBe(4500);
});

it('audits deactivating a delivery slot', function () {
    $slot = DeliverySlot::factory()->create();
    $slot->update(['is_active' => false]);

    expect(AdminAuditLog::query()->where('auditable_type', DeliverySlot::class)
        ->where('auditable_id', $slot->id)->where('action', 'deactivated')->exists())->toBeTrue();
});

it('audits a delivery discount-rule threshold change', function () {
    $rule = DeliveryDiscountRule::factory()->create(['min_subtotal' => 10000]);
    $rule->update(['min_subtotal' => 20000]);

    $log = AdminAuditLog::query()->where('auditable_type', DeliveryDiscountRule::class)
        ->where('auditable_id', $rule->id)->where('action', 'updated')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->new_values['min_subtotal'])->toBe(20000);
});
