<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\ProductUnit;

/**
 * Admin manual stock actions (prompt 37/38 §7). Admin enters a quantity in EITHER the
 * primary or sub unit; it is normalized to sub-units via {@see ProductUnitConverter} and
 * applied to the product's authoritative sub-unit balance. Never drives stock below zero.
 */
final class AdjustInventoryAction
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly ProductUnitConverter $converter,
    ) {
    }

    public function add(Product $product, ProductUnit $inputUnit, int $quantity, ?string $reason, ?int $performedBy): InventoryAdjustment
    {
        $qty = abs($quantity);

        return $this->inventory->applyToSubUnit(
            $this->subUnit($product),
            InventoryAdjustmentType::ManualAdd,
            $this->converter->toSubUnits($inputUnit, $qty),
            $reason,
            $performedBy,
            $inputUnit->unit_id,
            $qty,
        );
    }

    public function remove(Product $product, ProductUnit $inputUnit, int $quantity, ?string $reason, ?int $performedBy): InventoryAdjustment
    {
        $qty = abs($quantity);

        return $this->inventory->applyToSubUnit(
            $this->subUnit($product),
            InventoryAdjustmentType::ManualRemove,
            -$this->converter->toSubUnits($inputUnit, $qty),
            $reason,
            $performedBy,
            $inputUnit->unit_id,
            $qty,
        );
    }

    /** Correct the balance to an exact sub-unit target. */
    public function correctTo(Product $product, int $targetSubUnits, ?string $reason, ?int $performedBy): InventoryAdjustment
    {
        $sub = $this->subUnit($product);
        $delta = max(0, $targetSubUnits) - (int) $sub->stock_quantity;

        return $this->inventory->applyToSubUnit(
            $sub,
            InventoryAdjustmentType::Correction,
            $delta,
            $reason,
            $performedBy,
            $sub->unit_id,
            max(0, $targetSubUnits),
        );
    }

    private function subUnit(Product $product): ProductUnit
    {
        return $product->subUnit()->firstOrFail();
    }
}
