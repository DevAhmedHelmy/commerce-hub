<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Support\Enums\DeliveryDiscountType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Subtotal-threshold delivery discount (data-model #13, R6). Never stacked — DeliveryService picks
 * the single largest saving. `value` is minor units for `fixed`, integer percent for `percentage`.
 */
class DeliveryDiscountRule extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'value', 'min_subtotal', 'is_active'];

    protected function casts(): array
    {
        return [
            'type' => DeliveryDiscountType::class,
            'value' => 'integer',
            'min_subtotal' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
