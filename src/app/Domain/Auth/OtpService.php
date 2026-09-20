<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Auth\Contracts\OtpProvider;
use App\Domain\Customers\CustomerService;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Phone OTP lifecycle (R7, FR-001..FR-010, Principle VI): issue a hashed one-time code
 * with expiry, resend cooldown, request rate-limit and verify attempt limit; distinguish
 * incorrect vs expired; consume once. The plaintext code exists only long enough to hand
 * to the {@see OtpProvider} — it is never stored or returned.
 */
final class OtpService
{
    public function __construct(
        private readonly OtpProvider $provider,
        private readonly CustomerService $customers,
    ) {
    }

    public function request(string $phone, ?string $ip = null): OtpRequestResult
    {
        $phone = self::normalize($phone);
        $now = now();

        $challenge = $this->activeChallenge($phone);

        // Resend cooldown (FR-005).
        $cooldown = (int) config('otp.resend_cooldown_seconds');
        if ($challenge?->last_sent_at !== null) {
            $elapsed = (int) $challenge->last_sent_at->diffInSeconds($now, true);
            if ($elapsed < $cooldown) {
                return OtpRequestResult::throttled($cooldown - $elapsed, 'auth.otp.cooldown');
            }
        }

        // Per-phone request rate limit (FR-005/FR-010).
        $key = 'otp-request:'.$phone;
        $max = (int) config('otp.request.max');
        if (RateLimiter::tooManyAttempts($key, $max)) {
            return OtpRequestResult::throttled(RateLimiter::availableIn($key), 'auth.otp.too_many_requests');
        }
        RateLimiter::hit($key, (int) config('otp.request.window_seconds'));

        $code = $this->generateCode();

        $attributes = [
            'code_hash' => Hash::make($code),
            'expires_at' => $now->copy()->addSeconds((int) config('otp.ttl_seconds')),
            'consumed_at' => null,
            'attempts' => 0,
            'last_sent_at' => $now,
            'ip' => $ip,
        ];

        if ($challenge !== null) {
            $challenge->fill($attributes);
            $challenge->resend_count += 1;
            $challenge->save();
        } else {
            OtpVerification::create($attributes + ['phone' => $phone, 'resend_count' => 0]);
        }

        $this->provider->send($phone, $code);

        return OtpRequestResult::sent();
    }

    public function verify(string $phone, string $code): OtpVerifyResult
    {
        $phone = self::normalize($phone);
        $now = now();

        $challenge = $this->activeChallenge($phone);
        $maxAttempts = (int) config('otp.max_verify_attempts');

        if ($challenge === null) {
            return OtpVerifyResult::failure(OtpVerifyReason::Expired);
        }

        if ($challenge->attempts >= $maxAttempts) {
            return OtpVerifyResult::failure(OtpVerifyReason::Locked);
        }

        if ($challenge->expires_at->isPast()) {
            return OtpVerifyResult::failure(OtpVerifyReason::Expired);
        }

        if (! Hash::check($code, $challenge->code_hash)) {
            $challenge->increment('attempts');

            return OtpVerifyResult::failure(
                $challenge->attempts >= $maxAttempts ? OtpVerifyReason::Locked : OtpVerifyReason::Incorrect
            );
        }

        // One-time consume via a conditional update so a code cannot be reused (R11).
        $consumed = OtpVerification::query()
            ->whereKey($challenge->getKey())
            ->whereNull('consumed_at')
            ->update(['consumed_at' => $now]);

        if ($consumed === 0) {
            return OtpVerifyResult::failure(OtpVerifyReason::Expired);
        }

        $customer = $this->customers->findOrCreateByPhone($phone);
        $customer->forceFill(['last_login_at' => $now])->save();

        return OtpVerifyResult::success($customer, ! $customer->hasCompletedOnboarding());
    }

    /** Normalize to a compact E.164-ish form (leading + preserved, digits only). */
    public static function normalize(string $phone): string
    {
        $trimmed = trim($phone);
        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        return (str_starts_with($trimmed, '+') ? '+' : '').$digits;
    }

    private function activeChallenge(string $phone): ?OtpVerification
    {
        return OtpVerification::query()
            ->where('phone', $phone)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();
    }

    private function generateCode(): string
    {
        $length = (int) config('otp.length');
        $maxValue = (10 ** $length) - 1;

        return str_pad((string) random_int(0, $maxValue), $length, '0', STR_PAD_LEFT);
    }
}
