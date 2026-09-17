# Research & Decisions — Restaurant Supplies Ordering MVP

**Feature**: `001-restaurant-supplies-mvp` · **Phase**: 0 (research) · **Date**: 2026-09-17
**Companion to**: [plan.md](./plan.md). Format per decision: **Decision / Rationale / Alternatives**.
This document also serves as the **ADR log** (no separate ADR mechanism in this repo).

All decisions comply with [constitution v1.0.0](../../.specify/memory/constitution.md) and the
approved spec + clarifications (C1–C7). No application code is produced here.

---

## R0. Verified dependency compatibility (PHP 8.2 / Laravel 12 / Filament 5)

**Decision**: PHP **8.2** (hard max), Laravel **12.x**, MySQL **8**, **Filament v5.x** for admin
(Composer constraint `^5.8`). Blade + Alpine.js + Tailwind for the customer UI. No dependency
requiring PHP 8.3+. This is a **greenfield** project, so it targets the **current supported Filament
major**, not an older line.

**Verification (not from memory — checked Packagist package metadata, 2026-09-18)**:
- **Laravel 12** minimum PHP is **8.2** (supports 8.2/8.3/8.4). ✅ fits the 8.2 ceiling.
- **`filament/filament` v5.8.2** (latest stable): `require php: ^8.2`. ✅
- **`filament/support` v5.8.2** (where the framework constraint lives): `require php: ^8.2`,
  `ext-intl: *`, `illuminate/contracts: ^11.28|^12.0|^13.0` → **supports Laravel 12** ✅; also pulls
  `livewire/livewire: ^4.1` and `symfony/*: ^7.0|^8.0` (both PHP-8.2 compatible).
- **No Filament 5.x release requires PHP 8.3+.** ✅ (older 3.x needed `php ^8.1`; 4.x needed
  `php ^8.2`; 5.x needs `php ^8.2` — none require 8.3+.)
- Filament requires the **`intl`** PHP extension (in deployment prerequisites).

**Rationale for v5.x (greenfield → current major)**: a new build should adopt the **current
supported Filament core** for the longest maintenance runway and smallest long-term migration debt;
v5.x is fully compatible with the PHP 8.2 / Laravel 12 ceiling. We deliberately **do not** pick an
older major merely because it is "mature." The project prefers current Filament **core only**, **no
unnecessary third-party Filament plugins**, and the **lowest reasonable dependency surface**.

**Alternatives considered**: Filament v3.x / v4.x (rejected as the MVP pick — selecting an older
major on a greenfield project adds future migration debt for no MVP benefit; both remain
technically compatible); bespoke Blade/Livewire admin (rejected: reinvents CRUD, overengineering);
separate admin SPA (rejected: violates single-app constraint).

**Guardrail for `/speckit-tasks`**: before adding **any** non-core package (incl. Filament plugins),
verify its Composer `require` (php + illuminate) resolves on PHP 8.2 / Laravel 12 and add the pin.
See **R0a** for continuous PHP-8.2 enforcement during dependency resolution.

