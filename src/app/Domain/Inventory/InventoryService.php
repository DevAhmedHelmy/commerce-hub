<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Models\InventoryAdjustment;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;

/**
 * Sole writer of per-unit stock (prompt 32 / R24). Every change is applied under a row
 * lock inside a transaction, guarded non-negative, and recorded as an immutable
 * {@see InventoryAdjustment}. Order-time deduction/restore (DeductInventoryForOrder /
 * RestoreInventoryForCancellation) are added in Phases I/J on top of this foundation.
 */
final class InventoryService
{
    public function currentStock(ProductUnit $unit): int
    {
        return (int) $unit->stock_quantity;
    }

    public function hasStockFor(ProductUnit $unit, int $quantity): bool
    {
        return $this->currentStock($unit) >= $quantity;
    }

    /**
     * Apply a signed delta to a unit's stock under `lockForUpdate`, refusing to go below
     * zero, and append an adjustment row. Returns the recorded adjustment.
     *
     * @throws InsufficientStockException when the resulting balance would be negative
     */
    public function adjust(
        ProductUnit $unit,
        InventoryAdjustmentType $type,
        int $delta,
        ?string $reason = null,
        ?int $performedBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): InventoryAdjustment {
        return DB::transaction(function () use ($unit, $type, $delta, $reason, $performedBy, $referenceType, $referenceId) {
            /** @var ProductUnit $locked */
            $locked = ProductUnit::query()->whereKey($unit->getKey())->lockForUpdate()->firstOrFail();

            $before = (int) $locked->stock_quantity;
            $after = $before + $delta;

            if ($after < 0) {
                throw new InsufficientStockException($locked->id, $before, abs($delta));
            }

            $locked->stock_quantity = $after;
            $locked->save();

            return InventoryAdjustment::create([
                'product_unit_id' => $locked->id,
                'type' => $type->value,
                'quantity_delta' => $delta,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reason' => $reason,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'performed_by' => $performedBy,
            ]);
        });
    }
}
