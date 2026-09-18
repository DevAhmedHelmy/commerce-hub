---
description: "Implementation task breakdown — Restaurant Supplies Ordering MVP"
---

# Tasks: Restaurant Supplies Ordering MVP

**Input**: Design documents from `specs/001-restaurant-supplies-mvp/`
**Prerequisites**: plan.md, spec.md, research.md (R0–R23), data-model.md (17 tables),
contracts/service-contracts.md, contracts/http-and-admin-surfaces.md, quickstart.md, design/

**Tests**: **MANDATORY** for business-critical logic (Constitution Principle VII + prompt 10 §5).
Test tasks are included and are **NOT optional** for pricing, offers, cart, minimum order, delivery
fees/discounts, order creation/snapshots, status transitions, OTP, and checkout revalidation.

## HARD CONSTRAINTS (prompt 10)

- **Laravel application root = `src/`** at repository root. Every implementation path targets
  `src/...` (e.g. `src/app/...`, `src/routes/...`, `src/resources/...`, `src/database/...`,
  `src/tests/...`, `src/public/...`). Spec/design/docs stay outside `src/`.
- Stack: **PHP 8.2 (max), Laravel 12.x, MySQL 8, Blade + Alpine.js + Tailwind, Filament v5 (`^5.8`)**,
  PWA, one modular monolith, one MySQL DB. **No** Redis / WebSocket / Docker-prod / PHP 8.3+ deps.
- Business logic lives in `src/app/Domain/<Module>` services; controllers + Filament orchestrate only.
- Money = **integer minor units** + central `Money`. Domain identifiers **language-neutral**; Arabic-only
  UI (no switcher); managed content uses paired **`*_ar`/`*_en`** columns (R14).

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependency on an incomplete task)
- **[Story]**: US1–US13 (maps to spec.md user stories); Setup/Foundational/Polish have no story label

## User story → priority map

| Story | Title | Priority |
|---|---|---|
| US1 | Customer Sign-In & Onboarding | P1 |
| US2 | Catalog Browsing & Product Discovery | P1 |
| US3 | Cart, Selling Units & Tiered Pricing | P1 |
| US4 | Checkout & COD Order Placement | P1 |
| US5 | Admin Catalog & Commerce Management | P1 |
| US6 | Admin Order Management & Lifecycle | P1 |
| US7 | Admin Delivery & Store Setup | P1 |
| US8 | Delivery Discount Rules | P2 |
| US9 | Customer Order History with Immutable Snapshots | P2 |
| US10 | Public Landing Page | P2 |
| US11 | Admin Customer Directory | P2 |
| US12 | PWA Installability & Connectivity Integrity | P3 |
| US13 | UX Quality & Comprehensive State Coverage | P3 |

---

## Phase A — Repository & Laravel Foundation (Setup)

**Purpose**: Stand up the Laravel 12 app inside `src/` with the verified stack. No business features.

- [X] T001 Create `src/` directory at repository root (Laravel application root per hard constraint)
- [X] T002 Install Laravel **12.x** into `src/` (`composer create-project laravel/laravel:^12.0 .` run inside `src/`); confirm `src/public/index.php` is the web entry
- [X] T003 Pin production runtime in `src/composer.json` — add `config.platform.php = 8.2.x` and a `check-platform-reqs` composer script; verify `require php: ^8.2` (R0a)
- [X] T004 Configure `src/.env` and `src/.env.example` — `APP_LOCALE=ar`, `APP_FALLBACK_LOCALE=ar`, `APP_TIMEZONE`, MySQL vars, `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=sync`, `FILESYSTEM_DISK=public`, `OTP_DRIVER=log` (no secrets committed)
- [X] T005 Configure MySQL 8 connection with `utf8mb4` / `utf8mb4_unicode_ci` defaults in `src/config/database.php`
- [X] T006 Set Arabic default + fallback locale and RTL-aware config in `src/config/app.php` (and locale bootstrapping)
- [X] T007 [P] Install & configure Tailwind CSS + Alpine.js via Vite — `src/vite.config.js`, `src/tailwind.config.js` (logical/RTL-friendly), `src/resources/css/app.css`, `src/resources/js/app.js`
- [X] T008 Install **Filament v5** (`composer require filament/filament:^5.8`) and generate the admin panel provider `src/app/Providers/Filament/AdminPanelProvider.php` (path `/admin`, guard `users`, Arabic/RTL); ensure `ext-intl` documented
- [X] T009 [P] Install **Pest** dev tooling (`composer require --dev pestphp/pest pestphp/pest-plugin-laravel`) and initialize `src/tests/Pest.php`, `src/phpunit.xml` with a MySQL test connection
- [X] T010 [P] Run `php artisan storage:link` and confirm the local `public` disk for media (R18)
- [X] T011 Add PWA foundation stubs — routes for `/manifest.webmanifest` and `/offline`, and SW registration hook in `src/resources/js/app.js` (full SW in Phase M)
- [X] T012 [P] Align `docs`/quickstart references to the `src/` layout in project `README.md` (outside `src/`)

**Checkpoint**: `php artisan serve` runs from `src/`; `/admin` loads Filament login; assets build.

---

## Phase B — Shared Foundations (Foundational)

**⚠️ BLOCKS all user stories.** Money, enums, formatters, localization, settings, base UI.

