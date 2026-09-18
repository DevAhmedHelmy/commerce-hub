<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;

beforeEach(function () {
    $this->customer = Customer::factory()->onboarded()->create();
});

it('lists available and out-of-stock products but hides inactive ones', function () {
    $category = Category::factory()->create();
    $available = Product::factory()->for($category)->create(['name_ar' => 'أرز أبيض']);
    $outOfStock = Product::factory()->for($category)->outOfStock()->create(['name_ar' => 'سكر ناعم']);
    $inactive = Product::factory()->for($category)->inactive()->create(['name_ar' => 'زيت مخفي']);

    $response = $this->actingAs($this->customer, 'customer')->get("/categories/{$category->id}");

    $response->assertOk()
        ->assertSee('أرز أبيض')
        ->assertSee('سكر ناعم')
        ->assertDontSee('زيت مخفي');
});

it('hides inactive categories from the categories hub', function () {
    Category::factory()->create(['name_ar' => 'تصنيف ظاهر']);
    Category::factory()->inactive()->create(['name_ar' => 'تصنيف مخفي']);

    $response = $this->actingAs($this->customer, 'customer')->get('/categories');

    $response->assertOk()
        ->assertSee('تصنيف ظاهر')
        ->assertDontSee('تصنيف مخفي');
});

it('shows an empty state for a category with no visible products', function () {
    $category = Category::factory()->create();
    Product::factory()->for($category)->inactive()->create();

    $response = $this->actingAs($this->customer, 'customer')->get("/categories/{$category->id}");

    $response->assertOk()->assertSee(__('messages.states.empty_title'));
});

it('lets a customer view an out-of-stock product but marks it not orderable', function () {
    $product = Product::factory()->outOfStock()->create(['name_ar' => 'جبنة رومي']);

    $response = $this->actingAs($this->customer, 'customer')->get("/products/{$product->id}");

    $response->assertOk()
        ->assertSee('جبنة رومي')
        ->assertSee(__('domain.availability.out_of_stock'));
});

it('does not expose an inactive product detail', function () {
    $product = Product::factory()->inactive()->create();

    $this->actingAs($this->customer, 'customer')
        ->get("/products/{$product->id}")
        ->assertNotFound();
});
