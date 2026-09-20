<?php

declare(strict_types=1);

use App\Models\AdminAuditLog;
use App\Models\Customer;

it('blocks a deactivated customer from the app and lets an active one through', function () {
    $active = Customer::factory()->onboarded()->create();
    $this->actingAs($active, 'customer')->get('/home')->assertOk();

    $inactive = Customer::factory()->onboarded()->inactive()->create();
    $this->actingAs($inactive, 'customer')->get('/home')->assertRedirect(route('login'));
});

it('lets an authorized admin deactivate then reactivate a customer (audited), preserving data', function () {
    $this->actingAs(adminWithRole('manager'));
    $customer = Customer::factory()->onboarded()->create();

    $customer->update(['is_active' => false]);
    expect($customer->fresh()->is_active)->toBeFalse()
        ->and(AdminAuditLog::query()->where('auditable_type', Customer::class)->where('action', 'deactivated')->exists())->toBeTrue();

    $customer->update(['is_active' => true]);
    expect($customer->fresh()->is_active)->toBeTrue()
        ->and(AdminAuditLog::query()->where('auditable_type', Customer::class)->where('action', 'activated')->exists())->toBeTrue();
});

it('audits an admin customer profile edit', function () {
    $this->actingAs(adminWithRole('manager'));
    $customer = Customer::factory()->onboarded()->create(['business_name' => 'قديم']);

    $customer->update(['business_name' => 'اسم محدث من الإدارة']);

    $log = AdminAuditLog::query()->where('auditable_type', Customer::class)->where('action', 'updated')->latest('id')->first();
    expect($log)->not->toBeNull()->and($log->new_values['business_name'])->toBe('اسم محدث من الإدارة');
});

it('gates the customers admin screen by permission', function () {
    $this->actingAs(adminWithRole('manager'))->get('/admin/customers')->assertOk();
    $this->actingAs(adminWithRole('pricing_staff'))->get('/admin/customers')->assertForbidden();
});

it('lets an admin reach the customer edit page but denies staff without update permission', function () {
    $customer = Customer::factory()->onboarded()->create();

    $this->actingAs(adminWithRole('manager'))->get("/admin/customers/{$customer->id}/edit")->assertOk();
    // orders_staff has customers.view but not customers.update → edit forbidden.
    $this->actingAs(adminWithRole('orders_staff'))->get("/admin/customers/{$customer->id}/edit")->assertForbidden();
});
