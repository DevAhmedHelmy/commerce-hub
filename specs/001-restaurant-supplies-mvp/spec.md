# Feature Specification: Restaurant Supplies Ordering MVP

**Feature Branch**: `001-restaurant-supplies-mvp`
**Created**: 2026-09-17
**Status**: Draft
**Input**: User description: "Restaurant Supplies Ordering MVP — mobile-first PWA for a
single-branch, delivery-only, cash-on-delivery restaurant-supplies business."

## Product Objective

Deliver a professional, mobile-first ordering platform (installable as a PWA) that lets
repeat B2B customers of a restaurant-supplies business browse a categorized catalog,
choose products by selling unit with quantity/wholesale pricing, place cash-on-delivery
orders to eligible delivery areas within admin-managed time slots, and track those
orders — while a team of admin users manages the catalog, pricing, offers, delivery
rules, and order fulfilment from a single dashboard.

The MVP MUST feel like a trustworthy B2B restaurant-wholesale tool, not a generic
e-commerce template. All money and availability decisions are the business's authority
(server-side); the customer's device only proposes, the business confirms.

## Actors

- **Visitor** (unauthenticated): views the public landing page; may begin sign-in.
- **Customer** (authenticated via mobile-number OTP): a recurring B2B buyer — a
  restaurant/food-service business identified by a Business/Restaurant Name and a Contact
  Person Name — who browses, carts, orders, and views their own order history. Their
  profile and a single default delivery address are saved so they are not re-entered.
- **Admin user** (authenticated staff): manages catalog, pricing, offers, delivery
  configuration, settings, customers, and the order lifecycle. Multiple admins may act
  concurrently; fine-grained roles are out of scope for MVP.
- **System** (the platform itself): issues/verifies OTPs, computes prices/fees/discounts
  authoritatively, revalidates orders at checkout, assigns order numbers, and enforces
  status transitions.

## Clarifications

### Session 2026-09-17

- Q: C1 — When both an active product offer and an eligible quantity/wholesale tier exist
  for the selected selling unit, which price applies? → A: Compute both the eligible active
  offer unit price and the eligible tier unit price and apply whichever is LOWER for the
  customer (they do NOT stack). If only one mechanism applies, use it; if neither applies,
  use the normal active price. The final selected unit price is clearly shown. Intent: an
  active marketing offer must never make a wholesale customer pay more than an otherwise
  eligible tier price.
- Q: C2 — When multiple active delivery-discount rules qualify, how is the single benefit
  chosen? → A: Delivery discounts do NOT stack. Compute each qualifying rule's actual
  monetary saving against the current base delivery fee and apply the single largest
  saving. The final delivery charge can never go below zero (free delivery = a saving equal
  to the full base fee). Ties are broken deterministically by the higher qualifying minimum
  subtotal (the more restrictive rule). Qualification uses the eligible product subtotal,
  excluding delivery charges.
- Q: C3 — Order cancellation: who may cancel, and from which statuses? → A: A customer MAY
  self-cancel only while status is New; once Confirmed, self-cancel is unavailable and the
  customer must contact the business. Authorized admins MAY cancel from New, Confirmed,
  Preparing, or Out for Delivery. Delivered and already-Cancelled orders cannot transition
  to Cancelled. Cancellation retains the historical order record; an optional cancellation
  reason MAY be stored. No complex cancellation workflow in MVP.
- Q: C4 — Do delivery slots enforce numeric order capacity in MVP? → A: No. MVP does not
  manage numeric capacity per slot. Slots are admin-managed and active/inactive; customers
  select an active slot for an eligible date; if a slot becomes inactive before
  confirmation, checkout revalidation rejects it and requires selecting another available
  slot. Numeric per-slot capacity is future scope.
- Q: C5 — Is the customer modeled as a person, a business, or both? → A: Primarily B2B. The
  profile captures Business/Restaurant Name (primary commercial identity), Contact Person
  Name (person managing the order), Login Mobile Number, WhatsApp Number, and delivery
  details. No legal-company/KYC/tax-registration complexity in MVP.
- Q: C6 — Should the MVP UI expose multiple saved addresses? → A: The MVP UI exposes exactly
  ONE active/default delivery address per customer (created during onboarding, editable
  later, auto-reused at checkout). Requirements and domain assumptions must not preclude
  multiple saved addresses later; multiple-address selection UI is not part of MVP.
- Q: C7 — What is the minimum-order basis? → A: The effective product subtotal — product
  line totals after applicable product pricing, quantity tiers, and product offers —
  excluding the delivery fee and excluding delivery discounts. Original/list prices are not
  used when the customer pays a lower valid product price (e.g., effective products 480 +
  delivery 60 ⇒ minimum 500 not met, 20 short).

## User Scenarios & Testing *(mandatory)*

Priorities: **P1** = the minimum functional ordering loop (business is non-operational
without it). **P2** = high-value supporting capabilities. **P3** = experience polish that
is required for MVP acceptance but not for the loop to function.

### User Story 1 - Customer Sign-In & Onboarding (Priority: P1)

A customer signs in with their mobile number, receives and enters a one-time code, and —
if new — completes a saved profile (name, WhatsApp, delivery area and address details).
Returning customers go straight to browsing/ordering without re-entering details.

**Why this priority**: Ordering, saved delivery details, and per-customer order history
all require an identified customer. Nothing downstream works without it.

**Independent Test**: With OTP delivery stubbed/observable, a new number can complete
sign-in + profile and land in the app; the same number signing in again skips profile.

**Acceptance Scenarios**:

1. **Given** a valid, unregistered mobile number, **When** the customer requests a code
   and enters the correct code, **Then** they are asked to complete their profile before
   ordering.
2. **Given** a valid, previously-registered mobile number, **When** the customer verifies
   a correct code, **Then** they enter the app directly without re-completing the profile.
3. **Given** an incorrect code, **When** submitted, **Then** the customer sees a clear
   error and remains on the code screen able to retry or resend (subject to cooldown).
4. **Given** an expired code, **When** submitted, **Then** the customer is told it expired
   and offered a resend.
5. **Given** a customer abandons profile completion, **When** they return and sign in
   again, **Then** they resume onboarding (are not treated as fully registered) and no
   partial customer can place an order.

---

### User Story 2 - Catalog Browsing & Product Discovery (Priority: P1)

A customer browses products grouped by admin-managed categories, searches by name/brand,
opens a product to see its brand, description, image, availability, and its selling units.

**Why this priority**: Customers cannot order what they cannot find and inspect.

**Independent Test**: With seeded categories/products, a customer can open a category, see
its products, search, open a product detail showing available units, and observe correct
empty/out-of-stock/inactive states.

