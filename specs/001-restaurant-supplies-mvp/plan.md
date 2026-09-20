# Implementation Plan: Restaurant Supplies Ordering MVP

**Branch**: `001-restaurant-supplies-mvp` | **Date**: 2026-09-18 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `specs/001-restaurant-supplies-mvp/spec.md`

**Companion artifacts** (this planning phase):
[research.md](./research.md) (Phase 0 + ADR log) · [data-model.md](./data-model.md) ·
[contracts/service-contracts.md](./contracts/service-contracts.md) ·
[contracts/http-and-admin-surfaces.md](./contracts/http-and-admin-surfaces.md) ·
[quickstart.md](./quickstart.md)

**Authoritative design inputs**: [design/ux-architecture.md](./design/ux-architecture.md) ·
[design/wireframes.md](./design/wireframes.md) · [design/design-system.md](./design/design-system.md) ·
[design/screen-specifications.md](./design/screen-specifications.md) ·
[design/admin-design.md](./design/admin-design.md) ·
Governance: [constitution v1.0.0](../../.specify/memory/constitution.md)

> **Planning phase only.** No Laravel application code, migrations, models, controllers, Blade views,
> Filament resources, or JavaScript were created. All decisions comply with the constitution and the
> approved spec + clarifications C1–C7.

---

## Summary

Build a **mobile-first, installable PWA** for a single-branch, delivery-only, cash-on-delivery B2B
restaurant-supplies business, plus a **Filament admin** — as **one modular Laravel monolith** on one
MySQL database, deployable to **low-cost shared hosting**. Repeat B2B customers authenticate by
**phone → OTP**, browse an Arabic-first catalog of products with **multiple selling units and
quantity/wholesale tiers**, and place COD orders to eligible delivery areas within admin-managed time
slots. **All money is server-authoritative and deterministic** (Constitution II): pricing (lower-of
eligible offer vs. tier, never stacked), minimum-order, delivery fee + best-single delivery discount,
and order totals are computed by a **transport-agnostic service layer** (Constitution I/III) consumed
identically by Blade (customer), Filament (admin), and a future JSON API. Orders capture **immutable
commercial snapshots** (Constitution V). Money is stored/computed as **integer minor units** (EGP
piastres) via a `Money` value object; Arabic is default+fallback locale with **RTL-first** rendering
and localization readiness (no switcher in MVP).

Technical approach and all significant decisions are recorded as ADRs in
[research.md](./research.md) (R0–R23); the conceptual schema is in [data-model.md](./data-model.md);
service/HTTP/admin contracts are in [contracts/](./contracts/).

---

## Technical Context

**Language/Version**: PHP **8.2** (hard maximum — no dependency may require PHP 8.3+)
**Framework**: Laravel **12.x** (minimum PHP 8.2 — fits the ceiling)
**Primary Dependencies**: Filament **v5.x** (`^5.8`) admin — greenfield → **current supported major**
(verified `filament/filament` `php ^8.2`; `filament/support` `illuminate/contracts
^11.28|^12.0|^13.0` → Laravel 12; needs `ext-intl`; **no PHP 8.3+**). Blade + **Alpine.js** +
**Tailwind CSS** (customer PWA); **Pest** (or PHPUnit) for tests. Filament **core only, no unnecessary
plugins**. No Redis / WebSockets / Reverb / Scout / Meilisearch / Elasticsearch / Docker-in-prod / S3 in MVP.
**Storage**: MySQL **8** (InnoDB, `utf8mb4` / `utf8mb4_unicode_ci`); local **`public`** disk for
product images (`storage:link`), abstracted for future object storage/CDN.
**Testing**: Pest/PHPUnit — mandatory unit + feature coverage of all commercial logic (Constitution
VII), including the explicit boundary cases (tier 4→5, 9→10; minimum 499/500; discount thresholds;
expired offer; inactive area/slot; out-of-stock; double-submit).
**Target Platform**: Low-cost shared PHP hosting (PHP 8.2 FPM + MySQL 8 + HTTPS + cron); customer app
installable as PWA on a major mobile browser. No Docker required in production.
**Project Type**: Single modular Laravel monolith, three presentation surfaces (public Blade landing +
auth, customer PWA Blade/Alpine, Filament admin) over one shared service layer.
**Performance Goals**: Catalog browse/search feels instant on a typical mobile connection (SC-011);
new order visible to admin within one page refresh (SC-008, polling — no realtime). Listing cards use
a **lightweight "from" price** only; full tier/offer evaluation deferred to Details/Cart/Checkout (§44).
**Constraints**: Server-authoritative deterministic money (**integer minor units, no floats/decimal —
definitive**); immutable order snapshots; Arabic-first RTL; no OTP in production logs; runs
**fully synchronous** (no Redis/queue workers/Supervisor; async DB-queue-via-cron is a documented
fallback only). **PHP-8.2 dependency safety**: Composer `config.platform.php = 8.2.x` +
`composer check-platform-reqs` so nothing resolves a PHP-8.3+ package. MVP scope guard enforced (no
payment/inventory/RBAC/English UI/etc.).
**Scale/Scope**: Single branch; low-thousands product volume; multiple concurrent admins; **18 core
MVP tables** (+ framework tables). 17 customer screens (C01–C17) and 14 admin surfaces (A01–A14).

