/*
 * Service worker — Phase A foundation placeholder.
 *
 * The full caching strategy is implemented in Phase M (T116):
 *   - cache ONLY versioned static assets (CSS/JS), icons, logo, and the offline fallback
 *   - NEVER cache authenticated/customer-specific responses
 *     (/profile, /profile/address, /cart*, /checkout/*, /orders*, /onboarding/*, /otp/*, /verify)
 *   - no offline order creation, no background sync
 *
 * For now this SW installs and activates without caching anything, so registration
 * succeeds and can be progressively enhanced later without a re-architecture.
 */
self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

// Intentionally no 'fetch' handler yet: all requests go straight to the network
// (Phase M adds static-only caching + offline fallback).