**Acceptance Scenarios**:

1. **Given** active categories with products, **When** the customer opens a category,
   **Then** its available and out-of-stock products are listed and inactive products are
   not shown.
2. **Given** a search term, **When** entered, **Then** matching products (by name/brand)
   are returned, with a clear empty-result state when none match.
3. **Given** an out-of-stock product, **When** opened, **Then** it is viewable but cannot
   be added to cart and is clearly labelled out of stock.
4. **Given** a category with no active products, **When** opened, **Then** a clear empty
   state is shown (not an error).

---

### User Story 3 - Cart, Selling Units & Tiered Pricing (Priority: P1)

A customer selects a product's selling unit and quantity, adds it to the cart, and sees
the current unit price, any applied quantity/wholesale tier or active offer price, the
line total, and the cart subtotal. Cart figures are clearly labelled as estimates until
checkout revalidation.

**Why this priority**: Correct, transparent unit + quantity pricing is the core commercial
value of a wholesale supplies app.

**Independent Test**: With seeded products having multiple units and tier prices, a
customer can add items, cross quantity tiers up and down, switch units, and always see a
correct estimated line total and subtotal; changing away from a tier reverts the price.

**Acceptance Scenarios**:

1. **Given** a product with tiered pricing, **When** quantity crosses into a cheaper tier,
   **Then** the displayed unit price and line total update to the cheaper tier.
2. **Given** an item already at a cheaper tier, **When** quantity drops below that tier,
   **Then** the price reverts to the applicable higher tier.
3. **Given** a product with multiple units, **When** the customer changes the selected
   unit, **Then** pricing/tiers for the newly selected unit apply.
4. **Given** an active offer on a product/unit, **When** the item is shown in cart,
   **Then** the offer price is clearly indicated as an offer.
5. **Given** a product/unit with both an active offer and an eligible quantity tier, **When**
   the tier unit price is lower than the offer unit price, **Then** the tier price is applied
   (and vice-versa); the two never combine, and the applied final unit price is shown.
6. **Given** a unit with no quantity tiers, **When** added, **Then** a single flat unit
   price applies with no tier messaging.
7. **Given** an item in the cart whose product/unit becomes unavailable, **When** the
   customer views the cart, **Then** the item is clearly flagged as unavailable and is
   excluded from a valid checkout until resolved.

---

### User Story 4 - Checkout & Cash-on-Delivery Order Placement (Priority: P1)

A customer reviews a complete order summary — customer/contact, delivery area + address,
delivery date and time slot, priced line items, subtotal, delivery fee, delivery discount,
final delivery fee, and final total — then explicitly confirms a cash-on-delivery order.
The system revalidates all commercial and availability conditions server-side before
accepting, and surfaces any change for review rather than silently altering terms.

**Why this priority**: Placing a correct, server-authoritative order is the reason the
product exists.

**Independent Test**: With a valid cart, delivery area, and slot, a customer completes
checkout and receives an order number; if a price/availability/slot condition changed
since carting, checkout blocks with a clear review prompt instead of placing wrong terms.

**Acceptance Scenarios**:

1. **Given** a cart at/above the configured minimum order subtotal with an eligible area
   and available slot, **When** the customer confirms, **Then** an order is created in
   status **New** with a unique order number and the final server-computed total.
2. **Given** a cart below the minimum order subtotal, **When** the customer attempts
   checkout, **Then** checkout is prevented and the shortfall to reach the minimum is
   shown.
3. **Given** a price, offer, tier, delivery fee, or availability changed since the item
   was carted, **When** the customer confirms, **Then** the order is NOT placed on the
   old terms; the customer is shown what changed and must review the updated summary.
4. **Given** the selected delivery area became inactive, **When** the customer attempts to
   confirm, **Then** placement is blocked with a clear message to choose an eligible area.
5. **Given** the selected time slot became unavailable, **When** the customer attempts to
   confirm, **Then** placement is blocked and the customer is prompted to pick another
   available slot.
6. **Given** the device has no connectivity, **When** the customer attempts to confirm,
   **Then** the system MUST NOT indicate success; it shows a clear failure/retry state.

---

### User Story 5 - Admin Catalog & Commerce Management (Priority: P1)

An admin creates and maintains categories, products (brand, description, image, category,
availability), each product's multiple selling units, per-unit quantity/wholesale pricing
tiers, and temporary product offers.

**Why this priority**: There is nothing to sell and no prices to compute until an admin
can define the catalog and its commercial terms.

**Independent Test**: An admin can create a category, a product with two units, tiered
prices per unit, and a dated offer; the customer-facing catalog reflects these correctly.

**Acceptance Scenarios**:

1. **Given** the admin dashboard, **When** an admin creates/edits a category and toggles
   its active state, **Then** active categories appear to customers and inactive ones do
   not.
2. **Given** a product, **When** an admin adds multiple selling units and per-unit tier
   prices, **Then** customers see those units and the correct tier price at each quantity.
3. **Given** a product, **When** an admin marks it out of stock or inactive, **Then**
   customer behavior matches (visible-but-unorderable, or hidden, respectively).
4. **Given** a product, **When** an admin creates an offer with a start/end date and
   active state, **Then** the offer price applies to customers only while active and in
   range, and never after it expires or is deactivated.

---

### User Story 6 - Admin Order Management & Lifecycle (Priority: P1)

An admin views incoming and historical orders, searches/filters them, opens full order
detail (customer, delivery, and immutable commercial breakdown), and advances each order
through the allowed status lifecycle.

**Why this priority**: An order that cannot be seen and fulfilled by staff delivers no
business value.

**Independent Test**: After a customer places an order, an authorized admin sees it as
**New**, opens its details, and moves it through valid statuses; invalid transitions are
rejected.

**Acceptance Scenarios**:

1. **Given** a newly placed order, **When** an authorized admin opens the orders list,
   **Then** the order is visible as **New** without requiring real-time push.
2. **Given** an order in **New**, **When** the admin advances status along an allowed
   path, **Then** the new status is recorded and shown to the customer.
3. **Given** an order, **When** the admin attempts a transition that is not allowed,
   **Then** the transition is rejected with a clear reason.
4. **Given** many orders, **When** the admin searches/filters (e.g., by status, date, or
   customer), **Then** the matching orders are returned.
5. **Given** an order in New, Confirmed, Preparing, or Out for Delivery, **When** an
   authorized admin cancels it, **Then** it moves to Cancelled and its historical record is
   retained; **When** the order is Delivered or already Cancelled, **Then** cancellation is
   rejected.

