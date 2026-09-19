# Contracts — Application/Domain Service Layer

**Feature**: `001-restaurant-supplies-mvp` · **Phase**: 1 (design) · **Date**: 2026-09-17
**Companion to**: [plan.md](../plan.md), [research.md](../research.md), [data-model.md](../data-model.md).

These are **conceptual interface contracts** for the transport-agnostic service layer that Blade
(customer), Filament (admin), and a future JSON API all consume (Constitution Principle I & III). No
code is created here — signatures/DTOs are illustrative (PHP-like pseudocode) to make behavior
testable and unambiguous for `/speckit-tasks`. All money is **integer minor units** (`Money`);
all identifiers are **language-neutral**; the server is authoritative (Principle II).

## Value objects / DTOs

```
Money { int amount /* minor units, EGP */; string currency = 'EGP';
        plus/minus/times(int)/compareTo; static min(...Money); format() is a DISPLAY concern, not here }

PriceResult {
  Money base_unit_price;
  ?Money tier_unit_price;         // null if no eligible tier
  ?Money offer_unit_price;        // null if no active in-range offer
  Money  applied_unit_price;      // = min(applicable candidates), else base
  string applied_source;          // 'normal' | 'tier' | 'offer'
  Money  unit_saving;             // base - applied (>= 0)
  int    quantity;
  Money  line_total;              // applied_unit_price * quantity
}

DeliveryQuote {
  Money base_fee;
  ?int  applied_rule_id;          // null if none qualifies
  string? applied_rule_type;      // 'fixed'|'percentage'|'free_delivery'
  Money discount_amount;          // capped at base_fee
  Money final_fee;                // base_fee - discount_amount, floored at 0
}

CartView {
  CartLineView[] lines;           // each line carries PriceResult + availability flags
  Money product_subtotal;         // effective, excl. delivery
  bool  meets_minimum;
  Money minimum_order_amount;
  Money remaining_to_minimum;     // 0 if met
}

CheckoutReview {
  CartLineView[] lines;
  Money product_subtotal;
  DeliveryQuote delivery;
  Money minimum_order_amount;
  bool  meets_minimum;
  Money final_total;              // product_subtotal + delivery.final_fee
  Change[] changes;               // non-empty => changed-terms state; must re-review
  Problem[] blockers;             // inactive area, unavailable slot, OoS item, below-minimum
}

Change  { string kind; string ref; string message_key; ?Money from; ?Money to; }  // presentation via i18n
Problem { string kind; string ref; string message_key; }
```

> `message_key` values are **translation keys** (Arabic now, English later) — never business logic
> branches on message text (R13/§39).

## PricingService  `[R3, C1/BR-011, FR-018..FR-025]`

```
PricingService {
  // Full evaluation (Product Details, Cart, Checkout).
  PriceResult priceFor(ProductUnit unit, int quantity, DateTimeImmutable at);

  // Lightweight baseline for listing cards ("from X"): default unit, qty 1, no full tier scan (§44).
  Money baselineFromPrice(Product product, DateTimeImmutable at);
}
```
Rules: `applied_unit_price = min(base, eligible tier, active offer)`; offer & tier **never stack**;
none eligible → `base`. Deterministic for a given `(unit state, quantity, at)`. Examples:
`200/170/160→160`; `200/150/160→150`.

## CartService  `[R4, FR-026..FR-031, C7]`

