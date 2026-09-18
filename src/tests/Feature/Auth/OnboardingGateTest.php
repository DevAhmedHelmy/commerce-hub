<?php

declare(strict_types=1);

use App\Models\Customer;

it('blocks an incompletely-onboarded customer from ordering', function () {
    $customer = Customer::create(['phone' => '+201222222221']);

    $this->actingAs($customer, 'customer')
        ->get('/home')
        ->assertRedirect(route('onboarding.profile'));
});

it('lets a fully-onboarded customer reach the app', function () {
    $customer = Customer::create([
        'phone' => '+201222222222',
        'business_name' => 'مطعم',
        'contact_person_name' => 'سالم',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($customer, 'customer')
        ->get('/home')
        ->assertOk();
});

it('completes onboarding through profile then address and then allows ordering', function () {
    $customer = Customer::create(['phone' => '+201222222223']);

    $this->actingAs($customer, 'customer')
        ->post('/onboarding/profile', [
            'business_name' => 'مطعم البركة',
            'contact_person_name' => 'منى',
            'whatsapp_phone' => '+201000000000',
        ])
        ->assertRedirect(route('onboarding.address'));

    // Profile alone does not complete onboarding — the default address is required.
    expect($customer->fresh()->hasCompletedOnboarding())->toBeFalse();

    $this->actingAs($customer, 'customer')
        ->post('/onboarding/address', [
            'address_line' => 'شارع التحرير 10',
            'building' => '5',
        ])
        ->assertRedirect('/home');

    expect($customer->fresh()->hasCompletedOnboarding())->toBeTrue();
    $this->assertDatabaseHas('customer_addresses', [
        'customer_id' => $customer->id,
        'address_line' => 'شارع التحرير 10',
        'is_default' => true,
    ]);

    $this->actingAs($customer, 'customer')->get('/home')->assertOk();
});

it('redirects a guest to sign-in when hitting an ordering route', function () {
    $this->get('/home')->assertRedirect(route('login'));
});
