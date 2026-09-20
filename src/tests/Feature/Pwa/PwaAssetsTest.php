<?php

declare(strict_types=1);

it('serves the manifest with icons and standalone display', function () {
    $this->get('/manifest.webmanifest')
        ->assertOk()
        ->assertJsonPath('display', 'standalone')
        ->assertJsonFragment(['sizes' => '192x192'])
        ->assertJsonFragment(['sizes' => '512x512']);
});

it('serves an offline fallback route', function () {
    $this->get('/offline')->assertOk();
});

it('has a service worker that never caches authenticated routes', function () {
    $sw = file_get_contents(public_path('sw.js'));

    expect($sw)->toContain('PRIVATE_PREFIXES')
        ->toContain('/checkout')
        ->toContain('/orders')
        ->toContain('/cart')
        ->toContain('/admin')
        ->toContain('/otp');
});
