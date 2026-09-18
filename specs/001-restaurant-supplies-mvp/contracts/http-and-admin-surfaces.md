# Contracts — HTTP Routes & Admin (Filament) Surface Map

**Feature**: `001-restaurant-supplies-mvp` · **Phase**: 1 (design) · **Date**: 2026-09-17
**Companion to**: [service-contracts.md](./service-contracts.md), [plan.md](../plan.md),
[screen-specifications.md](../design/screen-specifications.md), [admin-design.md](../design/admin-design.md).

Conceptual surface map (no routes/controllers/resources created). Route **names/paths are
language-neutral** (§18); UI copy is localized (Arabic MVP). All controllers/Filament actions
**orchestrate services** (Principle III) — no business rules here. Screen IDs (C01–C17) map to
[screen-specifications.md]; admin IDs (A01–A15) map to [admin-design.md].

## Guards / middleware
- **`web`** (public + admin) and **`customer`** guard (session, OTP-authenticated) — separate
  identities (R8).
- Customer ordering routes: `auth:customer` + **onboarding-complete** gate (FR-006/FR-009).
- Admin: Filament panel behind `auth` (`users`) — separate panel.
- CSRF on all state-changing web requests; rate limiting on OTP endpoints (Principle VI).

## Public routes (no auth) — Blade
| Method | Path (neutral) | Screen | Purpose | Service |
|---|---|---|---|---|
| GET | `/` | C01 Landing | landing (hero, categories preview, offers, how-it-works, coverage, CTA, footer) | CatalogService, PromotionService, SettingsService |
| GET | `/login` | C02 Phone | phone entry | — |
| POST | `/otp/request` | C02→C03 | request OTP (rate-limited) | OtpService.request |
| GET | `/verify` | C03 OTP | OTP entry | — |
| POST | `/otp/verify` | C03 | verify OTP → session; route to onboarding or home | OtpService.verify, CustomerService |
| POST | `/otp/resend` | C03 | resend (cooldown) | OtpService.request |

## Customer routes (`auth:customer`) — Blade + Alpine (PWA shell)
| Method | Path | Screen | Purpose | Service |
|---|---|---|---|---|
| GET | `/onboarding/profile` | C04 | business profile form (pre-order gate) | CustomerService |
| POST | `/onboarding/profile` | C04 | save profile | CustomerService.completeOnboarding |
| GET | `/onboarding/address` / `/profile/address` | C05/C17 | address form (reused) | CustomerService, DeliveryService (active areas) |
| POST | `/profile/address` | C05 | save/update default address | CustomerService.setDefaultAddress |
| GET | `/home` | C06 | hub: search, categories, offers strip | CatalogService, PromotionService |
| GET | `/search` | C07 | search results (name/brand) | CatalogService.search |
| GET | `/categories` / `/categories/{category}` | C06/C08 | category listing | CatalogService |
| GET | `/products/{product}` | C09 | product details (units, tiers, applied best price) | CatalogService, PricingService |
| GET | `/cart` | C10 | cart (estimates, min-order progress) | CartService.view |
| POST | `/cart/items` | C10 | add item (unit+qty) | CartService.add |
| PATCH | `/cart/items/{item}` | C10 | update quantity | CartService.updateQuantity |
| DELETE | `/cart/items/{item}` | C10 | remove item | CartService.remove |
| GET | `/checkout/delivery` | C11 | Step 1: address/area/date/slot/COD | DeliveryService, CustomerService |
| POST | `/checkout/delivery` | C11→C12 | persist delivery choices; go to review | DeliveryService (validate) |
| GET | `/checkout/review` | C12 | Step 2: full summary + revalidation (changed-terms) | OrderService.review |
| POST | `/checkout/confirm` | C12 | place order (idempotent, submissionToken) | OrderService.place |
| GET | `/orders/success/{order}` | C13 | success (number/total/date/slot/address/status) | — |
| GET | `/orders` | C14 | order history (own only) | OrderService/queries |
| GET | `/orders/{order}` | C15 | order details (immutable snapshot) + cancel-if-allowed | OrderService |
| POST | `/orders/{order}/cancel` | C15 | customer self-cancel (only if `new`) | OrderService.transition |
| GET | `/profile` | C16 | profile view/edit | CustomerService, SettingsService |
| POST | `/logout` | — | end customer session | — |

**Checkout CTAs** (Arabic, D3): Step 1 `مراجعة الطلب` → `/checkout/review`; Step 2 `تأكيد الطلب` →
`/checkout/confirm`. `POST /checkout/confirm` carries a one-time **submissionToken** (idempotency,
R11); on changed terms it re-renders C12 with the diff (no placement, FR-044); on offline/failure it
never shows success (FR-047).

