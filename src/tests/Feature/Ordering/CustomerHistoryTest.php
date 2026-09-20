<?php

declare(strict_types=1);

use App\Domain\Ordering\OrderService;
use App\Domain\Support\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;

it('shows a customer only their own orders', function () {
    ['customer' => $customer, 'input' => $input] = checkoutSetup();
    $order = app(OrderService::class)->place($customer, $input)->order;
    $other = Customer::factory()->onboarded()->create();

    $this->actingAs($customer, 'customer')->get('/orders')->assertOk()->assertSee($order->order_number);
    $this->actingAs($other, 'customer')->get("/orders/{$order->id}")->assertNotFound();
});

it('runs the full HTTP checkout flow to a placed order', function () {
    ['customer' => $customer, 'input' => $input] = checkoutSetup();

    $this->actingAs($customer, 'customer')
        ->post('/checkout/delivery', [
            'address_id' => $input->addressId,
            'delivery_date' => $input->deliveryDate,
            'slot_id' => $input->slotId,
        ])->assertRedirect(route('checkout.review'));

    $this->actingAs($customer, 'customer')->get('/checkout/review')->assertOk();
    $this->actingAs($customer, 'customer')->post('/checkout/confirm')->assertRedirect();

    expect(Order::query()->where('customer_id', $customer->id)->count())->toBe(1);
});

it('lets a customer self-cancel a new order', function () {
    ['customer' => $customer, 'input' => $input] = checkoutSetup();
    $order = app(OrderService::class)->place($customer, $input)->order;

    $this->actingAs($customer, 'customer')->post("/orders/{$order->id}/cancel")->assertRedirect();

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

it('gates the admin orders screen by permission', function () {
    $this->actingAs(adminWithRole('orders_staff'))->get('/admin/orders')->assertOk();
    $this->actingAs(adminWithRole('pricing_staff'))->get('/admin/orders')->assertForbidden();
});

it('renders the admin order detail page with lifecycle actions', function () {
    ['customer' => $customer, 'input' => $input] = checkoutSetup();
    $order = app(OrderService::class)->place($customer, $input)->order;

    $this->actingAs(adminWithRole('orders_staff'))->get("/admin/orders/{$order->id}")->assertOk();
});
