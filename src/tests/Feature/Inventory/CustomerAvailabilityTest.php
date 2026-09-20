<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductUnit;

beforeEach(function () {
    $this->customer = Customer::factory()->onboarded()->create();
});

it('shows an admin-available product as out of stock when every unit has zero stock', function () {
    $product = Product::factory()->create(['name_ar' => 'دقيق فاخر']); // availability = available
    ProductUnit::factory()->outOfStock()->create(['product_id' => $product->id]);

    $response = $this->actingAs($this->customer, 'customer')->get("/products/{$product->id}");

    $response->assertOk()
        ->assertSee('دقيق فاخر')
        ->assertSee(__('domain.availability.out_of_stock'));
});

it('shows a product as available when it has an in-stock active unit', function () {
    $product = Product::factory()->create(['name_ar' => 'زيت ذرة']);
    ProductUnit::factory()->stock(15)->create(['product_id' => $product->id]);

    $response = $this->actingAs($this->customer, 'customer')->get("/products/{$product->id}");

    $response->assertOk()
        ->assertSee('زيت ذرة')
        ->assertSee(__('domain.availability.available'));
});