## Response/behavior contracts (customer)
- All list/detail endpoints return the screen's declared **states** (loading is client, empty/error
  server-rendered) per screen-specs (FR-068).
- Prices displayed via `MoneyFormatter` → `444 ج` (D2); estimates labelled in cart (FR-028).
- Availability: OoS product viewable but not orderable (FR-015); inactive hidden (FR-016).

## PWA endpoints/assets  `[R17, FR-066/FR-067]`
| Item | Notes |
|---|---|
| `/manifest.webmanifest` | name/short_name/description (Arabic; localizable later), theme/background, icons + maskable |
| `/sw.js` | service worker: **static-only cache** (versioned/hashed CSS+JS, icons, logo, safe public shell, offline fallback); **no** offline ordering/sync |
| `/offline` | static offline fallback (Blade) |
| icons | `public/icons/*` (192/512 + maskable) |

**SW cache privacy (MUST, R17)**: the service worker **must NOT cache** authenticated/customer-specific
responses — **`/profile`, `/profile/address`, `/cart` (+ `/cart/items*`), `/checkout/*`, `/orders*`,
`/onboarding/*`, `/otp/*`, `/verify`** and any authenticated document/JSON stay **network-driven** and
are **explicitly excluded** from cache storage (no `caches.put`). Only static assets + the offline
fallback are cached. This prevents leaking profile/address/cart/checkout/order/OTP data or serving
stale commercial terms.

## Admin (Filament v5) panel — `/admin` (guard: `users`)  `[R19, admin-design.md]`
Arabic-first (RTL). Every action **calls domain services**; no rule duplication (Principle III).

| Admin ID | Filament surface | Purpose | Service(s) |
|---|---|---|---|
| A01 | Panel login | staff auth (`users`) | — |
| A02 | Dashboard (widgets) | new/today/recent orders + indicators (+ **out-of-stock units count**); **polling** (no WS) | Order queries / InventoryService |
| A03 | Orders — List (filters: status/date/area/search) | triage/manage | OrderService/queries |
| A04 | Orders — View/Edit (status actions) | details + **status transition** + cancel-if-allowed | OrderService.transition |
| A05 | Customers — List (search) | directory | queries |
| A06 | Customers — View (+ order history) | profile + orders | queries |
| A07 | Categories Resource | CRUD + active + reorder | CatalogService/model |
| A08 | Products — List (availability quick-toggle) | catalog mgmt | CatalogService |
| A09 | Products — Edit (**tabs**: General/Media/Selling Units/Pricing/Offers/Availability/**Stock**) | product + units + tiers + offers + **per-unit stock + adjust** | Catalog/Pricing/Promotion/**Inventory** |
| A10 | Offers Resource (cross-product) | manage offers | PromotionService |
| A11 | Delivery Areas Resource | name/base_fee/active | model |
| A12 | Delivery Slots Resource | label/day_of_week/start_time/end_time/active/sort (no capacity) | model |
| A13 | Delivery Discount Rules Resource | type/value/min_subtotal/active | model/DeliveryService validation |
| A14 | Settings Page | minimum order + business/contact info | SettingsService |
| A15 | Inventory (prompt 32) | per-unit **stock column**, add/remove/correct actions (qty + reason + preview, never < 0), **read-only adjustment history**, in-/out-of-stock filter | InventoryService / AdjustInventoryAction |

Notes: **status change** (A04) is the highest-frequency op → one-click next-status + cancel via
`OrderService::transition` (validated, R10). **Availability toggle** (A08) inline. Product admin is
**tabbed** (A09), never one giant form (admin-design §8). Filament uses database **notifications +
polling** for new-order visibility (R16/FR-055). New orders visible without WebSockets.
**Inventory (prompt 32):** stock is deducted inside `OrderService::place` via
`InventoryService::deductForOrder` (row-locked, no overselling) and restored on eligible
cancellation via `InventoryService::restoreForCancellation` (idempotent). Customer add-to-cart and
checkout reject a unit with `stock_quantity = 0` or a requested qty exceeding stock (Arabic message,
no silent qty reduction); exact quantities are admin-only.

## Future API (not built in MVP, readiness only)  `[Principle I, §29]`
The same services back a future JSON API (`/api/*`, token/Sanctum) for a Flutter app — no business
logic duplication; responses would carry neutral identifiers + `Money` + a locale for presentation.
Explicitly **out of MVP**; documented so routing/services don't preclude it.
