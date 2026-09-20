# UX Architecture — Restaurant Supplies Ordering MVP

**Feature**: `001-restaurant-supplies-mvp`
**Source of truth**: [`../spec.md`](../spec.md) (approved specification)
**Document type**: UX / product-design planning only
**Created**: 2026-09-17
**Last updated**: 2026-09-17 (PM decisions: Featured = active offers; Latin digits + `ج`
currency; two-step checkout; currency display `444 ج`)
**Status**: Approved for Technical Planning

> Scope guard: This document plans **experience structure** only. It contains no application
> code, no Blade/routes/controllers, no migrations, no technical-architecture or
> `/speckit-plan` decisions, and no final visual design or marketing copy. Every screen,
> state, and rule below traces to the approved `spec.md`. Where `spec.md` already decides
> something, this document follows it. No new business requirements are introduced.

Traceability tags like `[FR-025]`, `[US4]`, `[C1]` reference the approved specification.

## PM-Approved Design Decisions (2026-09-17)

- **D1 — Featured Products = active-offer products.** "Featured" means products that
  currently have an **active, valid offer** `[FR-024]`. No popularity scoring, best-seller,
  view-count, sales-ranking, or manual curation logic enters MVP (would be a scope change).
  The UI labels this area as **offers / promoted products**, never implying popularity.
- **D2 — Numerals & currency.** The Arabic/RTL customer UI uses **Latin digits**
  `0 1 2 3 4 5 6 7 8 9` for prices, quantities, order numbers, OTP, and dates/times. **Currency
  displays as `444 ج`** — the amount, a single space, then only the Arabic letter **`ج`** (e.g.
  `150 ج`, `1,250 ج`, `12,500 ج`), with comma thousands separators where appropriate.
  **Do NOT use** `ج.م`, `م.ج`, `EGP`, `LE`, or Arabic-Indic digits. Mixed Arabic/English content
  (brand names, sizes, units) must render cleanly (bidi-safe). This is the approved presentation
  direction, not yet a hard-coded implementation concern. Sample phrasing: `السعر: 444 ج` ·
  `الإجمالي: 1,250 ج` · `خصم التوصيل: -50 ج` · `متبقي 80 ج لإتمام الحد الأدنى للطلب`.
- **D3 — Two-step checkout.** Checkout is **two screens**: **Step 1 — Delivery** (CTA
  `مراجعة الطلب`) and **Step 2 — Review & Confirm** (CTA `تأكيد الطلب`). Step 2 is the final
  commercial confirmation surface; server revalidation there must surface a **changed-terms**
  state and require re-review before confirming `[FR-043, FR-044]`.
- **D4 — Localization readiness (project-wide policy).** Arabic is the **only** exposed/
  selectable UI language in the MVP (Arabic-first customer PWA, landing, and admin; RTL active;
  **no language switcher**). Every design and future technical decision MUST avoid Arabic-only
  lock-in so **English (LTR)** can be added later without redesign or business-logic rewrite.
  English UI, a language switcher, per-user locale preference, and translated managed content
  are **future scope**; localization *readiness* is required now. Full policy in **§21**;
  planning handoff in **§23**.

---

## 1. Product UX Principles

B2B-wholesale principles, not consumer-fashion e-commerce.

1. **Repeat-order speed first.** The core user is a restaurant owner/manager re-ordering known
   supplies under time pressure. Optimise the *returning* path: saved profile + saved default
   address + fast catalog. Target: opening → placed order under 3 minutes `[SC-001]`.
2. **Clarity over decoration.** Density serves comprehension. Every element helps the buyer
   decide *what unit, how many, what price, what total* `[FR-069]`. No hero imagery inside
   the ordering flow, no product-hiding carousels, no ambiguous icons.
3. **Price truth is sacred.** Unit, quantity, applied unit price, and line total are
   unambiguous everywhere `[FR-018..FR-025]`. The lower-of offer-vs-tier rule `[C1/BR-011]`
   is *shown*, never inferred. Cart figures are **estimates**; the business confirms at
   checkout `[FR-028, FR-043]`.
4. **Trustworthy and professional.** Calm, legible, consistent, high-contrast. Trust cues:
   clear pricing, delivery terms, order numbers, statuses, honest errors. No dark patterns.
5. **Mobile-first, thumb-reachable.** Primary actions in the lower half; bottom navigation +
   sticky action bars are the backbone `[FR-066]`. One-handed use.
6. **Arabic / RTL native.** Layout, navigation, forms, icons are RTL-first with correct mixed
   Arabic/Latin handling and **Latin numerals** `[FR-066, SC-010, D2]` (§17).
7. **Honest system states.** Loading, empty, validation, error, disabled, out-of-stock are
   deliberate on every data-driven screen `[FR-068, US13]`. Connectivity is never faked — no
   false "order submitted" `[FR-047, FR-067]`.
8. **Wholesale-appropriate merchandising.** Emphasise units/packaging (bag, carton, bottle),
   wholesale/tier pricing, and offers — what a supplies buyer compares.

---

## 2. Information Architecture

### 2.1 Customer-facing IA (hierarchy)

```
Public (unauthenticated)
├─ Landing Page  (root "/")  → CTA → Sign-In
└─ Sign-In
    ├─ Phone Number
    ├─ OTP Verification
    └─ Onboarding (first-time only)
        ├─ Business Profile
        └─ Delivery Address (create default)

Authenticated Customer (app shell with bottom navigation)
├─ Home            (tab): search · category shortcuts · active-offer ("Offers") strip
├─ Categories      (tab) → Product Listing → Product Details
├─ Cart            (tab) → Checkout Step 1 (Delivery) → Checkout Step 2 (Review) → Success → Order Details
├─ Orders          (tab) → Order Details
└─ Profile         (tab) → Business Profile (edit) · Delivery Address (edit default)

Cross-cutting (pushed over the shell, not tabs)
├─ Product Details, Product Listing, Search Results
├─ Checkout (Step 1 → Step 2 → Success)  — bottom nav suppressed during checkout
└─ Global overlays: offline banner, PWA install affordance, toasts, bottom sheets
```

Relationships: **Home** is the fast hub over Categories/Search plus the **Offers** strip
(active-offer products only `[D1, FR-024]`). The spine is **Categories → Listing → Details →
Cart → Checkout (Delivery → Review) → Success → Order Details** `[US2–US4]`. **Profile** owns
the Business Profile and the single default Delivery Address `[FR-007, FR-008, C5, C6]`; the
Address form is reused in onboarding, Profile, and (as an edit sheet) Checkout Step 1.

### 2.2 Admin IA (hierarchy)