---

### User Story 7 - Admin Delivery & Store Setup (Priority: P1)

An admin configures delivery areas (with per-area base delivery fee and active state),
delivery time slots, the minimum order amount, and the business/contact information the
customer app displays.

**Why this priority**: Checkout (US4) cannot compute a delivery fee, offer valid slots, or
enforce the minimum without this configuration; it is part of the core loop's data.

**Independent Test**: An admin creates an active area with a fee and one or more slots and
sets a minimum order; a customer can then select that area/slot and see the fee at
checkout, with the minimum enforced.

**Acceptance Scenarios**:

1. **Given** delivery setup, **When** an admin creates an active area with a base fee,
   **Then** customers can select it and see that base fee at checkout.
2. **Given** an area, **When** an admin deactivates it, **Then** customers cannot place a
   new order to that area.
3. **Given** slot management, **When** an admin defines/enables slots, **Then** customers
   can select only enabled slots for an eligible date.
4. **Given** settings, **When** an admin sets the minimum order amount, **Then** checkout
   enforces exactly that value.

---

### User Story 8 - Delivery Discount Rules (Priority: P2)

An admin defines subtotal-threshold delivery discounts (fixed amount, percentage of
delivery fee, or free delivery). At checkout the qualifying benefit is applied to the
delivery fee based on the product subtotal (excluding delivery fee), and the customer sees
the discount clearly.

**Why this priority**: Valuable commercial lever, but the loop functions with a flat
per-area delivery fee; discounts are an enhancement layered on US7.

**Independent Test**: With rules seeded at subtotal thresholds, carts at various subtotals
show the correct single delivery benefit; a cart below all thresholds shows none.

**Acceptance Scenarios**:

1. **Given** a rule "subtotal ≥ 500 ⇒ 20 off delivery", **When** product subtotal is 600,
   **Then** the delivery fee is reduced by 20 and shown as a delivery discount.
2. **Given** a free-delivery rule at a threshold, **When** the subtotal qualifies, **Then**
   the final delivery fee is 0 and labelled free delivery.
3. **Given** the product subtotal is below every rule threshold, **When** at checkout,
   **Then** no delivery discount is applied.
4. **Given** multiple rules qualify simultaneously, **When** at checkout, **Then** exactly
   one benefit is applied — the rule producing the largest monetary saving against the base
   delivery fee, ties broken by the higher qualifying minimum subtotal — never stacked.
5. **Given** a qualifying free-delivery rule and a smaller fixed-discount rule, **When** at
   checkout, **Then** free delivery is applied (final delivery fee 0) because it yields the
   largest saving, and the final delivery charge is never below zero.

---

### User Story 9 - Customer Order History with Immutable Snapshots (Priority: P2)

An authenticated customer views their past orders (number, date, total, status) and opens
any order to see the exact commercial terms captured when it was placed, unaffected by
later changes to products, prices, units, offers, or delivery fees.

**Why this priority**: Builds trust and repeat ordering, but is not required to place the
first order. Snapshot integrity is a constitutional must (Principle V).

**Independent Test**: Place an order, then change the product's price/unit/offer as admin;
the historical order still shows the original figures.

**Acceptance Scenarios**:

1. **Given** past orders exist, **When** the customer opens order history, **Then** they
   see their own orders with number, date, total, and status.
2. **Given** an order was placed at certain prices, **When** the product's price/unit/offer
   later changes, **Then** the historical order still shows the original name, unit, unit
   price, quantity, discounts, and totals.
3. **Given** a customer, **When** they open history, **Then** they see only their own
   orders and never another customer's.

---

### User Story 10 - Public Landing Page (Priority: P2)

A visitor arrives at a public, mobile-first landing page that explains the business,
previews main categories, highlights offers/benefits, describes delivery coverage at a
high level and how ordering works, shows contact/footer info, and provides a clear
call-to-action to sign in / start ordering.

**Why this priority**: Important first impression and required for client presentation, but
a customer can reach ordering via direct sign-in without it; it does not affect the
ordering loop's correctness.

**Independent Test**: A visitor with no account can load the landing page, see all required
content sections, and follow the CTA into sign-in.

**Acceptance Scenarios**:

1. **Given** no authentication, **When** a visitor opens the site root, **Then** the
   landing page renders with hero, value proposition, category preview, offers/featured
   section, how-ordering-works, delivery/service info, CTA, and footer/contact.
2. **Given** the landing page, **When** the visitor taps the primary CTA, **Then** they are
   taken to the customer sign-in flow.
3. **Given** category/offer previews, **When** the underlying content is empty, **Then**
   the page still renders gracefully without broken sections.

---

### User Story 11 - Admin Customer Directory (Priority: P2)

An admin views and searches customers, sees a customer's profile/contact information, and
sees that customer's relevant order history.

**Why this priority**: Supports service and fulfilment, but the ordering loop and order
management (US6) already expose per-order customer details.

**Independent Test**: With customers and orders present, an admin can search a customer,
open their profile, and see their orders.

**Acceptance Scenarios**:

1. **Given** registered customers, **When** an admin searches by name/number, **Then**
   matching customers are listed.
2. **Given** a customer, **When** an admin opens their profile, **Then** contact/delivery
   info and their order history are shown.

---

### User Story 12 - PWA Installability & Connectivity Integrity (Priority: P3)

The customer experience is installable to the home screen where supported, behaves like an
app (consistent navigation, fast, RTL-ready), and never falsely reports success when the
device is offline.

**Why this priority**: Required for MVP acceptance as a PWA, but the ordering loop can be
demonstrated in-browser first.

**Independent Test**: On a supported device/browser, the app offers install; with the
network disabled, order submission shows a clear failure and no false confirmation.

**Acceptance Scenarios**:

1. **Given** a supported browser, **When** the customer uses the app, **Then** an install
   affordance is available and the installed app opens in an app-like display.
2. **Given** no connectivity, **When** the customer performs an action requiring the
   server, **Then** a clear offline/error state is shown and no success is implied.

---

### User Story 13 - UX Quality & Comprehensive State Coverage (Priority: P3)

Every critical customer and admin screen presents deliberate loading, empty, error,
disabled, validation, and (where relevant) out-of-stock states, and the overall experience
reads as professional, modern, trustworthy, uncluttered, and fast on mobile — treated as
acceptance, not optional polish.

**Why this priority**: Cross-cutting quality gate that hardens the other stories; layered
after functional slices exist.

**Independent Test**: A reviewer can, for each critical screen, trigger and observe each
declared state.

**Acceptance Scenarios**:

