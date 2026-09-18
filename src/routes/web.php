<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
