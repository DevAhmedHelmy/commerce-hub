<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Models\DeliveryArea;

/**
 * Audits delivery-area configuration changes (prompt 45 §5): fee, active state, name.
 */
final class DeliveryAreaObserver
{
    private const FIELDS = ['name_ar', 'name_en', 'base_fee', 'is_active', 'sort_order'];

    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function created(DeliveryArea $area): void
    {
        $this->audit->record(AuditAction::CREATED, $area, [], $this->audit->snapshot($area, self::FIELDS));
    }

    public function updated(DeliveryArea $area): void
    {
        [$old, $new] = $this->audit->changes($area, self::FIELDS);

        if ($new === []) {
            return;
        }

        if (array_keys($new) === ['is_active']) {
            $action = $area->is_active ? AuditAction::ACTIVATED : AuditAction::DEACTIVATED;
        } elseif (array_key_exists('base_fee', $new)) {
            $action = AuditAction::PRICE_CHANGED;
        } else {
            $action = AuditAction::UPDATED;
        }

        $this->audit->record($action, $area, $old, $new);
    }

    public function deleted(DeliveryArea $area): void
    {
        $this->audit->record(AuditAction::DELETED, $area, $this->audit->snapshot($area, self::FIELDS), []);
    }
}
