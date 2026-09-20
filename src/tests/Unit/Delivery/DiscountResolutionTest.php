<?php

declare(strict_types=1);

use App\Domain\Delivery\DeliveryService;
use App\Domain\Support\Enums\DeliveryDiscountType;
use App\Domain\Support\Money;
use App\Models\DeliveryArea;
use App\Models\DeliveryDiscountRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('chooses the single largest saving, never stacking', function () {
    $area = DeliveryArea::factory()->create(['base_fee' => 5000]);
    DeliveryDiscountRule::factory()->create(['type' => DeliveryDiscountType::Fixed->value, 'value' => 1000, 'min_subtotal' => 0]);
    DeliveryDiscountRule::factory()->create(['type' => DeliveryDiscountType::Percentage->value, 'value' => 50, 'min_subtotal' => 0]);
    DeliveryDiscountRule::factory()->create(['type' => DeliveryDiscountType::FreeDelivery->value, 'value' => 0, 'min_subtotal' => 0]);

    $quote = app(DeliveryService::class)->quote($area, Money::fromMinor(100000));

    // Free delivery (5000) beats fixed 1000 and 50% (2500); not summed.
    expect($quote->discountAmount->minorUnits)->toBe(5000)
        ->and($quote->finalFee->minorUnits)->toBe(0);
});

it('breaks a saving tie by the higher qualifying min_subtotal', function () {
    $area = DeliveryArea::factory()->create(['base_fee' => 5000]);
    DeliveryDiscountRule::factory()->create(['type' => DeliveryDiscountType::Fixed->value, 'value' => 2000, 'min_subtotal' => 50000]);
    $higher = DeliveryDiscountRule::factory()->create(['type' => DeliveryDiscountType::Fixed->value, 'value' => 2000, 'min_subtotal' => 80000]);

    $quote = app(DeliveryService::class)->quote($area, Money::fromMinor(100000));

    expect($quote->discountAmount->minorUnits)->toBe(2000)
        ->and($quote->appliedRuleId)->toBe($higher->id);
});

it('floors the final fee at zero (a fixed discount cannot exceed the base fee)', function () {
    $area = DeliveryArea::factory()->create(['base_fee' => 5000]);
    DeliveryDiscountRule::factory()->create(['type' => DeliveryDiscountType::Fixed->value, 'value' => 999999, 'min_subtotal' => 0]);

    $quote = app(DeliveryService::class)->quote($area, Money::fromMinor(100000));

    expect($quote->discountAmount->minorUnits)->toBe(5000)
        ->and($quote->finalFee->minorUnits)->toBe(0);
});
