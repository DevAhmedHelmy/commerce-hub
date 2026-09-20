<?php

declare(strict_types=1);

use App\Models\Customer;

it('always renders the landing page at / for guests and authenticated customers', function () {
    $this->get('/')->assertOk();

    $this->actingAs(Customer::factory()->onboarded()->create(), 'customer')->get('/')->assertOk();
});

it('sends a guest CTA to sign-in', function () {
    $this->get('/start')->assertRedirect(route('login'));
});

it('sends an onboarded customer CTA to the app home', function () {
    $this->actingAs(Customer::factory()->onboarded()->create(), 'customer')
        ->get('/start')->assertRedirect(route('home'));
});

it('sends a not-yet-onboarded customer CTA to onboarding', function () {
    $this->actingAs(Customer::factory()->create(), 'customer') // onboarding incomplete
        ->get('/start')->assertRedirect(route('onboarding.profile'));
});
