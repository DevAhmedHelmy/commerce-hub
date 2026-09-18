<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create();
});

it('renders the admin category resource pages', function () {
    $this->actingAs($this->admin)->get('/admin/categories')->assertOk();
    $this->actingAs($this->admin)->get('/admin/categories/create')->assertOk();
});

it('renders the admin product list and tabbed create form', function () {
    $this->actingAs($this->admin)->get('/admin/products')->assertOk();
    $this->actingAs($this->admin)->get('/admin/products/create')->assertOk();
});

it('renders the product edit page with the selling-units relation manager', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)->get("/admin/products/{$product->id}/edit")->assertOk();
});

it('blocks guests from the admin panel', function () {
    $this->get('/admin/products')->assertRedirect();
});