```
CartService {
  Cart      getOrCreate(Customer c);
  CartView  add(Customer c, ProductUnit unit, int qty);      // validates orderable; upserts line
  CartView  updateQuantity(Customer c, CartItem line, int qty);
  CartView  remove(Customer c, CartItem line);
  CartView  view(Customer c);                                // recompute estimates + min-order + flags
}
```
Cart persistence: **one persistent `carts` row per customer** (`UNIQUE(customer_id)`, R4);
`getOrCreate` returns that single row; after a placed order the **items are cleared** and the row is
reused (no cart status, no cart history). Cart is **not authoritative**: prices are `PricingService`
estimates (FR-028); unavailable items are flagged and excluded from a valid checkout (FR-029, US3 #7).
`meets_minimum` compares effective product subtotal (excl. delivery) to `settings.minimum_order_amount`
(C7).

## DeliveryService  `[R6, FR-032..FR-037, C2/BR-004]`

```
DeliveryService {
  bool          isAreaOrderable(DeliveryArea area);          // is_active
  DeliveryQuote quote(DeliveryArea area, Money effectiveProductSubtotal, DateTimeImmutable at);
  bool          isSlotSelectable(DeliverySlot slot, Date deliveryDate); // is_active + slot.day_of_week == weekday(date) + date not past
  DeliverySlot[] availableSlots(Date deliveryDate);                     // active slots whose day_of_week matches the date's weekday
}
```
Slots are **recurring weekday templates** (one row per weekday + time window, `day_of_week`,
`start_time`, `end_time`, `is_active`, `sort_order`; no capacity — R5). `quote`: base from area; among
**active** rules with `min_subtotal ≤ subtotal`, pick the **largest monetary saving** vs base fee;
tie-break **higher `min_subtotal`**; `final_fee ≥ 0`; no stacking.

## PromotionService  `[R3, FR-023/FR-024, BR-005, §12]`

```
PromotionService {
  ?Offer activeOfferFor(ProductUnit unit, DateTimeImmutable at); // active && starts_at<=at<=ends_at
}
```
Used by `PricingService`; offers are **server-evaluated** only; expired/inactive never applied.

## CatalogService  `[FR-011..FR-017, R15]`

```
CatalogService {
  Category[]        activeCategories();
  Paginator<Product> productsInCategory(Category c, Page p);   // available+OoS; excludes inactive
  Paginator<Product> search(string query, ?Category scope, Page p); // name/brand, Arabic+Latin
  ProductDetail     productDetail(Product p);                  // units, tiers-as-table, availability
}
```
Listing uses `PricingService::baselineFromPrice` only (§44). Inactive products/categories hidden
(FR-016/FR-017).

## OtpService + OtpProvider  `[R7, FR-001..FR-010, Principle VI]`

```
interface OtpProvider { void send(string phoneE164, string code); }   // LogOtpProvider (dev) | Sms/WhatsApp (future)

OtpService {
  OtpRequestResult request(string phone, string ip);   // validate phone; cooldown+rate limit; hash+store; send
  OtpVerifyResult  verify(string phone, string code);  // attempt limit; one-time consume; distinguish incorrect vs expired
}
OtpRequestResult { bool sent; ?int cooldown_seconds; string? message_key; }
OtpVerifyResult  { bool ok; enum reason { verified, incorrect, expired, locked }; ?Customer customer; bool needs_onboarding; }
```
Never returns/logs the code (prod). Enforces expiry, one-time use, resend cooldown, request rate
limit, verify attempt limit/lockout (R7/R11). `needs_onboarding` gates ordering (FR-006/FR-009).

## CustomerService  `[FR-006..FR-009, C5/C6]`

```
CustomerService {
  Customer  findOrCreateByPhone(string phone);
  Customer  completeOnboarding(Customer c, ProfileInput profile);   // business/contact/whatsapp
  Address   setDefaultAddress(Customer c, AddressInput a);          // one default (C6); area must be active
  Address   updateDefaultAddress(Customer c, AddressInput a);
  bool      canPlaceOrders(Customer c);                             // onboarding_completed_at != null
}
```
No KYC/tax (C5); single default address exposed, many-capable schema (C6).

## OrderService  `[R9..R12, FR-041..FR-052, Principle II/V]`

```
OrderService {
  CheckoutReview review(Customer c, CheckoutInput in);   // full server revalidation; builds changes[]/blockers[]
  PlaceResult    place(Customer c, CheckoutInput in, string submissionToken); // TX: revalidate->insert order+items->commit
  Order          transition(Order o, string toStatus, Actor by, ?string reason); // validates allowed transition
  bool           canCustomerCancel(Order o);             // status == 'new'
  bool           canAdminCancel(Order o);                // status in {new,confirmed,preparing,out_for_delivery}
}
PlaceResult { bool placed; ?Order order; ?CheckoutReview review_if_changed; ?string error_key; }
CheckoutInput { AddressRef address; DeliveryArea area; Date delivery_date; DeliverySlot slot; /* cart is server-owned */ }
```
`place()` re-revalidates inside the transaction (authoritative); if terms changed since review →
returns `review_if_changed` (no silent placement, FR-044). Snapshots written per R9. Offline/failure
→ no order, explicit error (FR-047). Order number allocated transactionally + `UNIQUE` (R11).
Transitions per R10/BR-012/C3.

## InventoryService  `[prompt 32, FR-071..FR-078, BR-013..BR-016, Principle II/VI]`

```
InventoryService {
  int  currentStock(ProductUnit unit);
  bool hasStockFor(ProductUnit unit, int quantity);

  // Manual admin change; locks the unit row, enforces non-negative result, writes history.
  InventoryAdjustment adjust(ProductUnit unit, string type, int delta, ?string reason, ?User by);
      // type in { initial, manual_add, manual_remove, correction }

  // Called INSIDE OrderService::place() TX: lockForUpdate() each unit row, verify all lines,
  // decrement, and write one `order` adjustment per line — or throw InsufficientStock (caller
  // aborts the whole transaction, no partial deduction). No overselling (BR-014).
  void deductForOrder(Order order, CheckoutLine[] lines);

  // Idempotent restore on eligible cancellation; writes `order_cancel_restore` adjustments once.
  // No-op if this order already has restore rows, or is delivered/already-cancelled (BR-015).
  void restoreForCancellation(Order order);
}

AdjustInventoryAction        // admin manual add/remove/correct wrapper over InventoryService::adjust
DeductInventoryForOrder      // OrderService::place uses InventoryService::deductForOrder
RestoreInventoryForCancellation  // OrderService::transition(cancel) uses restoreForCancellation
```
Stock is authoritative for orderability (`is_active AND stock_quantity > 0`, FR-074). Deduction is at
order **creation** under `lockForUpdate` (R11-style scoped locks; no Redis/distributed locks). Checkout
revalidation (`CheckoutReview`) surfaces a `Problem{kind:'insufficient_stock'}` / `Change` for a line
whose requested quantity now exceeds stock, forcing re-review before placement. `InventoryService` is
the **only** writer of `product_units.stock_quantity`; controllers/Filament call it, never mutate
directly (Principle III). Every write appends an immutable `inventory_adjustments` row (§18 / BR-016).

## ProductUnitConverter  `[prompt 37, FR-079..FR-086]`

```
ProductUnitConverter {
  // (primary_qty × conversion_to_sub_unit) for a primary line; (× 1) for a sub line.
  int toSubUnits(ProductUnit unit, int quantity);

  // Present an authoritative sub-unit balance as {primary, remainderSub} for admin display only.
  array formatStock(Product product, int subUnitBalance);   // e.g. 125 => {primary: 10, sub: 5}
}
```
Two-level only (primary → sub); MVP has no n-level trees. **Pure conversion — never touches pricing**
(sub price is independent, R25/§7). `InventoryService` normalizes every order line and every
primary-unit admin adjustment to **sub-units** via this converter before writing the single
authoritative sub-unit balance; adjustments record both the input unit/qty and the normalized delta.
Order snapshots capture the conversion factor used so history is stable if the factor later changes
(blocked while stock ≠ 0, §24).

## SettingsService  `[FR-062, FR-030, C7]`

```
SettingsService {
  Money minimumOrderAmount();       // integer minor units
  BusinessInfo businessInfo();      // name/phone/whatsapp/address for landing/footer/profile
  void  update(SettingsInput in);   // admin; invalidates cache
}
```

## AdminAuditService  `[prompt 39, Principle V/VI]`

```
AdminAuditService {
  AdminAuditLog record(string action, ?Model auditable, array old, array new, array metadata);
  array  changes(Model m, string[] fields);   // {old,new} for the changed audited fields
  array  snapshot(Model m, string[] fields);   // current values (create/delete events)
}
```

Single, consistent writer of `admin_audit_logs` (§9). Captures actor (`auth()->id()`, null = system),
neutral `action` code (`AuditAction`), morph target, only audited old/new fields (money as integer
minor units), and safe request metadata (ip/ua). **Redacts** sensitive fragments
(`password|secret|token|otp|code_hash|api_key|credential`) before persistence (§8). Callers invoke it
**after** a successful mutation so a rolled-back change never logs a false success (§10). Wiring:
model observers (`Product`/`ProductUnit`/`Unit`/`Setting`) + `AdjustInventoryAction` (references
`inventory_adjustment_id`, never replacing the ledger). Records are immutable/append-only.

## MoneyFormatter (presentation boundary)  `[R2/R13, §37, D2]`

```
MoneyFormatter { string format(Money m, Locale l); }   // ar => "1,250 ج" ; en (future) => localized
```
Centralized; **not** used inside domain math. Blade `<x-price>` / Filament column call this. Latin
digits always (D2); stored `Money` never changes with locale.

## LocalizedContent resolver (presentation boundary)  `[R14, prompt-09 authoritative]`

```
LocalizedContent { string get(HasLocalizedText model, string field, ?Locale l); }
// returns model.{field}_{locale}; if the *_en value is null/empty, falls back to *_ar.
```
Managed content lives in paired **`*_ar` (required) / `*_en` (nullable)** columns (categories,
products, product_units, product_offers, delivery_areas, delivery_slots, localizable settings). The
resolver is the **single** place locale→column selection happens — Blade `<x-...>`, Filament columns,
and controllers call it; **never** scatter `_ar`/`_en` selection across views/services. `brand`,
customer-entered text, and neutral identifiers are **not** resolved (single columns). **Business logic
never branches on localized text** (only IDs/amounts/neutral identifiers). Order rendering uses the
**snapshot** columns, not the resolver (history immutable, Principle V).

## Cross-cutting contract rules
- Services accept/return **domain objects + `Money` + neutral identifiers**; presentation maps
  neutral identifiers to Arabic (now) / English (later) via translation keys, and managed content via
  the **LocalizedContent** resolver above (`*_en ?: *_ar`).
- No service reads client-supplied money; totals are server-computed (Principle II).
- Every commercial method above has **mandatory unit tests** (R22 / Principle VII), incl. boundary
  cases (tier 4→5, 9→10; minimum 499/500; discount thresholds; expired offer; inactive area/slot;
  OoS; double-submit).
