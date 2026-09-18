<?php

declare(strict_types=1);

use App\Domain\Auth\Contracts\OtpProvider;
use App\Domain\Auth\OtpService;
use App\Domain\Auth\OtpVerifyReason;
use App\Domain\Auth\Providers\LogOtpProvider;
use App\Models\OtpVerification;

function bindOtpSpy(): object
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

function wrongCode(string $code): string
{
    return $code === '000000' ? '111111' : '000000';
}

it('reports expired for a code past its validity window', function () {
    $spy = bindOtpSpy();
    $otp = app(OtpService::class);

    $otp->request('+201111111111');
    OtpVerification::query()->where('phone', '+201111111111')->update(['expires_at' => now()->subMinute()]);

    $result = $otp->verify('+201111111111', $spy->code);

    expect($result->ok)->toBeFalse()
        ->and($result->reason)->toBe(OtpVerifyReason::Expired);
});

it('distinguishes an incorrect code from an expired one', function () {
    $spy = bindOtpSpy();
    $otp = app(OtpService::class);

    $otp->request('+201111111112');
    $result = $otp->verify('+201111111112', wrongCode($spy->code));

    expect($result->reason)->toBe(OtpVerifyReason::Incorrect);
});

it('consumes a code once — a second use fails', function () {
    $spy = bindOtpSpy();
    $otp = app(OtpService::class);

    $otp->request('+201111111113');
    $first = $otp->verify('+201111111113', $spy->code);
    $second = $otp->verify('+201111111113', $spy->code);

    expect($first->ok)->toBeTrue()
        ->and($second->ok)->toBeFalse();
});

it('blocks a resend during the cooldown window', function () {
    bindOtpSpy();
    $otp = app(OtpService::class);

    $otp->request('+201111111114');
    $second = $otp->request('+201111111114');

    expect($second->sent)->toBeFalse()
        ->and($second->messageKey)->toBe('auth.otp.cooldown');
});

it('locks verification after too many wrong attempts', function () {
    $spy = bindOtpSpy();
    $otp = app(OtpService::class);

    $otp->request('+201111111115');
    $wrong = wrongCode($spy->code);

    $last = null;
    for ($i = 0; $i < (int) config('otp.max_verify_attempts'); $i++) {
        $last = $otp->verify('+201111111115', $wrong);
    }

    expect($last->reason)->toBe(OtpVerifyReason::Locked);
});

it('rate-limits code requests within the window', function () {
    bindOtpSpy();
    $otp = app(OtpService::class);
    $phone = '+201111111116';

    for ($i = 0; $i < (int) config('otp.request.max'); $i++) {
        $otp->request($phone);
        $this->travel(61)->seconds();  // clear resend cooldown between requests
    }

    $result = $otp->request($phone);

    expect($result->sent)->toBeFalse()
        ->and($result->messageKey)->toBe('auth.otp.too_many_requests');
});

it('refuses to deliver a demo OTP in production', function () {
    $this->app->detectEnvironment(fn () => 'production');

    expect(fn () => (new LogOtpProvider())->send('+201000000000', '123456'))
        ->toThrow(RuntimeException::class);
});
