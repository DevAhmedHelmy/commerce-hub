# Quickstart — Restaurant Supplies Ordering MVP

**Feature**: `001-restaurant-supplies-mvp` · **Phase**: 1 (design) · **Date**: 2026-09-17
**Companion to**: [plan.md](./plan.md), [research.md](./research.md).

> Setup + deployment **reference** for the implementation phase. No project scaffolding is created by
> planning. Commands are indicative; run them during implementation (`/speckit-tasks` → build).

## 1. Verified stack (do not exceed)

| Component | Version / choice | Verified constraint (Packagist, 2026-09-18) |
|---|---|---|
| PHP | **8.2** (hard max) | Laravel 12 min = 8.2; **no dep may require 8.3+** |
| Laravel | **12.x** | requires PHP ≥ 8.2 (Laravel 13 needs 8.3+, so 12 is retained) |
| MySQL | **8.x** | single database |
| Admin | **Filament v5.x** (`^5.8`) | `filament/filament` `php ^8.2`; `filament/support` `illuminate/contracts ^11.28\|^12.0\|^13.0` → **L12 OK**; needs `ext-intl`; **no 8.3+** |
| UI | Blade + **Alpine.js** + **Tailwind CSS** | customer PWA |
| Tests | **Pest** (or PHPUnit) | commercial-logic tests mandatory |

Greenfield → **current supported Filament major (v5)**, core only, no unnecessary plugins (R0).
No Redis / WebSockets / Reverb / Scout / Meilisearch / Elasticsearch / Docker-in-prod / S3 for MVP
(research R1, R15–R18, R23).

**PHP 8.2 dependency safety (R0a)**: pin Composer's platform to production PHP —
`config.platform.php = 8.2.x` (exact patch at foundation) — so resolution can never pull a PHP-8.3+
package; run **`composer check-platform-reqs`** as a CI/deploy gate; verify any new package's
`require` (php + illuminate) before adding it.

## 2. Local development

**Prerequs**: PHP 8.2 (+ extensions §5), Composer, MySQL 8, Node 18+ (asset build only).

```bash
# after Laravel app + Filament are installed in the implementation phase:
composer install
cp .env.example .env && php artisan key:generate
# configure DB in .env (see §4), then:
php artisan migrate --seed          # schema + demo/seed data
php artisan storage:link            # local public image disk
npm install && npm run dev          # Tailwind + Alpine assets (dev)
php artisan serve                   # http://127.0.0.1:8000  (admin at /admin)
```

**Demo/dev OTP** (§48): `OTP_DRIVER=log` — the code is surfaced via a dev-only channel/banner
(never in production). Seed at least one admin user, a few categories/products/units/tiers/offers, a
delivery area + slots + a discount rule, and settings (minimum order + business info).

## 3. Test suite (Constitution Principle VII / research R22)

```bash
php artisan test                    # or: ./vendor/bin/pest
```
Mandatory coverage: pricing tiers & **lower-of offer/tier**; delivery discounts (largest-saving,
tie-break, floor-0); minimum order; `Money` math/rounding; status transitions; OTP flow; cart;
**checkout revalidation / changed-terms**; order creation + snapshot immutability; cancellation;
admin status update. Boundary tests: tier **4→5, 9→10**; min **499 blocked / 500 allowed**; discount
thresholds; expired offer; inactive area; inactive slot; out-of-stock; double-submit idempotency.

## 4. Environment configuration (`.env`) — key values

```
APP_ENV=local|staging|production      APP_DEBUG=false (staging/prod)   APP_URL=https://…
APP_LOCALE=ar                          APP_FALLBACK_LOCALE=ar          # Arabic default+fallback (R13)
DB_CONNECTION=mysql  DB_HOST=… DB_DATABASE=… DB_USERNAME=… DB_PASSWORD=…   # secret; never committed
SESSION_DRIVER=database  CACHE_STORE=database|file  QUEUE_CONNECTION=sync   # MVP default = sync; no Redis (R16)
FILESYSTEM_DISK=public                 # local image storage (R18)
OTP_DRIVER=log (non-prod) | sms|whatsapp (prod)   OTP_TTL_SECONDS=…  OTP_RESEND_COOLDOWN=…  # (R7)
MAIL_… (optional)                      # secrets via env only; .env is git-ignored (Principle VI)
```
Production hardening: `APP_DEBUG=false`, HTTPS, `SESSION_SECURE_COOKIE=true`, strong `APP_KEY`, no
test/static OTP behavior (§48). **No secrets in git** — only `.env.example` placeholders.

