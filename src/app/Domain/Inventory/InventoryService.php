<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;

/**
 * Sole writer of stock (prompt 37/38 / R25). The authoritative balance is a single
 * sub-unit quantity per product, held on the product's SUB-level {@see ProductUnit} row.
 * Every change is applied under a row lock, guarded non-negative, and recorded as an
 * immutable {@see InventoryAdjustment} capturing the admin's input unit/qty and the
 * normalized sub-unit delta. Order-time deduction/restore build on this in Phases I/J.
 */
final class InventoryService
{
    /** Authoritative sub-unit balance for a product. */
    public function currentStock(Product $product): int
    {
        return (int) ($product->subUnit()->value('stock_quantity') ?? 0);
    }

    public function hasStockFor(Product $product, int $subUnitQuantity): bool
    {
        return $this->currentStock($product) >= $subUnitQuantity;
    }

    /**
     * Apply a signed sub-unit delta to the product's SUB-level row under `lockForUpdate`,
     * refusing to go below zero, and append an adjustment row.
     *
     * @throws InsufficientStockException when the resulting balance would be negative
     */
    public function applyToSubUnit(
        ProductUnit $subUnit,
        InventoryAdjustmentType $type,
        int $subDelta,
        ?string $reason = null,
        ?int $performedBy = null,
        ?int $inputUnitId = null,
        ?int $inputQuantity = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): InventoryAdjustment {
        return DB::transaction(function () use ($subUnit, $type, $subDelta, $reason, $performedBy, $inputUnitId, $inputQuantity, $referenceType, $referenceId) {
            /** @var ProductUnit $locked */
            $locked = ProductUnit::query()->whereKey($subUnit->getKey())->lockForUpdate()->firstOrFail();

            $before = (int) $locked->stock_quantity;
            $after = $before + $subDelta;

            if ($after < 0) {
                throw new InsufficientStockException($locked->id, $before, abs($subDelta));
            }

            $locked->stock_quantity = $after;
            $locked->save();

            return InventoryAdjustment::create([
                'product_unit_id' => $locked->id,
                'type' => $type->value,
                'input_unit_id' => $inputUnitId,
                'input_quantity' => $inputQuantity,
                'quantity_delta' => $subDelta,
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
