<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Models\InventoryAdjustment;
use App\Models\ProductUnit;

/**
 * Admin-facing manual stock actions (prompt 32 §5): add, remove, and correct-to a target.
 * Thin wrapper translating an admin intent into a signed {@see InventoryService::adjust}.
 */
final class AdjustInventoryAction
{
    public function __construct(private readonly InventoryService $inventory)
    {
    }

    public function add(ProductUnit $unit, int $quantity, ?string $reason, ?int $performedBy): InventoryAdjustment
    {
        return $this->inventory->adjust($unit, InventoryAdjustmentType::ManualAdd, abs($quantity), $reason, $performedBy);
    }

    public function remove(ProductUnit $unit, int $quantity, ?string $reason, ?int $performedBy): InventoryAdjustment
    {
        return $this->inventory->adjust($unit, InventoryAdjustmentType::ManualRemove, -abs($quantity), $reason, $performedBy);
    }

    /** Set the balance to an exact target quantity (never negative). */
    public function correctTo(ProductUnit $unit, int $target, ?string $reason, ?int $performedBy): InventoryAdjustment
    {
        $delta = max(0, $target) - $this->inventory->currentStock($unit);

        return $this->inventory->adjust($unit, InventoryAdjustmentType::Correction, $delta, $reason, $performedBy);
    }
}