```
Admin (authenticated staff)
├─ Dashboard  (new/today/recent orders + indicators)                     [FR-053]
├─ Orders → Orders List (search/filter) → Order Details (status actions) [FR-054, FR-050]
├─ Customers → Customers List (search) → Customer Details (+ history)     [FR-056]
├─ Catalog
│   ├─ Categories (CRUD + active + arrange)                              [FR-057]
│   └─ Products (CRUD) → tabs: General · Media · Selling Units · Pricing · Offers · Availability
│                                                                        [FR-058, FR-059, FR-060]
├─ Delivery → Areas (fee+active) · Slots (active) · Discount Rules       [FR-061, FR-036, FR-040]
└─ Settings (minimum order amount; business/contact info)                [FR-062]
```

No IA node exists without a backing requirement.

---

## 3. Global Customer Navigation

**5-item bottom navigation** on the authenticated shell:

| Slot | Tab | Rationale |
|------|-----|-----------|
| 1 | **Home** | Fast hub: search + categories + Offers; default post-sign-in `[SC-001, D1]` |
| 2 | **Categories** | Primary browse backbone `[FR-011]` |
| 3 | **Cart** | One tap from checkout; item-count badge `[US3/US4]` |
| 4 | **Orders** | Repeat buyers check status often `[US9]` |
| 5 | **Profile** | Business profile + default address + sign-out `[FR-007, FR-008]` |

**Search is not a tab** — it is a persistent field atop Home and Categories (keeps the bar at
5; Categories earns the tab as a *place*, Search is an *action*) `[FR-012]`.

**Out of bottom navigation** (pushed screens / overlays): Landing, Phone, OTP, Onboarding;
Product Details, Listing, Search Results; **Checkout Step 1, Step 2, Order Success** (bottom
nav is suppressed during checkout to reduce accidental abandonment; replaced by a sticky CTA
bar); Address/Profile edit forms; global overlays. Cart badge shows the live line count.

---

## 4. Complete Screen Inventory (Customer)

**17 customer screens** (C01–C17). The reusable **Delivery Address Form (C05)** is counted
once but appears in three contexts (onboarding, Profile edit, Checkout Step 1 edit). The
**changed-terms** case is a *state of C12*, not a separate screen.

> States legend: **L**=loading, **E**=empty, **V**=validation, **Er**=error, **D**=disabled,
> **OoS**=out-of-stock. "—" = not applicable.

### C01 — Landing Page (public)
- **Purpose**: Explain the business; convert visitor → sign-in `[US10, FR-065]`.
- **Entry**: Root `/`; marketing links. **Primary**: tap **Start Ordering / Sign In**.
- **Secondary**: scroll sections; tap category preview; view contact/footer.
- **Components**: header, hero, primary CTA, category preview, business advantages, **Offers**
  (active-offer products `[D1]`), how-ordering-works, delivery/coverage, secondary CTA,
  contact/footer (§14).
- **States**: L (section content) · E (graceful empty previews `[US10 #3]`) · Er (content
  fail → static shell + CTA) · V — · D — · OoS (offer item sold out shown, not orderable).
- **Exit**: → C02.

### C02 — Sign-In: Phone Number (public)
- **Purpose**: Capture mobile, request OTP `[FR-001, US1]`. **Entry**: Landing CTA / "sign in
  to continue". **Primary**: submit → request code.
- **Secondary**: edit format; back to Landing.
- **Components**: phone input (RTL field, Latin digits `[D2]`), format helper, submit, terms
  note.
- **States**: L (sending) · V (invalid number pre-send `[FR-002]`) · Er (send fail /
  rate-limited with wait `[FR-005]`) · D (submit until valid) · E/OoS —.
- **Exit**: → C03.

### C03 — Sign-In: OTP Verification (public)
- **Purpose**: Verify the code `[FR-001, FR-003, FR-004]`. **Entry**: from C02. **Primary**:
  enter code → verify.
- **Secondary**: **Resend** (cooldown); change number.
- **Components**: OTP input (Latin digits `[D2]`), expiry countdown, resend with cooldown,
  distinct "incorrect" vs "expired" messaging `[FR-003]`.
- **States**: L (verifying) · V (wrong length/empty) · Er (incorrect; expired+resend; lockout
  with wait `[FR-005]`) · D (resend during cooldown) · E/OoS —.
- **Exit**: new → C04; returning → C06 `[US1 #2]`.

### C04 — Onboarding: Business Profile (first-time)
- **Purpose**: Capture Business/Restaurant Name, Contact Person, WhatsApp `[FR-007, C5]`.
  **Entry**: first OTP for unregistered/incomplete customer `[FR-006, FR-009]`. **Primary**:
  Continue → Address.
- **Secondary**: login mobile shown read-only; exit resumes-incomplete.
- **Components**: Business/Restaurant Name, Contact Person Name, WhatsApp, (login mobile
  pre-filled). No KYC/tax fields `[C5]`.
- **States**: L (save) · V (required; WhatsApp format) · Er (save fail) · D (Continue until
  valid) · E/OoS —.
- **Exit**: → C05; interruption resumes here `[FR-009, US1 #5]`.

### C05 — Delivery Address Form (reusable)
- **Purpose**: Create/edit the single default delivery address `[FR-008, C6]`. **Entry**:
  onboarding (after C04); Profile → edit; Checkout Step 1 → edit. **Primary**: Save (+ finish
  → Home in onboarding).
- **Secondary**: cancel/back (contextual).
- **Components**: **Area** selector (active areas only `[FR-033, BR-009]`), address line,
  building/location, floor, apartment/shop/unit, landmark, delivery notes.
- **States**: L (areas; save) · E (no active areas → contact-business guidance) · V (required;
  area) · Er (save fail) · D (Save until valid) · OoS —.
- **Exit**: onboarding → C06; Profile → C16/C17; Checkout → C11.

### C06 — Home (tab)
- **Purpose**: Fast hub: search, categories, active offers `[FR-011, FR-024, D1]`. **Entry**:
  post-auth default; Home tab. **Primary**: search or tap a category shortcut.
- **Secondary**: tap an offer; open a category; open Cart.
- **Components**: persistent search field, category shortcuts, **Offers strip** (active-offer
  products, labelled offers/promoted `[D1]`), link to full Categories.
- **States**: L (skeletons) · E (no offers → hide strip; no categories → browse-all guidance)
  · Er (retry) · OoS (offer item sold out, not orderable from strip) · V/D —.
- **Exit**: → C08, C07, C09, C10.

### C07 — Search
- **Purpose**: Find products by name/brand `[FR-012]`. **Entry**: search field on Home/
  Categories. **Primary**: enter query → results.
