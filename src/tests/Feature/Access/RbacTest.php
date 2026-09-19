<?php

declare(strict_types=1);

use App\Domain\Access\SuperAdminGuard;
use App\Domain\Inventory\AdjustInventoryAction;
use App\Domain\Inventory\InsufficientStockException;
use App\Models\Product;

it('grants the full permission set to super_admin', function () {
    $user = adminWithRole('super_admin');

    expect($user->can('roles.manage'))->toBeTrue()
        ->and($user->can('admin_users.create'))->toBeTrue()
        ->and($user->can('audit.view'))->toBeTrue()
        ->and($user->can('inventory.adjust'))->toBeTrue()
        ->and($user->can('pricing.update_base'))->toBeTrue();
});

it('gives the manager operational access but not user/role management', function () {
    $user = adminWithRole('manager');

    expect($user->can('products.update'))->toBeTrue()
        ->and($user->can('pricing.update_base'))->toBeTrue()
        ->and($user->can('inventory.adjust'))->toBeTrue()
        ->and($user->can('units.change_conversion'))->toBeTrue()
        ->and($user->can('audit.view'))->toBeTrue()
        ->and($user->can('roles.manage'))->toBeFalse()
        ->and($user->can('admin_users.create'))->toBeFalse();
});

it('limits orders_staff to order handling', function () {
    $user = adminWithRole('orders_staff');

    expect($user->can('orders.update_status'))->toBeTrue()
        ->and($user->can('orders.cancel'))->toBeTrue()
        ->and($user->can('customers.view'))->toBeTrue()
        ->and($user->can('pricing.update_base'))->toBeFalse()
        ->and($user->can('inventory.adjust'))->toBeFalse()
        ->and($user->can('roles.manage'))->toBeFalse();
});

it('limits inventory_staff to stock, not pricing or users', function () {
    $user = adminWithRole('inventory_staff');

    expect($user->can('inventory.adjust'))->toBeTrue()
        ->and($user->can('inventory.view_history'))->toBeTrue()
        ->and($user->can('products.view'))->toBeTrue()
        ->and($user->can('pricing.update_base'))->toBeFalse()
        ->and($user->can('pricing.manage_offers'))->toBeFalse()
        ->and($user->can('admin_users.view'))->toBeFalse()
        ->and($user->can('units.change_conversion'))->toBeFalse();
});

it('limits pricing_staff to pricing, not inventory or users', function () {
    $user = adminWithRole('pricing_staff');

    expect($user->can('pricing.update_base'))->toBeTrue()
        ->and($user->can('pricing.manage_tiers'))->toBeTrue()
        ->and($user->can('pricing.manage_offers'))->toBeTrue()
        ->and($user->can('products.update'))->toBeTrue()
        ->and($user->can('inventory.adjust'))->toBeFalse()
        ->and($user->can('roles.manage'))->toBeFalse();
});

it('lets a super_admin reach admin users and roles screens', function () {
    $this->actingAs(adminWithRole('super_admin'));

    $this->get('/admin/users')->assertOk();
    $this->get('/admin/roles')->assertOk();
});

it('forbids non-super-admins from user and role management screens', function () {
    $this->actingAs(adminWithRole('orders_staff'));

    $this->get('/admin/users')->assertForbidden();
    $this->get('/admin/roles')->assertForbidden();
});

it('refuses dashboard access to a deactivated admin', function () {
    $user = adminWithRole('manager');
    $user->update(['is_active' => false]);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('cannot deactivate the last active super_admin', function () {
    $only = adminWithRole('super_admin');

    expect(fn () => $only->update(['is_active' => false]))->toThrow(RuntimeException::class);
    expect($only->fresh()->is_active)->toBeTrue();
});

it('allows deactivating a super_admin when another active one remains', function () {
    $first = adminWithRole('super_admin');
    $second = adminWithRole('super_admin');

    $first->update(['is_active' => false]);

    expect($first->fresh()->is_active)->toBeFalse()
        ->and($second->fresh()->is_active)->toBeTrue();
});

it('guards role removal from the last active super_admin', function () {
    $only = adminWithRole('super_admin');
    $guard = app(SuperAdminGuard::class);

    expect(fn () => $guard->assertRolesKeepSuperAdmin($only, ['manager']))->toThrow(RuntimeException::class);
    expect(fn () => $guard->assertRolesKeepSuperAdmin($only, ['super_admin', 'manager']))->not->toThrow(RuntimeException::class);
});

it('still enforces business rules regardless of permission (no negative stock)', function () {
    $manager = adminWithRole('manager');
    $this->actingAs($manager);
    $product = Product::factory()->withUnits(conversion: 12, subStock: 0)->create();
    $sub = $product->subUnit()->first();

    expect(fn () => app(AdjustInventoryAction::class)->remove($product, $sub, 5, null, $manager->id))
        ->toThrow(InsufficientStockException::class);
});
