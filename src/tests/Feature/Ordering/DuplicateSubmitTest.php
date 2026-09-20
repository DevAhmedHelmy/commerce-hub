<?php

declare(strict_types=1);

use App\Domain\Ordering\OrderService;
use App\Models\Order;

it('creates exactly one order for a repeated submission token', function () {
    ['customer' => $customer, 'input' => $input] = checkoutSetup();
    $svc = app(OrderService::class);

    $first = $svc->place($customer, $input);
    $second = $svc->place($customer, $input); // same submission token

    expect($first->order->id)->toBe($second->order->id)
        ->and(Order::query()->where('customer_id', $customer->id)->count())->toBe(1);
});
