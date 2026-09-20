<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * B2B ordering customer, authenticated by phone OTP via the `customer` guard (R8).
 * Separate from admin {@see User}. Ordering is gated on onboarding completion (FR-009).
 */
class Customer extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;

    /** New customers are active by default (in-memory default so freshly-created models are active). */
    protected $attributes = [
        'is_active' => true,
    ];

    protected $fillable = [
        'phone',
        'is_active',
        'business_name',
        'contact_person_name',
        'whatsapp_phone',
        'onboarding_completed_at',
        'last_login_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'onboarding_completed_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(CustomerAddress::class)->where('is_default', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Ordering is allowed only once onboarding is complete (FR-009). */
    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarding_completed_at !== null;
    }
}
