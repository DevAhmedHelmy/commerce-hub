<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\Product;

beforeEach(function () {
    $this->customer = Customer::factory()->onboarded()->create();
});

it('finds products by Arabic name', function () {
    Product::factory()->create(['name_ar' => 'طماطم مصرية', 'brand' => 'Farm Frites']);
    Product::factory()->create(['name_ar' => 'بطاطس مجمدة', 'brand' => 'Almarai']);

    $response = $this->actingAs($this->customer, 'customer')->get('/search?q=طماطم');

    $response->assertOk()->assertSee('طماطم مصرية')->assertDontSee('بطاطس مجمدة');
});

it('finds products by brand including Latin brand names', function () {
    Product::factory()->create(['name_ar' => 'كاتشب', 'brand' => 'Heinz']);
    Product::factory()->create(['name_ar' => 'عصير', 'brand' => 'Juhayna']);

    $response = $this->actingAs($this->customer, 'customer')->get('/search?q=Heinz');

    $response->assertOk()->assertSee('كاتشب')->assertDontSee('عصير');
});

it('shows an empty-result state when nothing matches', function () {
    Product::factory()->create(['name_ar' => 'أرز']);

    $response = $this->actingAs($this->customer, 'customer')->get('/search?q=لا-يوجد-هذا');

    $response->assertOk()->assertSee(__('messages.states.empty_title'));
});

it('excludes inactive products from search results', function () {
    Product::factory()->inactive()->create(['name_ar' => 'منتج مخفي فريد']);

    $response = $this->actingAs($this->customer, 'customer')->get('/search?q=مخفي فريد');

    $response->assertOk()->assertSee(__('messages.states.empty_title'));
});
