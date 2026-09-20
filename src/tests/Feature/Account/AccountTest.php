<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\DeliveryArea;

it('shows the read-only account page with persistent profile and address', function () {
    $customer = Customer::factory()->onboarded()->create(['business_name' => 'مطعم الحفظ']);
    $area = DeliveryArea::factory()->create(['name_ar' => 'منطقة الحساب']);
    $customer->addresses()->create(['delivery_area_id' => $area->id, 'is_default' => true, 'address_line' => 'شارع ٥']);

    $this->actingAs($customer, 'customer')->get('/account')
        ->assertOk()
        ->assertSee('مطعم الحفظ')
        ->assertSee('منطقة الحساب');
});

it('no longer exposes customer self-edit routes (admin-only editing)', function () {
    expect(\Illuminate\Support\Facades\Route::has('account.profile'))->toBeFalse()
        ->and(\Illuminate\Support\Facades\Route::has('account.address'))->toBeFalse();

    // The old self-edit URLs are gone.
    $this->actingAs(Customer::factory()->onboarded()->create(), 'customer')
        ->get('/account/profile')->assertNotFound();
});
