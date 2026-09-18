<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Support\Concerns\HasLocalizedText;
use App\Domain\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Selling unit (data-model #6). `code` neutral; display name bilingual. `base_price` is
 * the normal unit price in integer minor units, exposed as {@see Money}.
 */
class ProductUnit extends Model
{
    use HasFactory;
    use HasLocalizedText;

    protected $fillable = [
        'product_id',
        'code',
        'display_name_ar',
        'display_name_en',
        'package_description_ar',
        'package_description_en',
        'base_price',
        'conversion_factor',
        'stock_quantity',
        'low_stock_threshold',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    public function basePriceMoney(): Money
    {
        return Money::fromMinor((int) $this->base_price);
    }

    public function isInStock(): bool
    {
        return (int) $this->stock_quantity > 0;
    }

    /** Authoritative unit orderability (prompt 32 §10 / FR-074): active AND in stock. */
    public function isOrderable(): bool
    {
        return $this->is_active && $this->isInStock();
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): void
    {
        $query->where('stock_quantity', '>', 0);
    }
}
