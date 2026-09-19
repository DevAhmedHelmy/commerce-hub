<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Catalog\CatalogController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Checkout\ConfirmController;
use App\Http\Controllers\Orders\OrdersController;
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

    // Customer catalog (US2) — onboarding-gated ordering surface.
    Route::middleware('onboarded')->group(function () {
        Route::get('/home', [CatalogController::class, 'home'])->name('home');
        Route::get('/categories', [CatalogController::class, 'categories'])->name('categories.index');
        Route::get('/categories/{category}', [CatalogController::class, 'category'])->name('categories.show');
        Route::get('/search', [CatalogController::class, 'search'])->name('search');
        Route::get('/products/{product}', [CatalogController::class, 'product'])->name('products.show');

        // Cart (US3 / Phase F) — persistent DB cart; server-recomputed; no stock reservation.
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
        Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
        Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');

        // Checkout (US4 / Phase H+I) — two steps + transactional placement.
        Route::get('/checkout/delivery', [CheckoutController::class, 'delivery'])->name('checkout.delivery');
        Route::post('/checkout/delivery', [CheckoutController::class, 'deliveryStore'])->name('checkout.delivery.store');
        Route::get('/checkout/review', [CheckoutController::class, 'review'])->name('checkout.review');
        Route::post('/checkout/confirm', [ConfirmController::class, 'confirm'])->name('checkout.confirm');

        // Orders (US9 / Phase I+J) — customer history, details, self-cancel.
        Route::get('/orders', [OrdersController::class, 'index'])->name('orders.index');
        Route::get('/orders/success/{order}', [OrdersController::class, 'success'])->name('orders.success');
        Route::get('/orders/{order}', [OrdersController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [OrdersController::class, 'cancel'])->name('orders.cancel');

        // Account (US1 / prompt 44) — persistent profile + default address (edited here, not checkout).
        Route::get('/account', [ProfileController::class, 'show'])->name('account.index');
        Route::get('/account/profile', [ProfileController::class, 'editProfile'])->name('account.profile');
        Route::post('/account/profile', [ProfileController::class, 'updateProfile'])->name('account.profile.update');
        Route::get('/account/address', [ProfileController::class, 'editAddress'])->name('account.address');
        Route::post('/account/address', [ProfileController::class, 'updateAddress'])->name('account.address.update');
    });
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
