<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Support\Concerns\HasLocalizedText;
use App\Domain\Support\Enums\AvailabilityStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Sellable product (data-model #5). Bilingual name/description; single natural `brand`.
 * Availability is a neutral enum; pricing lives on {@see ProductUnit}.
 */
class Product extends Model
{
    use HasFactory;
    use HasLocalizedText;

    protected $fillable = [
        'category_id',
        'name_ar',
        'name_en',
        'brand',
        'description_ar',
        'description_en',
        'image_path',
        'availability',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'availability' => AvailabilityStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function primaryUnit(): HasOne
    {
        return $this->hasOne(ProductUnit::class)->where('level', ProductUnit::LEVEL_PRIMARY);
    }

    public function subUnit(): HasOne
    {
        return $this->hasOne(ProductUnit::class)->where('level', ProductUnit::LEVEL_SUB);
    }

    /** Authoritative stock is the sub-unit balance (prompt 37 §8). */
    public function subStock(): int
    {
        return (int) ($this->units->firstWhere('level', ProductUnit::LEVEL_SUB)?->stock_quantity ?? 0);
    }

    public function conversionToSubUnit(): int
    {
        return (int) ($this->units->firstWhere('level', ProductUnit::LEVEL_PRIMARY)?->conversion_to_sub_unit ?? 1);
    }

    /**
     * Lightweight "from" unit for listing cards (§44): the primary sellable unit, else the
     * cheapest sellable unit. Reads the loaded `units` relation to avoid N+1 on listings.
     */
    public function baselineUnit(): ?ProductUnit
    {
        $sellable = $this->units->where('is_sellable', true)->where('is_active', true);

        return $sellable->firstWhere('level', ProductUnit::LEVEL_PRIMARY)
            ?? $sellable->sortBy('base_price')->first();
    }

    /**
     * Orderable = admin-available AND has an active sellable unit AND the product's sub-unit
     * stock is > 0 (prompt 37 §14 / FR-083/FR-084).
     */
    public function isOrderable(): bool
    {
        if ($this->availability !== AvailabilityStatus::Available) {
            return false;
        }

        $hasSellableUnit = $this->units->contains(fn (ProductUnit $u) => $u->is_active && $u->is_sellable);

        return $hasSellableUnit && $this->subStock() > 0;
    }

    /**
     * Stock-driven availability for display: inactive stays inactive; otherwise a product
     * with no orderable (in-stock, active) unit shows out-of-stock without a manual toggle.
     */
    public function displayAvailability(): AvailabilityStatus
    {
        if ($this->availability === AvailabilityStatus::Inactive) {
            return AvailabilityStatus::Inactive;
        }

        return $this->isOrderable() ? AvailabilityStatus::Available : AvailabilityStatus::OutOfStock;
    }

    /** Visible to customers = not inactive (available + out-of-stock) (FR-015/FR-016). */
    public function scopeVisible(Builder $query): void
    {
        $query->where('availability', '!=', AvailabilityStatus::Inactive->value);
    }

    public function scopeOrderable(Builder $query): void
    {
        $query->where('availability', AvailabilityStatus::Available->value);
    }
}
