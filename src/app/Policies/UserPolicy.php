<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Admin-user management (prompt 40 §8/§17). Super_admin-only in practice (only super_admin holds
 * the admin_users.* permissions by default; the Gate::before bypass also covers it). Last-active-
 * super-admin protection is enforced at the model layer, not here.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('admin_users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('admin_users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('admin_users.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('admin_users.update');
    }

    public function delete(User $user, User $model): bool
    {
        return false; // admins are deactivated, never hard-deleted
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
