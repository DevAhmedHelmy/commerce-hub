<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Customer delivery address (data-model #3). MVP exposes one default per customer;
 * the schema is many-capable for the future (C6).
 */
class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'delivery_area_id',
        'is_default',
        'address_line',
        'building',
        'floor',
        'unit',
        'landmark',
        'delivery_notes',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