- **Secondary**: clear query; open a result.
- **Components**: search input, typing affordance, results (product cards).
- **States**: L (searching) · E (no results → tips `[US2 #2]`) · Er (retry) · V (min length
  if any) · OoS (results may include labelled, unorderable OoS) · D —.
- **Exit**: → C09. No advanced filters (§19).

### C08 — Product Listing (category / search results)
- **Purpose**: Products within a category or a result set `[FR-011, US2]`. **Entry**: category
  shortcut/Categories tab; Search. **Primary**: open a product (`عرض المنتج` / tap card) → C09
  `[FR-013]`.
- **Secondary**: scroll/paginate. (No quick-add / no add-to-cart on the card — §8.)
- **Components**: category context header, product cards (§8), result count.
- **States**: L (skeleton cards) · E (empty/inactive category → distinct empty, not error
  `[US2 #4, FR-017]`) · Er (retry) · OoS (labelled; card still opens Details) · V/D —.
- **Exit**: → C09.

### C09 — Product Details
- **Purpose**: Unit + quantity + price decision; add to cart `[US3, FR-013, FR-018..FR-025]`
  (§9). **Entry**: Listing, Search, Home offers. **Primary**: **Add to Cart** `[FR-026]`.
- **Secondary**: change unit; change quantity; view tier table; go to Cart.
- **Components**: image, brand, name, description, availability, **unit selector**, **quantity
  stepper**, price block (normal / tier / offer / **applied best price** + savings), line
  total (§9).
- **States**: L · E (no description → graceful) · V (quantity bounds) · Er (load/add) · D (Add
  when OoS/inactive `[FR-015, FR-016]`) · OoS (label + disabled add).
- **Exit**: → C10 on add; back to Listing/Search.

### C10 — Cart (tab)
- **Purpose**: Review/adjust; estimated subtotal + minimum-order progress `[US3, FR-026..
  FR-031]` (§10). **Entry**: Cart tab; after add; badge. **Primary**: **Continue to delivery**
  → C11.
- **Secondary**: change qty/unit; remove; continue shopping.
- **Components**: cart lines (unit, unit price, qty control, line total), availability/price-
  change flags `[FR-029]`, **estimate** label `[FR-028]`, product subtotal, minimum-order
  progress `[FR-031]`. **No delivery amounts in the cart** — delivery fee/discount/final total
  appear only at Checkout `[FR-034, §10]`.
- **States**: L (re-price) · E (empty cart → friendly + browse) · V (qty limits) · Er (retry)
  · D (Checkout disabled below minimum / with unresolved unavailable items) · OoS (item
  flagged, excluded from valid checkout `[US3 #7]`).
- **Exit**: → C11.

### C11 — Checkout Step 1: Delivery
- **Purpose**: Confirm identity, address, area, date, slot, and payment method before review
  `[US4, FR-038..FR-041, D3]`. **Entry**: Cart → Proceed. **Primary CTA**: **`مراجعة الطلب`**
  (Review the order) → C12.
- **Secondary**: edit address (C05 sheet); change date; change slot; back to Cart.
- **Components**: business/customer identity summary (name, contact person, mobile, WhatsApp);
  saved default **address** + **Edit**; **delivery area** (from address); **delivery date**
  picker; **available slot** picker (active slots only `[FR-040]`); **payment method: Cash on
  Delivery** `[BR-008]`.
- **States**: L (areas/slots) · E (no active slots for date → prompt another date `[FR-039]`)
  · V (missing date/slot) · Er (area inactive `[FR-033]`; slot gone `[FR-039]`) · D (Review
  CTA disabled until date+slot valid) · OoS (unavailable cart item → return to Cart).
- **Exit**: → C12; edit → C05; back → C10.

### C12 — Checkout Step 2: Review & Confirm
- **Purpose**: Final commercial confirmation surface `[US4, FR-041..FR-047, D3]`. **Entry**:
  from C11. **Primary CTA**: **`تأكيد الطلب`** (Confirm the order) `[FR-042]`.
- **Secondary**: back to Step 1 to change delivery; review changed-terms diff; edit quantities
  (back to Cart).
- **Components** (read-mostly summary): compact customer info; delivery address; delivery
  date; delivery slot; **all products** with **selling unit**, **quantity**, **final unit
  price**, **line total**; **product subtotal**; **base delivery fee**; **delivery discount**;
  **final delivery fee**; **final order total**; **payment method (COD)** `[FR-041]`.
- **Behavior**: on entry and on Confirm, server **revalidates** everything `[FR-043]`. If any
  of price / stock-availability / selling-unit / expired-offer / changed delivery-discount /
  unavailable-slot changed, the customer **must not be silently confirmed** — show a **clear
  changed-terms state** (itemised diff) and require re-review before confirming `[FR-044, US4
  #3]`.
- **States**: L (revalidating) · **Changed-terms** (diff + re-confirm required — a first-class
  state) · V (below minimum `[FR-031]`) · Er (network — **no false success** `[FR-047, US4
  #6]`; area inactive; slot gone) · D (Confirm gated) · OoS (return to Cart) · E —.
- **Exit**: → C13 on success; back → C11.

### C13 — Order Success / Confirmation
- **Purpose**: Confirm placement + essentials `[FR-048, US4]`. **Entry**: successful confirm
  at C12. **Primary**: **View Order Details**.
- **Secondary**: continue shopping (Home).
- **Components**: success state, **order number**, final total, delivery date, slot, delivery
  address, current status (**New**).
- **States**: reached only on confirmed success (failures handled at C12; never shown as
  success). L/E/V/Er/D/OoS —.
- **Exit**: → C15; → C06.

### C14 — Orders List (tab)
- **Purpose**: The customer's own orders `[US9, FR-052]`. **Entry**: Orders tab; Success.
  **Primary**: open an order.
- **Secondary**: scroll history.
- **Components**: per order — number, date, total, status badge.
- **States**: L (skeleton rows) · E (no orders → empty + start-ordering) · Er (retry) ·
  V/D/OoS —.
- **Exit**: → C15.

### C15 — Order Details
- **Purpose**: Immutable historical order `[US9, FR-051, FR-052]`. **Entry**: Orders List;
  Success. **Primary**: read; **Cancel order** *only when allowed* (self-cancel while status =
  New) `[FR-050, BR-012]`.
- **Secondary**: contact business (post-Confirmed).
- **Components**: status + read-only timeline, line-item **snapshots** (name, unit, unit
  price, qty, discounts, line total), delivery data (area, address, date, slot), monetary
  breakdown, order number/date.
- **States**: L · V (cancel confirm dialog) · Er (load/cancel) · D (Cancel hidden/disabled
  when not allowed, with reason) · E/OoS —.
