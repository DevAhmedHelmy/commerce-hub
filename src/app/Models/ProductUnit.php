<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Inventory\ConversionChangeBlockedException;
use App\Domain\Support\Concerns\HasLocalizedText;
use App\Domain\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

/**
 * Product↔unit configuration at a level (data-model #6, prompt 37/38). Links a product to
 * a reusable {@see Unit} as `primary` or `sub` with a product-specific conversion. Both
 * levels are independently sellable and priced. The authoritative stock balance (sub-units)
 * lives on the SUB-level row's `stock_quantity`; the primary row's is unused.
 */
class ProductUnit extends Model
{
    use HasFactory;
    use HasLocalizedText;

    public const LEVEL_PRIMARY = 'primary';

    public const LEVEL_SUB = 'sub';

    protected $fillable = [
        'product_id',
        'unit_id',
        'level',
        'conversion_to_sub_unit',
        'is_sellable',
        'display_name_ar',
        'display_name_en',
        'package_description_ar',
        'package_description_en',
        'base_price',
        'stock_quantity',
        'low_stock_threshold',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'conversion_to_sub_unit' => 'integer',
            'is_sellable' => 'boolean',
            'base_price' => 'integer',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Service-level integrity guards (data-model §6, prompt 37 §18/§24). The conversion
     * factor must stay > 0, and it may not be changed once the product holds stock — existing
     * normalized sub-unit stock is never silently reinterpreted under a new factor.
     */
    protected static function booted(): void
    {
        static::saving(function (self $unit): void {
            if ((int) $unit->conversion_to_sub_unit < 1) {
                throw new InvalidArgumentException('conversion_to_sub_unit must be greater than zero.');
            }
        });

        static::updating(function (self $unit): void {
            if (! $unit->isDirty('conversion_to_sub_unit')) {
                return;
            }

            $subStock = (int) static::query()
                ->where('product_id', $unit->product_id)
                ->where('level', self::LEVEL_SUB)
                ->value('stock_quantity');

            if ($subStock > 0) {
                throw new ConversionChangeBlockedException((int) $unit->product_id, $subStock);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(ProductOffer::class);
    }

    public function basePriceMoney(): Money
    {
        return Money::fromMinor((int) $this->base_price);
    }

    /** Display label: per-product override if set, else the generic unit's localized name. */
    public function label(): string
    {
        $override = $this->localized('display_name');

        return $override !== '' ? $override : (string) ($this->unit?->localized('name') ?? '');
    }

    public function isPrimary(): bool
    {
        return $this->level === self::LEVEL_PRIMARY;
    }

    public function isSub(): bool
    {
        return $this->level === self::LEVEL_SUB;
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeSellable(Builder $query): void
    {
        $query->where('is_sellable', true);
    }
}
