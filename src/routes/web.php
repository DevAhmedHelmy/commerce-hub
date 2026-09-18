<?php

use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\Auth\OtpController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Customer authentication & onboarding (US1 / Phase C)
|--------------------------------------------------------------------------
| Phone → OTP → verify → session, then first-time profile + default address.
| Auth endpoints are rate-limited and CSRF-protected (web group) (FR-010).
*/

Route::middleware('guest:customer')->group(function () {
    Route::get('/login', [OtpController::class, 'showLogin'])->name('login');
    Route::post('/otp/request', [OtpController::class, 'request'])
        ->middleware('throttle:10,1')->name('otp.request');
    Route::get('/verify', [OtpController::class, 'showVerify'])->name('otp.verify.show');
    Route::post('/otp/verify', [OtpController::class, 'verify'])
        ->middleware('throttle:10,1')->name('otp.verify');
    Route::post('/otp/resend', [OtpController::class, 'resend'])
        ->middleware('throttle:10,1')->name('otp.resend');
});

Route::middleware('auth:customer')->group(function () {
    Route::post('/logout', [OtpController::class, 'logout'])->name('logout');

    // Onboarding (authenticated but not yet gated by completion).
    Route::get('/onboarding/profile', [OnboardingController::class, 'showProfile'])->name('onboarding.profile');
    Route::post('/onboarding/profile', [OnboardingController::class, 'saveProfile'])->name('onboarding.profile.save');
    Route::get('/onboarding/address', [OnboardingController::class, 'showAddress'])->name('onboarding.address');
    Route::post('/onboarding/address', [OnboardingController::class, 'saveAddress'])->name('onboarding.address.save');

    // Ordering entry — onboarding-gated. Phase D (T045) replaces this with the real home hub (C06).
    Route::get('/home', fn () => view('home'))->middleware('onboarded')->name('home');
});

/*
|--------------------------------------------------------------------------
| PWA foundation (Phase A)
|--------------------------------------------------------------------------
| Minimal manifest + offline fallback. The full service-worker caching
| strategy (static-only; never caches authenticated routes) is built in
| Phase M. Metadata is Arabic now and localizable later.
*/

Route::get('/manifest.webmanifest', function () {
    return response()->json([
        'name' => 'مستلزمات المطاعم',
        'short_name' => 'المستلزمات',
        'description' => 'منصة طلب مستلزمات المطاعم — دفع عند الاستلام.',
        'lang' => 'ar',
        'dir' => 'rtl',
        'start_url' => '/',
        'scope' => '/',
        'display' => 'standalone',
        'background_color' => '#ffffff',
        'theme_color' => '#0f766e',
        // icons[] are added in Phase M (T114/T115).
    ], 200, ['Content-Type' => 'application/manifest+json']);
})->name('pwa.manifest');

Route::view('/offline', 'offline')->name('pwa.offline');