**Unknowns / NEEDS CLARIFICATION**: **None blocking.** Deferred-by-design (documented, not required
for MVP, do not block `/speckit-tasks`): exact SMS/WhatsApp OTP vendor (behind `OtpProvider`
contract, R7); real English translations for the already-present `*_en` columns (populated when the
business provides them, R14 — the schema itself is settled, not deferred); FULLTEXT-vs-LIKE search
tuning (data-volume dependent, R15).

---

## Constitution Check

*GATE: evaluated before Phase 0 and re-evaluated after Phase 1 design. Result: **PASS** at both gates.*

| # | Principle | How the plan satisfies it | Gate |
|---|-----------|---------------------------|------|
| I | Modular Monolith, API-Ready Core | One Laravel app; domain modules `app/Domain/<Module>` expose public services; Blade/Filament/future-API all consume the same services; no second deployable service (R1, contracts). | ✅ PASS |
| II | Server-Authoritative & Deterministic Commerce Math | Integer minor-unit `Money`, no floats; `PricingService`/`DeliveryService`/`OrderService` compute all totals server-side; client amounts never trusted; checkout revalidation + in-transaction re-revalidation; determinism unit-tested (R2, R3, R6, R9, R12, R22). | ✅ PASS |
| III | Business Logic Lives in Services | Named services/actions are the single source of truth (Otp, Customer, Catalog, Pricing, Promotion, Cart, Delivery, Order, Settings); controllers/Filament actions orchestrate only (R3, R6, R9, R19, contracts). | ✅ PASS |
| IV | Growth-Ready, Data-Driven Domain Model | Multi-unit products, per-unit + per-quantity pricing, multi-address-capable customers (one default in UI), delivery rules as data, bilingual `*_ar`/`*_en` managed-content columns from day one (English introduced with no migration), reserved customer-specific-pricing path; indexes justified against query patterns (data-model, R13/R14). | ✅ PASS |
| V | Immutable Order Snapshots | `orders`/`order_items` snapshot product/brand/unit/price/qty/discounts/fees/totals; no soft-delete on orders; history never reads live catalog (R9, data-model §14–15). | ✅ PASS |
| VI | Security by Default | Separate customer/admin guards; Form-Request validation; hashed one-time OTP with expiry/cooldown/rate+attempt limits and no prod logging; CSRF; mass-assignment protection; secure uploads; no secrets in git (R7, R8, R21). | ✅ PASS |
| VII | Tests Mandatory for Business-Critical Logic | Mandatory unit tests for pricing/tiers/lower-of, delivery discounts, minimum order, money, status transitions; feature tests for OTP/cart/checkout-revalidation/order/cancellation/admin; explicit boundary cases (R22, quickstart §3). | ✅ PASS |
| VIII | Mobile-First, Accessible, RTL-Ready UX | Mobile-first PWA; Arabic/RTL with logical properties; clear price/unit presentation; every data-driven screen defines loading/empty/error/disabled/validation/out-of-stock states (design docs, R13, R17). | ✅ PASS |
| IX | Spec-Driven Development & Controlled MVP Scope | Following Spec Kit gate chain; scope guard honored (no payment/inventory/RBAC/English-UI/microservices, etc.); future items recorded, not built (spec Out-of-Scope, R20). | ✅ PASS |

**Technology & Infrastructure Constraints** (hard gates): PHP 8.2 max ✅, Laravel 12 ✅, MySQL 8 ✅,
Blade/Alpine/Tailwind ✅, Filament version verified 8.2/L12-compatible ✅, PWA ✅, one app/one DB, no
Redis/WebSocket/Docker-prod requirement ✅, low-cost hosting + documented upgrade path ✅.

