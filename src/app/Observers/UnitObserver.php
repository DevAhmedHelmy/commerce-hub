<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Models\Unit;

/**
 * Audits generic unit create/update (prompt 39 §1). An is_active-only toggle is recorded as
 * activate/deactivate; other edits are `updated`.
 */
final class UnitObserver
{
    private const FIELDS = ['code', 'name_ar', 'name_en', 'is_active', 'sort_order'];

    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function created(Unit $unit): void
    {
        $this->audit->record(AuditAction::CREATED, $unit, [], $this->audit->snapshot($unit, self::FIELDS));
    }

    public function updated(Unit $unit): void
    {
        [$old, $new] = $this->audit->changes($unit, self::FIELDS);

        if ($new === []) {
            return;
        }

        if (array_keys($new) === ['is_active']) {
            $action = $unit->is_active ? AuditAction::ACTIVATED : AuditAction::DEACTIVATED;
        } else {
            $action = AuditAction::UPDATED;
        }

        $this->audit->record($action, $unit, $old, $new);
    }
}
