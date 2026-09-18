<?php

declare(strict_types=1);

namespace App\Domain\Auth\Providers;

use App\Domain\Auth\Contracts\OtpProvider;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Dev/demo OTP channel (R7). Writes the code to the application log so a developer
 * can complete sign-in without a real SMS gateway. It refuses to run in production —
 * production MUST bind a real provider and MUST NEVER expose a test OTP (T134).
 */
final class LogOtpProvider implements OtpProvider
{
    public function send(string $phoneE164, string $code): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('LogOtpProvider must not be used in production.');
        }

        Log::channel(config('logging.default'))->info('OTP issued (dev only)', [
            'phone' => $phoneE164,
            'code' => $code,
        ]);
    }
}
