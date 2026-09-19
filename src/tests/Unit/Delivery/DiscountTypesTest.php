<?php

declare(strict_types=1);

use App\Domain\Delivery\DeliveryService;
use App\Domain\Support\Enums\DeliveryDiscountType;
use App\Domain\Support\Money;
use App\Models\DeliveryArea;
use App\Models\DeliveryDiscountRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('computes a fixed delivery discount', function () {
    $area = DeliveryArea::factory()->create(['base_fee' => 5000]);
    DeliveryDiscountRule::factory()->create(['type' => DeliveryDiscountType::Fixed->value, 'value' => 1000, 'min_subtotal' => 0]);

    $quote = app(DeliveryService::class)->quote($area, Money::fromMinor(100000));

    expect($quote->discountAmount->minorUnits)->toBe(1000)
        ->and($quote->finalFee->minorUnits)->toBe(4000);
});

it('computes a percentage delivery discount', function () {
    $area = DeliveryArea::factory()->create(['base_fee' => 5000]);
    DeliveryDiscountRule::factory()->create(['type' => DeliveryDiscountType::Percentage->value, 'value' => 20, 'min_subtotal' => 0]);

    $quote = app(DeliveryService::class)->quote($area, Money::fromMinor(100000));

    expect($quote->discountAmount->minorUnits)->toBe(1000)
        ->and($quote->finalFee->minorUnits)->toBe(4000);
});

it('computes a free-delivery discount (fee waived to zero)', function () {
    $area = DeliveryArea::factory()->create(['base_fee' => 5000]);
    DeliveryDiscountRule::factory()->create(['type' => DeliveryDiscountType::FreeDelivery->value, 'value' => 0, 'min_subtotal' => 0]);

    $quote = app(DeliveryService::class)->quote($area, Money::fromMinor(100000));

    expect($quote->discountAmount->minorUnits)->toBe(5000)
        ->and($quote->finalFee->minorUnits)->toBe(0);
});

it('returns the base fee when no rule qualifies', function () {
    $area = DeliveryArea::factory()->create(['base_fee' => 5000]);
    DeliveryDiscountRule::factory()->create(['type' => DeliveryDiscountType::Fixed->value, 'value' => 1000, 'min_subtotal' => 90000]);

    $quote = app(DeliveryService::class)->quote($area, Money::fromMinor(10000));

    expect($quote->finalFee->minorUnits)->toBe(5000)
        ->and($quote->appliedRuleId)->toBeNull();
});