- **Exit**: back to C14.

### C16 — Profile (tab)
- **Purpose**: View/edit business profile + default address; sign out `[FR-007, FR-008]`.
  **Entry**: Profile tab. **Primary**: edit Business Profile or Delivery Address.
- **Secondary**: sign out; business/contact info; WhatsApp the business.
- **Components**: Business/Restaurant Name, Contact Person, login mobile, WhatsApp, default
  area + address summary, edit entry points, sign-out.
- **States**: L · V (edit) · Er (load/save) · D (Save until valid) · E/OoS —.
- **Exit**: → profile-field edit or → C17. No multi-address UI `[C6, §19]`.

### C17 — Delivery Address (view/edit)
- **Purpose**: Profile-context view of the single default address; edit reuses C05 `[FR-008,
  C6]`. **Entry**: Profile. **Primary**: edit (→ C05).
- **Secondary**: back.
- **Components**: current default address summary, edit button.
- **States**: L · E (address missing → prompt add) · V/Er (in edit) · D · OoS —.
- **Exit**: → C05 → back to Profile.

**Global overlays (not screens):** offline banner `[FR-067]`, PWA install affordance
`[FR-066]`, toasts, bottom sheets (date/slot/address edit).

---

## 5. Authentication Flow

**First-time**

```
C01 Landing ─[Start]─▶ C02 Phone ─[request]─▶ C03 OTP
  ─[verified, new]─▶ C04 Business Profile ─▶ C05 Delivery Address ─▶ C06 Home
```

**Returning**

```
C02 Phone ─[request]─▶ C03 OTP ─[verified, complete]─▶ C06 Home
```

**Branches** `[FR-002..FR-005, FR-009, US1, Edge]`: invalid number → inline validation, no
send `[FR-002]`; incorrect OTP → distinct error, retry within limits `[FR-003]`; expired OTP →
"expired" + resend `[FR-004]`; resend → after cooldown only, countdown shown `[FR-005]`;
rate-limit/lockout → clear wait `[FR-005]`; interrupted onboarding → resumes *incomplete*,
cannot order until complete `[FR-006, FR-009, US1 #5]`. Returning path is shortest possible
`[SC-002]`.

---

## 6. Shopping Flow

**Happy path (two-step checkout `[D3]`)**

```
C06 Home
 └▶ C08 Listing (or C07 Search)
      └▶ C09 Product Details
            ├─ select Selling Unit         [FR-018, FR-022]
            ├─ select Quantity             [FR-021]
            ├─ see applied best price       [C1/BR-011, FR-025]
            └▶ Add to Cart                  [FR-026]
                 └▶ C10 Cart  (estimate; minimum-order progress) [FR-028, FR-031]
                      └▶ C11 Checkout Step 1 — Delivery
                           ├─ address (saved default, editable)  [FR-008]
                           ├─ date + slot                        [FR-038, FR-040]
                           ├─ COD                                [BR-008]
                           └▶ [مراجعة الطلب] C12 Checkout Step 2 — Review & Confirm
                                 ├─ full commercial summary       [FR-041]
                                 ├─ server revalidation           [FR-043]
                                 ├─ changed-terms? → re-review     [FR-044]
                                 └▶ [تأكيد الطلب] C13 Success       [FR-048]
                                       └▶ C15 Order Details        [FR-051]
```

**Alternate / recovery**: continue shopping from C10/C11/C12 without losing cart; modify cart
at C10 (estimate until checkout `[FR-028]`); unit change re-adds with new unit pricing
`[FR-022]`; below minimum blocks at C10/C12 with shortfall `[FR-031]`; unavailable item
flagged at C10, excluded `[US3 #7]`; **changed terms at C12 → diff + re-confirm** `[FR-044]`;
area inactive / slot unavailable at C11/C12 → targeted fix `[FR-033, FR-039]`; offline at
Confirm → failure/retry, never success `[FR-047]`.

---

## 7. Product Discovery UX

Grounded in existing MVP data only (categories, products, offers, name/brand search).

**Home** `[C06]`: category shortcuts `[FR-011]`; **Offers strip** = active-offer products,
labelled *offers/promoted* — **this is the entire "Featured" concept for MVP** `[D1, FR-024]`;
no popularity/best-seller/ranking data is introduced. Sold-out offer items are labelled and
unorderable `[FR-015]`.

**Category listing** `[C08]`: active-only category header `[FR-017]`; product cards (§8);
distinct empty state for empty/inactive categories `[US2 #4]`.

**Search** `[C07]`: single query over **name and brand** `[FR-012]`; instant-ish `[SC-011]`;
clear **no-results** empty state `[US2 #2]`; **no advanced filters/sorting** `[§19]` (search
may be scoped to a category as navigation context, not a filter panel).

---

## 8. Product Card Content Hierarchy

Scan-and-decide unit, not a full pricing surface. Priority (leading → trailing, RTL-aware):

1. **Image** (graceful fallback if missing).
2. **Availability / OoS badge** — if OoS, it dominates; the card still opens Details to view but
   the product is unorderable `[FR-015]` (inactive never appears `[FR-016]`).
3. **Offer badge** — when an active offer exists `[D1, FR-024]`.
4. **Brand** (small, secondary).
5. **Product name** (primary label).
6. **Default selling-unit / package info** — e.g. "Bag 2.5 KG" `[FR-018]`.
7. **Starting/default price** — default unit at qty 1 (applied best price for the baseline
   `[C1/BR-011]`), prefixed "from …" (e.g. `from 180 ج`) when units/tiers vary. Latin digits
   `[D2]`.
8. **Previous price** — struck-through **only** when an active offer makes the price lower
   `[FR-024]`.
9. **View action** — the card (and an explicit `عرض المنتج ›` affordance) opens **Product
   Details (C09)**. The card MUST NOT add to cart, quick-add, auto-add a default unit, or open a
   unit-picker sheet — unit + quantity + applied price are chosen on Product Details
   `[FR-018..FR-025]`.

**Not on the card** (moved to Details to avoid clutter): full tier tables `[FR-019]`, the
multi-unit selector `[FR-018]`, quantity stepper + live line total `[FR-020]`, savings math /
applied-best-price explanation `[FR-025]`.

---

## 9. Product Details UX

The key comprehension screen `[FR-069]`. Hierarchy (RTL-aware):

