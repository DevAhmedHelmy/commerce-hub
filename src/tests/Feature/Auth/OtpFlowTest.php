<?php

declare(strict_types=1);

use App\Domain\Auth\Contracts\OtpProvider;
use App\Models\Customer;

/** In-memory OTP channel that captures the plaintext code for assertions. */
function fakeOtpProvider(): object
{
    $spy = new class implements OtpProvider
    {
        public ?string $code = null;

        public function send(string $phoneE164, string $code): void
        {
            $this->code = $code;
        }
    };

    app()->instance(OtpProvider::class, $spy);

    return $spy;
}

it('requests a code and stores the active phone in session', function () {
    $spy = fakeOtpProvider();

    $response = $this->post('/otp/request', ['phone' => '+201000000001']);

    $response->assertRedirect(route('otp.verify.show'));
    expect($spy->code)->not->toBeNull()->toMatch('/^\d{6}$/');
    $this->assertEquals('+201000000001', session('otp_phone'));
});

it('verifies a correct code for a new number and requires onboarding', function () {
    $spy = fakeOtpProvider();

    $this->post('/otp/request', ['phone' => '+201000000002']);
    $response = $this->post('/otp/verify', ['code' => $spy->code]);

    $response->assertRedirect(route('onboarding.profile'));
    $this->assertAuthenticated('customer');
    $this->assertDatabaseHas('customers', ['phone' => '+201000000002']);
});

it('lets a returning onboarded customer skip profile and enter the app', function () {
    Customer::create([
        'phone' => '+201000000003',
        'business_name' => 'مطعم الاختبار',
        'contact_person_name' => 'أحمد',
        'onboarding_completed_at' => now(),
    ]);

    $spy = fakeOtpProvider();

    $this->post('/otp/request', ['phone' => '+201000000003']);
    $response = $this->post('/otp/verify', ['code' => $spy->code]);

    $response->assertRedirect('/home');
    $this->assertAuthenticated('customer');
});

it('rejects an invalid phone number before sending a code', function () {
    $spy = fakeOtpProvider();

    $response = $this->from('/login')->post('/otp/request', ['phone' => 'not-a-phone']);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('phone');
    expect($spy->code)->toBeNull();
});
