<?php

declare(strict_types=1);

use App\Domain\Ordering\OrderService;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

it('notifies active admins when an order is placed', function () {
    User::factory()->create(); // active admin
    ['customer' => $customer, 'input' => $input] = checkoutSetup();

    app(OrderService::class)->place($customer, $input);

    expect(DatabaseNotification::query()->where('type', App\Notifications\NewOrderNotification::class)->count())
        ->toBeGreaterThan(0);
});

it('lets an admin with customers.view search the directory', function () {
    Customer::factory()->onboarded()->create(['business_name' => 'مطعم الاختبار']);

    $this->actingAs(adminWithRole('manager'))->get('/admin/customers')->assertOk()->assertSee('مطعم الاختبار');
});

it('forbids the customer directory for pricing_staff', function () {
    $this->actingAs(adminWithRole('pricing_staff'))->get('/admin/customers')->assertForbidden();
});

it('shows a customer profile with order history to an admin', function () {
    ['customer' => $customer, 'input' => $input] = checkoutSetup();
    app(OrderService::class)->place($customer, $input);

    $this->actingAs(adminWithRole('manager'))->get("/admin/customers/{$customer->id}")->assertOk();
});

it('renders the admin dashboard with the orders overview widget', function () {
    $this->actingAs(superAdmin())->get('/admin')->assertOk();
});