1. **Image** (fallback). 2. **Brand** then **Product name**. 3. **Availability** (OoS disables
ordering `[FR-014, FR-015]`). 4. **Description** (collapsible). 5. **Selling-unit selector** —
segmented/list, one clearly selected `[FR-018, FR-022]`; selecting re-prices below.
6. **Quantity selector** — large +/− with direct entry, bounds honoured `[FR-021, §18]`.
7. **Price block** (anti-confusion core):
   - **Normal price** (per selected unit).
   - **Tier/wholesale price** — if tiers exist, a small **tier table** (qty range → unit
     price) with the **active tier highlighted** for the chosen quantity `[FR-019, FR-021]`.
   - **Active offer price** — when applicable `[FR-024]`.
   - **Applied (best) unit price** — the single used price = **lower of** eligible offer vs
     eligible tier, else the one that applies, else normal `[C1/BR-011, FR-025]`. Visually
     dominant, labelled ("Your price").
   - **Savings** vs normal, when lower.
8. **Line total** = applied unit price × quantity, live `[FR-020]`.
9. **Add to Cart** — sticky; disabled with reason on OoS/inactive `[FR-015, FR-016]`.

**Anti-ambiguity** `[FR-069]`: selected unit always visible near price + Add; quantity echoed
in the line-total expression (`3 × 170 = 510 ج`); applied price tagged by source ("tier
price"/"offer price"/"best price"); when both offer and tier exist, show both candidates, mark
the winner, and state they do **not** stack `[C1, US3 #5]`; flat-priced unit shows one price,
no tier UI `[FR-019, US3 #6]`.

---

## 10. Cart UX

Honest estimates; unmistakable unit/quantity/price. The cart is a **products-only** surface:
it shows product economics and minimum-order progress, and **deliberately shows no delivery
amounts** — delivery fee, delivery discount, final delivery fee, and final order total belong to
Checkout, after delivery details are validated `[FR-027, FR-028, FR-034, §1.3]`.

**Cart line**: thumbnail + name + **selected unit** (prominent) `[FR-018]`; **quantity
control** (+/−, direct entry) with live re-price `[FR-021]`; **current unit price** with a tag
when tier/offer/best `[FR-020, C1]`; **line total** `[FR-020]`; **Remove** (with undo if
feasible) `[FR-026]`; **availability/price-change flags** — badged if OoS/inactive/unit-
unavailable or price/tier/offer changed `[FR-029]`; unavailable items separated and **excluded
from checkout** `[US3 #7]`.

**Totals / progress**: **estimate label** `[FR-028]`; **product subtotal** (effective, after
pricing/tiers/offers) `[FR-027, C7]`; **minimum-order progress** (required minimum + remaining;
Checkout disabled below it `[FR-030, FR-031, US4 #2]`) — measured on **effective product
subtotal excluding delivery** `[C7/BR-002]`. **No delivery fee, delivery discount, final
delivery fee, or delivery-inclusive total is shown in the cart** — these first appear on Checkout
Step 2 `[FR-034, FR-041]`. Primary action: **Continue to delivery** → C11 (disabled below
minimum / with unresolved unavailable items).

---

## 11. Checkout UX — Two-Step `[D3]`

### 11.1 Pattern decision — **two-step checkout** (supersedes the earlier single-scroll
recommendation)

Per PM decision D3, checkout is **two screens**: Step 1 collects/verifies delivery inputs;
Step 2 is the final commercial confirmation. Rationale for the split (over one long scroll):

- Separates **inputs** (address/date/slot) from the **binding commercial review**, so the
  final total and confirm live on a focused, uncluttered surface `[FR-041, FR-042]`.
- The **changed-terms** revalidation `[FR-044]` is clearest as a state on the dedicated review
  screen, where the customer's sole job is to accept the final terms.
- Reduces mis-taps on Confirm by ensuring delivery choices are settled before the money screen.

Guardrails to keep it fast `[SC-001]`: Step 1 pre-fills everything from saved data (one
default address, known contact); the only required choices are **date** and **slot**; a sticky
CTA advances each step; back-navigation preserves state.

### 11.2 Step 1 — Delivery (`C11`)  — CTA `مراجعة الطلب`
Contains: business/customer identity summary; saved delivery address (+ **edit** → C05);
delivery area; delivery date; available delivery slot; **payment method: Cash on Delivery**
`[FR-041, FR-038, FR-040, BR-008]`. Validation: date + slot required and valid; area active
`[FR-033, FR-039]`. Advancing runs a first revalidation pass so obvious problems surface before
the review screen.

### 11.3 Step 2 — Review & Confirm (`C12`)  — CTA `تأكيد الطلب`
Contains (read-mostly): compact customer info; delivery address; delivery date; delivery slot;
**all products** with selling unit, quantity, final unit price, line total; **product
subtotal**; **base delivery fee**; **delivery discount**; **final delivery fee**; **final order
total**; **payment method (COD)** `[FR-041]`. This is the final commercial surface.

**Revalidation & changed-terms** `[FR-043, FR-044]`: on entering Step 2 and on Confirm, the
server recomputes everything (prices, tiers, offers, availability, selling unit, area, fee/
discount, slot, minimum). If **price / stock-availability / selling-unit / expired-offer /
changed delivery-discount / unavailable-slot** changed, the customer is **not** silently
confirmed: show a **changed-terms** state with an itemised diff and require explicit re-review
before `تأكيد الطلب` `[US4 #3]`. Minimum not met → Confirm disabled with shortfall `[FR-031]`.
Offline/server failure at Confirm → explicit failure + retry, **no success shown** `[FR-047,
US4 #6]`.

States (both steps): L · E (no slots for date, Step 1) · V (date/slot/min) · Er (area inactive,
slot gone, network) · D (CTA gated) · OoS (return to Cart) · plus **changed-terms** on Step 2.

---

## 12. Orders UX

**Orders List** `[C14, FR-052]`: rows show **order number, date, total, status** (colour-coded
badge, Latin digits `[D2]`); newest-first; empty state when none; no filters in MVP.

**Order Details** `[C15, FR-051, FR-052]`: current **status** + read-only progression (New →
… → Delivered, or Cancelled; no live tracking `[§19]`); line items as **historical snapshots**
(name, unit, unit price, qty, discounts, line total) unaffected by later catalog changes
`[FR-051, US9 #2]`; delivery data (area, address, date, slot); totals (subtotal, delivery fee,
delivery discount, final total); **cancellation action shown only when allowed** — self-cancel
appears only while status = **New**, else hidden/disabled with a short reason `[FR-050, BR-012,
US6 #5]`; cancelling keeps the historical record. No Buy Again `[§19]`.

---

## 13. Profile UX

Minimal `[C5, C6]`. Profile (`C16`) shows Business/Restaurant Name, Contact Person, login
mobile (read-only), WhatsApp, default address summary + area with Edit (→ C17/C05), sign-out.
No multi-address management, no address book `[C6, §19]`, no KYC/tax fields `[C5]`.

---

## 14. Landing Page UX

