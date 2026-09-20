<?php

declare(strict_types=1);

namespace App\Domain\Auth;

/**
 * Neutral outcome of an OTP verification (R7/FR-003). Presentation maps these to
 * Arabic messages; logic branches on the identifier, never on translated text.
 */
enum OtpVerifyReason: string
{
    case Verified = 'verified';
    case Incorrect = 'incorrect';
    case Expired = 'expired';
    case Locked = 'locked';

    public function messageKey(): string
    {
        return 'auth.otp.'.$this->value;
    }
}
