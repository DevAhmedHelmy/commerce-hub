<?php

declare(strict_types=1);

use App\Models\AdminAuditLog;
use App\Models\Category;
use App\Models\LandingPageSetting;
use App\Models\LandingSection;

it('renders active sections in order and hides inactive ones', function () {
    LandingPageSetting::singleton();
    LandingSection::create(['key' => 'features', 'title_ar' => 'قسم ظاهر', 'is_active' => true, 'sort_order' => 1]);
    LandingSection::create(['key' => 'about', 'title_ar' => 'قسم مخفي', 'is_active' => false, 'sort_order' => 2]);

    $this->get('/')->assertOk()->assertSee('قسم ظاهر')->assertDontSee('قسم مخفي');
});

it('reflects an admin-edited hero title on the landing page', function () {
    LandingPageSetting::singleton()->update(['hero_title_ar' => 'عنوان مُحدَّث من الإدارة']);
    LandingSection::create(['key' => 'hero', 'is_active' => true, 'sort_order' => 0]);

    $this->get('/')->assertOk()->assertSee('عنوان مُحدَّث من الإدارة');
});

it('shows active categories from the Category model and excludes inactive', function () {
    LandingSection::create(['key' => 'categories', 'is_active' => true, 'sort_order' => 0, 'settings' => ['limit' => 8]]);
    Category::factory()->create(['name_ar' => 'تصنيف ظاهر', 'is_active' => true]);
    Category::factory()->create(['name_ar' => 'تصنيف مخفي', 'is_active' => false]);

    $this->get('/')->assertSee('تصنيف ظاهر')->assertDontSee('تصنيف مخفي');
});

it('never renders unsafe social link schemes', function () {
    LandingPageSetting::singleton()->update(['facebook_url' => 'javascript:alert(1)']);
    LandingSection::create(['key' => 'contact', 'is_active' => true, 'sort_order' => 0]);

    $this->get('/')->assertOk()->assertDontSee('javascript:alert(1)', false);
});

it('audits an admin landing-settings change', function () {
    $this->actingAs(superAdmin());

    LandingPageSetting::singleton()->update(['hero_title_ar' => 'عنوان جديد']);

    expect(AdminAuditLog::query()->where('auditable_type', LandingPageSetting::class)->where('action', 'updated')->exists())
        ->toBeTrue();
});

it('gates landing management by permission', function () {
    $this->actingAs(adminWithRole('manager'))->get('/admin/landing-sections')->assertOk();
    $this->actingAs(adminWithRole('manager'))->get('/admin/manage-landing-page')->assertOk();

    $this->actingAs(adminWithRole('orders_staff'))->get('/admin/landing-sections')->assertForbidden();
    $this->actingAs(adminWithRole('orders_staff'))->get('/admin/manage-landing-page')->assertForbidden();
});
