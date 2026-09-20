/*
 * Service worker (US12, R17). Caches STATIC assets only. Authenticated/dynamic content is never
 * cached (privacy + integrity): OTP/auth, profile, addresses, cart, checkout, orders, admin.
 * No offline order creation, no background sync. Bump CACHE_VERSION to invalidate old assets.
 */
const CACHE_VERSION = 'emdad-static-v1';
const OFFLINE_URL = '/offline';

const STATIC_ASSETS = [
    OFFLINE_URL,
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/manifest.webmanifest',
];

// Never cache these authenticated/dynamic path prefixes.
const PRIVATE_PREFIXES = [
    '/login', '/verify', '/otp', '/onboarding', '/account',
    '/cart', '/checkout', '/orders', '/profile', '/admin',
];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE_VERSION).then((cache) => cache.addAll(STATIC_ASSETS)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_VERSION).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

function isPrivate(url) {
    return PRIVATE_PREFIXES.some((p) => url.pathname === p || url.pathname.startsWith(p + '/'));
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Authenticated/dynamic: always network, never cached; offline → honest fallback for navigations.
    if (isPrivate(url)) {
        event.respondWith(
            fetch(request).catch(() =>
                request.mode === 'navigate' ? caches.match(OFFLINE_URL) : Response.error()
            )
        );
        return;
    }

    // Static/public: cache-first, then network; fall back to offline page for navigations.
    event.respondWith(
        caches.match(request).then((cached) =>
            cached ||
            fetch(request)
                .then((response) => {
                    if (response.ok && ['style', 'script', 'image'].includes(request.destination)) {
                        const copy = response.clone();
                        caches.open(CACHE_VERSION).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(() => (request.mode === 'navigate' ? caches.match(OFFLINE_URL) : Response.error()))
        )
    );
});