Public, mobile-first, client-presentable `[US10, FR-065]`. Section order (content/behavior,
**no final copy**): 1) Header (logo + compact Sign-In). 2) Hero (value proposition + primary
CTA; no carousel). 3) Primary CTA → C02. 4) Category preview (graceful empty `[US10 #3]`).
5) Business advantages (trust bullets). 6) **Featured offers** = active-offer products, labelled
offers/promoted `[D1]`; hidden gracefully if none. 7) How ordering works (3–4 steps).
8) Delivery/coverage (high-level; areas are admin-managed). 9) Secondary CTA. 10) Contact/
footer (from Settings `[FR-062]`). Mobile: single-column, early + repeated CTA, lazy images,
renders with empty previews `[US10 #3]`, RTL-first (§17).

---

## 15. Admin UX

Operational efficiency, not decoration `[FR-053..FR-064]`; list/detail + forms. Full inventory
and behaviors are in [`admin-design.md`](./admin-design.md). Summary of top-level destinations
(14): Login, Dashboard, Orders List, Order Details, Customers List, Customer Details,
Categories, Products List, Product Edit (tabbed), Offers, Delivery Areas, Delivery Slots,
Delivery Discount Rules, Settings. **Product administration is tabbed** — General · Media ·
Selling Units · Pricing · Offers · Availability — never one giant form `[FR-058, FR-059,
FR-060]`. High-frequency actions optimised for fewest clicks: advance order status `[FR-050,
FR-054]`; see new orders `[FR-053, FR-055]`; toggle availability `[FR-058]`; activate/
deactivate categories/areas/slots/offers/rules `[FR-057, FR-061]`.

---

## 16. Responsive UX (conceptual)

**Customer (mobile-first)**: mobile = single-column, bottom nav, sticky action bars, bottom
sheets; tablet = wider column, optional 2-up grids, content parity; desktop = centered
constrained width, wider grid, same IA scaled up `[FR-066]`. **Admin (desktop-first, tablet-
usable)**: desktop = multi-column list/detail + side nav; tablet = condensed/scrollable tables,
single-column forms, primary actions reachable.

---

## 17. Arabic / RTL Requirements

RTL designed-in `[FR-066, SC-010, D2]`:

- **Direction**: document RTL; nav, lists, cards, forms, bottom-nav mirror horizontally.
- **Icons/arrows**: directional icons mirror; non-directional (search, cart, user) do not.
- **Numerals**: **Latin digits** `0-9` for prices, quantities, order numbers, OTP, dates/times
  `[D2]` — consistent across the app.
- **Currency**: shown as **`1,250 ج`** (Latin grouped number + single space + `ج`; never
  `ج.م`/`م.ج`/`EGP`/`LE`) `[D2]`;
  amounts stay grouped with labels; applied-price emphasis (§9) preserved in RTL.
- **Mixed Arabic/Latin**: bidi isolation around embedded Latin tokens (brands, "KG", sizes) so
  numbers/punctuation don't reorder; product text aligned to the reading edge; brand legible.
- **Forms**: RTL-aligned labels/inputs/validation; numeric inputs accept LTR digit entry within
  an RTL field without cursor confusion; error text with its field.
- **Bottom navigation**: mirrored order; active-state + cart badge positioned for RTL; touch
  targets unchanged.

---

## 18. Accessibility (baseline)

`[FR-068, US13]`: comfortable **touch targets** with spacing; **labels** on every control,
including **icon-only** (search, remove, +/−); visible **focus states** (incl. custom steppers/
selectors); sufficient **color contrast** with never-color-alone status/offer/OoS cues;
inline, field-associated, announced **validation**; **quantity controls** operable by tap +
direct entry with announced bounds; **disabled controls** visually + programmatically disabled
with an available reason; perceivable non-color/non-motion-only feedback for state changes.

---

## 19. MVP vs Future UX

Out of MVP (future reference only, never surfaced as available): multiple saved addresses /
address book `[C6]`; online payment `[BR-008]`; credit/wallet/loyalty; Buy Again / repeat /
recurring orders; live tracking / driver scheduling; advanced filters/sorting; customer-
specific pricing UI; slot capacity/quota UI `[C4]`; fine-grained admin roles UI `[FR-064]`;
**popularity/best-seller/ranking-based featured** (Featured = active offers only `[D1]`);
**English UI, a language switcher, per-user locale preference, translated product/category
content, multilingual notifications, and multilingual SEO** (localization = future scope `[D4,
§21–§23]`; readiness is required now). MVP layouts must not preclude these but must not surface
them.

---

## 20. Output Summary

- **Total customer screens: 17** (C01–C17), plus non-screen global overlays. Checkout is two
  screens (C11 Delivery, C12 Review & Confirm) per D3; the changed-terms case is a state of
  C12. The Delivery Address form (C05) is one screen reused in three contexts.
- **Admin top-level destinations: 14** (Product Edit is tabbed; see admin-design.md).
- **Primary customer navigation:** 5-item bottom bar — Home · Categories · Cart · Orders ·
  Profile; Search persistent (not a tab); Cart badge; nav suppressed during checkout.
- **Checkout model:** **two-step** — Step 1 Delivery (`مراجعة الطلب`) → Step 2 Review & Confirm
  (`تأكيد الطلب`), with a first-class changed-terms revalidation state `[D3, FR-044]`.
- **Featured:** active-offer products only, labelled offers/promoted `[D1]`.
- **Numerals/currency:** Latin digits; `1,250 ج` `[D2]`.
- **Language:** Arabic-only exposed, Arabic-first, RTL active, **no switcher** in MVP;
  **localization-ready** for future English (LTR) `[D4, §21]`.

**Top 5 UX risks** (unchanged in substance): (1) pricing comprehension (unit × qty × tier ×
offer, lower-of) `[FR-025, C1]`; (2) checkout changed-terms moment `[FR-044]` — now isolated on
Step 2, which mitigates it; (3) minimum-order-basis confusion (products only, excl. delivery)
`[C7]`; (4) RTL + mixed script (Latin digits now fixed by D2) `[§17]`; (5) offline false-success
`[FR-047]`.

**Genuine blocking design questions:** none remain. D1 (Featured), D2 (numerals/currency `444 ج`),
and D3 (two-step checkout) resolve all prior blockers. Two earlier non-blocking defaults were
**decided against** in this audit and removed from every document: product cards do **not**
quick-add/auto-add (card → Product Details only), and the cart shows **no** delivery amounts
(delivery fee/discount/final total appear only at Checkout).

**Consistency with spec & constitution:** no contradictions found. This document adds no
business requirements; `spec.md` remains authoritative. Design choices not fixed by the spec
are called out with rationale.