## 5. Production hosting (low-cost shared hosting; no Docker) — research R23 / §47

**Required**: PHP **8.2** FPM with extensions: `intl` (Filament), `pdo_mysql`, `mbstring`, `openssl`,
`tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` (or `imagick`). MySQL **8**. HTTPS.

**Deploy steps** (indicative):
```bash
composer install --no-dev --optimize-autoloader   # platform pinned to PHP 8.2 (R0a)
composer check-platform-reqs                       # deploy gate: fails if any dep needs PHP 8.3+
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
# assets built in CI/locally and uploaded:
npm ci && npm run build
```
**Web root** → `public/`. **Writable**: `storage/`, `bootstrap/cache`. **Cron** (single entry):
```
* * * * * php /path/artisan schedule:run >> /dev/null 2>&1
```
**Queue = synchronous by default (R16)** — no persistent worker, no Supervisor, no Redis. The single
cron runs **scheduled maintenance** (e.g. expired-OTP cleanup). It also drains a **database queue**
(`queue:work --stop-when-empty`) **only if** the async OTP fallback is later enabled (not required by
the sync-default MVP). **Backups**: nightly MySQL dump + `storage/`. **Logs**: Laravel daily logs;
**OTP codes must never appear in logs** (Principle VI/R7).

## 6. Upgrade path (no redesign) — research R23

```
Shared hosting (MVP)
  → VPS (persistent PHP-FPM)
    → Redis (cache + queue)  → persistent queue worker (supervisor)   # only if async work is added
      → object storage + CDN for media (config swap; MediaService abstraction, R18)
        → when hosting supports PHP 8.3+: evaluate Laravel major upgrade (Laravel 12 security EOL ~Feb 2027)
          → optional JSON API (Sanctum) for a Flutter client
```
All are **config/infrastructure** changes enabled by the modular monolith + service layer; no
business-logic rewrite (Constitution Principle I & IV). **Filament is already the current major (v5)**
— no Filament upgrade is pending. **Laravel 12 lifecycle note (R23)**: Laravel 12 is in its
security-fixes period (EOL ~**Feb 2027**); it is retained for MVP because PHP 8.2 is a hard server
constraint (Laravel 13 needs PHP 8.3+). Upgrading Laravel is a documented maintenance trigger, **not**
MVP scope, and does not block implementation.

## 7. Localization readiness (Arabic-only MVP) — research R13/R14, design D4

- `lang/ar/` translation catalog is the default+fallback; `lang/en/` added later (no switcher in MVP).
- Domain identifiers neutral (statuses/unit codes/discount types); labels are translations.
- Managed content (category/product/unit/offer/area/slot display text + localizable settings) uses
  **paired `*_ar` (required) / `*_en` (nullable) columns from day one** (R14, authoritative) resolved
  by a **centralized localized-content resolver** (`*_en ?: *_ar`). Admins enter Arabic only; English
  is optional and never blocks saving; no machine translation. `brand`, customer-entered text, and
  neutral/numeric fields stay single. Introducing English later needs **no schema migration** and no
  commerce-logic rewrite.
- Currency (`444 ج`), dates, numbers via centralized formatters; stored values neutral (integer minor
  units, definitive — R2).
- PWA manifest + landing SEO metadata localizable later. **Service worker caches static assets only**
  and MUST NOT cache authenticated pages (profile/cart/checkout/orders/OTP) — privacy (R17).

## 8. Definition of ready for `/speckit-tasks`
Spec + clarifications approved; design docs approved (`Approved for Technical Planning`); this plan +
research + data-model + contracts + quickstart complete; Constitution Check = PASS; verified stack
pinned. `/speckit-tasks` can now generate P1→P3 vertical slices with accompanying commercial-logic
tests.
