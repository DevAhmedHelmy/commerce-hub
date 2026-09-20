<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Support\Concerns\HasLocalizedText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Repeatable marketing card for a landing section (prompt 46 §10): benefits, steps, highlights.
 * Never used for products/categories/offers/delivery. `link_url` is validated to safe schemes.
 */
class LandingSectionItem extends Model
{
    use HasLocalizedText;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(LandingSection::class, 'landing_section_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
