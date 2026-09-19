<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Access\SuperAdminGuard;
use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Models\User;

/**
 * Audits admin-user lifecycle (prompt 40 §19) and enforces last-super-admin safety (§17).
 * Passwords are never audited (§19 / prompt 39 §8: the service also redacts them defensively).
 */
final class UserObserver
{
    private const FIELDS = ['name', 'email', 'is_active'];

    public function __construct(
        private readonly AdminAuditService $audit,
        private readonly SuperAdminGuard $guard,
    ) {
    }

    public function created(User $user): void
    {
        $this->audit->record(AuditAction::CREATED, $user, [], $this->audit->snapshot($user, self::FIELDS));
    }

    public function updating(User $user): void
    {
        // Refuse to deactivate the last active super_admin. The row is still active in the DB at
        // this point, so the active-super-admin count includes this user.
        $deactivating = $user->isDirty('is_active') && ! $user->is_active && $user->getOriginal('is_active');

        if ($deactivating && $user->hasRole(SuperAdminGuard::ROLE) && $this->guard->activeSuperAdminCount() <= 1) {
            throw new \RuntimeException('لا يمكن إلغاء تفعيل آخر مدير نظام نشط.');
        }
    }

    public function updated(User $user): void
    {
        [$old, $new] = $this->audit->changes($user, self::FIELDS);

        if ($new === []) {
            return;
        }

        if (array_keys($new) === ['is_active']) {
            $action = $user->is_active ? AuditAction::ACTIVATED : AuditAction::DEACTIVATED;
        } else {
            $action = AuditAction::UPDATED;
        }

        $this->audit->record($action, $user, $old, $new);
    }
}
