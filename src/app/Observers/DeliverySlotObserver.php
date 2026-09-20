<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Models\DeliverySlot;

/**
 * Audits delivery-slot configuration changes (prompt 45 §5).
 */
final class DeliverySlotObserver
{
    private const FIELDS = ['label_ar', 'label_en', 'day_of_week', 'start_time', 'end_time', 'is_active', 'sort_order'];

    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function created(DeliverySlot $slot): void
    {
        $this->audit->record(AuditAction::CREATED, $slot, [], $this->audit->snapshot($slot, self::FIELDS));
    }

    public function updated(DeliverySlot $slot): void
    {
        [$old, $new] = $this->audit->changes($slot, self::FIELDS);

        if ($new === []) {
            return;
        }

        if (array_keys($new) === ['is_active']) {
            $action = $slot->is_active ? AuditAction::ACTIVATED : AuditAction::DEACTIVATED;
        } else {
            $action = AuditAction::UPDATED;
        }

        $this->audit->record($action, $slot, $old, $new);
    }

    public function deleted(DeliverySlot $slot): void
    {
        $this->audit->record(AuditAction::DELETED, $slot, $this->audit->snapshot($slot, self::FIELDS), []);
    }
}
