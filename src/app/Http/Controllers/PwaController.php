<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * PWA manifest (US12, R17). Arabic-first, standalone, with 192/512 + maskable icons. The service
 * worker (public/sw.js) caches static assets only and never authenticated content.
 */
class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => 'إمداد — مستلزمات المطاعم',
            'short_name' => 'إمداد',
            'description' => 'منصة طلب مستلزمات المطاعم — دفع عند الاستلام.',
            'lang' => 'ar',
            'dir' => 'rtl',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#03488E',
            'icons' => [
                ['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icons/maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }
}