- [X] T013 [P] Create language-neutral enums in `src/app/Domain/Support/Enums/` — `OrderStatus`, `AvailabilityStatus`, `DiscountType`, `PaymentMethod`, `AppliedPriceSource`, `UnitCode`
- [X] T014 [P] Implement `Money` value object (integer minor units, EGP, plus/minus/times/compareTo/min, half-up rounding defined once) in `src/app/Domain/Support/Money.php`
- [X] T015 Implement `MoneyFormatter` (`444 ج`; two decimals only if piastres; Latin digits) in `src/app/Domain/Support/MoneyFormatter.php` and Blade component `src/resources/views/components/price.blade.php` (depends on T014)
- [X] T016 [P] Implement locale-aware date/time presentation formatter in `src/app/Domain/Support/DateTimeFormatter.php` (stored timestamps neutral)
- [X] T017 [P] Implement `LocalizedContent` resolver (`*_en ?: *_ar`) + `HasLocalizedText` model trait in `src/app/Domain/Support/LocalizedContent.php` and `src/app/Domain/Support/Concerns/HasLocalizedText.php` (R14)
- [X] T018 [P] Create Arabic translation catalogs in `src/lang/ar/` — `validation.php`, `messages.php`, `domain.php` (status/discount/availability/payment labels keyed by neutral identifier); no hard-coded UI strings
- [X] T019 [P] Implement `MediaService` (validated mime jpeg/png/webp, size limit, ULID path `products/{ulid}.{ext}`, thumbnail) in `src/app/Domain/Support/MediaService.php` (disk-abstracted, R18)
- [X] T020 [P] Create RTL base layouts — `src/resources/views/layouts/app.blade.php` (customer PWA shell) and public layout, with `dir="rtl"` + logical properties (design D1/D2)
- [X] T021 [P] Create reusable state components in `src/resources/views/components/states/` — `loading`, `empty`, `error` (design §13/FR-068 foundations)
- [X] T022 Create `settings` migration (key-value, `UNIQUE(key)`) in `src/database/migrations/` and `Setting` model in `src/app/Models/Setting.php` (data-model #17)
- [X] T023 Implement `SettingsService` (`minimumOrderAmount(): Money`, `businessInfo()`, `update()` with cache invalidation) in `src/app/Domain/Settings/SettingsService.php` (depends on T014, T022)
- [X] T024 Unit test `Money` arithmetic, rounding (half-up), min(), and formatter output in `src/tests/Unit/MoneyTest.php` (depends on T014/T015 — not parallel with them)

**Checkpoint**: Money/enums/formatters/localization/settings usable by all stories.

---

## Phase C — Authentication & Customer Onboarding (US1) 🎯 MVP

**Goal**: Phone → OTP → verify → session; first-time profile + default address; returning users skip profile.
**Independent Test**: With `OTP_DRIVER=log`, a new number completes OTP + profile and lands in the app; the same number signing in again skips profile; incomplete profile cannot order.

### Tests (MANDATORY)

- [ ] T025 [P] [US1] Feature test OTP request/verify happy path + `needs_onboarding` in `src/tests/Feature/Auth/OtpFlowTest.php`
- [ ] T026 [P] [US1] Feature test OTP edge cases — expired, invalid code, already-consumed, resend cooldown, attempt limit, request rate limit in `src/tests/Feature/Auth/OtpAbuseTest.php`
- [ ] T027 [P] [US1] Feature test onboarding gating — incomplete customer blocked from ordering; returning customer skips profile in `src/tests/Feature/Auth/OnboardingGateTest.php`

### Implementation

- [ ] T028 [P] [US1] Create migrations `customers`, `customer_addresses`, `otp_verifications` in `src/database/migrations/` (data-model #2, #3, #16; indexes `UNIQUE(phone)`, `INDEX(phone,expires_at)`)
- [ ] T029 [P] [US1] Create models `Customer`, `CustomerAddress`, `OtpVerification` in `src/app/Models/` (Customer soft-deletes; casts; relations)
- [ ] T030 [US1] Configure the `customer` auth guard + provider (session) in `src/config/auth.php`; keep `users` guard for admin (R8)
- [ ] T031 [P] [US1] Define `OtpProvider` contract in `src/app/Domain/Auth/Contracts/OtpProvider.php` and `LogOtpProvider` (dev/demo, non-prod) in `src/app/Domain/Auth/Providers/LogOtpProvider.php`; bind by `OTP_DRIVER` in a service provider
- [ ] T032 [US1] Implement `OtpService::request()` / `verify()` — hashed code, expiry, one-time consume, resend cooldown, request rate limit, verify attempt limit/lockout, distinguish incorrect vs expired, never log the code in prod — in `src/app/Domain/Auth/OtpService.php` (depends on T028, T031)
- [ ] T033 [US1] Implement `CustomerService` (`findOrCreateByPhone`, `completeOnboarding`, `setDefaultAddress`, `updateDefaultAddress`, `canPlaceOrders`) in `src/app/Domain/Customers/CustomerService.php` (depends on T029)
- [ ] T034 [P] [US1] Form Requests (rules separate from Arabic messages) — `RequestOtpRequest`, `VerifyOtpRequest`, `CompleteProfileRequest`, `SaveAddressRequest` in `src/app/Http/Requests/Auth/`
- [ ] T035 [US1] Auth controllers + routes (`/login`, `/otp/request`, `/verify`, `/otp/verify`, `/otp/resend`, `/onboarding/profile`, `/onboarding/address`) in `src/app/Http/Controllers/Auth/` and `src/routes/web.php` (rate-limited, CSRF)
- [ ] T036 [US1] `onboarding-complete` middleware gate for customer ordering routes in `src/app/Http/Middleware/EnsureOnboarded.php` (FR-009)
- [ ] T037 [P] [US1] Blade screens C02 phone, C03 OTP (resend/cooldown), C04 profile, C05 address in `src/resources/views/auth/` (RTL, states)

**Checkpoint**: US1 fully functional and independently testable.

---

## Phase D — Catalog: Admin Management + Customer Browsing (US5 admin, US2 customer)

**Goal (US5)**: Admin creates categories, products (brand, image, availability), and selling units.
**Goal (US2)**: Customer browses categories, searches by name/brand, opens product detail with units.
**Independent Test**: Admin creates a category + product with two units; customer sees them, searches, opens detail; OoS viewable-not-orderable, inactive hidden, empty states correct.

### Tests (MANDATORY where commercial/behavioral)

- [ ] T038 [P] [US2] Feature test catalog browse + availability rules (OoS visible-unorderable, inactive hidden, empty category state) in `src/tests/Feature/Catalog/BrowseTest.php`
- [ ] T039 [P] [US2] Feature test search by Arabic name + brand, with empty-result state, in `src/tests/Feature/Catalog/SearchTest.php`

### Implementation

- [ ] T040 [P] [US5] Migrations `categories`, `products`, `product_units` in `src/database/migrations/` — paired `*_ar`/`*_en` managed columns, single `brand`, `availability` enum, indexes `products(category_id,availability,sort_order)`, `products(name_ar)`, `products(brand)`, `product_units(product_id,is_active,sort_order)` (data-model #4,#5,#6; R14)
- [ ] T041 [P] [US5] Models `Category`, `Product`, `ProductUnit` (use `HasLocalizedText`; relations; casts; default-unit guard) in `src/app/Models/`
- [ ] T042 [US2] Implement `CatalogService` (`activeCategories`, `productsInCategory` paginated, `search` name_ar/brand, `productDetail` with units+availability) in `src/app/Domain/Catalog/CatalogService.php` (depends on T041)
- [ ] T043 [P] [US5] Filament `CategoryResource` (CRUD, active toggle, reorder) in `src/app/Filament/Resources/CategoryResource.php` (A07)
- [ ] T044a [US5] Filament `ProductResource` core + General tab (category, `name_ar`/`name_en`, `brand`, `description_ar`/`description_en`, sort, list with availability column) in `src/app/Filament/Resources/ProductResource.php` (A08/A09; no business logic)
- [ ] T044b [US5] Product Media tab (image upload via `MediaService`) + Availability quick-toggle (available/out_of_stock/inactive) in `src/app/Filament/Resources/ProductResource.php` + `src/app/Filament/Resources/ProductResource/Pages/` (depends on T044a, T019)
- [ ] T044c [US5] Selling Units relation manager (code, `display_name_ar`/`display_name_en`, package desc, `base_price`, default/active/sort) in `src/app/Filament/Resources/ProductResource/RelationManagers/UnitsRelationManager.php` (depends on T044a, T041)
- [ ] T045 [P] [US2] Customer catalog controllers + routes `/home` (C06), `/categories` + `/categories/{category}` (C08), `/search` (C07), `/products/{product}` (C09) in `src/app/Http/Controllers/Catalog/` and `src/routes/web.php`
- [ ] T046 [P] [US2] Blade views + components — home hub, category listing, search results, product detail (unit selector, availability badge) in `src/resources/views/catalog/` and `src/resources/views/components/` (ProductCard **does NOT add to cart**; add happens on detail — prompt §Phase D)
- [ ] T047 [US2] Wire listing cards to `PricingService::baselineFromPrice` placeholder (lightweight "from" price only; full pricing in Phase E) — mark card price integration point in `src/resources/views/components/product-card.blade.php`

**Checkpoint**: Admin-created catalog is browsable/searchable by customers with correct states.

---

## Phase E — Pricing & Offers (US3 pricing, US5 admin) — BUSINESS-CRITICAL

**Goal**: Deterministic unit/tier/offer pricing with the lower-of rule; admin manages tiers + offers.
**Independent Test**: Seed a unit with tiers + an active offer; `PricingService` returns the correct applied price and source for boundary quantities; expired/inactive offers never apply.

### Tests (MANDATORY — NON-OPTIONAL)

- [ ] T048 [P] [US3] Unit test base price + tier boundaries **4→5** and **9→10** (up and down) in `src/tests/Unit/Pricing/TierBoundaryTest.php`
- [ ] T049 [P] [US3] Unit test offer eligibility (active in-range applies; expired/inactive/out-of-range never applies) in `src/tests/Unit/Pricing/OfferEligibilityTest.php`
- [ ] T050 [P] [US3] Unit test **lower-of** tier vs offer, never stacked (`200/170/160→160`; `200/150/160→150`; only-one; neither→normal) + `PriceResult` fields/source in `src/tests/Unit/Pricing/LowerOfRuleTest.php`
- [ ] T051 [P] [US3] Unit test flat-priced unit (no tiers → no tier messaging) and `baselineFromPrice` in `src/tests/Unit/Pricing/BaselinePriceTest.php`

### Implementation

- [ ] T052 [P] [US5] Migrations `product_price_tiers`, `product_offers` in `src/database/migrations/` (indexes `product_price_tiers(product_unit_id,min_quantity)`, `product_offers(product_unit_id,is_active,starts_at,ends_at)`; offer `title_ar/title_en`) (data-model #7,#8; R14)
- [ ] T053 [P] [US5] Models `ProductPriceTier`, `ProductOffer` (casts; validity scopes) in `src/app/Models/`
- [ ] T054 [P] [US3] `PriceResult` DTO in `src/app/Domain/Pricing/PriceResult.php` (base/tier?/offer?/applied/applied_source/unit_saving/quantity/line_total)
- [ ] T055 [US3] `PromotionService::activeOfferFor(unit, at)` (server-evaluated validity) in `src/app/Domain/Promotions/PromotionService.php` (depends on T053)
- [ ] T056 [US3] `PricingService::priceFor(unit, qty, at)` (lower-of, deterministic) + `baselineFromPrice()` in `src/app/Domain/Pricing/PricingService.php` (depends on T053, T054, T055)
- [ ] T057 [US2] Wire product detail + listing cards to real `PricingService` (detail = full evaluation; cards = baseline only, §44) in `src/app/Http/Controllers/Catalog/` and card component
- [ ] T058 [US5] Admin pricing tiers management (relation manager under `ProductResource` Pricing tab, non-overlapping ascending validation) in `src/app/Filament/Resources/ProductResource/` (calls model/service)
- [ ] T059 [US5] Filament `OfferResource` cross-product (normal/offer price, dates, active) in `src/app/Filament/Resources/OfferResource.php` (A10)

**Checkpoint**: Pricing is deterministic, explainable, tested at boundaries; admin manages tiers/offers.

---

## Phase F — Cart & Minimum Order (US3)

**Goal**: One persistent DB cart per customer; add/update/remove; server-recomputed estimates; minimum-order progress.
**Independent Test**: Customer adds items, crosses tiers up/down, switches units; subtotal + min-order progress correct; unavailable items flagged/excluded; cart shows **no** authoritative delivery estimate.

### Tests (MANDATORY)

- [ ] T060 [P] [US3] Feature test cart add/update/remove + tier recalculation on quantity change in `src/tests/Feature/Cart/CartOperationsTest.php`
- [ ] T061 [P] [US3] Feature test unavailable item flagged & excluded from valid checkout; offer expiry updates estimate in `src/tests/Feature/Cart/CartAvailabilityTest.php`
- [ ] T062 [P] [US3] Unit/feature test minimum order on effective subtotal — **499 blocked / 500 allowed** (excludes delivery) in `src/tests/Feature/Cart/MinimumOrderTest.php`

### Implementation

- [ ] T063 [P] [US3] Migrations `carts` (**`UNIQUE(customer_id)`**, no status column) and `cart_items` (`UNIQUE(cart_id,product_unit_id)`, `quantity>=1`, optional non-authoritative `last_seen_unit_price`) in `src/database/migrations/` (data-model #9,#10; R4)
- [ ] T064 [P] [US3] Models `Cart`, `CartItem` in `src/app/Models/`
- [ ] T065 [US3] `CartView` DTO (lines with `PriceResult` + availability flags, `product_subtotal`, `meets_minimum`, `minimum_order_amount`, `remaining_to_minimum`) in `src/app/Domain/Cart/CartView.php`
- [ ] T066 [US3] `CartService` (`getOrCreate` single row, `add`, `updateQuantity`, `remove`, `view` recompute via PricingService + SettingsService) in `src/app/Domain/Cart/CartService.php` (depends on T056, T023, T064)
- [ ] T067 [US3] Cart controller + routes `/cart` (C10), `POST /cart/items`, `PATCH /cart/items/{item}`, `DELETE /cart/items/{item}` in `src/app/Http/Controllers/Cart/` and `src/routes/web.php` (auth:customer + onboarded)
- [ ] T068 [P] [US3] Cart Blade C10 with quantity/unit controls, estimate labels (FR-028), min-order progress, unavailable flags, **no delivery-fee estimate**, in `src/resources/views/cart/` + `src/resources/views/components/cart-item.blade.php`

**Checkpoint**: US3 cart works end-to-end with tested pricing and minimum-order rules.

---

## Phase G — Delivery Administration & Calculation (US7 areas/slots/fees, US8 discounts)

**Goal (US7)**: Admin manages delivery areas (+base fee), recurring weekday slots, and the minimum order.
**Goal (US8)**: Deterministic delivery-discount resolution (largest single saving, tie-break, floor 0).
**Independent Test**: Seed area + slots + rules; `DeliveryService::quote` returns correct base/rule/discount/final across thresholds; inactive area/slot rejected; slot weekday must match date.

### Tests (MANDATORY)

- [ ] T069 [P] [US8] Unit test delivery discounts — fixed, percentage, free-delivery saving computation in `src/tests/Unit/Delivery/DiscountTypesTest.php`
- [ ] T070 [P] [US8] Unit test multiple-eligible → largest saving, tie-break higher `min_subtotal`, no stacking, floor 0 / cannot exceed fee in `src/tests/Unit/Delivery/DiscountResolutionTest.php`
- [ ] T071 [P] [US7] Feature test area eligibility (inactive blocked) and slot selectability (inactive rejected, weekday match, not past) in `src/tests/Feature/Delivery/AvailabilityTest.php`

### Implementation

- [ ] T072 [P] [US7] Migrations `delivery_areas`, `delivery_slots` (weekday model: `day_of_week`, `start_time`, `end_time`, `label_ar/label_en`), `delivery_discount_rules` in `src/database/migrations/` (indexes `delivery_areas(is_active,sort_order)`, `delivery_slots(day_of_week,is_active,sort_order)`, `delivery_discount_rules(is_active,min_subtotal)`) (data-model #11,#12,#13; R5/R6)
- [ ] T073 [P] [US7] Models `DeliveryArea`, `DeliverySlot`, `DeliveryDiscountRule` (HasLocalizedText where applicable) in `src/app/Models/`
- [ ] T074 [P] [US8] `DeliveryQuote` DTO (base_fee, applied_rule_id?, applied_rule_type?, discount_amount, final_fee) in `src/app/Domain/Delivery/DeliveryQuote.php`
- [ ] T075 [US8] `DeliveryService` (`isAreaOrderable`, `quote` largest-saving/tie-break/floor-0, `isSlotSelectable`, `availableSlots(date)` by weekday) in `src/app/Domain/Delivery/DeliveryService.php` (depends on T073, T074)
- [ ] T076 [P] [US7] Filament `DeliveryAreaResource` (name_ar/en, base_fee, active, sort) in `src/app/Filament/Resources/DeliveryAreaResource.php` (A11)
- [ ] T077 [P] [US7] Filament `DeliverySlotResource` (label_ar/en, day_of_week, start/end time, active, sort — no capacity) in `src/app/Filament/Resources/DeliverySlotResource.php` (A12)
- [ ] T078 [P] [US8] Filament `DeliveryDiscountRuleResource` (type, value, min_subtotal, active; validation percentage 0–100) in `src/app/Filament/Resources/DeliveryDiscountRuleResource.php` (A13)
- [ ] T079 [US7] Filament `Settings` page (minimum order amount + business/contact info via `SettingsService`) in `src/app/Filament/Pages/ManageSettings.php` (A14)

**Checkpoint**: Delivery fees/discounts deterministic and tested; admin can configure delivery + settings.

---

## Phase H — Checkout: two-step + revalidation (US4)

**Goal**: Step 1 Delivery → Step 2 Review & Confirm; server revalidates everything; changed terms force re-review; below-minimum blocked.
**Independent Test**: With a valid cart/area/slot, review shows full server-computed summary; when a price/offer/availability/fee/slot/area changed since carting, confirm is blocked with a structured diff.

### Tests (MANDATORY)

- [ ] T080 [P] [US4] Feature test checkout review builds correct summary + blocks below minimum in `src/tests/Feature/Checkout/ReviewSummaryTest.php`
- [ ] T081 [P] [US4] Feature test changed-terms detection — price change, expired offer, out-of-stock, unit inactive, delivery-fee change, discount change, inactive slot, inactive area → review required, no placement in `src/tests/Feature/Checkout/ChangedTermsTest.php`

### Implementation

- [ ] T082 [P] [US4] DTOs `CheckoutReview`, `Change`, `Problem`, `CheckoutInput` in `src/app/Domain/Ordering/DTO/` (message_key = translation keys, never branched on)
- [ ] T083a [US4] Revalidation collectors — recompute each cart line (product active/available, unit active, price/tier/offer via `PricingService`), effective subtotal + minimum-order check, and `DeliveryQuote` (area active, fee, discount, slot selectable) in `src/app/Domain/Ordering/CheckoutRevalidator.php` (depends on T056, T066, T075, T023)
- [ ] T083b [US4] `OrderService::review(customer, CheckoutInput)` — assemble `CheckoutReview` from collectors, build `changes[]` (diff vs last-seen terms) + `blockers[]` (inactive area/slot, OoS, below-minimum) in `src/app/Domain/Ordering/OrderService.php` (depends on T083a, T082)
- [ ] T084 [US4] Checkout controllers + routes `GET/POST /checkout/delivery` (C11), `GET /checkout/review` (C12) in `src/app/Http/Controllers/Checkout/` and `src/routes/web.php`
- [ ] T085 [P] [US4] Blade C11 (saved-address reuse/edit, area, delivery date, slot, COD) and C12 (full summary, changed-terms state, confirm CTA) in `src/resources/views/checkout/` (RTL, states)
- [ ] T086 [US4] `CheckoutInput` Form Request + delivery-step validation (active area, selectable slot, not-past date) in `src/app/Http/Requests/Checkout/CheckoutDeliveryRequest.php`

**Checkpoint**: Review step surfaces authoritative totals and blocks stale/invalid terms.

---

## Phase I — Order Placement & Snapshots (US4 placement, US9 snapshot integrity)

**Goal**: Transactional COD order creation with immutable snapshots, human-friendly unique order number, duplicate-submit protection, cart clear, rollback on failure.
**Independent Test**: Confirming a valid review creates a `new` order with `ORD-######`; later catalog/price/offer changes do not alter the stored order; double-submit creates exactly one order.

### Tests (MANDATORY)

- [ ] T087 [P] [US4] Feature test transactional creation (order + items atomic), rollback on failure (no header without items) in `src/tests/Feature/Ordering/OrderCreationTest.php`
- [ ] T088 [P] [US4] Feature test duplicate-submit idempotency (one-time submission token → single order) and `UNIQUE(order_number)` in `src/tests/Feature/Ordering/DuplicateSubmitTest.php`
- [ ] T089 [P] [US9] Feature test immutable snapshots — change product/price/unit/offer after placement; historical order unchanged in `src/tests/Feature/Ordering/SnapshotImmutabilityTest.php`

### Implementation

- [ ] T090 [P] [US4] Migrations `orders` (snapshot columns incl. delivery date + slot label/times + money fields, `UNIQUE(order_number)`, indexes `(customer_id,created_at)`,`(status,created_at)`,`(delivery_date)`,`(created_at)`) and `order_items` (snapshot columns, nullable refs) in `src/database/migrations/` (data-model #14,#15)
- [ ] T091 [P] [US4] Models `Order`, `OrderItem` (immutable-after-create posture; casts; relations) in `src/app/Models/`
- [ ] T092 [US4] Order-number generator `ORD-######` from `AUTO_INCREMENT` id + offset inside the TX, `UNIQUE` backstop + retry in `src/app/Domain/Ordering/OrderNumber.php` (R9)
- [ ] T093a [US4] `OrderService::place()` transaction skeleton — open DB transaction, re-run revalidation (T083a), guard changed-terms (return `PlaceResult.review_if_changed`, no placement), enforce one-time `submissionToken` idempotency in `src/app/Domain/Ordering/OrderService.php` (depends on T083b, T092)
- [ ] T093b [US4] Persist immutable order header + `order_item` snapshots and allocate `order_number` within the transaction in `src/app/Domain/Ordering/OrderService.php` (depends on T093a, T090, T091)
- [ ] T093c [US4] Clear cart items on success, roll back fully on failure, return `PlaceResult` (placed | error) — no partial order, no false success in `src/app/Domain/Ordering/OrderService.php` (depends on T093b, T066)
- [ ] T094 [US4] Confirm route `POST /checkout/confirm` (one-time submission token, no false success offline/failure) in `src/app/Http/Controllers/Checkout/ConfirmController.php` and `src/routes/web.php` (FR-047)
- [ ] T095 [P] [US4] Success screen C13 `GET /orders/success/{order}` (number/total/date/slot/address/status) in `src/app/Http/Controllers/Orders/` + `src/resources/views/orders/success.blade.php`

**Checkpoint**: Customers can place an immutable COD order; snapshots proven stable.

---

## Phase J — Order Lifecycle & Customer History (US6 admin, US9 customer)

**Goal (US6)**: Admin lists/filters/opens orders and advances status per allowed transitions incl. cancellation matrix.
**Goal (US9)**: Customer views own order history + details (immutable) and self-cancels only while `new`.
**Independent Test**: Placed order shows to admin as `new`; valid transitions succeed, invalid rejected; customer sees only own orders and can cancel only from `new`.

### Tests (MANDATORY)

- [ ] T096 [P] [US6] Unit test status transition validator — allowed forward path + all disallowed transitions rejected in `src/tests/Unit/Ordering/StatusTransitionTest.php`
- [ ] T097 [P] [US6] Feature test cancellation matrix — customer cancels only `new`; admin cancels from new/confirmed/preparing/out_for_delivery; not from delivered/cancelled in `src/tests/Feature/Ordering/CancellationMatrixTest.php`
- [ ] T098 [P] [US9] Feature test customer sees only own orders; history reflects placement-time snapshot in `src/tests/Feature/Ordering/CustomerHistoryTest.php`

### Implementation

- [ ] T099 [US6] Status transition validator + `OrderService::transition(order, toStatus, actor, reason?)`, `canCustomerCancel`, `canAdminCancel` in `src/app/Domain/Ordering/OrderService.php` (R10/BR-012)
- [ ] T100 [P] [US9] Customer order list C14 `GET /orders` (own only) + details C15 `GET /orders/{order}` + `POST /orders/{order}/cancel` in `src/app/Http/Controllers/Orders/` and `src/routes/web.php`
- [ ] T101 [P] [US9] Blade C14/C15 (order card, status badge, immutable breakdown, cancel-if-`new`) in `src/resources/views/orders/` + `src/resources/views/components/order-card.blade.php`, `status-badge.blade.php`
- [ ] T102 [US6] Filament `OrderResource` list with filters (status/date/area/search) in `src/app/Filament/Resources/OrderResource.php` (A03)
- [ ] T103 [US6] Filament Order view/edit page — full detail + one-click status actions + cancel (calls `OrderService::transition`, no logic in resource) in `src/app/Filament/Resources/OrderResource/Pages/` (A04)

**Checkpoint**: Full order lifecycle works for admin and customer; transitions tested.

---

## Phase K — Admin Dashboard & Customer Directory (US6 dashboard, US11 directory)

**Goal (US6)**: Dashboard shows new/today/recent orders + today's sales, visible without realtime (polling).
**Goal (US11)**: Admin searches customers and views a customer profile + their order history.
**Independent Test**: New order appears on the dashboard within a page refresh/poll; admin searches a customer and sees their orders.

### Tests (MANDATORY where behavioral)

- [ ] T104 [P] [US6] Feature test new order becomes visible to admin via DB notification + polling (no WebSocket) in `src/tests/Feature/Admin/NewOrderVisibilityTest.php`
- [ ] T105 [P] [US11] Feature test customer directory search + order-history view in `src/tests/Feature/Admin/CustomerDirectoryTest.php`

### Implementation

- [ ] T106 [US6] Emit admin **database notification** on order placement (surfaced via Filament polling) — wire in `OrderService::place` + `src/app/Notifications/NewOrderNotification.php` (R16/FR-055)
- [ ] T107 [P] [US6] Dashboard widgets (new orders, today's orders, today's sales, recent orders) with polling in `src/app/Filament/Widgets/` (A02; efficient aggregate queries)
- [ ] T108 [P] [US11] Filament `CustomerResource` list + search in `src/app/Filament/Resources/CustomerResource.php` (A05)
- [ ] T109 [US11] Customer view page with profile + order history (eager-loaded) in `src/app/Filament/Resources/CustomerResource/Pages/` (A06)

**Checkpoint**: Admins can triage new orders and inspect customers.

---

## Phase L — Public Landing Page (US10)

**Goal**: Arabic-first landing reusing real catalog/offers/settings; graceful when empty; CTA to sign-in.
**Independent Test**: Visitor loads `/`, sees all required sections, and the CTA routes to sign-in; empty content renders gracefully.

### Tests

- [ ] T110 [P] [US10] Feature test landing renders all sections, graceful-empty, and CTA → `/login` in `src/tests/Feature/Landing/LandingPageTest.php`

### Implementation

- [ ] T111 [US10] Landing controller + `GET /` route (reuse CatalogService/PromotionService/SettingsService) in `src/app/Http/Controllers/LandingController.php` and `src/routes/web.php`
- [ ] T112 [P] [US10] Landing Blade C01 — header, hero, categories preview, offers, benefits, ordering steps, delivery coverage, CTA, footer/contact (no CMS) in `src/resources/views/landing/` (RTL, empty-safe)

**Checkpoint**: Public landing page live and linked to sign-in.

---

## Phase M — PWA Completion (US12)

**Goal**: Installable PWA; static-only caching; offline fallback; never caches authenticated content; never false-success offline.
**Independent Test**: App offers install and opens app-like; offline, an order attempt shows failure (no false success); SW does not cache profile/cart/checkout/orders/OTP responses.

### Tests

- [ ] T113 [P] [US12] Feature/asset test — manifest served, offline route present; assert SW config excludes authenticated routes in `src/tests/Feature/Pwa/PwaAssetsTest.php`

### Implementation

- [ ] T114 [P] [US12] `manifest.webmanifest` (Arabic name/short_name/description, theme/background) served via route/controller in `src/app/Http/Controllers/PwaController.php`
- [ ] T115 [P] [US12] PWA icons 192/512 + maskable in `src/public/icons/`
- [ ] T116 [US12] Service worker `src/public/sw.js` — cache **static only** (versioned CSS/JS, icons, logo, offline page); **explicitly bypass** `/profile`, `/profile/address`, `/cart*`, `/checkout/*`, `/orders*`, `/onboarding/*`, `/otp/*`, `/verify` (no `caches.put`); network-first dynamic; no offline order; no background sync (R17)
- [ ] T117 [P] [US12] Offline fallback page `/offline` (static Blade) in `src/resources/views/offline.blade.php`
- [ ] T118 [US12] Install affordance + SW registration/versioning in `src/resources/js/app.js`; ensure order confirm never reports success without server confirmation

**Checkpoint**: PWA installable; private content never cached; offline integrity holds.

---

## Phase N — Quality / Performance / Security / State Coverage (US13 + cross-cutting)

**Purpose**: Harden all stories; enforce state coverage; verify performance, security, and PHP 8.2 safety.

- [ ] T119 Authorization review — customer ownership on orders/cart, admin panel restricted to `users`, server-side gates on every protected action (`src/app/Http/Middleware/`, policies)
- [ ] T120 [P] Verify CSRF on all state-changing web routes and mass-assignment guards (`$fillable`/Form Requests) across `src/app/Models/` and `src/app/Http/Requests/`
- [ ] T121 [P] Secure file uploads (mime/size/non-executable path) audit in `src/app/Domain/Support/MediaService.php`
- [ ] T122 [P] Session/cookie security config (secure cookies, session driver) + OTP abuse protection review in `src/config/session.php` and `OtpService`
- [ ] T123 N+1 review + eager loading on catalog/orders/customer/dashboard queries; add pagination to all lists (`src/app/Domain/**`, controllers, Filament resources)
- [ ] T124 [P] Verify DB indexes match query patterns (data-model §42) via a migration audit note in `src/database/migrations/`
- [ ] T125 [P] Image optimization / WebP + thumbnail generation in `MediaService` and product card rendering
- [ ] T126a [US13] State-coverage audit for **customer** screens (home/category/search/product/cart/checkout/orders/profile) — loading/empty/error/disabled/validation/out-of-stock, against screen-specifications, in `src/resources/views/**`
- [ ] T126b [US13] State-coverage audit for **admin** (Filament) resources/pages — empty/validation/disabled states + Arabic labels, against admin-design, in `src/app/Filament/**`
- [ ] T127 [P] [US13] Arabic RTL + mixed Arabic/English (brands) + Latin-digit review across customer + admin UI; centralized `444 ج` formatting used everywhere (no inline currency)
- [ ] T128 [P] [US13] Accessibility + mobile responsiveness pass (labels, focus, contrast, touch targets) on critical screens
- [ ] T129 Run `composer check-platform-reqs` and confirm no dependency requires PHP 8.3+ (R0a)
- [ ] T130 Run full Pest suite (`php artisan test`) and ensure all mandatory business-logic tests pass

**Checkpoint**: Constitution VI/VII/VIII satisfied; performance and PHP-8.2 safety verified.

---

## Phase O — Staging / Demo Readiness

**Purpose**: Seed demonstrable data and safe demo OTP; verify installability.

- [ ] T131 [P] Seeders — admin user, categories, products, units, price tiers, offers in `src/database/seeders/`
- [ ] T132 [P] Seeders — delivery areas, weekday slots, a delivery discount rule, settings (minimum order + business info) in `src/database/seeders/`
- [ ] T133 [P] Seeder — sample orders across statuses for demo/dashboard in `src/database/seeders/`
- [ ] T134 Demo OTP safety — `LogOtpProvider` surfaces the code via a non-prod dev channel/banner only; guard prevents any static/test OTP behavior when `APP_ENV=production` (bind check in provider)
- [ ] T135 [P] Staging environment instructions (env, seed, HTTPS) appended to `quickstart.md` (docs, outside `src/`)
- [ ] T136 PWA installability manual test checklist on a supported mobile browser (docs)

**Checkpoint**: System is demoable to the client with realistic Arabic data.

---

## Phase P — Production Deployment Preparation

**Purpose**: Low-cost shared-hosting deployment (no Docker); production safety.

- [ ] T137 Document `src/public` as web root + shared-hosting deploy notes in `quickstart.md` (docs)
- [ ] T138 [P] Production `.env` template (`APP_DEBUG=false`, HTTPS, `SESSION_SECURE_COOKIE=true`, real `OTP_DRIVER`, no secrets committed) in `src/.env.example`
- [ ] T139 Production build steps — `composer install --no-dev --optimize-autoloader` (platform-pinned), `npm ci && npm run build`, `php artisan config:cache route:cache view:cache` (docs)
- [ ] T140 [P] Writable dirs (`src/storage`, `src/bootstrap/cache`) + `php artisan storage:link` in deploy notes
- [ ] T141 [P] Single cron `schedule:run` for maintenance (expired-OTP cleanup); document DB-queue drain as fallback-only (R16/R23)
- [ ] T142 [P] Backup strategy (nightly MySQL dump + `storage/`) and log review (OTP never in logs) in deploy notes
- [ ] T143 Production OTP prohibition verification (no static/test OTP in prod) + final smoke test checklist (docs)

**Checkpoint**: Deployable to low-cost hosting with production safety verified.

---

## Dependencies & Execution Order

### Phase dependencies

- **Phase A (Setup)** → no deps.
- **Phase B (Foundational)** → after A. **BLOCKS all user-story phases.**
- **Phase C (US1)** → after B.
- **Phase D (US5/US2)** → after B (US2 customer views depend on US5 catalog data existing).
- **Phase E (US3/US5)** → after D (needs `product_units`).
- **Phase F (US3)** → after E (cart estimates need `PricingService`) + B (settings/min-order).
- **Phase G (US7/US8)** → after B (independent of catalog; can run parallel to D/E/F).
- **Phase H (US4)** → after F + G (review needs pricing, cart, delivery, settings).
- **Phase I (US4/US9)** → after H.
- **Phase J (US6/US9)** → after I (needs placed orders).
- **Phase K (US6/US11)** → after I (dashboard/new-order notification) and C (customers).
- **Phase L (US10)** → after D/E (reuses catalog/offers) + B (settings).
- **Phase M (US12)** → after core customer screens exist (C, D, F, H, I).
- **Phase N** → after all targeted stories.
- **Phase O** → after N. **Phase P** → after O.

### Dependency graph (after B, three branches converge at H)

```text
A → B ┬─→ C ─────────────────────────┐        (US1 auth: prerequisite for I/K, shorter than catalog branch)
      ├─→ D → E → F ─────────────────┤→ H → I → J → K
      └─→ G ───────────────────────── ┘         (US7/US8 delivery: parallel to D→E→F)
                                        L (after D/E) ·  M (after C,D,F,H,I) ·  N → O → P (after stories)
```

- **C (auth)**, the **D→E→F** catalog→pricing→cart chain, and **G** (delivery) all start once **B**
  completes and run **in parallel**. **H (checkout)** needs **F + G**; **I (placement)** additionally
  needs **C** (an authenticated customer).

### Critical path (longest chain — corrected, resolves prior contradiction)

**A → B → D → E → F → H → I → J → K → (L) → M → N → O → P**

- The **D→E→F** branch (catalog → pricing → cart, ~31 tasks) dominates, so it — not G — is on the
  critical path. **G is NOT on the critical path** (it is a shorter parallel branch merging at H).
  **C is a prerequisite of I** but is a shorter parallel branch (it is not the longest path).
- *(The earlier "A→B→C→D→E→F→G→H→I→J" line implied a single linear chain including G; that was
  inconsistent with G running parallel to D–F. This graph is authoritative.)*

### Parallel opportunities

- Setup: T007, T009, T010, T012 in parallel after T002–T006.
- Foundational: T013, T014, T016, T017, T018, T019, T020, T021 in parallel; T015/T023/**T024** after their deps.
- **Phase G (US7/US8) runs in parallel with Phases C and D–F** (different modules/files) once B is done.
- Within a story: all `[P]` migrations/models/DTOs/tests in parallel; services after models; UI after services.
- All `[P]` test files within a phase run in parallel (authored first, then implementation).

---

## Parallel Example: Phase E (Pricing)

```bash
# Tests first (all parallel):
Task: "Unit test tier boundaries 4→5, 9→10 in src/tests/Unit/Pricing/TierBoundaryTest.php"
Task: "Unit test offer eligibility in src/tests/Unit/Pricing/OfferEligibilityTest.php"
Task: "Unit test lower-of rule in src/tests/Unit/Pricing/LowerOfRuleTest.php"
Task: "Unit test flat price + baseline in src/tests/Unit/Pricing/BaselinePriceTest.php"

# Then models/DTO in parallel:
Task: "Create ProductPriceTier, ProductOffer migrations in src/database/migrations/"
Task: "Create ProductPriceTier, ProductOffer models in src/app/Models/"
Task: "Create PriceResult DTO in src/app/Domain/Pricing/PriceResult.php"
```

---

## Implementation Strategy

### MVP first (P1 ordering loop)

1. Phase A (Setup) → Phase B (Foundational).
2. Phase C (US1 auth/onboarding) — **first executable vertical slice**.
3. Phases D → E → F → G → H → I → J (US2–US7, US9 core) = the complete P1 ordering loop.
4. **STOP & VALIDATE**: a customer can OTP-login, browse, cart with correct tiered/offer pricing, checkout with revalidation, and place an immutable COD order that an admin can manage.

### Incremental delivery (P2 → P3)

5. Phase G US8 (delivery discounts) + Phase K US11 (customer directory) + Phase L US10 (landing) — P2 layered on the loop.
6. Phase M US12 (PWA) + Phase N US13 (state coverage/quality) — P3.
7. Phase O (demo) → Phase P (production).

### Notes

- `[P]` = different files, no incomplete-task dependency.
- Every commercial method has mandatory tests (Constitution VII): pricing/tiers/lower-of, delivery discounts, minimum order, money, status transitions, OTP, checkout revalidation, order creation/snapshots.
- Filament resources/actions call domain services — **no** pricing/order/delivery logic inside Filament.
- Scope guard: no payment/credit/inventory/warehouses/branches/drivers/loyalty/coupons/recurring/Buy-Again/customer-specific pricing/English UI/language switcher/advanced RBAC/microservices.
- Commit after each task or logical group; stop at any checkpoint to validate a story independently.