1. **Given** any data-driven customer screen, **When** data is loading, empty, or errored,
   **Then** the corresponding distinct state is shown (never a blank or misleading view).
2. **Given** an unorderable item (out of stock / unavailable unit), **When** displayed,
   **Then** its controls are disabled with a clear reason.
3. **Given** a form, **When** input is invalid, **Then** inline validation communicates the
   problem before submission is accepted.

---

### Edge Cases

**Authentication / onboarding**

- Invalid or malformed phone number → rejected with guidance before any code is sent.
- Incorrect OTP → clear error, retry allowed within rate limits.
- Expired OTP → clear "expired" message with resend option.
- Resend requested before cooldown elapses → resend blocked with remaining wait shown.
- Excessive attempts (codes requested or wrong entries) → temporary lockout/backoff
  communicated from a UX perspective (no silent failure).
- Onboarding interrupted (app closed mid-profile) → resumes as incomplete; cannot order
  until profile is complete.

**Catalog / cart**

- Empty category, empty search results, loading, and error states are distinct.
- Out-of-stock product remains visible but unorderable; inactive product is hidden.
- Product/unit becomes out of stock, inactive, or unavailable while in the cart → item
  flagged, excluded from valid checkout until resolved.
- Price, tier, or offer changes while item is in cart → estimate updates; final terms are
  set only at checkout revalidation.
- Offer expires between carting and checkout → offer no longer applied at checkout review.
- Quantity has no matching tier / a single flat price → no tier messaging.

**Delivery / checkout**

- Product subtotal below minimum order → checkout blocked; shortfall shown.
- Selected delivery area deactivated → new order to that area blocked with a message.
- No slots available for a chosen date, slot deactivated during checkout, or a past/invalid
  date chosen → blocked with a prompt to pick a valid slot/date.
- Multiple delivery discount rules qualify → exactly one benefit applied (never stacked).
- Delivery fee/discount changes between cart and checkout → surfaced for review.
- Device offline at confirmation → no false success; retry state shown.
- Concurrent change: an admin edits price/availability at the moment of checkout →
  revalidation catches it and forces review.

**Orders / admin**

- Two admins act on the same order → status transitions must remain consistent and only
  allowed transitions succeed. Customer self-cancel is limited to **New**; admins may cancel
  from **New**, **Confirmed**, **Preparing**, or **Out for Delivery**; **Delivered** and
  **Cancelled** orders cannot be cancelled.
- Historical order must never change when catalog/pricing/delivery config later changes.

## Requirements *(mandatory)*

Requirement language: **MUST** = mandatory; **SHOULD** = strong default, deviations
justified; **MAY** = optional/permitted.

### Functional Requirements — Authentication & Customer Profile

- **FR-001**: The system MUST let a customer sign in using their mobile phone number via a
  one-time code (OTP) flow: enter number → receive code → enter code → verify.
- **FR-002**: The system MUST validate phone-number format before issuing a code and reject
  invalid numbers with clear guidance.
- **FR-003**: The system MUST reject incorrect codes and clearly distinguish "incorrect"
  from "expired".
- **FR-004**: The system MUST expire codes after a limited validity window and allow the
  customer to request a new code.
- **FR-005**: The system MUST enforce a resend cooldown and MUST rate-limit code requests
  and verification attempts, communicating waits/lockouts to the customer (UX level). Exact
  numeric thresholds are configurable; see Assumptions.
- **FR-006**: The system MUST require first-time customers to complete a profile before
  they can place an order, and MUST NOT require returning customers to re-complete it.
- **FR-007**: The customer profile MUST capture at least: Business/Restaurant Name (the
  primary commercial identity), Contact Person Name (the person managing the order), login
  mobile number, WhatsApp number, delivery area, delivery address, and address detail fields
  (building/location, floor, apartment/shop/unit, landmark, delivery notes) where applicable.
  The MVP MUST NOT introduce legal-company/KYC/tax-registration fields.
- **FR-008**: The system MUST persist the customer's profile and a single active/default
  delivery address so they are NOT re-entered on every order; the customer creates this
  address during onboarding, MAY edit it later, and checkout MUST auto-reuse it. The MVP UI
  MUST expose exactly ONE delivery address per customer; requirements MUST NOT preclude
  multiple saved addresses in future, and multiple-address selection UI is out of MVP scope.
- **FR-009**: The system MUST resume interrupted onboarding as incomplete and MUST prevent
  an incompletely-onboarded customer from placing an order.
- **FR-010**: The system MUST NOT reveal OTP values in any client response or user-visible
  channel, and MUST treat authentication endpoints as security-sensitive (rate-limited,
  server-validated).

### Functional Requirements — Catalog, Categories & Products

- **FR-011**: Customers MUST be able to browse products grouped by admin-managed
  categories.
- **FR-012**: Customers MUST be able to search products by name and brand, with a clear
  empty-result state.
- **FR-013**: Customers MUST be able to open a product detail showing name, brand,
  category, image, description, selling/package information, and availability.
- **FR-014**: The system MUST represent product availability as one of **Available**,
  **Out of Stock**, or **Inactive**.
- **FR-015**: Out-of-stock products MUST remain viewable but MUST NOT be orderable.
- **FR-016**: Inactive products MUST NOT be shown or orderable to customers.
- **FR-017**: Category and product lists MUST present distinct normal, empty, loading, and
  error states, and MUST hide inactive categories from customers.

### Functional Requirements — Selling Units & Pricing

- **FR-018**: A product MUST support one or more selling units, and the selected unit MUST
  be clearly shown in product detail, cart, checkout, and order history.
- **FR-019**: The system MUST support quantity/wholesale price tiers defined per selling
  unit, and MUST support units/products with a single flat price and no tiers.
- **FR-020**: The system MUST display, per cart line, the current unit price, any applied
  tier or offer price, the quantity, and the line total.
- **FR-021**: When quantity crosses a tier boundary (up or down), the system MUST reflect
  the correct tier's unit price and line total.
- **FR-022**: When the customer changes the selected unit, the system MUST apply that
  unit's own pricing and tiers.
- **FR-023**: The system MUST support temporary product offers defined by normal price,
  offer price, start date, end date, and active state, and MUST apply an offer price only
  while the offer is active and within its date range.
- **FR-024**: Expired or inactive offers MUST NOT be applied, and active offers MUST be
  clearly indicated to the customer.
- **FR-025**: When the selected product/unit has both an active offer and an eligible
  quantity/wholesale tier, the system MUST compute both the eligible offer unit price and
  the eligible tier unit price and apply whichever yields the LOWER unit price for the
  customer; the two MUST NOT stack. If only one mechanism applies, that one MUST be used; if
  neither applies, the normal active price MUST be used. The final selected unit price MUST
  be clearly communicated to the customer.

