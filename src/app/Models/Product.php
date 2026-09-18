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

    public function defaultUnit(): HasMany
    {
        return $this->units()->where('is_default', true);
    }

    /**
     * Lightweight "from" unit for listing cards (§44): default unit, else cheapest
     * active unit. Reads the loaded `units` relation to avoid N+1 on listings.
     */
    public function baselineUnit(): ?ProductUnit
    {
        return $this->units->firstWhere('is_default', true)
            ?? $this->units->sortBy('base_price')->first();
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
