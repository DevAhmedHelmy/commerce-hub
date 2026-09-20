<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Role management (prompt 40 §9/§17). View gated by `roles.view`; any mutation by `roles.manage`
 * (super_admin only by default). Registered explicitly in AppServiceProvider (Role lives in the
 * package namespace, outside policy auto-discovery).
 */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.manage');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('roles.manage');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('roles.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('roles.manage');
    }
}