### Functional Requirements — Cart

- **FR-026**: Customers MUST be able to add items (product + selling unit + quantity),
  change quantity, and remove items.
- **FR-027**: The cart MUST show selected unit, current unit price, line total, and cart
  subtotal.
- **FR-028**: Cart totals shown before checkout MUST be presented as estimates that are not
  binding until checkout revalidation.
- **FR-029**: The cart MUST handle and clearly flag: empty cart, product becoming out of
  stock, product becoming inactive, unit becoming unavailable, price change, tier-pricing
  change, and offer expiry, and MUST exclude unavailable items from a valid checkout.

### Functional Requirements — Minimum Order

- **FR-030**: The system MUST enforce an admin-configurable minimum order threshold measured
  against the effective product subtotal — the sum of product line totals after applicable
  product pricing, quantity tiers, and product offers — excluding the delivery fee and
  excluding any delivery discounts. Original/list prices MUST NOT be used when the customer
  pays a lower valid product price.
- **FR-031**: When the subtotal is below the minimum, the system MUST prevent checkout,
  MUST show the required minimum, and SHOULD show the remaining amount to reach it.

### Functional Requirements — Delivery Areas, Fees & Discounts

- **FR-032**: The system MUST support admin-managed delivery areas, each with an active
  state and its own base delivery fee.
- **FR-033**: Customers MUST select an eligible (active) delivery area as part of their
  saved delivery information, and the system MUST block new orders to inactive areas with a
  clear message.
- **FR-034**: The delivery fee for an order MUST be determined by the selected delivery
  area, and checkout MUST show base delivery fee, any delivery discount, and final delivery
  fee before confirmation.
- **FR-035**: The system MUST support admin-managed delivery discount rules of types fixed
  amount, percentage of delivery fee, and free delivery, qualified by product subtotal
  (excluding delivery fee).
- **FR-036**: Delivery discount rules MUST NOT stack. When multiple rules qualify, the
  system MUST compute each qualifying rule's actual monetary saving against the current base
  delivery fee and apply the single rule producing the largest saving. A delivery discount
  MUST never reduce the final delivery charge below zero (free delivery equals a saving of
  the full base delivery fee). If two rules produce the same final saving, the system MUST
  deterministically apply the rule with the higher qualifying minimum subtotal (the more
  restrictive rule).
- **FR-037**: Any applied delivery discount MUST be clearly shown to the customer in
  cart/checkout.

### Functional Requirements — Delivery Date & Time Slots

- **FR-038**: The system MUST let the customer choose a delivery date and an available,
  admin-managed delivery time slot.
- **FR-039**: The system MUST handle slot unavailable, slot deactivated during checkout, no
  slots for a date, past/invalid date, and date change, blocking confirmation with clear
  guidance where the selection is not valid.
- **FR-040**: MVP delivery slots MUST NOT enforce numeric per-slot order capacity. Slots are
  admin-managed with an active/inactive state, and customers MUST be able to select any
  active slot for an eligible date. If a selected slot becomes inactive before confirmation,
  checkout revalidation MUST reject it and require selecting another available slot. Numeric
  per-slot capacity is explicitly future scope.

### Functional Requirements — Checkout & Order Placement

- **FR-041**: Checkout MUST present a complete pre-confirmation review: customer name /
  mobile / WhatsApp; delivery area / full address / date / slot; line items with unit,
  quantity, unit price, line total, and product discounts; subtotal; delivery fee; delivery
  discount; final delivery fee; and final total; with payment method **Cash on Delivery**.
- **FR-042**: The customer MUST explicitly confirm the order.
- **FR-043**: Before accepting confirmation, the system MUST revalidate, server-side, all
  commercial and availability conditions (prices, tiers, offers, availability, area
  eligibility, delivery fee/discount, slot availability, minimum order).
- **FR-044**: If any condition changed since carting, the system MUST NOT place the order on
  the old terms; it MUST inform the customer of the change and require review of the updated
  summary before any placement.
- **FR-045**: All monetary totals MUST be computed server-side and MUST NOT be trusted from
  the client (constitution Principle II).
- **FR-046**: On successful placement, the system MUST create the order in status **New**,
  assign a unique order number, and record the final server-computed total.
- **FR-047**: The system MUST NOT indicate a successful submission when the request did not
  reach/complete on the server (e.g., offline), showing a clear failure/retry state instead.

### Functional Requirements — Order Confirmation, Lifecycle & History

- **FR-048**: After placement, the customer MUST see a success state with order number,
  final total, delivery date, delivery slot, delivery address, and current status, and MUST
  be able to open order details.
- **FR-049**: The order lifecycle statuses MUST be: **New**, **Confirmed**, **Preparing**,
  **Out for Delivery**, **Delivered**, **Cancelled**; the initial status after customer
  confirmation MUST be **New**.
- **FR-050**: The system MUST enforce order status transitions; the intended forward path is
  New → Confirmed → Preparing → Out for Delivery → Delivered, and disallowed transitions MUST
  be rejected. Cancellation rules: a customer MAY self-cancel only while status is **New**
  (self-cancel is unavailable once **Confirmed**; thereafter the customer must contact the
  business outside self-service); an authorized admin MAY cancel from **New**, **Confirmed**,
  **Preparing**, or **Out for Delivery**. **Delivered** and already-**Cancelled** orders MUST
  NOT transition to Cancelled. Cancellation MUST retain the historical order record; an
  optional cancellation reason MAY be stored. No further cancellation workflow is in MVP
  scope.
- **FR-051**: Each placed order MUST store immutable snapshots of its commercial facts —
  product name, selling unit, unit price, quantity, applied discounts, line totals, delivery
  fee, delivery discount, and final total — so later catalog/pricing/delivery changes do NOT
  alter historical orders (constitution Principle V).
- **FR-052**: Authenticated customers MUST see a list of their own orders (number, date,
  total, status) and MUST be able to open historical order details reflecting the terms at
  time of placement; a customer MUST NOT see another customer's orders.

### Functional Requirements — Admin Dashboard & Management

- **FR-053**: Authorized admins MUST see a dashboard summarizing at least new orders,
  today's orders, recent orders, and useful high-level order/sales indicators (no advanced
  analytics required).
- **FR-054**: Admins MUST be able to view, search/filter, and open orders with full customer,
  delivery, and commercial detail, and change status per allowed transitions.
- **FR-055**: A newly placed order MUST become visible to authorized admins without a
  real-time push mechanism (no WebSocket requirement for MVP).
