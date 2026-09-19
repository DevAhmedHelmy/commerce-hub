<?php

declare(strict_types=1);

use App\Domain\Cart\CartService;
use App\Domain\Ordering\DTO\CheckoutInput;
use App\Domain\Ordering\OrderService;
use App\Models\DeliveryArea;
use App\Models\Order;
use Illuminate\Support\Str;

it('places repeated orders from saved data without re-entering profile/address (prompt 44)', function () {
    ['customer' => $customer, 'input' => $input, 'sub' => $sub] = checkoutSetup();
    $svc = app(OrderService::class);

    // The customer submits ONLY address + slot ids — never business/contact/address text.
    $first = $svc->place($customer, $input);
    expect($first->isPlaced())->toBeTrue()
        ->and($first->order->business_name)->toBe($customer->business_name)
        ->and($first->order->address_line)->not->toBeNull();

    // Second order: refill cart, reuse the same saved address automatically.
    app(CartService::class)->add($customer, $sub, 1);
    $second = $svc->place($customer, new CheckoutInput(
        addressId: $input->addressId, deliveryDate: $input->deliveryDate,
        slotId: $input->slotId, submissionToken: (string) Str::uuid(),
    ));

    expect($second->isPlaced())->toBeTrue()
        ->and(Order::query()->where('customer_id', $customer->id)->count())->toBe(2);
});

it('keeps order snapshots when the customer profile/address/area changes later', function () {
    ['customer' => $customer, 'input' => $input] = checkoutSetup();
    $order = app(OrderService::class)->place($customer, $input)->order;
    $origBusiness = $order->business_name;
    $origArea = $order->delivery_area_name;

    $customer->update(['business_name' => 'اسم نشاط جديد']);
    $customer->defaultAddress()->first()->update(['address_line' => 'عنوان مختلف']);
    DeliveryArea::query()->whereKey($order->delivery_area_id)->update(['name_ar' => 'منطقة مختلفة']);

    $order->refresh();
    expect($order->business_name)->toBe($origBusiness)
        ->and($order->delivery_area_name)->toBe($origArea)
        ->and($order->business_name)->not->toBe('اسم نشاط جديد');
});

it('never lets a customer order using another customer\'s address', function () {
    ['customer' => $attacker] = checkoutSetup(); // attacker has own cart/address
    ['input' => $victimInput] = checkoutSetup(); // a different customer's address id

    $result = app(OrderService::class)->place($attacker, new CheckoutInput(
        addressId: $victimInput->addressId, // NOT owned by the attacker
        deliveryDate: $victimInput->deliveryDate,
        slotId: $victimInput->slotId,
        submissionToken: (string) Str::uuid(),
    ));

    expect($result->isPlaced())->toBeFalse()
        ->and(Order::query()->where('customer_id', $attacker->id)->count())->toBe(0);
});

it('snapshots the delivery fee so a later area-fee change never alters old orders', function () {
    ['customer' => $customer, 'input' => $input, 'sub' => $sub] = checkoutSetup();
    $svc = app(OrderService::class);

    $first = $svc->place($customer, $input)->order;
    expect($first->final_delivery_fee)->toBe(3000);

    DeliveryArea::query()->whereKey($first->delivery_area_id)->update(['base_fee' => 7000]);
    expect($first->fresh()->final_delivery_fee)->toBe(3000); // snapshot unchanged

    app(CartService::class)->add($customer, $sub, 1);
    $second = $svc->place($customer, new CheckoutInput(
        addressId: $input->addressId, deliveryDate: $input->deliveryDate,
        slotId: $input->slotId, submissionToken: (string) Str::uuid(),
    ))->order;

    expect($second->final_delivery_fee)->toBe(7000); // new order uses current fee
});
