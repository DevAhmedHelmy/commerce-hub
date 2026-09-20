<?php

declare(strict_types=1);

use App\Domain\Landing\LandingPageService;
use App\Models\LandingPageSetting;
use App\Models\LandingSection;

it('persists branding settings and exposes company name + logo', function () {
    LandingPageSetting::singleton()->update([
        'company_name_ar' => 'شركة الاختبار',
        'logo_light_path' => 'branding/logo.png',
    ]);
    app(LandingPageService::class)->flush();

    $svc = app(LandingPageService::class);
    expect($svc->companyName())->toBe('شركة الاختبار')
        ->and($svc->logoUrl('light'))->toContain('branding/logo.png');
});

it('falls back to a company-name text brand when no logo is configured', function () {
    LandingPageSetting::singleton()->update(['company_name_ar' => 'بدون شعار', 'logo_light_path' => null]);
    app(LandingPageService::class)->flush();

    $svc = app(LandingPageService::class);
    expect($svc->logoUrl('light'))->toBeNull()
        ->and($svc->companyName())->toBe('بدون شعار');
});

it('renders the configured company name on the landing header (not framework branding)', function () {
    LandingPageSetting::singleton()->update(['company_name_ar' => 'علامة العميل']);
    LandingSection::create(['key' => 'hero', 'is_active' => true, 'sort_order' => 0]);
    app(LandingPageService::class)->flush();

    $this->get('/')->assertOk()->assertSee('علامة العميل')->assertDontSee('Laravel', false);
});

it('brands the admin panel with the client name and shows no Filament promo widget', function () {
    LandingPageSetting::singleton()->update(['company_name_ar' => 'لوحة العميل']);
    app(LandingPageService::class)->flush();

    // The Filament promo/info widget content (Documentation/GitHub links) must be gone.
    // (Note: compiled asset URLs still contain "filament" — that's not brand identity.)
    $this->actingAs(superAdmin())->get('/admin')
        ->assertOk()
        ->assertSee('لوحة العميل')
        ->assertDontSee('Documentation', false)
        ->assertDontSee('filamentphp.com', false);
});