- **FR-056**: Admins MUST be able to view and search customers and see a customer's profile
  and relevant order history.
- **FR-057**: Admins MUST be able to create, edit, activate/deactivate, and (where useful)
  arrange categories.
- **FR-058**: Admins MUST be able to create/edit products; activate/deactivate; mark
  available/out of stock; manage images; assign category; and manage brand/details.
- **FR-059**: Admins MUST be able to manage multiple selling units per product and per-unit
  pricing including quantity/wholesale tiers.
- **FR-060**: Admins MUST be able to create/manage temporary product offers.
- **FR-061**: Admins MUST be able to create/edit/activate/deactivate delivery areas and
  configure base delivery fees; manage delivery slots; and create/edit/activate/deactivate
  delivery discount rules.
- **FR-062**: Admins MUST be able to configure settings including at least the minimum order
  amount and the business/contact information shown in the customer experience.

### Functional Requirements — Simple Inventory (prompt 32, CORE MVP)

> **Scope amendment (prompt 32):** simple per-selling-unit inventory quantity is now a **core MVP
> requirement**, superseding earlier "inventory quantities out of MVP" statements. Warehouses,
> suppliers, purchasing, batch/expiry, costing, and unit-conversion stock remain out of scope.

- **FR-071**: The system MUST maintain an integer stock balance **per selling unit** (not per
  product); each unit's balance is independent (e.g. Bag 2.5KG = 80, Carton = 25).
- **FR-072**: Admins MUST be able to view each unit's current stock, **add** stock, **reduce/correct**
  stock, and see stock status; every manual change MUST require a quantity and record a reason where
  the UX calls for it, show current stock, and preview the resulting stock.
- **FR-073**: The system MUST NOT allow a unit's stock balance to go below zero (MVP: no negative
  balance; corrections cannot drive it negative).
- **FR-074**: A selling unit with `stock_quantity = 0` MUST be treated as **out of stock** — not
  orderable, shown as "غير متوفر", and rejected by add-to-cart and checkout. Admins MUST NOT have to
  manually toggle out-of-stock when stock naturally reaches zero. Authoritative unit orderability =
  `is_active = true AND stock_quantity > 0`.
- **FR-075**: Successful order placement MUST atomically decrement the ordered units' stock inside the
  order transaction, using row-level locking to prevent overselling under concurrency; if any line
  lacks sufficient stock the order MUST NOT be created and **no** stock may be partially deducted —
  the customer is returned a structured changed/unavailable result to review.
- **FR-076**: Inventory MUST be deducted at order **creation** (status New), not at later
  confirmation; no reservation-expiry timers in MVP.
- **FR-077**: Cancelling an order from an eligible non-delivered state MUST restore its deducted
  stock **exactly once** (idempotent — never double-restock); delivered and already-cancelled orders
  MUST NOT restore stock. Historical order snapshots remain unchanged.
- **FR-078**: Every stock change (manual or automatic) MUST be recorded in a durable, non-editable
  inventory adjustment history capturing unit, type, delta, before/after quantities, reason/source,
  admin/order reference, and timestamp.

**Acceptance Scenarios**:

1. **Given** a unit at stock 10, **When** an admin adds 20, **Then** stock is 30 and an adjustment
   (`manual_add`, +20, before 10, after 30) is recorded.
2. **Given** a unit at stock 5, **When** an admin corrects it down, **Then** stock cannot go below 0
   and the change is recorded.
3. **Given** a unit reaches stock 0, **When** a customer views it, **Then** it shows Out of Stock and
   cannot be added to cart or checked out.
4. **Given** two concurrent orders for the last available quantity, **When** both confirm, **Then**
   exactly one succeeds and the other is blocked with a changed/unavailable review — no overselling.
5. **Given** a valid multi-line order, **When** placed, **Then** all lines' stock decrement atomically
   and matching `order` adjustments are recorded; a failure rolls back every deduction.
6. **Given** a placed order is cancelled from an eligible state, **When** cancelled, **Then** its stock
   is restored once (`order_cancel_restore`); repeating cancellation never double-restocks; a delivered
   order never restores.

### Functional Requirements — Two-Level Product Units (prompt 37, CORE MVP)

> **Amendment (prompt 37):** units become a reusable module; each product defines a **primary** and a
> **sub** unit with a product-specific conversion. This **supersedes** FR-071's "per selling unit"
> stock wording — inventory is now one authoritative **sub-unit** balance per product.

- **FR-079**: The system MUST maintain a reusable catalog of **units** (language-neutral `code` +
  Arabic/English names), managed by admins, independent of any product.
- **FR-080**: Each product MUST define exactly **one primary unit and one sub unit** with a
  product-specific conversion factor (`1 primary = N sub`, N > 0); primary ≠ sub. No third level.
- **FR-081**: Both the primary and sub units MUST be independently sellable; a customer MAY order
  quantities of either (e.g. 2 cartons + 5 pieces of the same product).
- **FR-082**: Each sellable unit MUST have its **own** price (and its own tiers/offers); the sub-unit
  price MUST NOT be derived automatically from the primary price. The lower-of tier/offer rule
  (FR-025/BR-011) applies per selected unit.
- **FR-083**: Inventory MUST be stored and computed in the **sub unit** as the single authoritative
  balance per product. Admin stock displays MAY show `primary + remainder sub` (calculated), but the
  stored balance is sub-units.
- **FR-084**: Admins MUST be able to add/remove/correct stock entered in **either** the primary or the
  sub unit; primary entries MUST be converted to sub-units before the balance changes; stock MUST
  never go below zero; each change records the input unit/quantity **and** the normalized sub-unit
  delta.
- **FR-085**: Order placement MUST deduct the **normalized sub-unit** quantity for all lines atomically
  under locking (no overselling); order snapshots MUST preserve the unit, the conversion factor used,
  and the normalized sub-unit quantity, so later conversion-factor changes never alter history.
- **FR-086**: Changing a product's conversion factor MUST be blocked while the product has non-zero
  stock (stock is never silently re-interpreted); a stock correction/reset is required first.

**Acceptance Scenarios**:

1. **Given** `1 carton = 12 pieces` and stock 125 pieces, **When** admin adds 10 cartons, **Then**
   stock becomes 245 pieces and the adjustment records input=10 cartons, normalized delta +120.
2. **Given** the same product, **When** a customer orders 2 cartons + 5 pieces, **Then** 29 pieces are
   deducted atomically (no overselling) and snapshots store the factor 12.
3. **Given** carton = 1,200 ج and piece = 110 ج, **When** the carton price changes, **Then** the piece
   price is unaffected (independent pricing).
