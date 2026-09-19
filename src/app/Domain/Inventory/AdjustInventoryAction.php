<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\ProductUnit;

/**
 * Admin manual stock actions (prompt 37/38 §7). Admin enters a quantity in EITHER the
 * primary or sub unit; it is normalized to sub-units via {@see ProductUnitConverter} and
 * applied to the product's authoritative sub-unit balance. Never drives stock below zero.
 *
 * Each successful manual change also writes an admin audit entry (prompt 39 §7) referencing the
 * authoritative {@see InventoryAdjustment} ledger row — the ledger stays the stock truth; the
 * audit log records the action at a higher level. If the stock change throws, no audit is written.
 */
final class AdjustInventoryAction
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly ProductUnitConverter $converter,
        private readonly AdminAuditService $audit,
    ) {
    }

    public function add(Product $product, ProductUnit $inputUnit, int $quantity, ?string $reason, ?int $performedBy): InventoryAdjustment
    {
        $qty = abs($quantity);

        $adjustment = $this->inventory->applyToSubUnit(
            $this->subUnit($product),
            InventoryAdjustmentType::ManualAdd,
            $this->converter->toSubUnits($inputUnit, $qty),
            $reason,
            $performedBy,
            $inputUnit->unit_id,
            $qty,
        );

        return $this->auditStock(AuditAction::STOCK_ADDED, $product, $inputUnit, $qty, $adjustment);
    }

    public function remove(Product $product, ProductUnit $inputUnit, int $quantity, ?string $reason, ?int $performedBy): InventoryAdjustment
    {
        $qty = abs($quantity);

        $adjustment = $this->inventory->applyToSubUnit(
            $this->subUnit($product),
            InventoryAdjustmentType::ManualRemove,
            -$this->converter->toSubUnits($inputUnit, $qty),
            $reason,
            $performedBy,
            $inputUnit->unit_id,
            $qty,
        );

        return $this->auditStock(AuditAction::STOCK_REMOVED, $product, $inputUnit, $qty, $adjustment);
    }

    /** Correct the balance to an exact sub-unit target. */
    public function correctTo(Product $product, int $targetSubUnits, ?string $reason, ?int $performedBy): InventoryAdjustment
    {
        $sub = $this->subUnit($product);
        $delta = max(0, $targetSubUnits) - (int) $sub->stock_quantity;

        $adjustment = $this->inventory->applyToSubUnit(
            $sub,
            InventoryAdjustmentType::Correction,
            $delta,
            $reason,
            $performedBy,
            $sub->unit_id,
            max(0, $targetSubUnits),
        );

        return $this->auditStock(AuditAction::STOCK_CORRECTED, $product, $sub, max(0, $targetSubUnits), $adjustment);
    }

    /**
     * Record a higher-level audit entry for a successful manual stock change, referencing the
     * authoritative ledger row so the two histories never conflict.
     */
    private function auditStock(string $action, Product $product, ProductUnit $inputUnit, int $inputQty, InventoryAdjustment $adjustment): InventoryAdjustment
    {
        $this->audit->record(
            $action,
            $product,
            ['stock_quantity' => $adjustment->quantity_before],
            ['stock_quantity' => $adjustment->quantity_after],
            [
                'inventory_adjustment_id' => $adjustment->id,
                'input_unit_id' => $inputUnit->unit_id,
                'input_quantity' => $inputQty,
                'normalized_delta' => $adjustment->quantity_delta,
            ],
        );

        return $adjustment;
    }

    private function subUnit(Product $product): ProductUnit
    {
        return $product->subUnit()->firstOrFail();
    }
}
