import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Keep the browser UI (address bar) colour in sync with the theme token, so the brand
// colour is never hard-coded in a component and follows system light/dark (theme.css).
function syncThemeColor() {
    const meta = document.querySelector('meta[name="theme-color"]');
    if (!meta) return;
    const primary = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim();
    if (primary) meta.setAttribute('content', primary);
}

syncThemeColor();
window.matchMedia?.('(prefers-color-scheme: dark)').addEventListener?.('change', syncThemeColor);
// Re-sync the browser UI colour when the user switches Light/Dark/System (theme-switcher).
window.addEventListener('themechange', syncThemeColor);

// PWA service worker registration (foundation only; full caching strategy in Phase M).
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            /* SW registration is best-effort; never block the app. */
        });
    });
}
