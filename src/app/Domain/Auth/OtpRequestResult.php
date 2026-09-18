<?php

declare(strict_types=1);

namespace App\Domain\Auth;

/**
 * Outcome of requesting an OTP (R7). `messageKey` is a translation key (never branched on).
 */
final readonly class OtpRequestResult
{
    public function __construct(
        public bool $sent,
        public ?int $cooldownSeconds = null,
        public ?string $messageKey = null,
    ) {
    }

    public static function sent(): self
    {
        return new self(true);
    }

    public static function throttled(int $cooldownSeconds, string $messageKey): self
    {
        return new self(false, $cooldownSeconds, $messageKey);
    }
}
