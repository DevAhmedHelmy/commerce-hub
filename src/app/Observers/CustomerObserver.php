<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Models\Customer;

/**
 * Audits admin edits to a customer (prompt 48 §25): profile changes, activation/deactivation, and
 * phone (login-identity) changes. Never logs OTP/secrets; phone old/new is recorded for traceability.
 */
final class CustomerObserver
{
    private const FIELDS = ['business_name', 'contact_person_name', 'phone', 'whatsapp_phone', 'is_active'];

    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function updated(Customer $customer): void
    {
        [$old, $new] = $this->audit->changes($customer, self::FIELDS);

        if ($new === []) {
            return;
        }

        if (array_keys($new) === ['is_active']) {
            $action = $customer->is_active ? AuditAction::ACTIVATED : AuditAction::DEACTIVATED;
        } else {
            $action = AuditAction::UPDATED;
        }

        $this->audit->record($action, $customer, $old, $new);
    }
}
