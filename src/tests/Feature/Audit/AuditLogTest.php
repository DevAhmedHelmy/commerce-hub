<?php

declare(strict_types=1);

use App\Domain\Audit\AdminAuditService;
use App\Domain\Inventory\AdjustInventoryAction;
use App\Domain\Inventory\InsufficientStockException;
use App\Domain\Settings\SettingsService;
use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\AdminAuditLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Setting;

beforeEach(function () {
    $this->admin = superAdmin();
});

it('audits a product update with old/new values and the actor', function () {
    $this->actingAs($this->admin);
    $product = Product::factory()->create(['name_ar' => 'اسم قديم']);

    $product->update(['name_ar' => 'اسم جديد']);

    $log = AdminAuditLog::query()
        ->where('auditable_type', Product::class)->where('auditable_id', $product->id)
        ->where('action', 'updated')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->old_values['name_ar'])->toBe('اسم قديم')
        ->and($log->new_values['name_ar'])->toBe('اسم جديد')
        ->and($log->user_id)->toBe($this->admin->id);
});

it('records a base price change as price_changed with old/new minor units', function () {
    $this->actingAs($this->admin);
    $product = Product::factory()->withUnits(conversion: 12, subStock: 0, primaryPrice: 120000)->create();
    $primary = $product->primaryUnit()->first();

    $primary->update(['base_price' => 130000]);

    $log = AdminAuditLog::query()
        ->where('auditable_type', ProductUnit::class)->where('auditable_id', $primary->id)
        ->where('action', 'price_changed')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->old_values['base_price'])->toBe(120000)
        ->and($log->new_values['base_price'])->toBe(130000);
});

it('audits a manual stock add and references the inventory ledger row', function () {
    $this->actingAs($this->admin);
    $product = Product::factory()->withUnits(conversion: 12, subStock: 0)->create();
    $sub = $product->subUnit()->first();

    $adjustment = app(AdjustInventoryAction::class)->add($product, $sub, 7, 'restock', $this->admin->id);

    $log = AdminAuditLog::query()
        ->where('auditable_type', Product::class)->where('auditable_id', $product->id)
        ->where('action', 'stock_added')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->metadata['inventory_adjustment_id'])->toBe($adjustment->id)
        ->and($log->new_values['stock_quantity'])->toBe(7);
});

it('does not write a false-success audit when the stock change fails', function () {
    $this->actingAs($this->admin);
    $product = Product::factory()->withUnits(conversion: 12, subStock: 0)->create();
    $sub = $product->subUnit()->first();
    $before = AdminAuditLog::query()->where('action', 'stock_removed')->count();

    expect(fn () => app(AdjustInventoryAction::class)->remove($product, $sub, 5, null, $this->admin->id))
        ->toThrow(InsufficientStockException::class);

    expect(AdminAuditLog::query()->where('action', 'stock_removed')->count())->toBe($before);
});

it('audits a business setting change with the changed key', function () {
    $this->actingAs($this->admin);

    app(SettingsService::class)->update(['minimum_order_amount' => '50000']);

    $log = AdminAuditLog::query()
        ->where('auditable_type', Setting::class)->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->metadata['key'])->toBe('minimum_order_amount')
        ->and($log->new_values['value'])->toBe('50000');
});

it('redacts sensitive fields centrally', function () {
    $log = app(AdminAuditService::class)->record(
        'updated',
        null,
        ['password' => 'old-secret'],
        ['password' => 'new-secret', 'api_token' => 'abc', 'name' => 'Ahmed'],
    );

    expect($log->new_values['password'])->toBe('[redacted]')
        ->and($log->new_values['api_token'])->toBe('[redacted]')
        ->and($log->new_values['name'])->toBe('Ahmed')
        ->and($log->old_values['password'])->toBe('[redacted]');
});

it('lets an admin view the audit log list and a record detail', function () {
    $this->actingAs($this->admin);
    $product = Product::factory()->create(['name_ar' => 'قديم']);
    $product->update(['name_ar' => 'جديد']);
    $log = AdminAuditLog::query()->latest('id')->first();

    $this->get('/admin/audit-logs')->assertOk();
    $this->get("/admin/audit-logs/{$log->id}")->assertOk();
});

it('blocks guests and customers from the audit log', function () {
    $this->get('/admin/audit-logs')->assertRedirect();

    $customer = Customer::factory()->onboarded()->create();
    $this->actingAs($customer, 'customer')->get('/admin/audit-logs')->assertRedirect();
});

it('exposes no create/edit/delete on the audit resource', function () {
    expect(AuditLogResource::canCreate())->toBeFalse()
        ->and(AuditLogResource::canEdit(new AdminAuditLog()))->toBeFalse()
        ->and(AuditLogResource::canDelete(new AdminAuditLog()))->toBeFalse()
        ->and(AuditLogResource::canDeleteAny())->toBeFalse();
});
