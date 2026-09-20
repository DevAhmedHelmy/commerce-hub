<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A cart line: one selected {@see ProductUnit} with a quantity (data-model #10). The same product
 * may appear under different units (separate rows). `last_seen_unit_price` is non-authoritative.
 */
class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_unit_id', 'quantity', 'last_seen_unit_price'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'last_seen_unit_price' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }
}
