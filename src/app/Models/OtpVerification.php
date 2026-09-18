<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Hashed one-time phone challenge (data-model #16). Managed exclusively by
 * {@see \App\Domain\Auth\OtpService}; the plaintext code is never stored here.
 */
class OtpVerification extends Model
{
    protected $fillable = [
        'phone',
        'code_hash',
        'expires_at',
        'consumed_at',
        'attempts',
        'resend_count',
        'last_sent_at',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'attempts' => 'integer',
            'resend_count' => 'integer',
        ];
    }
}
