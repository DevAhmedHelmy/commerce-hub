<?php

declare(strict_types=1);

use App\Domain\Ordering\OrderService;
use App\Domain\Settings\SettingsService;
use App\Models\Order;

it('builds a review summary and blocks below the minimum order', function () {
    app(SettingsService::class)->update(['minimum_order_amount' => '100000']); // 1000 ج
    ['customer' => $customer, 'input' => $input] = checkoutSetup(qty: 1, subStock: 100, subPrice: 50000);

    $review = app(OrderService::class)->review($customer, $input);

    expect($review->productSubtotal->minorUnits)->toBe(50000)
        ->and($review->meetsMinimum)->toBeFalse()
        ->and($review->blockers)->toContain('checkout.errors.below_minimum')
        ->and($review->canPlace())->toBeFalse();
});

it('detects a changed price and refuses placement until re-review', function () {
    ['customer' => $customer, 'input' => $input, 'sub' => $sub] = checkoutSetup(qty: 1, subStock: 100, subPrice: 50000);

    // Price changes after the item was carted (last_seen captured at add time).
    $sub->update(['base_price' => 99000]);

    $review = app(OrderService::class)->review($customer, $input);
    expect($review->changes)->toContain('checkout.changes.price');

    $result = app(OrderService::class)->place($customer, $input);
    expect($result->isPlaced())->toBeFalse()
        ->and(Order::query()->where('customer_id', $customer->id)->count())->toBe(0);
});

it('blocks placement when the delivery slot is not selectable', function () {
    ['customer' => $customer, 'input' => $input] = checkoutSetup();
    // Deactivate the chosen slot after selection.
    App\Models\DeliverySlot::query()->update(['is_active' => false]);

    $review = app(OrderService::class)->review($customer, $input);

    expect($review->blockers)->toContain('checkout.errors.slot')
        ->and($review->canPlace())->toBeFalse();
});
