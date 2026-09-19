<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\DeliveryArea;

it('shows the account page with persistent profile and address', function () {
    $customer = Customer::factory()->onboarded()->create(['business_name' => 'مطعم الحفظ']);
    $area = DeliveryArea::factory()->create(['name_ar' => 'منطقة الحساب']);
    $customer->addresses()->create(['delivery_area_id' => $area->id, 'is_default' => true, 'address_line' => 'شارع ٥']);

    $this->actingAs($customer, 'customer')->get('/account')
        ->assertOk()
        ->assertSee('مطعم الحفظ')
        ->assertSee('منطقة الحساب');
});

it('persists a profile edit from the account page', function () {
    $customer = Customer::factory()->onboarded()->create();

    $this->actingAs($customer, 'customer')->post('/account/profile', [
        'business_name' => 'اسم محدث',
        'contact_person_name' => 'مسؤول محدث',
        'whatsapp_phone' => '01099998888',
    ])->assertRedirect(route('account.index'));

    expect($customer->fresh()->business_name)->toBe('اسم محدث');
});

it('persists a default-address edit with delivery area from the account page', function () {
    $customer = Customer::factory()->onboarded()->create();
    $area = DeliveryArea::factory()->create();
    $customer->addresses()->create(['is_default' => true, 'address_line' => 'قديم']);

    $this->actingAs($customer, 'customer')->post('/account/address', [
        'delivery_area_id' => $area->id,
        'address_line' => 'عنوان جديد',
    ])->assertRedirect(route('account.index'));

    $address = $customer->fresh()->defaultAddress()->first();
    expect($address->address_line)->toBe('عنوان جديد')
        ->and($address->delivery_area_id)->toBe($area->id);
});
