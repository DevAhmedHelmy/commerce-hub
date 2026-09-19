<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Support\Concerns\HasLocalizedText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Recurring weekday delivery window (data-model #12). One row per (weekday, window); no capacity.
 * `day_of_week` is ISO 1=Mon..7=Sun (neutral). Times are neutral `H:i`.
 */
class DeliverySlot extends Model
{
    use HasFactory;
    use HasLocalizedText;

    protected $fillable = ['label_ar', 'label_en', 'day_of_week', 'start_time', 'end_time', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['day_of_week' => 'integer', 'is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
