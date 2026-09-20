<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\LandingPageSetting;
use App\Models\LandingSection;

it('renders the landing page with a sign-in link and dynamic categories', function () {
    LandingPageSetting::singleton();
    LandingSection::create(['key' => 'categories', 'title_ar' => 'التصنيفات', 'is_active' => true, 'sort_order' => 1, 'settings' => ['limit' => 8]]);
    Category::factory()->create(['name_ar' => 'تصنيف الهبوط', 'is_active' => true]);

    $this->get('/')
        ->assertOk()
        ->assertSee('تصنيف الهبوط')
        ->assertSee(route('login'), false);
});

it('renders gracefully with no landing content', function () {
    $this->get('/')->assertOk();
});
