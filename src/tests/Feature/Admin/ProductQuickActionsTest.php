<?php

declare(strict_types=1);

use App\Domain\Inventory\AdjustInventoryAction;
use App\Domain\Ordering\OrderService;
use App\Models\AdminAuditLog;
use App\Models\Product;

it('preserves historical order prices when the unit base price is later edited (mandatory)', function () {
    ['customer' => $customer, 'input' => $input, 'sub' => $sub] = checkoutSetup(qty: 2, subStock: 100, subPrice: 50000);

    $order = app(OrderService::class)->place($customer, $input)->order;
    $item = $order->items()->first();
    expect($item->applied_unit_price)->toBe(50000);

    // Quick Edit Price changes the unit's base price (future orders only).
    $sub->update(['base_price' => 88000]);

    // Historical snapshot is immutable; new price is live for future pricing.
    expect($order->items()->first()->applied_unit_price)->toBe(50000)
        ->and($order->items()->first()->line_total)->toBe(100000)
        ->and($sub->fresh()->base_price)->toBe(88000);
});

it('adds stock through the domain by primary unit (normalized) and audits it', function () {
    $this->actingAs(superAdmin());
    $product = Product::factory()->withUnits(conversion: 12, subStock: 0)->create();
    $primary = $product->primaryUnit()->first();

    app(AdjustInventoryAction::class)->add($product, $primary, 10, 'استلام', auth()->id());

    expect($product->fresh()->subStock())->toBe(120) // 10 cartons × 12
        ->and(AdminAuditLog::query()->where('action', 'stock_added')->where('auditable_id', $product->id)->exists())->toBeTrue();
});

it('renders the admin products page with stock + quick actions for a manager', function () {
    Product::factory()->withUnits()->create();

    $this->actingAs(adminWithRole('manager'))->get('/admin/products')->assertOk();
});

it('records a base-price change as an audit price_changed event', function () {
    $this->actingAs(superAdmin());
    $product = Product::factory()->withUnits(conversion: 12, subStock: 0, primaryPrice: 120000)->create();

    $product->primaryUnit()->first()->update(['base_price' => 130000]);

    $log = AdminAuditLog::query()->where('action', 'price_changed')->latest('id')->first();
    expect($log)->not->toBeNull()
        ->and($log->old_values['base_price'])->toBe(120000)
        ->and($log->new_values['base_price'])->toBe(130000);
});
