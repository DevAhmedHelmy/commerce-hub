<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Inventory\InventoryAdjustmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable stock-change audit record (data-model #18). Only created (never updated) —
 * hence no `updated_at`. Written exclusively by {@see \App\Domain\Inventory\InventoryService}.
 */
class InventoryAdjustment extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_unit_id',
        'type',
        'quantity_delta',
        'quantity_before',
        'quantity_after',
        'reason',
        'reference_type',
        'reference_id',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => InventoryAdjustmentType::class,
            'quantity_delta' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
        ];
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
