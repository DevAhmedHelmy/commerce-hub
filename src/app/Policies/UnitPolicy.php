<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

/**
 * Generic-units authorization (prompt 40 §4 units.*). super_admin bypasses via Gate::before.
 */
class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('units.view');
    }

    public function view(User $user, Unit $unit): bool
    {
        return $user->can('units.view');
    }

    public function create(User $user): bool
    {
        return $user->can('units.create');
    }

    public function update(User $user, Unit $unit): bool
    {
        return $user->can('units.update');
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $user->can('units.update');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('units.update');
    }
}
