<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Support\Concerns\HasLocalizedText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A toggleable, ordered landing section (prompt 46 §9). `key` is language-neutral (hero,
 * categories, featured_products, features, about, contact); `settings` holds validated config
 * such as `{"limit": 6}`. Dynamic sections pull real domain data — never duplicated here.
 */
class LandingSection extends Model
{
    use HasLocalizedText;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'settings' => 'array'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(LandingSectionItem::class);
    }

    public function limit(int $default): int
    {
        return (int) (($this->settings['limit'] ?? $default));
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