Sources (verified 2026-09-18): [filament/filament — Packagist](https://packagist.org/packages/filament/filament) ·
[filament/support — Packagist](https://packagist.org/packages/filament/support) ·
[Laravel 12 PHP requirement](https://laravel.com/docs/12.x/releases).

---

## R0a. PHP 8.2 dependency-safety (Composer platform emulation)

**Decision**: The production runtime is capped at **PHP 8.2**. Development may occur on PHP 8.3+, so
dependency resolution **MUST emulate the production runtime** so Composer can never resolve a package
(direct or transitive) that requires PHP 8.3+. During project foundation the implementation MUST:
- Set Composer's platform config to the production PHP line — conceptually
  `config.platform.php = 8.2.x` (exact patch chosen at foundation) — so `composer update/require`
  resolves against PHP 8.2.
- Run **`composer check-platform-reqs`** (or equivalent) as a **deployment/CI gate**.
- Verify a candidate package's `require` (php + illuminate) **before** adding any significant
  dependency, and record the pin.

**Rationale**: enforces the hard runtime constraint continuously and prevents "works locally, breaks
in production" PHP-version drift. (No `composer.json` is created in this correction pass.)

**Alternatives**: relying on developer discipline only (rejected: not enforceable); pinning after the
fact (rejected: allows bad resolutions to land first).

---

## R1. Architecture style — modular monolith

**Decision**: One repo, one Laravel app, one MySQL DB. Domain modules under `app/Domain/<Module>`
(Auth, Customers, Catalog, Pricing, Promotions, Cart, Delivery, Ordering, Settings, Support) each
exposing public **services**; presentation surfaces (public Blade, customer PWA, Filament admin)
and a future JSON API consume the same services. No microservices, Redis, WebSockets, Docker-in-prod,
Elasticsearch, Kubernetes, separate frontend/admin deploys.

**Rationale**: Constitution Principle I; MVP speed with a clean API-extraction path; runs on shared
hosting. Module boundaries prevent cross-reaching and keep rules reusable across surfaces.

**Alternatives**: package-per-module / monorepo packages (rejected: overhead unjustified at MVP);
hexagonal/DDD tactical overkill (rejected: "avoid unnecessary abstraction"). We use pragmatic
service+action classes, thin Eloquent models, and enums — not full DDD aggregates.

---

## R2. Money handling

**Decision**: Store and compute money as **integer minor units** (piastres; 1 EGP = 100). DB column
type `BIGINT` (or `INT UNSIGNED` where safe) for amounts; a PHP **`Money` value object**
(`amount:int`, `currency:'EGP'`) wraps arithmetic. All pricing/fee/discount/total math uses integer
ops; **no floats**. Rounding is defined **once** in `Money` (round half-up to the minor unit) and
reused. Percentage delivery discounts compute `floor`/`round` deterministically on integers.
Presentation formats to `444 ج` only at the display boundary (formatter/Blade component), never in
domain logic.

**This is the definitive, authoritative money representation** for the project (no
DECIMAL-vs-integer ambiguity remains): **integer minor units + a central `Money`** everywhere —
storage, calculation, and transport. Binary floating point MUST NEVER be used for monetary values.

**Display of piastres**: the display boundary converts minor units to EGP for presentation. MVP
business pricing is entered in **whole EGP**, so amounts normally render as `444 ج` (no decimals). If
a value ever carries non-zero piastres (e.g. a computed percentage discount), the formatter renders
the minor part with two decimals (e.g. `12.50 ج`) using Latin digits; stored values remain exact
integer minor units and are never pre-rounded for display.

**Rationale**: Constitution Principle II (deterministic, testable, no float drift); auditable;
locale/language-neutral storage (§11, §37). Integer minor units eliminate binary-float rounding
error entirely and serialize cleanly for a future API.

**Alternatives (rejected, not open)**: `DECIMAL(10,2)` columns (float-safe but a second numeric model
to reason about, weaker for API serialization — **not** retained as an equal option after this
decision); native float/double (**rejected**, violates Principle II).

**Serialization boundary**: services return `Money` (or int minor units); controllers/Filament/API
map to display via the central `MoneyFormatter`. Stored values never change with locale.

---

## R3. Pricing model (lower-of offer vs. tier) — `PricingService`

**Decision**: A deterministic `PricingService::priceFor(productUnit, quantity, at)` returns a
**`PriceResult`** value object:
`{ base_unit_price, tier_unit_price?|null, offer_unit_price?|null, applied_unit_price,
applied_source: normal|tier|offer, unit_saving, line_total, quantity }`.
Algorithm (per C1/BR-011, FR-025): compute `base` (unit normal price); compute `tier` if a tier's
`[min_qty..]` matches `quantity`; compute `offer` if an active in-range offer targets the unit;
**applied = min(candidates that apply)**; if none apply → `base` (`normal`). Offer and tier **never
stack**. `applied_source` records which candidate won for UI ("best · tier"/"best · offer").
`line_total = applied_unit_price × quantity` (integer). Server is authoritative; the same function
powers Product Details estimate, Cart estimate, and checkout revalidation.

**Rationale**: single source of truth (Principle III), deterministic (Principle II), explainable
(FR-069). Worked examples: `Normal 200 / Tier 170 / Offer 160 → 160`; `Normal 200 / Tier 150 /
Offer 160 → 150`.

**Alternatives**: precedence-based (offer-overrides-tier) — **rejected** (contradicts approved C1);
a generalized promotion-rules engine — **rejected** (§12, overengineering).

**Performance**: listing pages call a **lightweight** path (default unit, qty 1 baseline "from" price)
— not full tier evaluation per product (§44). Full evaluation only on Details/Cart/Checkout.

---

## R4. Cart persistence — one persistent cart row per customer

**Decision**: **Database-backed cart**, exactly **one persistent `carts` row per customer**, enforced
by a plain **`UNIQUE(customer_id)`** index (no partial/filtered index needed — MySQL 8 has no partial
unique indexes, so we deliberately avoid any "many carts but one active" invariant). The row is
created lazily on first add. Cart lines live in `cart_items`. **After a successful order the cart's
items are cleared; the `carts` row itself may remain for reuse.** There is **no cart status column**
and **no cart history** — order history belongs to `orders`, not carts.

**Rationale**: customers authenticate **before** ordering (OTP) and return across devices/sessions
(§13); a single per-customer DB cart survives session loss, is trivially MySQL-enforceable, and is
ready for a future mobile/API client (Principle I). No Redis. The cart is **not financially
authoritative** — `cart_items` reference product/unit/quantity only; prices shown are estimates
recomputed by `PricingService`; checkout recalculates everything (Principle II, FR-028).

**Alternatives (rejected)**: "one active cart among many per customer" (rejected: needs a
partial/filtered unique index MySQL doesn't support, or app-level guards — ambiguous and error-prone);
session cart (lost on session expiry, not API-friendly); guest-session→DB merge (unnecessary — auth
precedes ordering, so there is no pre-auth cart). `cart_items` store **no authoritative price** (an
optional non-authoritative `last_seen_unit_price` display cache is never trusted).

---

## R5. Delivery slot model — recurring weekday templates (concrete)

**Decision (concrete MVP model)**: Slots are **admin-managed recurring weekday templates**, **one row
per (weekday, time window)**. `delivery_slots` columns: `label` (Arabic, e.g. "1–4 مساءً"),
**`day_of_week`** (single weekday, ISO 1=Mon…7=Sun), `start_time TIME`, `end_time TIME`, `is_active`,
`sort_order` — **no numeric capacity** (C4/FR-040), no JSON day-set/bitmask. At checkout the customer
picks a **delivery date** (not past) and an **active** slot whose `day_of_week` matches that date's
weekday. `DeliveryService::availableSlots(date)` returns active slots for that weekday, ordered by
`sort_order`/`start_time`. Checkout **revalidation rejects** a slot that became inactive or whose
weekday no longer matches the chosen date, and requires re-selection (R12).

**Snapshot on order**: order placement snapshots the chosen `delivery_date` plus the slot's
`delivery_slot_label`, `slot_start_time`, `slot_end_time` onto `orders` (R9), so later slot edits or
deletion never mutate historical orders (Principle V).

**Rationale**: the simplest concrete model satisfying FR-038–FR-040 — trivially MySQL-representable
(no JSON), no scheduling engine, no per-date row explosion, no capacity. One weekday per row keeps
querying and admin management obvious. Deterministic business representation (chosen date + slot id)
is separate from localized display (§38).

**Alternatives (rejected)**: multi-weekday `days` set/bitmask JSON on one row (rejected here in favor
of the more explicit one-row-per-weekday model — simpler queries, no JSON parsing); per-date slot rows
(needless volume/admin toil); capacity counters (explicitly future scope, C4); driver
scheduling/route planning (out of scope).

---

## R6. Delivery fee + discount resolution — `DeliveryService`

**Decision**: `DeliveryService::quote(area, effectiveProductSubtotal)` returns a
**`DeliveryQuote`**: `{ base_fee, applied_rule?|null, discount_amount, final_fee }`. Base fee from
the (active) `delivery_areas.base_fee`. Discount: evaluate every **active** `delivery_discount_rule`
whose `min_subtotal ≤ effectiveProductSubtotal`; for each compute the **monetary saving** vs the
current base fee — `fixed` → `min(value, base_fee)`; `percentage` → `round(base_fee × pct/100)`
capped at `base_fee`; `free_delivery` → `base_fee`. Choose the **single largest saving** (no
stacking, C2/BR-004); **tie-break = higher `min_subtotal`** (more restrictive). `final_fee =
base_fee − discount_amount`, floored at **0**. Qualification uses **effective product subtotal
excluding delivery** (C7/BR-003).

**Rationale**: deterministic, server-authoritative (Principles II/III), matches approved C2 exactly;
result object exposes base/selected-rule/discount/final for UI (§17, FR-037).

**Alternatives**: stacking discounts (rejected, contradicts C2); admin-priority selection (rejected:
approved rule is largest-saving with min-subtotal tie-break).

---

## R7. OTP authentication + provider abstraction

**Decision**: Separate **customer** identity from **admin** identity (R8). Customer login: phone →
OTP → verify → customer session. OTP stored in `otp_verifications` as a **hash** (never plaintext),
with `expires_at`, `consumed_at`, `attempts`, and request/verify counters for rate limiting.
Provider behind an **`OtpProvider` contract** (`send(phone, code): void`): MVP ships a **`LogOtpProvider`
for local/demo** (writes code to a dev-only channel, never in production) and leaves a real SMS/
WhatsApp implementation for later — **no paid vendor chosen now**. OTP service enforces: expiry
(short, config), **one-time use** (mark `consumed_at`), **resend cooldown**, **request rate limit**,
**verify attempt limit** (lockout/backoff), and **never logs OTP values in production**.

**Rationale**: Constitution Principle VI + spec FR-001–FR-010, C5. Contract keeps OTP logic
language-neutral and vendor-swappable (§6, §14). Hashing + counters resist abuse.

**Alternatives**: Laravel Fortify/Sanctum OTP flows (rejected: heavier than needed, and Fortify has
a noted Filament-v4 interaction — avoided; not required for v3 either); storing plaintext codes
(**rejected**, Principle VI).

**Dev/prod (§48)**: `OTP_DRIVER=log` in local/staging surfaces the code safely (dev channel/UI
banner in non-prod only); production uses a real provider and **must never** expose static/test OTP.

---

## R8. Customer vs admin authentication separation

**Decision**: Two identities, two guards. **`users`** = admin/staff (default `web` guard, Filament
panel auth). **`customers`** = ordering customers with a dedicated **`customer` guard + provider**
(session-based) authenticated via the OTP flow. Customers are **not** placed in `users`.

**Rationale**: §6 + Principle VI; different auth mechanisms (password vs OTP), different data, clean
authorization boundaries; Filament panel restricted to `users`.

**Alternatives**: single `users` table with a role flag (rejected per explicit instruction and
because OTP-only customers shouldn't share the admin table); Sanctum tokens for customers (deferred:
sessions suffice for the PWA; tokens are a future-API concern).

---

## R9. Order creation, transaction, snapshots, order number

**Decision**:
- **Transaction**: `OrderService::place(customer, checkoutInput)` runs inside a single DB
  transaction that (a) re-runs full server revalidation, (b) inserts the `orders` header, (c) inserts
  all `order_items`, (d) commits — or rolls back entirely. No header-without-items; no partial order.
- **Snapshots (immutable)**: `order_items` capture `product_id?`, `product_unit_id?`,
  `product_name`, `brand?`, `unit_name`, `package_description?`, `quantity`, `base_unit_price`,
  `applied_unit_price`, `applied_source`, `unit_saving`, `line_total`. `orders` capture
  business/contact identity (name, contact person, phone, WhatsApp), delivery area **name** + full
  address fields, delivery date + slot **label/time**, `product_subtotal`, `base_delivery_fee`,
  `delivery_discount`, `final_delivery_fee`, `final_total`, `payment_method='cod'`, `status`,
  `order_number`. Historical orders never read live catalog (Principle V, FR-051).
- **Order number (concrete, concurrency-safe)**:
  - **Format**: `ORD-` + a **zero-padded 6+ digit** decimal, e.g. `ORD-100001` (human-readable,
    stable width for a long time; not the raw PK exposed verbatim).
  - **Generation timing**: allocated **inside the order-creation DB transaction**, **after** the
    `orders` header row is inserted, by deriving the number from that row's `AUTO_INCREMENT` `id`
    plus a fixed display offset (e.g. `100000`), then persisting it to `order_number` in the same
    transaction. Never client-supplied.
  - **Uniqueness enforcement**: a **`UNIQUE(order_number)`** column constraint (hard DB guarantee).
  - **Collision / race handling**: MySQL `AUTO_INCREMENT` guarantees distinct `id`s under concurrent
    inserts → distinct derived numbers with no application lock; the `UNIQUE` constraint is the
    backstop, and on the astronomically unlikely violation the transaction is retried. **Gaps are
    acceptable** (a rolled-back transaction may burn an id) — order numbers are human-friendly
    references, not a gapless ledger.
  - **No distributed ID infrastructure**, no client values.

**Rationale**: Principles II/V; spec FR-041–FR-052; pragmatic snapshots (no event sourcing, §20).
The id-derived scheme is the simplest concurrency-safe strategy for a low/medium-volume MVP.

**Alternatives (rejected)**: a dedicated counter row with `SELECT … FOR UPDATE` for gapless numbers
(rejected: introduces a single-row lock hotspot on every order for a cosmetic gapless property MVP
doesn't need); UUID/ULID order numbers (rejected: not human-friendly); client-generated numbers
(rejected: Principle II).

**Alternatives**: recompute history from catalog (**rejected**, Principle V); UUID order numbers
(rejected: not human-friendly); event sourcing (rejected: §20 overengineering).

---

## R10. Order status model + transitions

**Decision**: Language-neutral status enum: `new, confirmed, preparing, out_for_delivery, delivered,
cancelled` (Arabic/English are display labels, §22/§5-neutral-ids). A **transition validator**
(domain method, not a workflow engine) enforces: forward path `new→confirmed→preparing→
out_for_delivery→delivered`; **customer self-cancel only from `new`**; **admin cancel from
`new|confirmed|preparing|out_for_delivery`**; **no** transition from `delivered`/`cancelled`.
Invalid transitions are rejected server-side (SC-012). Optional `cancellation_reason`, `cancelled_by`.

**Rationale**: BR-007/BR-012, C3; "domain method/transition validator is sufficient" (§22).

**Alternatives**: state-machine package (rejected: overkill for 6 states); free-form status strings
(rejected: not enforceable/neutral).

---

## R11. Concurrency & data integrity

**Decision**:
- **Duplicate order / double-click**: idempotency via a per-checkout **submission token** (one-time)
  + a DB transaction; a unique guard prevents two orders from one token.
- **Order-number generation**: derive `ORD-######` from the inserted row's `AUTO_INCREMENT` `id`
  (+ fixed offset) inside the transaction, with `UNIQUE(order_number)` as the backstop and
  transaction retry on the improbable violation (concrete details in R9). No counter-row lock, no
  distributed IDs.
- **OTP concurrency**: unique active-OTP handling per phone; verification uses a conditional update
  (`consumed_at IS NULL`) so a code can be consumed once; counters are updated atomically.
- **Price/availability change during checkout**: caught by **revalidation** (R12) → changed-terms,
  never a silent placement.
- **Item became unavailable during checkout**: revalidation flags it; order blocked until resolved.

**Rationale**: §43; targeted DB constraints/locks, **no distributed locking** (shared hosting).

**Alternatives**: app-level mutexes/Redis locks (rejected: infra dependency); optimistic version
columns everywhere (rejected: unnecessary; scoped locks + unique constraints suffice).

---

## R12. Two-step checkout + revalidation (changed-terms)

**Decision**: Step 1 (Delivery) collects/validates address, area, date, slot, COD; Step 2 (Review)
calls `OrderService::revalidate(checkoutInput)` returning a **`CheckoutReview`**:
`{ lines[], product_subtotal, delivery: DeliveryQuote, minimum_order, meets_minimum,
final_total, changes[] }`. `changes[]` diffs the customer's last-seen terms vs freshly computed
(price/tier/offer/availability/selling-unit/area/fee/discount/slot). If `changes[]` is non-empty →
UI shows the **changed-terms** state and blocks confirm until re-reviewed; **never** places silently
(FR-043/FR-044, US4 #3). Confirm calls `OrderService::place()` which revalidates **again** inside the
transaction (source of truth). Offline/failure → explicit failure, **no false success** (FR-047).

**Rationale**: Principle II; §18; isolates the money decision on Step 2 (design D3).

**Alternatives**: single revalidation only at submit (rejected: worse UX — surface diffs on Step 2
entry); trusting Step-2 client totals (**rejected**, Principle II).

---

## R13. Localization architecture readiness (Arabic-only MVP)

**Decision**: Arabic is the **default and fallback** locale; RTL active; **no switcher** in MVP.
Readiness (per design D4 / ux §21–§23):
- **UI copy** in Laravel translation files under `lang/ar/` (add `lang/en/` later); no hard-coded
  user-facing strings in logic. Validation messages localized (rule ≠ message, §39).
- **Domain identifiers language-neutral** (statuses, unit `code`, discount `type`, payment `cod`,
  availability). Display labels are translations keyed by identifier.
- **Managed content** (selected translatable category/product/unit/offer/area/slot display text):
  stored as **bilingual `*_ar` + `*_en` columns from day one** (Arabic required, English nullable),
  read through a **centralized localized-content resolver** — see **R14** (this supersedes the earlier
  "Arabic-only now, add later" idea). Arabic remains the only exposed MVP language; no switcher.
- **RTL/LTR** from document direction + logical CSS properties; no manual reversal (§36).
- **Currency/date/number** are centralized presentation formatters (§37/§38): `Money` → `444 ج`;
  dates via a locale-aware formatter; Latin digits always (D2); stored values neutral.
- **PWA metadata** (`name`/`short_name`/`description`) sourced so they can be localized later (§40).
- **Landing SEO metadata** localizable later (§20 design); Arabic-only now.
- **Caching** localized output later uses per-locale keys (§33) — not needed while Arabic-only.

**Rationale**: constitution Principle IV + design D4; adding English later touches presentation +
translation files, not commerce logic (§29/§34).

**Alternatives**: full bilingual schema now (rejected, §35 overengineering); ignore localization
(rejected, violates D4/Principle IV).

---

## R14. Managed-content localization — bilingual `*_ar` / `*_en` columns from day one (AUTHORITATIVE)

> **This decision supersedes the earlier prompt-08 recommendation** (keep managed content Arabic-only
> and add translation storage later). It is now the **authoritative localization-content schema
> decision**.

**Decision**: For **selected business-managed content genuinely likely to be translated**, create
**paired `*_ar` and `*_en` columns from the beginning**, so introducing English later needs **no
schema migration**. Concretely:

- **Bilingual pairs** (apply `*_ar`/`*_en` only to these):
  - `categories`: `name_ar`/`name_en` (+ `description_ar`/`description_en` if descriptions are kept)
  - `products`: `name_ar`/`name_en` (+ `description_ar`/`description_en` if managed)
  - `product_units`: `display_name_ar`/`display_name_en` (e.g. كيس/Bag, كرتونة/Carton, عبوة/Pack);
    `package_description_ar`/`package_description_en` if that label is stored
  - `product_offers`: `title_ar`/`title_en` (+ `description_ar`/`description_en` if stored)
  - `delivery_areas`: `name_ar`/`name_en`
  - `delivery_slots`: `label_ar`/`label_en` (only the display label — **never** the raw `start_time`/
    `end_time`/`day_of_week`)
  - `settings`/landing managed text (if DB-stored): follow the same pattern **where practical**
    (e.g. `business_name_ar`/`business_name_en`, `business_address_ar`/`business_address_en`); neutral
    settings (minimum-order amount, phone, WhatsApp) stay single.

- **MVP values & constraints (DB strategy — preferred, documented)**: **`*_ar` is required**;
  **`*_en` is nullable** at the database level. Admins enter Arabic only (English not required to
  save; MVP stays fully operable in Arabic). English is **not** machine-translated. `*_en` is left
  **null** until a real translation is provided (chosen over auto-copying Arabic into `*_en` so the
  system never mistakes an untranslated field for a real translation). *(If a future implementer
  prefers to persist Arabic into `*_en` instead, that is permitted but MUST be documented as
  "`*_en` may contain untranslated Arabic.")* **No translation-status workflow** is built.

- **Centralized resolver (fallback)**: a single **localized-content resolver** (model accessor /
  presentation helper — exact form decided in implementation) returns, for the current locale:
  Arabic → `*_ar`; future English → `*_en`, **falling back to `*_ar` when `*_en` is null/empty**.
  Locale-selection logic MUST NOT be scattered across Blade/controllers/Filament/services.

- **Stays language-neutral (NO `*_ar`/`*_en`)**: IDs, FKs, slugs, SKUs/product codes, unit technical
  `code`, status/enum identifiers (`new`, `confirmed`, `out_for_delivery`, `fixed`, `percentage`,
  `free_delivery`, `cod`, availability), quantities, prices/money, discount types, dates/times, order
  numbers, phone/WhatsApp, booleans. Translated only at presentation.

- **Brand**: a **single `brand`** column (natural commercial name, e.g. Heinz, Farm Frites) — **no**
  `brand_ar`/`brand_en` unless a real business need emerges.

- **Customer-entered data**: **single Unicode columns**, never duplicated by locale — `business_name`,
  `contact_person_name`, `whatsapp_phone`, address lines, `landmark`, `delivery_notes`. Changing UI
  language later must not alter/duplicate user content.

- **Order snapshots**: snapshot the **displayed** commercial text used at placement (MVP = the Arabic
  values) into **single** snapshot columns (`order_items.product_name`, `unit_name`,
  `package_description`; `orders.delivery_area_name`, `delivery_slot_label`). History never reads live
  `*_ar`/`*_en`; snapshots stay immutable (Principle V). Future English orders snapshot whatever text
  was displayed at placement.

**Rationale**: the business expects English later; paying the small cost of paired columns now avoids a
future schema migration purely to add a language, while **commerce/domain logic still references only
IDs/amounts/neutral identifiers** — so adding English is a data + presentation change, never a rewrite
of pricing/order/delivery logic (Constitution IV). The scope guard (§12 of prompt 09) keeps pairs off
neutral/brand/customer-entered/numeric fields, so the schema stays understandable.

**Alternatives (rejected)**: Arabic-only-now + additive `*_translations`/JSON later (the prior
recommendation — **rejected/superseded**: forces a later migration the business will predictably
need); `*_ar`/`*_en` on **every** text column (rejected: violates the scope guard, unmaintainable);
machine-translation or a translation-status workflow (rejected: out of MVP scope).

---

## R15. Search (MySQL only, cheap & deterministic)

**Decision**: **MySQL-based catalog search — no Scout, Meilisearch, Elasticsearch, or any external
search service (MVP or otherwise for MVP scope).** Initial implementation target: **indexed
`LIKE`/prefix search** over `products.name_ar`, `products.brand`, and (where appropriate)
`categories.name_ar`, scoped to active/visible products and paginated. Collation `utf8mb4_unicode_ci`
handles **Arabic content + naturally English brand/product tokens** in the same query. This is the
simplest indexed SQL approach and is appropriate for the initial catalog size. `*_en` currently may
be null (or, if the alternative strategy is used, contain untranslated Arabic), so it is **not** used
for meaningful English search yet.

**FULLTEXT is NOT a mandatory MVP dependency.** InnoDB `FULLTEXT` on `name_ar/brand` MAY be evaluated
**later, only if real production data shows `LIKE`-based search is insufficient**; it is an optimization
path, not an MVP requirement. When real English content lands, search is **extended to `*_en`** columns
**without replacing** the existing Arabic search — no commerce-logic change (R14).

**Rationale**: §25; low-cost shared hosting; low-thousands product volume performs fine with proper
indexes and `LIKE`/prefix matching; keeps the dependency surface minimal.

**Alternatives (rejected)**: Scout + Meilisearch/ES (external infra dependency); mandatory FULLTEXT
from day one (premature — deferred to data-driven evaluation); no search (violates FR-012).

---

## R16. Notifications (admin new-order) & queue & cache

**Decision**:
- **Notifications**: **database notifications** + Filament's built-in **polling** on the orders
  list/dashboard so a new order becomes visible without WebSockets/Reverb/Redis (§31, FR-055).
  Customer-facing notifications beyond OTP are **not** added (not in spec).
- **Queue (one clear MVP default)**:
  - **MVP default = fully synchronous.** All normal business actions run synchronously; **database
    notifications are written synchronously**; **no persistent queue worker is required**; **no
    Redis; no Supervisor**; `QUEUE_CONNECTION=sync`.
  - **OTP sending is synchronous** with **strict provider connection/read timeouts** so a slow
    provider cannot hang a request.
  - **Approved fallback (only if a later OTP provider actually needs async handling)**: switch to the
    **Laravel database queue** drained by the **scheduler/cron** (`schedule:run` →
    `queue:work --stop-when-empty`). This is **not created unless needed** — `/speckit-tasks` MUST NOT
    build database-queue infrastructure preemptively.
  - **Upgrade path**: persistent worker + Redis queue on a VPS (post-MVP).
  - The single MVP cron entry (`schedule:run`) is used for **scheduled maintenance** (e.g. expired
    OTP cleanup); it only also drains a queue **if** the async fallback is later enabled.
- **Cache**: Laravel **file or database** cache; cache only stable, high-read data (e.g. settings,
  active categories) with explicit invalidation on admin edits. **Do not** cache pricing/cart/order
  calculations (§33).

**Rationale**: simplest low-cost approach; no premature optimization; deterministic commerce stays
uncached.

**Alternatives**: Reverb/WebSockets (rejected: §31); Redis (rejected MVP); caching dynamic totals
(rejected: Principle II risk).

---

## R17. PWA strategy + authenticated-content privacy

**Decision**: Add `manifest.webmanifest` (name/short_name/description/theme/background/icons incl.
maskable) and register a **service worker** with a **privacy-safe, static-only cache**.

**Service worker MUST cache only (allow-list)**: **versioned/hashed static CSS & JS**, **icons**,
**logo**, other **safe public shell/static assets**, and the **offline fallback page**.

**Service worker MUST NOT cache** any sensitive authenticated or customer-specific response — it MUST
NOT store HTML/JSON for: **profile, addresses, cart, checkout, order history, order details, or
OTP/authentication responses**. These dynamic authenticated routes stay **network-driven** (no SW
`Cache`/`caches.put` for them); they are **explicitly excluded** from SW cache storage (e.g. the
fetch handler bypasses caching for `/profile`, `/cart`, `/checkout/*`, `/orders*`, `/onboarding/*`,
`/otp/*`, `/verify`, and any authenticated document/response). Serving stale authenticated content from
cache is prohibited.

**Also**: **no offline ordering**, no background sync, no offline data store; the app **never** reports
order success unless the server confirmed creation (FR-047/FR-067). Manifest strings are localizable
later (§40); Arabic now, single PWA (no duplicate apps).

**Rationale**: §27/§40 + privacy — installability (FR-066) with minimal caching suited to shared
hosting, without ever exposing another session's or a stale customer's private data via the cache.

**Alternatives (rejected)**: caching authenticated pages for "offline browsing" (rejected: leaks
private data, stale commercial terms); Workbox-heavy offline caching / IndexedDB sync (complexity,
§27); no PWA (violates FR-066).

---

## R18. Product images / storage

**Decision**: **Local `public` disk** (`storage/app/public` + `storage:link`). Uploads validated
(mime: jpeg/png/webp; max size, e.g. 2–4 MB; image dimensions sanity). Deterministic path/naming
(e.g. `products/{ulid}.{ext}`). Prefer **WebP** where practical; generate a display/thumbnail size
for cards. Filament handles admin upload; a `MediaService`/config abstracts the disk so switching to
**object storage/CDN later** is a config change (§28, Principle IV).

**Rationale**: no S3 dependency for MVP; low-cost; upgradeable.

**Alternatives**: S3/object storage now (rejected MVP); external image CDN (deferred upgrade).

---

## R19. Admin (Filament) resource map

**Decision**: Filament **v5** panel restricted to `users` (admin guard), Arabic-first (RTL), following
[`admin-design.md`](./design/admin-design.md). Resources/pages: **Dashboard** (new/today/recent +
indicators via widgets + polling), **Orders** (list/filters/details + status actions), **Customers**
(list/details + history), **Categories**, **Products** (tabbed: General/Media/Selling Units/Pricing/
Offers/Availability), **Offers** (cross-product list), **Delivery Areas**, **Delivery Slots**,
**Delivery Discount Rules**, **Settings**. Filament actions **call domain services** (e.g. status
change → `OrderService::transition`), never re-implement rules (Principle III, §29).

**Rationale**: operational speed; single source of truth; no duplicated logic.

**Alternatives**: custom admin (rejected, §29); logic inside Filament actions (rejected, Principle III).

---

## R20. Admin users / RBAC

**Decision**: Multiple admins via standard authenticated `users` + Filament panel access. **No
`spatie/laravel-permission`**, no fine-grained RBAC in MVP (FR-063/FR-064, §30). Future path: add a
roles/permissions layer without schema rewrite (documented).

**Rationale**: §30; avoid unneeded dependency/complexity.

**Alternatives**: RBAC package now (rejected: no concrete MVP need).

---

## R21. Security posture

**Decision** (Principle VI, §41): separate customer/admin guards + server-side authorization on every
protected action; **Form Requests** for all input validation; **hashed one-time OTP** with expiry +
resend cooldown + request/verify rate & attempt limits (Laravel rate limiter) + **no OTP in prod
logs**; **CSRF** on all web state-changing requests; **mass-assignment** protection via explicit
`$fillable`/Form-Request DTOs; **secure uploads** (validated mime/size, non-executable public path);
Filament admin behind auth; sensitive data kept out of logs; **no secrets in git** (`.env`, config,
`.env.example` only). Production config hardened (HTTPS, secure/session cookies, `APP_DEBUG=false`).

**Alternatives**: none (constitutional baseline; not optional).

---

## R22. Testing strategy (pragmatic levels)

**Decision** (Principle VII, §45):
- **Unit/domain (mandatory)**: `PricingService` tiers + **lower-of** rule; `DeliveryService`
  discounts (largest-saving, tie-break, floor-0); minimum-order (effective subtotal basis);
  `Money` arithmetic/rounding; **status transition** validator.
- **Feature**: OTP request/verify/resend/lockout; profile onboarding gating; catalog browse/search;
  cart add/update/remove + availability flags; **checkout revalidation** + changed-terms; order
  creation + snapshot immutability; cancellation rules; admin status update.
- **Boundary (explicit)**: tier edges **4→5, 9→10**; minimum **499 blocked / 500 allowed**; delivery
  discount thresholds; **expired offer**; **inactive area**; **inactive slot**; **out-of-stock**;
  **double submission** idempotency.
- **Levels**: prefer unit + feature (HTTP) tests; **minimal** browser automation (only where JS
  interaction is essential). Framework: **Pest** (readable, first-class Laravel) or PHPUnit.

**Rationale**: constitution mandates commercial-logic tests; boundary cases prevent money bugs.

**Alternatives**: heavy end-to-end browser suites (rejected: slow, brittle, §45).

---

## R23. Deployment & environments

**Decision** (§47/§48): Shared hosting with **PHP 8.2** (+ extensions: `intl`, `pdo_mysql`,
`mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`/`imagick`),
**MySQL 8**, HTTPS, writable `storage/`+`bootstrap/cache`, `storage:link`, a single **cron** running
`schedule:run` (for **scheduled maintenance** such as OTP cleanup; it also drains the DB queue **only
if** the async OTP fallback of R16 is enabled — not required by the sync-default MVP). Composer install
`--no-dev --optimize-autoloader` **with the production platform pinned to PHP 8.2** (R0a) and
`composer check-platform-reqs` run as a deploy gate; Node build for Tailwind/Alpine assets (built
artifacts deployed; no Node needed in prod runtime).
**No Docker required.** Environments: **local**, **staging/demo**, **production**; OTP driver `log`
in non-prod, real provider in prod (production must never expose test OTP). **Upgrade path**: VPS →
Redis (cache/queue) → persistent workers → object storage → CDN, all config-level, no redesign.

**Rationale**: constitution infra constraints; low-cost first; upgradeable.

**Alternatives**: Docker/Kubernetes (rejected MVP); serverless (rejected: mismatch).

---

## Risks & mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Filament plugin lag on Laravel 12 | Low/Med | Current Filament **v5** core only; **no unnecessary plugins**; verify any plugin's Composer reqs (php + illuminate) before adding |
| PHP 8.3+ creep via a transitive dep | Medium | **Composer `config.platform.php = 8.2.x`** emulation (R0a) + `composer check-platform-reqs` deploy gate; verify every significant dep before adding |
| **Laravel 12 lifecycle** (security-fixes period, EOL **Feb 2027**) | Medium | PHP-8.2 compatibility stays correct; documented **infrastructure upgrade trigger**: when hosting can run PHP 8.3+, evaluate upgrading Laravel to the then-current supported major. Not MVP scope; does not block implementation (see below) |
| Money/rounding correctness | High | Integer minor units + central `Money` (definitive, R2) + boundary unit tests |
| Changed-terms UX confusing | Medium | Structured `CheckoutReview.changes[]` + dedicated Step-2 state |
| Shared-hosting OTP send latency/reliability | Medium | **Sync with strict provider timeouts** (R16 default); async **DB-queue+cron** fallback only if provider needs it; provider behind `OtpProvider` contract |
| Arabic search quality in MySQL | Medium | `utf8mb4_unicode_ci`; indexed `LIKE`/prefix default; **FULLTEXT only if data shows LIKE insufficient** (not mandatory) |
| Order-number race under load | Low | `AUTO_INCREMENT`-derived `ORD-######` inside the TX + `UNIQUE(order_number)` backstop + retry (R9); no counter lock |
| Double-submit duplicate orders | Medium | One-time submission token + transaction + unique guard |
| PWA leaking private/stale customer data via cache | Medium | SW caches **static assets only**; authenticated routes (profile/cart/checkout/orders/OTP) **excluded** from cache, network-driven (R17) |

### Laravel 12 lifecycle note (maintenance risk, not MVP scope)

Laravel **12 is retained** because **PHP 8.2 is a hard server constraint** and **Laravel 13 requires
PHP 8.3+**. Laravel 12 is currently in its **security-fixes support period**, with security support
continuing until its documented **EOL around February 2027**. **Infrastructure upgrade trigger**: when
the hosting environment can support **PHP 8.3+**, evaluate upgrading Laravel to the then-current
supported major (and, if desired, Filament to its then-current major). This is a **documented
maintenance risk**, **not** an MVP scope addition, and **must not block** implementation.

## Unresolved technical decisions

**None blocking.** Deferred-by-design (documented, not required for MVP): exact SMS/WhatsApp OTP vendor
(behind `OtpProvider`, R7); **real English translations** for the already-present `*_en` columns
(populated when the business provides them — the localization **schema is settled**, `*_ar`/`*_en` from
day one, R14); FULLTEXT-vs-LIKE tuning (data-volume dependent, R15); exact Composer `platform.php` patch
(chosen at foundation, R0a); Laravel-major upgrade (triggered by future PHP 8.3+ hosting, lifecycle note
above). None block `/speckit-tasks`.
