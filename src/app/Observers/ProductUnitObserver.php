<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Models\ProductUnit;

/**
 * Audits product-unit configuration and price changes (prompt 39 §1/§5/§6). Base-price edits
 * are recorded as `price_changed` with old/new minor-unit values. Stock changes are NOT audited
 * here — they own the `inventory_adjustments` ledger and are audited by AdjustInventoryAction.
 */
final class ProductUnitObserver
{
    private const FIELDS = ['unit_id', 'level', 'conversion_to_sub_unit', 'is_sellable', 'base_price', 'is_active'];

    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function created(ProductUnit $unit): void
    {
        $this->audit->record(
            AuditAction::CREATED,
            $unit,
            [],
            $this->audit->snapshot($unit, self::FIELDS),
            ['product_id' => $unit->product_id, 'level' => $unit->level],
        );
    }

    public function updated(ProductUnit $unit): void
    {
        [$old, $new] = $this->audit->changes($unit, self::FIELDS);

        if ($new === []) {
            return; // stock-only save (own ledger) — nothing to audit here
        }

        $action = array_key_exists('base_price', $new) ? AuditAction::PRICE_CHANGED : AuditAction::UPDATED;

        $this->audit->record($action, $unit, $old, $new, ['product_id' => $unit->product_id, 'level' => $unit->level]);
    }

    public function deleted(ProductUnit $unit): void
    {
        $this->audit->record(
            AuditAction::DELETED,
            $unit,
            $this->audit->snapshot($unit, self::FIELDS),
            [],
            ['product_id' => $unit->product_id, 'level' => $unit->level],
        );
    }
}
