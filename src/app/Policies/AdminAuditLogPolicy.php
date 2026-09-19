<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminAuditLog;
use App\Models\User;

/**
 * Audit log is view-only and gated by `audit.view` (prompt 40 §16). Never writable via the UI.
 */
class AdminAuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('audit.view');
    }

    public function view(User $user, AdminAuditLog $log): bool
    {
        return $user->can('audit.view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, AdminAuditLog $log): bool
    {
        return false;
    }

    public function delete(User $user, AdminAuditLog $log): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
