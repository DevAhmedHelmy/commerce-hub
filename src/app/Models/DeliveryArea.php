<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Support\Concerns\HasLocalizedText;
use App\Domain\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Deliverable area with a base fee (data-model #11). Inactive areas block new orders.
 */
class DeliveryArea extends Model
{
    use HasFactory;
    use HasLocalizedText;

    protected $fillable = ['name_ar', 'name_en', 'base_fee', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['base_fee' => 'integer', 'is_active' => 'boolean'];
    }

    public function baseFeeMoney(): Money
    {
        return Money::fromMinor((int) $this->base_fee);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
