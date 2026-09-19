<?php

declare(strict_types=1);

use App\Models\Category;

it('renders the landing page with a sign-in CTA', function () {
    Category::factory()->create(['name_ar' => 'تصنيف الهبوط', 'is_active' => true]);

    $this->get('/')
        ->assertOk()
        ->assertSee('تصنيف الهبوط')
        ->assertSee(route('login'), false);
});

it('renders gracefully with no catalog content', function () {
    $this->get('/')->assertOk();
});