4. **Given** a product with non-zero stock, **When** admin tries to change its conversion factor,
   **Then** the change is blocked until stock is reset/corrected.

### Functional Requirements — Admin Audit Log (prompt 39, CORE MVP)

- **FR-087**: The system MUST record critical admin write actions in an **append-only** audit trail
  capturing the actor, the entity, the action, old values, new values, and timestamp.
- **FR-088**: Audited actions MUST cover (as each area exists) product create/update/activate/image
  changes, unit create/update/activate and conversion changes, base-price/tier/offer changes,
  manual inventory add/remove/correct, order status changes and cancellations, and critical
  delivery/settings changes. Monetary old/new values MUST be stored in integer minor units.
- **FR-089**: The audit trail MUST reference — never replace — the authoritative inventory ledger;
  MUST redact sensitive fields (passwords, OTP/codes, tokens, secrets); MUST be viewable only by
  authorized admins via a **read-only** screen; and MUST NOT be editable/deletable through the admin UI.

**Acceptance Scenarios**:

1. **Given** an admin edits a product's base price, **When** the change is saved, **Then** an audit
   entry records the old and new price (minor units), the actor, and the timestamp.
2. **Given** an admin adds stock, **When** the ledger row is written, **Then** the audit entry
   references the inventory adjustment id and does not duplicate the ledger.
3. **Given** a stock change that fails (would go below zero), **When** it is rejected, **Then** no
   audit entry is created.
4. **Given** a customer session, **When** it requests the audit screen, **Then** access is denied.

### Functional Requirements — Admin Users & Authorization

- **FR-063**: The system MUST support multiple authorized admin users who may manage the
  defined administration functions.
- **FR-064**: All administrative actions MUST be authorized server-side; the MVP MAY treat
  all authorized admins as having equal access (fine-grained RBAC is out of scope) unless a
  security separation need is identified.

### Functional Requirements — Landing Page & PWA

- **FR-065**: The system MUST provide a public landing page (no authentication) containing
  at least: hero, value proposition, main categories preview, featured/offers section,
  how-ordering-works, delivery/service info, a clear sign-in/start-ordering CTA, and
  footer/contact info; it MUST render gracefully when preview content is empty.
- **FR-066**: The customer-facing experience MUST be installable as a PWA (installable to
  home screen where supported), mobile-first, app-like, with consistent navigation and
  Arabic/RTL readiness. Offline ordering is NOT required.
- **FR-067**: The customer experience MUST present a clear offline/error state and MUST NOT
  falsely indicate an order was submitted when offline.

### Functional Requirements — UX Quality & State Coverage

- **FR-068**: Critical customer and admin screens MUST define and present, where applicable,
  loading, empty, error, disabled, validation, and out-of-stock states.
- **FR-069**: Pricing and unit selection MUST be presented clearly and unambiguously so the
  customer is never confused about which unit or price applies.
- **FR-070**: UI/UX quality (professional, modern, trustworthy, uncluttered, fast on mobile,
  restaurant-wholesale appropriate) MUST be treated as part of acceptance, not optional
  polish. (Final visual design is produced in a later dedicated design phase.)

### Business Rules

- **BR-001**: The server is the sole authority for all prices, fees, discounts, and totals;
  client-supplied amounts are never trusted (constitution Principle II).
- **BR-002**: Minimum-order qualification uses the effective product subtotal (product line
  totals after applicable product pricing, quantity tiers, and offers), excluding delivery
  fee and excluding delivery discounts (see FR-030).
- **BR-003**: Delivery-discount qualification uses the effective product subtotal excluding
  delivery fee and delivery discounts.
- **BR-004**: At most one delivery discount benefit applies per order (no stacking); when
  several qualify, the one giving the largest monetary saving against the base delivery fee
  wins, with ties broken by the higher qualifying minimum subtotal. The final delivery
  charge never goes below zero.
- **BR-011**: When both an active offer and an eligible quantity tier apply to a selected
  product/unit, the effective unit price is the LOWER of the two (never combined); if only
  one applies it is used; if neither applies the normal active price is used.
- **BR-012**: A customer may self-cancel only while an order is **New**; authorized admins
  may cancel from **New**, **Confirmed**, **Preparing**, or **Out for Delivery**;
  **Delivered** and **Cancelled** orders cannot be cancelled. Cancellation preserves the
  immutable historical order record.
- **BR-005**: Offers apply only within their active date range and active state; expiry
  removes the offer at the next pricing evaluation, including checkout revalidation.
- **BR-006**: Orders are immutable commercial snapshots once placed; subsequent catalog,
  pricing, offer, or delivery-config changes never alter existing orders.
- **BR-007**: Order status may only move along allowed transitions; the initial status is
  **New**.
- **BR-008**: Payment method for MVP is Cash on Delivery only; the business is
  single-branch, delivery-only.
- **BR-009**: Only active delivery areas and enabled slots are selectable for new orders.
- **BR-010**: A customer may only view and act on their own orders; admins act on all orders
  subject to authorization.
- **BR-013**: Inventory is tracked per selling unit as a non-negative integer balance; a unit is
  orderable only when active and its balance is > 0 (stock is authoritative over a manual
  out-of-stock toggle) (prompt 32).
- **BR-014**: Stock is deducted atomically at order creation under row-level locking; insufficient
  stock on any line blocks the whole order with no partial deduction — no overselling.
- **BR-015**: Cancellation from an eligible non-delivered state restores stock exactly once
  (idempotent); delivered/already-cancelled orders never restore. Order snapshots stay immutable.
- **BR-016**: Every stock change is recorded in an immutable inventory adjustment history
  (unit, type, delta, before/after, reason/source, admin/order reference, timestamp).

### Key Entities *(conceptual — no implementation/schema implied)*

- **Customer**: a recurring B2B buyer identified by a Business/Restaurant Name plus a
  Contact Person Name and login mobile number, with a WhatsApp number and saved delivery
  information. The MVP UI exposes exactly one active/default delivery address; the structure
  MUST allow multiple saved addresses in future. No KYC/tax-registration data in MVP.
- **Delivery Address / Delivery Details**: area + address + building/floor/unit/landmark +
  notes associated with a customer.
- **Category**: admin-managed grouping of products, with active state and optional ordering.
- **Product**: sellable item with brand, description, image, category, availability state,
  and one or more selling units.
- **Selling Unit**: a way to buy a product (e.g., bag, carton, bottle) carrying its own
  pricing and tiers; part of the commercial offer shown everywhere.
