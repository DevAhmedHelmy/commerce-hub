<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable order line snapshot (data-model #15). Preserves the selected unit, conversion factor,
 * normalized sub-unit quantity deducted, and applied pricing at placement time (Principle V).
 */
class OrderItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'conversion_factor' => 'integer',
            'sub_unit_quantity' => 'integer',
            'base_unit_price' => 'integer',
            'applied_unit_price' => 'integer',
            'unit_saving' => 'integer',
            'line_total' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function lineTotalMoney(): Money
    {
        return Money::fromMinor((int) $this->line_total);
    }
}