**No violations → Complexity Tracking is empty (nothing to justify).**

---

## Project Structure

### Documentation (this feature)

```text
specs/001-restaurant-supplies-mvp/
├── plan.md                              # This file (/speckit-plan output)
├── research.md                          # Phase 0: decisions + ADR log (R0–R23)
├── data-model.md                        # Phase 1: 18 core tables, indexes, constraints
├── quickstart.md                        # Phase 1: setup, env, deployment, upgrade path
├── contracts/
│   ├── service-contracts.md             # Transport-agnostic service layer (DTOs + signatures)
│   └── http-and-admin-surfaces.md       # Blade routes + Filament resource/page map + PWA endpoints
├── design/                              # Approved UX/UI (authoritative inputs, not produced here)
│   ├── ux-architecture.md               # IA, flows, decisions D1–D4
│   ├── wireframes.md                    # Screen wireframes
│   ├── design-system.md                 # Tokens, RTL, currency/number rules
│   ├── screen-specifications.md         # C01–C17 + per-screen states
│   └── admin-design.md                  # A01–A14 admin surfaces
└── tasks.md                             # Phase 2 (/speckit-tasks — NOT created here)
```

### Source Code — target layout for the implementation phase

**HARD CONSTRAINT (prompts 10/11):** the Laravel application lives in **`src/`** at repository root;
spec/design/docs stay outside `src/`. Standard Laravel 12 skeleton with a domain layer. **No files
created now**; this is the agreed target structure `/speckit-tasks` will populate.

```text
restaurant-supplies-pwa/                 # repository root (spec/design/docs live here, NOT in src/)
├── .specify/  specs/    prompts/   # planning & governance (outside src/)
└── src/                                 # ← Laravel application root
    ├── app/
    │   ├── Domain/                       # Business logic (Constitution I & III) — surface-agnostic
    │   │   ├── Auth/                     # OtpService, OtpProvider contract, LogOtpProvider (dev)
    │   │   ├── Customers/                # CustomerService, profile + default address
    │   │   ├── Catalog/                  # CatalogService (categories, products, units, search)
    │   │   ├── Pricing/                  # PricingService, PriceResult
    │   │   ├── Promotions/               # PromotionService (offer validity)
    │   │   ├── Cart/                     # CartService, CartView
    │   │   ├── Delivery/                 # DeliveryService, DeliveryQuote (areas/slots/discounts)
    │   │   ├── Ordering/                 # OrderService, CheckoutReview, status transition validator
    │   │   ├── Settings/                 # SettingsService (minimum order, business info)
    │   │   └── Support/                  # Money, MoneyFormatter, LocalizedContent, enums, MediaService
    │   ├── Http/{Controllers,Requests,Middleware}/   # orchestrate services; customer guard + onboarding gate
    │   ├── Filament/{Resources,Pages,Widgets}/       # Admin panel → calls services
    │   ├── Models/                       # Thin Eloquent models
    │   ├── Notifications/                # NewOrderNotification (DB notification)
    │   └── Providers/                    # Bindings (OtpProvider, MediaService disk, Filament panel)
    ├── database/{migrations,seeders,factories}/       # 18 core tables + framework tables
    ├── resources/
    │   ├── views/                        # Blade: landing, auth, customer PWA, components (<x-...>)
    │   │   └── components/               # Header, BottomNav, ProductCard, PriceDisplay, QtySelector,
    │   │                                 # UnitSelector, CartItem, OrderCard, StatusBadge, states
    │   ├── js/  css/                     # Alpine.js (minimal) + SW registration; Tailwind (RTL/logical)
    ├── lang/ar/                          # default+fallback; en/ added later — no switcher in MVP
    ├── public/                           # ← web root: manifest.webmanifest, sw.js, icons/, index.php
    ├── routes/                           # web.php (public+customer); Filament panel provider
    └── tests/{Unit,Feature}/            # pricing/delivery/minimum/Money/transitions; OTP/cart/checkout/orders/admin
```

**Structure Decision**: **Single modular Laravel monolith** (Constitution I) rooted at **`src/`**.
Business rules live in `src/app/Domain/<Module>` services; `src/app/Http` (Blade/Alpine customer +
public) and `src/app/Filament` (admin) are thin consumers; a future `/api` surface can reuse them
without duplication. One MySQL database. This is the layout `/speckit-tasks` builds against (all
implementation paths are `src/...`).

---

