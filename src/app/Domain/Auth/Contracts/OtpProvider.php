<?php

declare(strict_types=1);

namespace App\Domain\Auth\Contracts;

/**
 * Vendor-agnostic OTP delivery channel (R7). MVP ships {@see \App\Domain\Auth\Providers\LogOtpProvider}
 * for dev/demo; a real SMS/WhatsApp implementation binds in production. Implementations
 * MUST NOT return or log the code in production.
 */
interface OtpProvider
{
    public function send(string $phoneE164, string $code): void;
}