- **Price Tier**: quantity range → unit price, defined per selling unit; optional.
- **Offer**: temporary normal/offer price with date range and active state, tied to a
  product/unit.
- **Cart / Cart Line**: customer's in-progress selection (product + unit + quantity) with
  estimated pricing.
- **Delivery Area**: named area with active state and base delivery fee.
- **Delivery Slot**: admin-managed date/time window with an active/inactive state,
  selectable at checkout while active; no numeric per-slot capacity in MVP (future scope).
- **Delivery Discount Rule**: subtotal-threshold rule granting a fixed/percentage/free
  delivery benefit.
- **Order**: a placed, immutable record with order number, status, customer + delivery
  snapshot, line-item snapshots, monetary breakdown, and final total.
- **Order Line (snapshot)**: captured product name, unit, unit price, quantity, discounts,
  line total at time of placement.
- **Admin User**: authorized staff member managing the business.
- **Settings**: business-wide configuration including minimum order amount and business/
  contact information.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A returning customer can go from opening the app to a placed order in under 3
  minutes for a typical repeat basket.
- **SC-002**: A new customer can complete phone sign-in and profile onboarding in under 2
  minutes (excluding time waiting for the code to arrive).
- **SC-003**: 100% of placed orders reflect server-computed totals; there is no case where a
  client-supplied total determines what is charged/recorded.
- **SC-004**: 100% of historical orders display their original commercial terms after
  subsequent product/price/unit/offer/delivery changes (no historical drift).
- **SC-005**: For every price-tier, offer, minimum-order, delivery-fee, and delivery-discount
  scenario in this spec, the customer-visible figure at checkout matches the business's
  configured rules (0 mismatches in acceptance testing).
- **SC-006**: In 100% of "condition changed since carting" cases, the customer is shown the
  change and no order is placed on stale terms.
- **SC-007**: 0 occurrences of a false "order submitted" message when the device is offline.
- **SC-008**: A newly placed order appears to an authorized admin within one normal page
  refresh/navigation (no real-time infrastructure required).
- **SC-009**: 100% of the critical screens enumerated in this spec present each of their
  declared states (loading/empty/error/disabled/validation/out-of-stock as applicable).
- **SC-010**: The customer app is installable as a PWA on at least one major mobile browser
  that supports installation, and renders correctly in RTL.
- **SC-011**: 95% of catalog browse/search interactions return results promptly enough to
  feel instant to the user on a typical mobile connection.
- **SC-012**: 0 disallowed order-status transitions succeed in acceptance testing.

## Assumptions

- Currency is EGP; a single currency is used throughout the MVP.
- The business operates one branch, delivery-only, Cash on Delivery only.
- OTP delivery channel (e.g., SMS/WhatsApp) and provider are NOT decided here; the flow is
  provider-agnostic (constitution/plan will decide the mechanism).
- Reasonable default security thresholds apply unless configured otherwise: OTP validity of
  a few minutes, a resend cooldown on the order of ~30–60 seconds, and rate limits on code
  requests and verification attempts; exact numbers are admin/config decisions, not product
  behavior that changes these requirements.
- Category and product content (names, images, descriptions, brands) are provided by the
  business via admin tools; the spec does not fix specific category names.
- Product images may be provided by the business; presentation must degrade gracefully when
  an image is missing.
- "Relevant order history" for a customer (admin view) means that customer's own placed
  orders.
- Search covers at least product name and brand; broader search scope is not required for
  MVP.
- Dashboard indicators are high-level counts/summaries; no advanced analytics or reporting.
- The data model must remain compatible with the documented future-growth items (see
  "Future Growth — Not MVP") without implementing them now (constitution Principle IV).

## Resolved Business Decisions

All previously open decisions (C1–C7) are now resolved and recorded in the
[Clarifications](#clarifications) session log above, and are embedded in the relevant
requirements, business rules, acceptance scenarios, and entities:

- **C1** — Offer vs. tier pricing: lower-of the two, never combined → FR-025, BR-011, US3 #5.
- **C2** — Best delivery discount: largest monetary saving, no stacking, tie-break by higher
  qualifying minimum subtotal, never below zero → FR-036, BR-004, US8 #4–#5.
- **C3** — Cancellation: customer self-cancel only in New; admin cancel from
  New/Confirmed/Preparing/Out for Delivery; not from Delivered/Cancelled; record retained →
  FR-050, BR-012, US6 #5, Edge Cases.
- **C4** — Delivery slots: no numeric per-slot capacity in MVP; active/inactive only;
  revalidation rejects a deactivated slot → FR-040, Delivery Slot entity.
- **C5** — Customer is primarily B2B: Business/Restaurant Name + Contact Person Name; no
  KYC/tax fields → FR-007, Actors, Customer entity.
- **C6** — Single active/default delivery address exposed in MVP UI; multiple addresses
  preserved structurally for the future → FR-008, Customer entity.
- **C7** — Minimum order uses the effective product subtotal (after pricing/tiers/offers),
  excluding delivery fee and delivery discounts → FR-030, BR-002.

## Out of Scope (Explicit MVP Exclusions)

> **Amendment (prompt 32):** **simple per-selling-unit inventory quantity is now CORE MVP**
> (FR-071–FR-078). Only the *advanced* inventory capabilities below remain out of scope.

The following are explicitly NOT part of the MVP and MUST NOT enter implementation without
an approved scope change (they are recorded as future opportunities, not requirements):
online/electronic payment; credit accounts; customer credit limits; supplier management;
purchasing; goods receiving; warehouses; multi-warehouse stock; stock reservations &
reservation-expiry timers; batch/lot tracking; expiry dates; FIFO/LIFO; stock valuation/cost
accounting; n-level / arbitrary unit-conversion trees (two-level primary→sub IS in MVP, prompt 37);
barcode scanning; stock transfer; automated procurement;
multiple branches; driver management; route optimization; live driver tracking; loyalty
points; wallet; advanced coupons; advanced promotion engine; sales representatives;
recurring orders; Buy Again / repeat order; accounting integration; ERP integration;
advanced reports/analytics; customer-specific pricing; native Android/iOS app; Flutter app;
microservices; real-time WebSocket order updates; offline ordering; fine-grained admin RBAC;
delivery-driver scheduling.

## Future Growth — Not MVP (Must Remain Possible)

The MVP MUST avoid requirements that would make these future capabilities impossible
(constitution Principle IV), without designing them now: customer-specific price lists;
additional price tiers; multiple saved customer addresses; multiple branches; warehouses;
inventory quantities; credit customers; additional payment methods; mobile API / Flutter
app consuming the same business logic; salesperson accounts; repeat orders; recurring
orders; advanced promotions.
