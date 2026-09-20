<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-unit quantity price tier (data-model #7). unit_price in integer minor units.
 */
class ProductPriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_unit_id',
        'min_quantity',
        'unit_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'integer',
            'unit_price' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function unitPriceMoney(): Money
    {
        return Money::fromMinor((int) $this->unit_price);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
