<?php

declare(strict_types=1);

use App\Domain\Support\Enums\OrderStatus;

it('allows only the forward lifecycle path', function () {
    expect(OrderStatus::New->forwardTransitions())->toBe([OrderStatus::Confirmed])
        ->and(OrderStatus::Confirmed->forwardTransitions())->toBe([OrderStatus::Preparing])
        ->and(OrderStatus::Preparing->forwardTransitions())->toBe([OrderStatus::OutForDelivery])
        ->and(OrderStatus::OutForDelivery->forwardTransitions())->toBe([OrderStatus::Delivered])
        ->and(OrderStatus::Delivered->forwardTransitions())->toBe([])
        ->and(OrderStatus::Cancelled->forwardTransitions())->toBe([]);
});

it('marks delivered and cancelled as terminal', function () {
    expect(OrderStatus::Delivered->isTerminal())->toBeTrue()
        ->and(OrderStatus::Cancelled->isTerminal())->toBeTrue()
        ->and(OrderStatus::New->isTerminal())->toBeFalse()
        ->and(OrderStatus::OutForDelivery->isTerminal())->toBeFalse();
});

it('lets the customer cancel only while new; admin any non-terminal', function () {
    expect(OrderStatus::New->isCustomerCancellable())->toBeTrue()
        ->and(OrderStatus::Confirmed->isCustomerCancellable())->toBeFalse()
        ->and(OrderStatus::New->isAdminCancellable())->toBeTrue()
        ->and(OrderStatus::OutForDelivery->isAdminCancellable())->toBeTrue()
        ->and(OrderStatus::Delivered->isAdminCancellable())->toBeFalse()
        ->and(OrderStatus::Cancelled->isAdminCancellable())->toBeFalse();
});