## Architecture Overview

- **Three surfaces, one core** (contracts/http-and-admin-surfaces.md): Public Blade (landing C01 +
  OTP entry C02/C03); Customer PWA (Blade + minimal Alpine, C04–C17); Filament admin (A01–A14). All
  call the same `app/Domain` services — no rule duplication (Constitution I/III).
- **Domain modules**: Auth (customer OTP), Customers, Catalog, Pricing, Promotions, Cart, Delivery,
  Ordering, Settings, Support (Money/formatters/media) — plus Administration as the Filament
  presentation over them. Not separate deployables (R1).
- **Authentication**: two identities/guards — admin `users` (web guard, Filament) and `customers`
  (dedicated `customer` session guard via OTP). Customers are never placed in `users` (R7/R8).
- **Money (definitive)**: **integer minor units** (EGP piastres) end-to-end via `Money`; **no
  float/decimal money**; rounding defined once; `MoneyFormatter` renders `444 ج` at the display
  boundary only (two decimals only if a value carries piastres) (R2, contracts).
- **Pricing**: `PricingService::priceFor(unit, qty, at)` → `PriceResult` with lower-of eligible
  offer/tier (never stacked), full explainability; lightweight `baselineFromPrice` for listings (R3).
- **Cart**: **database-backed**, **one persistent `carts` row per customer** (`UNIQUE(customer_id)` —
  no "active" status, no partial index, no cart history); items cleared after a placed order, row
  reused; **not financially authoritative** — checkout recomputes everything (R4).
- **Delivery**: `DeliveryService::quote(area, subtotal, at)` → `DeliveryQuote` (base fee, single
  largest-saving discount, tie-break higher min-subtotal, floor 0); **recurring weekday slot templates**
  (one row per `day_of_week` + time window, no capacity) (R5/R6).
- **Checkout**: two steps (Delivery → Review); `OrderService::review` builds `CheckoutReview` with a
  `changes[]` diff (changed-terms → re-review, never silent placement); `OrderService::place`
  re-revalidates inside a single DB transaction and writes immutable snapshots (incl. delivery
  date + slot label/times) + a concurrency-safe `order_number` (R9/R11/R12).
- **Order number**: `ORD-######` derived from the row's `AUTO_INCREMENT` `id` (+ fixed offset) inside
  the creation transaction, `UNIQUE(order_number)` backstop + retry; gaps acceptable; no counter lock,
  no distributed IDs, never client-supplied (R9).
- **Ordering**: language-neutral status enum + transition validator (customer self-cancel only from
  `new`; admin cancel from new/confirmed/preparing/out_for_delivery) (R10).
- **Notifications/queue/cache**: DB notifications (written **synchronously**) + Filament polling for new
  orders (no WebSockets). **Queue MVP default = fully synchronous** (no worker/Supervisor/Redis); OTP
  send is sync with strict provider timeouts; **async DB-queue-via-cron is a documented fallback only**,
  not built preemptively. File/DB cache for stable data only — never for pricing/cart/order math (R16).
- **PWA**: manifest + service worker caching **static assets only** (versioned CSS/JS, icons, logo,
  offline fallback) + offline fallback page; authenticated routes (profile/cart/checkout/orders/OTP)
  **excluded from cache, network-driven** — no leaking private/stale data; **no offline ordering**,
  never falsely reports success (R17).
- **Localization**: Arabic default+fallback, RTL-first, neutral domain identifiers, translation
  catalogs, centralized currency/date formatters. **Managed content uses paired `*_ar`/`*_en` columns
  from day one** (Arabic required, English nullable) for selected translatable fields
  (category/product/unit/offer/area/slot display text + localizable settings), read via a
  **centralized localized-content resolver** (`*_en ?: *_ar`); `brand`, customer-entered text, and all
  neutral/numeric fields stay single; order snapshots store the displayed value. Adding English later
  is a data + presentation change with **no schema migration and no commerce-logic rewrite**
  (R13/R14 — authoritative, supersedes the earlier "Arabic-only now, add later"). No language switcher
  in MVP.
- **Deployment**: shared hosting (PHP 8.2 + `intl` etc., MySQL 8, HTTPS, single `schedule:run` cron for
  maintenance; **no Docker**); `composer install` with platform pinned to PHP 8.2 + `check-platform-reqs`
  gate (R0a); documented upgrade path VPS → Redis → workers → object storage → CDN with no redesign
  (R23, quickstart §5–6).
