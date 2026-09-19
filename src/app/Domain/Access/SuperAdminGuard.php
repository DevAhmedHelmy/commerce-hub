<?php

declare(strict_types=1);

namespace App\Domain\Access;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use RuntimeException;

/**
 * Protects the system from losing its last administrator (prompt 40 §17). The application must
 * never be left with zero active super_admins, so deactivating — or removing the role from — the
 * last active super_admin is refused.
 */
final class SuperAdminGuard
{
    public const ROLE = RolesAndPermissionsSeeder::ROLE_SUPER_ADMIN;

    /** Count of currently active users holding the super_admin role. */
    public function activeSuperAdminCount(): int
    {
        return User::query()->where('is_active', true)->role(self::ROLE)->count();
    }

    /** True when this user is an active super_admin and the only one left. */
    public function isLastActiveSuperAdmin(User $user): bool
    {
        if (! $user->is_active || ! $user->hasRole(self::ROLE)) {
            return false;
        }

        return $this->activeSuperAdminCount() <= 1;
    }

    /** @throws RuntimeException when the change would leave zero active super_admins */
    public function assertCanDeactivate(User $user): void
    {
        if ($this->isLastActiveSuperAdmin($user)) {
            throw new RuntimeException('لا يمكن إلغاء تفعيل آخر مدير نظام نشط.');
        }
    }

    /**
     * @param  list<string>  $newRoles  the roles the user will hold after the change
     *
     * @throws RuntimeException when the change would strip the last active super_admin's role
     */
    public function assertRolesKeepSuperAdmin(User $user, array $newRoles): void
    {
        if ($this->isLastActiveSuperAdmin($user) && ! in_array(self::ROLE, $newRoles, true)) {
            throw new RuntimeException('لا يمكن إزالة دور مدير النظام من آخر مدير نشط.');
        }
    }
}