---

## 21. Localization Readiness (Project-Wide Policy) `[D4]`

This is a **design + architecture readiness policy**, not an MVP feature. It adds **no** exposed
English UI and **no** switcher to the MVP, and changes **no** business rule. It requires that
every screen, component, and future technical decision remain valid when the UI language changes.

### 21.1 Language strategy
- **MVP**: Arabic is the primary and **only** exposed/selectable language. Arabic-first across
  the customer PWA, landing page, and admin dashboard, including validation, notifications, error
  states, empty states, order statuses, and all buttons/navigation labels. **RTL** is the active
  layout direction. **No language switcher.**
- **Future**: the system must support adding **English (LTR)** later without major redesign or
  business-logic rewrite. Future locale set = `{ ar, en }`. A switcher may be added later
  (future scope, §32/§22-below and §19).

### 21.2 Localization influences every design decision
Localization is not a later cosmetic concern. Every screen/component MUST avoid: fixed-width text
assumptions; Arabic-specific spacing hacks; **manual character reversal**; text embedded inside
images as the only meaning; hard-coded directional margins/paddings (use **logical**
start/end spacing, not left/right); icons that assume RTL forever; labels sized only for Arabic
text length; and any layout rule tied to a single writing direction. All component sizing MUST
tolerate **text expansion/contraction** (English labels are often longer or shorter than Arabic).

### 21.3 RTL / LTR structural readiness
MVP = **RTL**; future English = **LTR**. Direction MUST come from document/layout direction only
(logical properties), never from manually reversed strings. Mirroring expectations:

| Element | RTL (MVP) → LTR (future) |
|---|---|
| Page alignment | reading edge right → left |
| Headers / app bars | title+actions mirror |
| Back buttons / chevrons | point to trailing edge; flip on direction change |
| Breadcrumbs / pagination | order + arrows mirror |
| Tabs / step indicators | order mirrors; "1 of 2" progression flips |
| Bottom navigation | item order mirrors; badges reposition |
| Cards | media/text/badge alignment mirrors |
| Forms | label/field/help/validation alignment mirrors |
| Validation messages | align to field's reading edge |
| Tables | column order + numeric alignment mirror (§24) |
| Dropdowns / selects | open alignment mirrors |
| Modals / bottom sheets | content alignment mirrors; sheet handle unchanged |
| Toasts | text alignment mirrors; position policy consistent |
| Quantity controls | `( − ) [ n ] ( + )` remains numeric-LTR internally, block mirrors |
| Order timeline/status | progression direction mirrors |
| Product-details layout | image/details columns mirror (§9) |

Directional icons (back, forward, chevrons, nav arrows) mirror via layout; non-directional icons
(search, cart, user) never change (§22-icons).

### 21.4 UI copy separation
All user-facing copy is **translatable presentation content**, not business logic. This covers
navigation labels, buttons, validation/error messages, empty-state text, notifications, order
status labels, availability labels, checkout labels, admin labels, dashboard widget labels, auth/
OTP messages, success/warning messages, minimum-order/delivery messages, and app-controlled PWA
prompts. During design, treat every visible string as a presentation string keyed for future
translation — never a value that business logic branches on.

### 21.5 Business logic is language-neutral (canonical identifiers)
Domain/business identifiers MUST be language-neutral; Arabic (and future English) are display
mappings only. No business rule branches on translated text. Canonical mappings (identifiers are
illustrative of the required neutrality, not a schema decision):

| Domain | Neutral identifier | Arabic (MVP display) | Future English display |
|---|---|---|---|
| Order status | `new` | جديد | New |
| Order status | `confirmed` | تم التأكيد | Confirmed |
| Order status | `preparing` | قيد التحضير | Preparing |
| Order status | `out_for_delivery` | خرج للتوصيل | Out for Delivery |
| Order status | `delivered` | تم التوصيل | Delivered |
| Order status | `cancelled` | ملغي | Cancelled |
| Availability | `available` | متاح | Available |
| Availability | `out_of_stock` | غير متوفر | Out of Stock |
| Availability | `inactive` | غير نشط | Inactive |
| Discount type | `fixed` / `percentage` / `free_delivery` | (localized labels) | (localized labels) |
| Payment type | `cod` | الدفع عند الاستلام | Cash on Delivery |

The same rule applies to customer/account states, delivery states, promotion types, and
validation-rule identifiers: identifiers stay neutral; labels are localized presentation.

### 21.6 Managed content localization readiness
Separate **application UI translation** from **business-managed content** (category name/
description, product name/description, offer title/description, marketing banners, and any future
dynamic landing content). MVP: Arabic-only managed content is acceptable. Do **not** force
bilingual fields now. But the future data model MUST avoid an irreversible Arabic-only structure;
`/speckit-plan` evaluates approaches (translation tables / JSON locale fields / separate localized
content entities). No implementation decision here.

