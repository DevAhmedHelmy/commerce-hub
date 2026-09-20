<?php

declare(strict_types=1);

use App\Domain\Support\MediaService;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('constrains image type and size via reusable rules (prompt 34)', function () {
    $rules = MediaService::imageRules();

    expect($rules)->toContain('image')
        ->toContain('mimes:jpeg,png,webp')
        ->toContain('max:'.MediaService::MAX_KILOBYTES);
});

it('stores an uploaded image under a deterministic ULID path', function () {
    Storage::fake('public');
    $service = new MediaService('public');

    $path = $service->store(UploadedFile::fake()->create('ketchup.jpg', 200, 'image/jpeg'), 'products');

    expect($path)->toStartWith('products/')->toEndWith('.jpg');
    Storage::disk('public')->assertExists($path);
});

it('replaces and removes a stored image', function () {
    Storage::fake('public');
    $service = new MediaService('public');

    $first = $service->store(UploadedFile::fake()->create('a.png', 100, 'image/png'), 'products');
    $second = $service->store(UploadedFile::fake()->create('b.png', 100, 'image/png'), 'products');
    $service->delete($first); // replace = store new + delete old

    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);
});

it('renders a fallback for a product with no image', function () {
    $customer = Customer::factory()->onboarded()->create();
    $product = Product::factory()->withUnits()->create(['name_ar' => 'منتج بلا صورة', 'image_path' => null]);

    $this->actingAs($customer, 'customer')
        ->get("/products/{$product->id}")
        ->assertOk()
        ->assertDontSee('/storage/', false); // no image URL rendered
});

it('renders the product image when present', function () {
    $customer = Customer::factory()->onboarded()->create();
    $product = Product::factory()->withUnits()->create(['image_path' => 'products/abc.jpg']);

    $this->actingAs($customer, 'customer')
        ->get("/products/{$product->id}")
        ->assertOk()
        ->assertSee('products/abc.jpg', false);
});
