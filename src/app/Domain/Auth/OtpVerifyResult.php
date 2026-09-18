<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Models\Customer;

/**
 * Outcome of verifying an OTP (R7). On success carries the resolved customer and
 * whether onboarding is still required (FR-006/FR-009).
 */
final readonly class OtpVerifyResult
{
    public function __construct(
        public bool $ok,
        public OtpVerifyReason $reason,
        public ?Customer $customer = null,
        public bool $needsOnboarding = false,
    ) {
    }

    public static function success(Customer $customer, bool $needsOnboarding): self
    {
        return new self(true, OtpVerifyReason::Verified, $customer, $needsOnboarding);
    }

    public static function failure(OtpVerifyReason $reason): self
    {
        return new self(false, $reason);
    }
}