### 21.7 Brand names & bidi
Official brand names stay in their natural commercial language (e.g. `Heinz`, `Farm Frites`,
`Hellmann's`) — never artificially translated. The UI MUST handle mixed-direction strings
(Arabic sentence + Latin brand + Latin numbers + units) with proper **bidi isolation** so Latin
tokens and numbers do not break the RTL layout (and won't break LTR later).

### 21.8 Units & packaging
Selling-unit **identifiers** are language-neutral (`bag`, `carton`, `pack`); Arabic display
(`كيس`, `كرتونة`, `عبوة`) and future English (`Bag`, `Carton`, `Pack`) are localized labels.
Never use an Arabic display value as a domain/DB identifier. Packaging sizes (`2.5 KG`,
`6 × 1 KG`) are numeric/technical and must render correctly in both RTL and LTR (bidi-safe).

### 21.9 Numbers
Latin digits `0-9` for prices, quantities, OTP, order numbers, product/package sizes, and dates/
times (D2). Future English MUST NOT require changing **stored** numeric values — only presentation
formatting if ever needed.

### 21.10 Currency
Arabic MVP display = `444 ج` (Latin digits, comma thousands separators, single space before `ج`;
never `EGP`/`LE`/`ج.م`/`م.ج`) `[D2]`. Future English presentation may use a different localized
format; **currency values themselves are language-independent**. No future formatting implemented
now.

### 21.11 Dates & times
Date/time presentation is locale-aware; do **not** hard-code formats into screen structure. MVP
Arabic may use readable Arabic labels with Latin digits. Future English may need different month
names, ordering, or time labels — components must accept a locale-aware formatter later.

### 21.12 Validation & error messages
Distinguish the **validation rule** (neutral) from the **localized message**. All customer/admin
validation and errors are translatable: required fields, invalid phone, incorrect/expired OTP,
out-of-stock, unavailable slot, minimum-order not reached, pricing changed, delivery area
unavailable, server errors, offline/no-connection. Logic never depends on message text.

### 21.13 Notifications
Notification content is localized **presentation templates**; the event/business logic that
triggers them is neutral. Future English templates (order status changes, OTP, admin new-order,
etc.) must be addable without changing events/logic. MVP customer-facing notification content is
Arabic.

### 21.14 OTP
OTP flow is Arabic-first in MVP. OTP **logic** — generation, expiry, rate limiting, verification,
provider integration — is language-neutral and never tied to Arabic text. Future English requires
only translated UI/message templates.

### 21.15 Landing page readiness
Landing is Arabic-only in MVP, but section layouts must tolerate English text length; hero,
navigation, and CTAs support both directions and resize naturally; content is not embedded in
graphics; category/cards support longer English names. Future English localization must not
require a new layout.

### 21.16 Admin readiness
Admin is Arabic-first in MVP (e.g. لوحة التحكم، الطلبات، العملاء، المنتجات، التصنيفات، العروض،
مناطق التوصيل، الإعدادات). Future English must not require changing admin navigation architecture.
Tables/forms support RTL now and LTR later, with localized labels, actions, status badges,
validation, and notifications. Do **not** duplicate Arabic/English labels in MVP. (See
`admin-design.md`.)

### 21.17 Search
Search is not structurally single-language. MVP searches Arabic product/category content plus any
naturally-Latin brand/product identifiers that exist `[FR-012]`. Design assumes searchable text
may be Arabic, Latin, or mixed. Future planning must preserve searching both Arabic and English
content once English is added. No advanced multilingual search in MVP scope.

### 21.18 URLs / routing
Route identity is not tied to Arabic display labels; future planning uses language-neutral route
names/identifiers. Localized public URLs are a possible future enhancement — English localization
must not depend on rewriting domain/business routes.

### 21.19 PWA metadata readiness
Future PWA setup must allow localizing app name, short name, description, install presentation, and
any future shortcuts. MVP manifest values may be Arabic. Do not create language-specific duplicate
PWAs. Planning must not make PWA-metadata localization impossible.

### 21.20 SEO / metadata readiness
Landing metadata (title, meta description, Open Graph title/description, social-sharing content)
conceptually supports future localization. MVP needs Arabic only; multilingual SEO is future
scope — document readiness only.

### 21.21 Images & media
Never rely on Arabic text embedded inside product/marketing images as the **only** source of
meaning. Prefer UI text layered separately from imagery. Product images remain language-neutral
where possible, so future English does not require recreating every asset.

### 21.22 Icons
Icons are semantically appropriate in both directions. Directional icons (back arrow, forward
chevron, nav arrows) mirror via layout direction; non-directional icons stay unchanged. Do not
bake RTL into asset selection where a mirrored layout suffices.

### 21.23 Customer-generated content
Fields like Restaurant Name, Contact Person, Address, Landmark, Delivery Notes may contain Arabic,
English, or both — validated as Unicode/mixed, **never Arabic-only**. Changing the UI language
does **not** change stored customer content: a future English UI may still display Arabic-entered
addresses (mixed-direction rendering required, §21.7). Addresses are entered/displayed in the
Arabic-first UI in MVP but stored language-independently.

### 21.24 Database readiness (requirement for planning)
No schema is defined here. But `/speckit-plan` MUST design storage that distinguishes: (a)
**language-neutral domain data** (identifiers/enums/amounts), (b) **customer-entered content**
(mixed-language Unicode), and (c) **potentially translatable managed content** (category/product/
offer text). Planning must avoid choices that would make adding English a destructive schema
rewrite.

### 21.25 API readiness
Future API consumers (e.g. Flutter) may choose a locale. Core business responses stay
language-neutral where appropriate; localization belongs at the **presentation/application
boundary**, not the domain-calculation layer (aligns with constitution Principle I & II). Adding
English must not duplicate business logic.

### 21.26 Reporting & exports
Out of MVP. Future reports/exports may need localized labels while stored commercial values remain
language-neutral. No reporting added to MVP; only note that localization must not be blocked.

### 21.27 Accessibility + localization
Localization must not degrade accessibility (§18): translated text stays readable; controls resize
for text; labels stay associated with inputs; **focus order follows the active direction**;
screen-reader labels are translatable; status meaning never relies on color alone; icon-only
controls have **localized** accessible names.

### 21.28 Fallback locale
Intended future strategy: Arabic is the primary/fallback locale; English is the future secondary.
If a translation is missing later, Arabic may act as fallback. No fallback mechanics implemented
in design — documented for planning.

### 21.29 Translation completeness (future)
When English is introduced, all user-facing text (customer PWA, admin, auth, validation,
notifications, PWA text, landing metadata, statuses, errors) must be translated consistently — no
half-translated app. No MVP action beyond readiness.

---

## 22. Localization — MVP Exclusions (future scope)

Explicitly **out of MVP** (future capabilities, never surfaced now): English user interface;
language selector/switcher; per-user locale preference; locale persistence; translated product/
category managed content; multilingual notifications; multilingual SEO; localized public URLs;
reporting/export localization. A future switcher UI (e.g. `العربية | English`) may later live in
the landing header, customer profile/settings, or admin preferences — **not** in MVP wireframes,
and no language-selection onboarding now. **Localization readiness itself IS required now.**

---

## 23. Localization Technical-Planning Handoff Checklist (for `/speckit-plan`)

No technical decision is made here. `/speckit-plan` MUST explicitly address:

1. Translation-file / message-catalog structure (UI copy separated from logic).
2. Laravel locale strategy (app locale, direction, formatting) — presentation-boundary only.
3. RTL/LTR switching mechanism (logical properties; direction from layout, not reversed text).
4. Language-neutral enum/status identifiers (per §21.5) with localized display mapping.
5. Localized validation messages (rule vs message separation).
6. Localized notification templates (event/logic neutral).
7. Localized admin labels/actions/status badges (no duplicated bilingual labels in MVP).
8. Localized PWA metadata strategy (name/short name/description/shortcuts).
9. Formatting strategy for money / date / time (locale-aware presentation; stored values neutral).
10. Database strategy for future translatable **managed** content (translation tables / JSON
    locale fields / localized entities) without destructive rewrite (§21.24).
11. Search implications for Arabic/English content once English is added.
12. Test strategy for **RTL now + future LTR** (visual + a11y + bidi).
13. Fallback locale behavior (Arabic primary/fallback).
14. Cache considerations if localized output is cached (per-locale keys).

Marking this document `Approved for Technical Planning` includes the requirement that the plan
covers this checklist.
