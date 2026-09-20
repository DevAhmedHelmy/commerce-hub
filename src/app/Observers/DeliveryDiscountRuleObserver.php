<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Models\DeliveryDiscountRule;

/**
 * Audits delivery discount-rule changes (prompt 45 §5): type/value/threshold/active.
 */
final class DeliveryDiscountRuleObserver
{
    private const FIELDS = ['name', 'type', 'value', 'min_subtotal', 'is_active'];

    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function created(DeliveryDiscountRule $rule): void
    {
        $this->audit->record(AuditAction::CREATED, $rule, [], $this->audit->snapshot($rule, self::FIELDS));
    }

    public function updated(DeliveryDiscountRule $rule): void
    {
        [$old, $new] = $this->audit->changes($rule, self::FIELDS);

        if ($new === []) {
            return;
        }

        if (array_keys($new) === ['is_active']) {
            $action = $rule->is_active ? AuditAction::ACTIVATED : AuditAction::DEACTIVATED;
        } else {
            $action = AuditAction::UPDATED;
        }

        $this->audit->record($action, $rule, $old, $new);
    }

    public function deleted(DeliveryDiscountRule $rule): void
    {
        $this->audit->record(AuditAction::DELETED, $rule, $this->audit->snapshot($rule, self::FIELDS), []);
    }
}
