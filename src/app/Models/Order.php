<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Support\Enums\OrderStatus;
use App\Domain\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Placed order header (data-model #14). Immutable commercial record — only `status`/cancellation
 * fields change after placement (Principle V). All money is integer minor units.
 */
class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'delivery_date' => 'date',
            'placed_at' => 'datetime',
            'product_subtotal' => 'integer',
            'base_delivery_fee' => 'integer',
            'delivery_discount' => 'integer',
            'final_delivery_fee' => 'integer',
            'final_total' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function finalTotalMoney(): Money
    {
        return Money::fromMinor((int) $this->final_total);
    }

    public function scopeForCustomer(Builder $query, int $customerId): void
    {
        $query->where('customer_id', $customerId);
    }
}
