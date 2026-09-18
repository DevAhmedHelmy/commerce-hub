# Data Model — Restaurant Supplies Ordering MVP

**Feature**: `001-restaurant-supplies-mvp` · **Phase**: 1 (design) · **Date**: 2026-09-17
**Companion to**: [plan.md](./plan.md), [research.md](./research.md).

Conceptual data model (no migrations/DDL created — implementation phase). Engine **InnoDB**,
charset **utf8mb4**, collation **utf8mb4_unicode_ci** (Arabic + Latin). **Money = integer minor
units** (`BIGINT`, EGP piastres) per research R2 — never floats. Timestamps `TIMESTAMP`/`DATETIME`
(UTC stored, locale-neutral). PKs are `BIGINT UNSIGNED AUTO_INCREMENT` unless noted; some public
identifiers use ULIDs.

**Conventions**: `is_active BOOLEAN` toggles; `sort_order INT` for admin ordering; `deleted_at` only
where soft-delete is justified (below). All **domain identifiers are language-neutral** (statuses,
unit `code`, discount `type`) — Arabic/English are presentation labels, not stored business values.

**Core MVP tables: 18** (excluding Laravel framework tables: `sessions`, `cache`, `jobs`,
`failed_jobs`, `job_batches`, `migrations`, `password_reset_tokens` for admins;
`notifications` is a framework table surfaced for admin new-order alerts). **Simple
inventory (prompt 32, core MVP)** adds `inventory_adjustments` (#18) and a
`product_units.stock_quantity` balance — see §6 and §18.

---

## 1. `users` — admin/staff  `[FR-063, R8]`
- **Purpose**: administrators who use the Filament panel. **Not** customers.
- **Columns**: `id`; `name`; `email` (login); `password` (hashed); `remember_token`; timestamps.
- **Uniqueness**: `UNIQUE(email)`.
- **FKs**: none.
- **Indexes**: unique email (implicit).
- **Lifecycle/soft-delete**: no soft-delete (deactivation via removal or a future `is_active`).
- **History/audit**: none in MVP.

## 2. `customers` — ordering customers (B2B)  `[FR-006..FR-009, C5, R8]`
- **Purpose**: recurring B2B buyers authenticated by phone OTP.
- **Columns**: `id`; `phone` (login mobile, E.164 normalized); `business_name`;
  `contact_person_name`; `whatsapp_phone` (nullable); `onboarding_completed_at` (nullable — gates
  ordering, FR-009); `last_login_at`; timestamps.
- **Uniqueness**: `UNIQUE(phone)`.
- **FKs**: none (addresses reference customer).
- **Indexes**: `UNIQUE(phone)` (login/lookup); `INDEX(business_name)` (admin search, FR-056).
- **Lifecycle/soft-delete**: `deleted_at` (soft-delete) — preserves order history references.
- **History/audit**: no KYC/tax fields (C5). Mixed Arabic/Latin content allowed (Unicode).

## 3. `customer_addresses` — delivery addresses  `[FR-007, FR-008, C6, R-arch]`
- **Purpose**: customer delivery details; **one default** exposed in MVP, structured for many later.
- **Columns**: `id`; `customer_id` FK; `delivery_area_id` FK; `is_default BOOLEAN`; `address_line`;
  `building` (nullable); `floor` (nullable); `unit` (apartment/shop/unit, nullable); `landmark`
  (nullable); `delivery_notes` (nullable); timestamps.
- **Uniqueness**: one default per customer — enforce in app + a partial/guard (e.g.
  `UNIQUE(customer_id, is_default)` where `is_default=1`, emulated via app logic on MySQL).
- **FKs**: `customer_id → customers.id` (cascade on delete of customer via soft-delete policy);
  `delivery_area_id → delivery_areas.id` (restrict).
- **Indexes**: `INDEX(customer_id)`; `INDEX(delivery_area_id)`.
- **Lifecycle/soft-delete**: soft-delete optional; MVP hard-manages a single default.
- **History/audit**: order snapshots copy address at placement (R9) — later address edits don't alter
  past orders.

## 4. `categories` — product categories  `[FR-011, FR-017, FR-057]`
- **Purpose**: admin-managed groupings; customer browse.
- **Columns**: `id`; **`name_ar`** (required), **`name_en`** (nullable, R14); `slug?`
  (language-neutral route id, §18); `description_ar?`, `description_en?` (if descriptions kept);
  `is_active`; `sort_order`; timestamps.
- **Uniqueness**: `UNIQUE(slug)` if used (neutral; business identity does not depend on localized name).
- **Indexes**: `INDEX(is_active, sort_order)` (active listing order); `INDEX(name_ar)` (search).
- **Lifecycle/soft-delete**: `is_active` (inactive hidden from customers); soft-delete optional.
- **History/audit**: order snapshots don't depend on category.

## 5. `products` — sellable items  `[FR-013..FR-016, FR-058, R3]`
- **Purpose**: catalog product; **pricing lives on units, not here** (R3/§8).
- **Columns**: `id`; `category_id` FK; **`name_ar`** (required), **`name_en`** (nullable, R14);
  **`brand?`** (single natural commercial value, e.g. "Heinz" — **no** `brand_ar`/`brand_en`, R14);
  `description_ar?`, `description_en?` (if managed); `image_path?` (local disk, R18); `availability`
  ENUM-like (`available|out_of_stock|inactive`, neutral); `is_searchable`(derived/`availability`);
  `sort_order`; timestamps.
- **Uniqueness**: none required (name not unique).
- **FKs**: `category_id → categories.id` (restrict).
- **Indexes**: `INDEX(category_id, availability, sort_order)` (category listing, hides inactive);
  `INDEX(name_ar)` and `INDEX(brand)` for indexed `LIKE`/prefix search (R15; `name_en` searched later
  when real English lands). **FULLTEXT(name_ar,brand) is NOT an MVP index** — evaluated later only if
  real data shows `LIKE` insufficient; `INDEX(availability)`.
- **Lifecycle/soft-delete**: `availability='inactive'` hides from customers (FR-016); soft-delete
  optional (order items snapshot name/brand so deletion won't corrupt history).
- **History/audit**: `order_items` snapshot `product_name`/`brand` (R9).

## 6. `product_units` — selling units  `[FR-018, FR-022, §9, R3]`
- **Purpose**: a way to buy a product (bag/carton/bottle); independently priceable.
- **Columns**: `id`; `product_id` FK; `code` (**language-neutral**: `bag|carton|pack|bottle|…`);
  **`display_name_ar`** (required, e.g. "كيس 2.5 كجم"), **`display_name_en`** (nullable, e.g. "Bag
  2.5 KG", R14); `package_description_ar?`, `package_description_en?` (if that label is stored);
  `base_price` (BIGINT minor units — **normal unit price**); `conversion_factor?` (nullable, reserved
  for future inventory, unused in MVP, §9);
  **`stock_quantity INT UNSIGNED` (current inventory balance per selling unit, default 0, never < 0 —
  prompt 32)**; `low_stock_threshold INT UNSIGNED?` (nullable; optional visual admin warning only);
  `is_active`; `is_default BOOLEAN`; `sort_order`; timestamps.
- **Availability rule (authoritative, prompt 32 §10)**: a unit is **orderable** iff
  `is_active = true` AND `stock_quantity > 0`. `is_active = false` → administratively unavailable;
  `stock_quantity = 0` (active) → **out of stock** (never manually toggled). The product-level
  `availability` enum still gates the *product* (available/out_of_stock/inactive); the **unit stock
  balance** is authoritative for whether that specific unit can be added/ordered.
- **Inventory writes**: `stock_quantity` is mutated **only** inside a transaction via `InventoryService`
  with `lockForUpdate()` on the row (order deduction, manual adjustment, cancellation restore); every
  change writes an `inventory_adjustments` row (§18). Never written directly by controllers/Filament.
- **Uniqueness**: `UNIQUE(product_id, code)`; one default per product (app-enforced).
- **FKs**: `product_id → products.id` (cascade).
- **Indexes**: `INDEX(product_id, is_active, sort_order)`; `INDEX(product_id, is_default)`.
- **Lifecycle/soft-delete**: `is_active`; soft-delete optional (order items snapshot unit name).
- **History/audit**: `order_items` snapshot `unit_name`/`package_description` (R9). `base_price` here
  is current; snapshot copies applied price at order time.

## 7. `product_price_tiers` — quantity/wholesale tiers  `[FR-019, FR-021, R3]`
- **Purpose**: per-unit quantity breaks; optional (flat-priced unit has none).
- **Columns**: `id`; `product_unit_id` FK; `min_quantity INT` (tier applies for `qty ≥ min_quantity`
  up to the next tier's min − 1); `unit_price` (BIGINT minor units); `is_active`; timestamps.
- **Uniqueness**: `UNIQUE(product_unit_id, min_quantity)` (no duplicate tier starts).
- **FKs**: `product_unit_id → product_units.id` (cascade).
- **Indexes**: `INDEX(product_unit_id, min_quantity)` (tier lookup by quantity, ascending).
- **Constraints**: non-overlapping ascending ranges (app-validated on admin save); `min_quantity ≥ 1`.
- **Lifecycle/soft-delete**: `is_active`; hard-delete acceptable (order snapshots capture applied
  price).
- **History/audit**: not referenced by history except via snapshot.

## 8. `product_offers` — temporary offers  `[FR-023, FR-024, BR-005, §12, R3]`
- **Purpose**: time-boxed discounted price for a product unit (or product-level applying to units).
- **Columns**: `id`; `product_unit_id` FK (target unit; or `product_id` + apply-to-units — MVP:
  **per-unit target**); `offer_price` (BIGINT minor units); `starts_at`; `ends_at`; `is_active`;
  `title_ar?`, `title_en?` (nullable; `description_ar?`/`description_en?` if stored — R14); timestamps.
- **Uniqueness**: app-guard against overlapping active offers per unit (or "latest active wins" —
  decided at implementation; evaluation is server-side).
- **FKs**: `product_unit_id → product_units.id` (cascade).
- **Indexes**: `INDEX(product_unit_id, is_active, starts_at, ends_at)` (active-offer lookup).
- **Constraints**: `ends_at ≥ starts_at`; only active + in-range applied (BR-005).
- **Lifecycle/soft-delete**: `is_active`; hard-delete acceptable (snapshots capture applied price).
- **History/audit**: `order_items.applied_source='offer'` + snapshot price records that an offer
  applied.

## 9. `carts` — the customer's single persistent cart  `[FR-026, R4]`
- **Purpose**: exactly **one persistent cart row per customer**; **not financially authoritative**.
- **Columns**: `id`; `customer_id` FK; timestamps. **No `status` column** (no "active/inactive"
  invariant); **no cart history** (order history lives on `orders`).
- **Uniqueness**: **`UNIQUE(customer_id)`** — a plain unique index (MySQL-enforceable; no
  partial/filtered index needed). One row per customer, created lazily on first add.
- **FKs**: `customer_id → customers.id` (cascade).
- **Indexes**: `UNIQUE(customer_id)` (also serves lookup).
- **Lifecycle**: after a successful order the **items are cleared**; the `carts` **row may remain** for
  reuse. No soft-delete.

## 10. `cart_items` — cart lines  `[FR-026, FR-028, R4]`
- **Purpose**: selection = product + unit + quantity. **No authoritative price** stored.
- **Columns**: `id`; `cart_id` FK; `product_id` FK; `product_unit_id` FK; `quantity INT`;
  `last_seen_unit_price?` (nullable, **non-authoritative** display cache only); timestamps.
- **Uniqueness**: `UNIQUE(cart_id, product_unit_id)` (one line per unit; quantity mutates).
- **FKs**: `cart_id → carts.id` (cascade); `product_id → products.id` (restrict);
  `product_unit_id → product_units.id` (restrict).
- **Indexes**: `INDEX(cart_id)`; unique above.
- **Constraints**: `quantity ≥ 1`. Prices recomputed by `PricingService`; `last_seen_*` never trusted
  (Principle II, FR-028).
- **Lifecycle**: removed with cart.

## 11. `delivery_areas` — areas + base fee  `[FR-032, FR-033, §15]`
- **Purpose**: admin-managed eligible areas with a base delivery fee.
- **Columns**: `id`; **`name_ar`** (required), **`name_en`** (nullable, R14); `base_fee` (BIGINT minor
  units); `is_active`; `sort_order`; timestamps.
- **Uniqueness**: `UNIQUE(name_ar)` (business identity does not depend on the English name).
- **Indexes**: `INDEX(is_active, sort_order)` (active-area selection lists).
- **Lifecycle/soft-delete**: `is_active` (inactive blocks new orders, FR-033); soft-delete optional
  (orders snapshot area name).
- **History/audit**: `orders` snapshot `delivery_area_name` (the displayed value at placement) +
  `base_delivery_fee`.

## 12. `delivery_slots` — recurring weekday templates  `[FR-038..FR-040, C4, R5]`
- **Purpose**: admin-managed availability windows, **one row per (weekday, time window)**; **no
  numeric capacity** (C4), no driver scheduling/route planning.
- **Columns**: `id`; **`label_ar`** (required, e.g. "1–4 مساءً"), **`label_en`** (nullable, R14 — only
  the display label is localized); **`day_of_week TINYINT`** (single weekday, ISO 1=Mon…7=Sun;
  **neutral, never localized**); `start_time TIME`; `end_time TIME` (**neutral**); `is_active`;
  `sort_order`; timestamps. **No JSON day-set/bitmask.**
- **Uniqueness**: none strict — app-guard obvious duplicates (same weekday + identical window).
- **Indexes**: `INDEX(day_of_week, is_active, sort_order)` (availability lookup for a chosen date's
  weekday, R5).
- **Constraints**: `end_time > start_time`; `day_of_week` in 1..7.
- **Lifecycle/soft-delete**: `is_active` (inactive rejected at checkout revalidation, R5/R12);
  soft-delete optional (orders snapshot slot label/time so hard-delete is safe).
- **History/audit**: `orders` snapshot `delivery_slot_label` + `slot_start_time`/`slot_end_time` +
  chosen `delivery_date`, so later slot edits/deletion never alter historical orders (Principle V).

## 13. `delivery_discount_rules` — subtotal-threshold discounts  `[FR-035, FR-036, C2, R6]`
- **Purpose**: deterministic delivery-fee discounts; **no stacking**, largest-saving wins.
- **Columns**: `id`; `name`; `type` (**neutral**: `fixed|percentage|free_delivery`); `value` (BIGINT
  minor units for `fixed`; integer percent 0–100 for `percentage`; ignored for `free_delivery`);
  `min_subtotal` (BIGINT minor units — qualifying effective product subtotal); `is_active`;
  timestamps.
- **Uniqueness**: `UNIQUE(name)`.
- **Indexes**: `INDEX(is_active, min_subtotal)` (qualification scan + tie-break by higher
  min_subtotal, R6).
- **Constraints**: `percentage` in [0,100]; `value ≥ 0`; `min_subtotal ≥ 0`.
- **Lifecycle/soft-delete**: `is_active`; hard-delete acceptable (orders snapshot discount amount).
- **History/audit**: `orders` snapshot `delivery_discount` (amount) + `final_delivery_fee`.

## 14. `orders` — placed order header (immutable snapshot)  `[FR-041..FR-052, R9, Principle V]`
- **Purpose**: transactional, immutable commercial record.
- **Columns**: `id`; `order_number` (human-friendly `ORD-######`, derived from `AUTO_INCREMENT` `id`
  + fixed offset inside the creation TX, `UNIQUE`; concrete scheme in R9); `customer_id` FK;
  **snapshots** → `business_name`, `contact_person_name`, `phone`, `whatsapp_phone`,
  `delivery_area_name`, `address_line`, `building?`, `floor?`, `unit?`, `landmark?`, `delivery_notes?`;
  `delivery_date DATE`; `delivery_slot_label`, `slot_start_time`, `slot_end_time`;
  **money (BIGINT minor units)** → `product_subtotal`, `base_delivery_fee`, `delivery_discount`,
  `final_delivery_fee`, `final_total`; `payment_method` (neutral: `cod`); `status` (neutral enum,
  R10) default `new`; `cancellation_reason?`; `cancelled_by?` (`customer|admin`); `placed_at`;
  timestamps.
- **Uniqueness**: `UNIQUE(order_number)` (concurrency backstop, R11).
- **FKs**: `customer_id → customers.id` (restrict — keep history even if customer soft-deleted).
- **Indexes**: `UNIQUE(order_number)`; `INDEX(customer_id, created_at)` (customer history, FR-052);
  `INDEX(status, created_at)` (admin lists/filters, FR-054); `INDEX(delivery_date)` (ops);
  `INDEX(created_at)` (dashboard today/recent).
- **Constraints**: `final_delivery_fee ≥ 0`; `final_total = product_subtotal + final_delivery_fee`
  (app-enforced at creation).
- **Lifecycle/soft-delete**: **never mutated commercially** post-placement except `status`/cancel
  fields; **no soft-delete** (immutable record, Principle V/BR-006).
- **History/audit**: this IS the historical record; catalog changes never alter it.

## 15. `order_items` — order line snapshots (immutable)  `[FR-051, R9, Principle V]`
- **Purpose**: immutable per-line commercial snapshot.
- **Columns**: `id`; `order_id` FK; `product_id?` (nullable ref for convenience); `product_unit_id?`
  (nullable ref); **snapshots** → `product_name`, `brand?`, `unit_name`, `package_description?`,
  `quantity INT`; **money** → `base_unit_price`, `applied_unit_price`, `applied_source`
  (`normal|tier|offer`), `unit_saving`, `line_total`; timestamps.
- **Uniqueness**: none.
- **FKs**: `order_id → orders.id` (cascade delete with order — but orders aren't deleted);
  `product_id`/`product_unit_id` nullable **SET NULL**-style refs (snapshots survive if catalog
  removed).
- **Indexes**: `INDEX(order_id)`.
- **Constraints**: `quantity ≥ 1`; `line_total = applied_unit_price × quantity` (app-enforced).
- **Lifecycle**: immutable; created only inside the order transaction (R9).

## 16. `otp_verifications` — OTP challenges  `[FR-001..FR-010, R7, Principle VI]`
- **Purpose**: secure phone OTP with expiry, one-time use, rate/attempt limiting.
- **Columns**: `id`; `phone`; `code_hash` (**hashed**, never plaintext); `expires_at`;
  `consumed_at?`; `attempts INT` default 0; `resend_count INT`; `last_sent_at`; `ip?`; timestamps.
- **Uniqueness**: manage one active challenge per phone (app-enforced; conditional consume, R11).
- **Indexes**: `INDEX(phone, expires_at)` (lookup + cleanup); `INDEX(consumed_at)`.
- **Constraints**: `expires_at > created_at`; attempts/resend enforced in service + Laravel rate
  limiter.
- **Lifecycle**: expired/consumed rows pruned by scheduled cleanup; **OTP code never logged in prod**.
- **History/audit**: no plaintext OTP stored or logged.

## 17. `settings` — business configuration  `[FR-062, FR-030, C7, §14]`
- **Purpose**: singleton/key-value business settings.
- **Columns (key-value)**: `id`; `key` (neutral); `value` (string/JSON); timestamps.
  (Money settings like `minimum_order_amount` stored as **integer minor units**.)
- **Localization (R14)**: neutral settings stay single (`minimum_order_amount`, `business_phone`,
  `business_whatsapp`). Localizable **textual** business/landing content follows the `*_ar`/`*_en`
  pattern **where practical** via paired keys (e.g. `business_name_ar`/`business_name_en`,
  `business_address_ar`/`business_address_en`) resolved through the same content resolver.
- **Uniqueness**: `UNIQUE(key)`.
- **Indexes**: `UNIQUE(key)`.
- **Lifecycle**: admin-editable; cache with invalidation on save (R16).
- **History/audit**: none.

## 18. `inventory_adjustments` — durable stock-change history  `[prompt 32, Principle V/VI]`
- **Purpose**: append-only audit trail of every change to a unit's `stock_quantity`. Not a
  costing/valuation ledger — a simple, traceable history (no suppliers/batches/FIFO).
- **Columns**: `id`; `product_unit_id` FK; `type` (**neutral**: `initial|manual_add|manual_remove|
  order|order_cancel_restore|correction`); `quantity_delta INT` (signed: + adds, − removes);
  `quantity_before INT UNSIGNED`; `quantity_after INT UNSIGNED`; `reason?` (nullable free text);
  `reference_type?` + `reference_id?` (nullable polymorphic-ish link, e.g. `order`/order id);
  `performed_by?` (nullable FK → `users.id`, set for manual admin actions, null for automatic);
  `created_at` (no `updated_at` — rows are immutable).
- **Uniqueness**: for order deductions, app-enforces **one deduction set per (order)** and
  cancellation restore **once per order** (idempotency, prompt 32 §8) — guarded by checking existing
  `order`/`order_cancel_restore` rows for the `reference` inside the transaction.
- **FKs**: `product_unit_id → product_units.id` (cascade); `performed_by → users.id` (nullOnDelete).
- **Indexes**: `INDEX(product_unit_id, created_at)` (per-unit history); `INDEX(reference_type,
  reference_id)` (order linkage + idempotency lookup); `INDEX(type)`.
- **Constraints**: `quantity_after = quantity_before + quantity_delta`; `quantity_after ≥ 0`
  (never negative — enforced in `InventoryService` under `lockForUpdate`); rows are **immutable**
  (no edit/delete from normal admin UI).
- **History/audit**: this IS the inventory audit record; it must not be silently editable/deletable.

## (Framework) `notifications` — database notifications  `[FR-055, R16]`
- Laravel's standard `notifications` table (morphable `notifiable`, `type`, `data` JSON, `read_at`)
  for **admin new-order** notifications surfaced via Filament polling. No custom columns needed.

---

## Relationships (summary)

```
users (admin)                         customers ──1:N── customer_addresses ──N:1── delivery_areas
                                          │  └─1:1── carts ──1:N── cart_items ──N:1── product_units
                                          └─1:N── orders ──1:N── order_items                       │
categories ──1:N── products ──1:N── product_units ──1:N── product_price_tiers                      │
                                             └─1:N── product_offers ───────────────────────────────┘
delivery_slots (recurring)     delivery_discount_rules      settings (kv)      otp_verifications (by phone)
```

## Localization (managed content) — cross-cutting  `[R14, AUTHORITATIVE — supersedes prompt-08 "Arabic-only now"]`
- Selected translatable managed content uses **paired `*_ar` / `*_en` columns from day one**:
  `categories.name(+description)`, `products.name(+description)`, `product_units.display_name
  (+package_description)`, `product_offers.title(+description)`, `delivery_areas.name`,
  `delivery_slots.label`, and localizable `settings` text. **`*_ar` required, `*_en` nullable**; a
  **centralized localized-content resolver** returns `*_en ?: *_ar` (falls back to Arabic). English is
  never required to save; no machine translation; no translation-status workflow.
- **Single (never localized)**: `brand`; all **customer-entered** text (`business_name`,
  `contact_person_name`, address/`landmark`/`delivery_notes`, `whatsapp_phone`); all **neutral**
  identifiers/numerics (IDs, FKs, slugs, `code`, statuses, discount `type`, `payment_method`,
  quantities, money, dates/times, `order_number`, phones).
- **Order snapshots** copy the **displayed** text at placement into **single** snapshot columns
  (`order_items.product_name`/`unit_name`/`package_description`, `orders.delivery_area_name`/
  `delivery_slot_label`); history never reads live `*_ar`/`*_en` (Principle V).
- Commerce/domain logic references **IDs/amounts/neutral identifiers only**, never localized text, so
  turning on English is a data + presentation change with **no schema migration and no rewrite** of
  pricing/order/delivery logic (Constitution IV).

## Money & determinism (cross-cutting) — definitive
- **Definitive representation**: every money column is `BIGINT` **integer minor units** (EGP
  piastres). **No `FLOAT`/`DOUBLE`, no `DECIMAL`** money columns; no binary-float math anywhere (R2).
- Display converts at the presentation boundary via `MoneyFormatter` (`444 ج`; two decimals only if a
  value carries non-zero piastres); stored values are never pre-rounded for display.
- Totals are **computed by services** and persisted as snapshots on `orders`/`order_items`; they are
  never recomputed from live catalog for historical display (Principle V).
- `minimum_order_amount` compares against **effective product subtotal** (after pricing/tiers/offers,
  excluding delivery), per C7/BR-002.

## Indexing rationale (maps to §42 query patterns)
- **Customer login/lookup**: `customers.UNIQUE(phone)`.
- **Category/product listing**: `products(category_id, availability, sort_order)`.
- **Product search**: `products(name_ar)`, `products(brand)` (+ FULLTEXT candidate, R15; `name_en`
  added to search when real English content lands).
- **Availability filter**: covered by the composite above / `products(availability)`.
- **Unit + tier lookup**: `product_units(product_id, is_active, sort_order)`,
  `product_price_tiers(product_unit_id, min_quantity)`.
- **Active offer lookup**: `product_offers(product_unit_id, is_active, starts_at, ends_at)`.
- **Cart**: `cart_items.UNIQUE(cart_id, product_unit_id)`, `carts(customer_id)`.
- **Customer orders / number / status / date**: `orders(customer_id, created_at)`,
  `orders.UNIQUE(order_number)`, `orders(status, created_at)`, `orders(created_at)`.
- **Active delivery areas**: `delivery_areas(is_active, sort_order)`.
- **Delivery discount qualification**: `delivery_discount_rules(is_active, min_subtotal)`.
- **OTP**: `otp_verifications(phone, expires_at)`.
- Indexes are **deliberately lean** (no speculative indexes); composites chosen to serve the exact
  listing/filter/lookup paths above (constitution Principle IV / Code Quality).

## Soft-delete decisions (summary)
- **Soft-delete**: `customers` (preserve order links). Optional on `categories`/`products`/`units`
  (order snapshots make hard-delete safe; choose soft-delete if admins expect "restore").
- **No soft-delete**: `orders`, `order_items` (immutable historical truth), `otp_verifications`
  (pruned), `settings`, `cart*` (transient).