- **Maintenance risk (documented, not MVP scope)**: **Laravel 12** is in its security-fixes period
  (EOL ~**Feb 2027**); retained because PHP 8.2 is a hard server cap (Laravel 13 needs PHP 8.3+).
  **Upgrade trigger**: when hosting supports PHP 8.3+, evaluate upgrading Laravel to the then-current
  major. Filament is already the current major (v5) — no Filament upgrade pending (R23).

For full rationale/alternatives of every decision, see [research.md](./research.md) R0–R23.

---

## Phase 0 — Research (COMPLETE)

All NEEDS CLARIFICATION resolved; dependency compatibility verified (not from memory). Output:
[research.md](./research.md) containing decisions R0–R23 (doubling as the ADR log), a risks table, and
the explicit "no blocking unresolved decisions" statement.

**Filament verification (Packagist, 2026-09-18):** `filament/filament` **v5.8.2** requires `php ^8.2`;
`filament/support` **v5.8.2** requires `php ^8.2`, `ext-intl *`, `illuminate/contracts
^11.28|^12.0|^13.0` (→ **Laravel 12 OK**), and pulls `livewire/livewire ^4.1` + `symfony/* ^7.0|^8.0`
(all PHP-8.2 compatible). **No Filament 5.x release requires PHP 8.3+.** Because this is a **greenfield**
build, MVP pins **v5.x (`^5.8`)** — the current supported major (longest maintenance runway, least
future migration debt), **core only, no unnecessary plugins**. Filament requires the **`intl`**
extension (in deployment prerequisites). See **R0a** for continuous PHP-8.2 enforcement
(`config.platform.php = 8.2.x` + `composer check-platform-reqs`).

---

## Phase 1 — Design & Contracts (COMPLETE)

- **Data model** ([data-model.md](./data-model.md)): 18 core MVP tables with purpose, columns
  (conceptual types), FKs, uniqueness, indexes, constraints, soft-delete decisions, and history/audit
  notes; money columns are `BIGINT` minor units; indexes mapped to §42 query patterns.
- **Contracts** ([contracts/](./contracts/)): transport-agnostic service signatures + DTOs
  (`Money`, `PriceResult`, `DeliveryQuote`, `CartView`, `CheckoutReview`, `Change`, `Problem`) and the
  full HTTP route + Filament surface map + PWA endpoints, with each action bound to a service.
- **Quickstart** ([quickstart.md](./quickstart.md)): verified stack table, local dev, mandatory test
  coverage, `.env` keys, low-cost production deployment, and the no-redesign upgrade path.
- **Agent context**: `CLAUDE.md` already references this plan and all Phase 0/1 artifacts (no marker
  block present to rewrite; left intact).

Post-design Constitution re-check: **PASS** (table above holds after design; no new violations).

---

## Complexity Tracking

*No constitutional violations — no complexity to justify.*

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| — | — | — |

---

## Scope Guard (explicitly NOT in MVP)

Online/electronic payment, customer credit/limits, **advanced** inventory (warehouses, multi-warehouse,
suppliers, purchasing, goods receiving, batch/lot & expiry, FIFO/LIFO, costing/valuation,
n-level/arbitrary unit-conversion trees, barcode, stock transfer, automated procurement, reservation timers),
multiple branches, drivers/route optimization/live tracking, loyalty/wallet, advanced
coupons/promotion engine, recurring orders, Buy Again, customer-specific price lists, English UI,
language switcher, microservices, fine-grained/advanced RBAC. **Readiness** for these is preserved in
the data model and service layer (Constitution IV) but **none is implemented** (spec Out-of-Scope; R20).

> **Amendment (prompt 32):** **simple inventory quantity is now CORE MVP** —
> `inventory_adjustments` + `InventoryService` (FR-071..FR-078, data-model §18).
>
> **Amendment (prompt 37):** units are a **reusable module** + per-product **two-level** primary/sub
> configuration with product-specific conversion; inventory is one authoritative **sub-unit** balance
> per product; both units independently sellable/priced (R25, FR-079..FR-086, data-model §6/§19). This
> supersedes the independent per-unit stock balance. Only the *advanced* inventory + n-level conversion
> above remain out of scope.

---

## Definition of Ready for `/speckit-tasks`

Spec + clarifications approved; design docs approved; plan + research + data-model + contracts +
quickstart complete; **Constitution Check = PASS** (both gates); stack pinned and verified. Ready to
generate P1→P3 vertical slices with accompanying mandatory commercial-logic tests.
