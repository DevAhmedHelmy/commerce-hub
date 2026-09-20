<?php

declare(strict_types=1);

use App\Models\DeliveryArea;

it('lets a delivery manager reach delivery admin screens', function () {
    $this->actingAs(adminWithRole('manager'));

    $this->get('/admin/delivery-areas')->assertOk();
    $this->get('/admin/delivery-slots')->assertOk();
    $this->get('/admin/delivery-discount-rules')->assertOk();
    $this->get('/admin/manage-settings')->assertOk();
});

it('forbids delivery admin screens for orders_staff', function () {
    $this->actingAs(adminWithRole('orders_staff'));

    $this->get('/admin/delivery-areas')->assertForbidden();
    $this->get('/admin/delivery-discount-rules')->assertForbidden();
});

it('renders delivery area create/edit forms', function () {
    $this->actingAs(superAdmin());
    $area = DeliveryArea::factory()->create();

    $this->get('/admin/delivery-areas/create')->assertOk();
    $this->get("/admin/delivery-areas/{$area->id}/edit")->assertOk();
});

it('saves settings through the settings page service', function () {
    $this->actingAs(superAdmin());

    $this->get('/admin/manage-settings')->assertOk();

    app(App\Domain\Settings\SettingsService::class)->update(['minimum_order_amount' => '75000']);
    expect(app(App\Domain\Settings\SettingsService::class)->minimumOrderAmount()->minorUnits)->toBe(75000);
});
