<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Support\Concerns\HasLocalizedText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Product category (data-model #4). Bilingual name/description via {@see HasLocalizedText};
 * `is_active` controls customer visibility (FR-017).
 */
class Category extends Model
{
    use HasFactory;
    use HasLocalizedText;

    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'description_ar',
        'description_en',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @param  Builder<Category>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
