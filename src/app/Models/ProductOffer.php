<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Support\Concerns\HasLocalizedText;
use App\Domain\Support\Money;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Time-boxed offer for a selling unit (data-model #8, BR-005). Validity is evaluated
 * server-side only; offer_price in integer minor units.
 */
class ProductOffer extends Model
{
    use HasFactory;
    use HasLocalizedText;

    protected $fillable = [
        'product_unit_id',
        'offer_price',
        'starts_at',
        'ends_at',
        'is_active',
        'title_ar',
        'title_en',
    ];

    protected function casts(): array
    {
        return [
            'offer_price' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function offerPriceMoney(): Money
    {
        return Money::fromMinor((int) $this->offer_price);
    }

    public function isValidAt(DateTimeInterface $at): bool
    {
        return $this->is_active
            && $this->starts_at <= $at
            && $this->ends_at >= $at;
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
