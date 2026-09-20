<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Idempotent RBAC seeder (prompt 40 §9). Creates the granular permissions and the five approved
 * roles, then syncs each role's default permission set. Safe to re-run: it uses firstOrCreate and
 * syncPermissions, and never blindly deletes unknown records. Guard is the admin `web` guard.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    private const GUARD = 'web';

    /** All granular permissions (prompt 40 §4). */
    public const PERMISSIONS = [
        'products.view', 'products.create', 'products.update', 'products.activate',
        'units.view', 'units.create', 'units.update', 'units.activate', 'units.assign_to_product', 'units.change_conversion',
        'pricing.view', 'pricing.update_base', 'pricing.manage_tiers', 'pricing.manage_offers',
        'inventory.view', 'inventory.adjust', 'inventory.view_history',
        'orders.view', 'orders.update_status', 'orders.cancel',
        'customers.view', 'customers.update', 'customers.activate',
        'delivery.view', 'delivery.manage_areas', 'delivery.manage_slots', 'delivery.manage_discounts',
        'settings.view', 'settings.update',
        'landing.view', 'landing.manage',
        'audit.view',
        'admin_users.view', 'admin_users.create', 'admin_users.update', 'admin_users.deactivate',
        'roles.view', 'roles.manage',
    ];

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_ORDERS_STAFF = 'orders_staff';

    public const ROLE_INVENTORY_STAFF = 'inventory_staff';

    public const ROLE_PRICING_STAFF = 'pricing_staff';

    /** Arabic labels (presentation-only). */
    public const ROLE_LABELS = [
        self::ROLE_SUPER_ADMIN => 'مدير النظام',
        self::ROLE_MANAGER => 'مدير',
        self::ROLE_ORDERS_STAFF => 'موظف الطلبات',
        self::ROLE_INVENTORY_STAFF => 'موظف المخزون',
        self::ROLE_PRICING_STAFF => 'موظف الأسعار والمبيعات',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, self::GUARD);
        }

        // super_admin holds every permission (also short-circuited by a Gate::before bypass).
        $this->syncRole(self::ROLE_SUPER_ADMIN, self::PERMISSIONS);

        // manager: all operational permissions + audit view; NOT roles/admin-user management.
        $this->syncRole(self::ROLE_MANAGER, array_values(array_filter(
            self::PERMISSIONS,
            static fn (string $p): bool => ! str_starts_with($p, 'roles.') && ! str_starts_with($p, 'admin_users.'),
        )));

        $this->syncRole(self::ROLE_ORDERS_STAFF, [
            'orders.view', 'orders.update_status', 'orders.cancel', 'customers.view',
        ]);

        $this->syncRole(self::ROLE_INVENTORY_STAFF, [
            'products.view', 'units.view', 'inventory.view', 'inventory.adjust', 'inventory.view_history',
        ]);

        $this->syncRole(self::ROLE_PRICING_STAFF, [
            'products.view', 'products.update',
            'pricing.view', 'pricing.update_base', 'pricing.manage_tiers', 'pricing.manage_offers',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @param list<string> $permissions */
    private function syncRole(string $name, array $permissions): void
    {
        $role = Role::findOrCreate($name, self::GUARD);
        $role->syncPermissions($permissions);
    }
}
