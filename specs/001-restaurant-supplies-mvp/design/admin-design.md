# Admin Design — Restaurant Supplies Ordering MVP

**Feature**: `001-restaurant-supplies-mvp`
**Sources of truth**: [`../spec.md`](../spec.md), [`ux-architecture.md`](./ux-architecture.md)
**Type**: Admin UX design (operational). No application code.
**Status**: Approved for Technical Planning
**Created**: 2026-09-17

The admin console will later be built on **Filament** (per the constitution's stack). This
document plans admin **experience and structure**, optimized for **operational speed**, not
visual customization. It uses Filament's conventional resource/list/detail patterns
deliberately — bespoke admin UI is a non-goal. Desktop-first, tablet-usable `[§16]`.

Global admin rules: server-side authorization on every action `[FR-064]`; all authorized admins
have equal access in MVP (no fine-grained RBAC) `[FR-063, FR-064]`; Latin digits + `1,250 ج`
formatting `[D2]`; historical orders are immutable `[FR-051, BR-006]`.

**Localization readiness (admin)** `[D4, ux §21.16]`: admin is **Arabic-first** and RTL in MVP
(e.g. لوحة التحكم، الطلبات، العملاء، المنتجات، التصنيفات، العروض، مناطق التوصيل، الإعدادات), with
**no language switcher**. The admin navigation architecture, tables, and forms MUST support adding
**English (LTR)** later without workflow redesign. All admin labels, actions, status badges,
validation, and notifications are **translatable presentation**; **do not duplicate Arabic/English
labels in MVP**. Order status / availability / discount-type / payment-type values are
**language-neutral identifiers** with localized display (ux §21.5) — e.g. an order stored as
`out_for_delivery` renders `خرج للتوصيل` now and `Out for Delivery` later; filters/actions key off
the identifier, never the Arabic text. Tables mirror RTL↔LTR (column order, numeric alignment) and
tolerate mixed Arabic/Latin product/brand names (bidi-safe); forms use logical label/field/help
alignment and tolerate English text expansion. See §16 for the admin localization checklist.

---

## 1. Navigation Groups

Left sidebar (Filament navigation groups), ordered by daily frequency:

| Group | Items | Spec |
|-------|-------|------|
| **Operations** | Dashboard · Orders · Customers | `[FR-053, FR-054, FR-056]` |
| **Catalog** | Categories · Products · Offers | `[FR-057, FR-058, FR-060]` |
| **Delivery** | Delivery Areas · Delivery Slots · Delivery Discount Rules | `[FR-061, FR-040, FR-036]` |
| **Settings** | Settings (min order, business/contact info) | `[FR-062]` |

Rationale: Operations first (the daily job is processing orders); Catalog and Delivery are
setup/maintenance; Settings is rare. Selling Units and Pricing Tiers are **not** top-level —
they live inside a Product (see §8) so admins maintain a product in one place.

---

## 2. Dashboard  `[FR-053]`

Purpose: at-a-glance operational state on login; **not** analytics `[FR-053, §Out-of-scope]`.

**Widgets (stat + list):**
- **New orders** — count of status = New (the primary call to action) + quick link to filtered
  list.
- **Today's orders** — count placed today.
- **Recent orders** — compact table (last N): order #, business name, total, status, time →
  row click opens Order Details.
- **High-level indicators** — e.g. orders by status (small counts), today's order value sum.
  Deliberately simple; no charts/BI required.

States: loading (skeleton widgets), empty (e.g. "No new orders"), error (retry). A new customer
order appears here on refresh/navigation — no realtime/WebSocket needed `[FR-055]`.

---

## 3. Orders — List  `[FR-054]`

**Table columns**: Order # · Business (customer) name · Date/time · Items (count) · Total ·
**Status** (colored chip) · quick actions.

**Filters** `[FR-054]`:
- **Status** (New / Confirmed / Preparing / Out for Delivery / Delivered / Cancelled) —
  default view surfaces actionable statuses (New first).
- **Date / date range**.
- **Search** by order number or customer (name/mobile).
- Optional: delivery area, delivery date.

**Behavior**: sortable columns (date, total, status); default sort = newest / New-first;
pagination. Row click → Order Details.

States: loading, empty ("No orders match"), error. Latin digits with `ج` currency in totals.

---

## 4. Orders — Details  `[FR-054, FR-050, FR-051]`

Read-mostly detail with the **status action** as the primary control.

**Sections**:
- **Header**: Order # · current **status chip** · placed date/time · **status actions** (§5).
- **Customer**: business name, contact person, mobile, WhatsApp `[FR-041]`.
- **Delivery**: area, full address (building/floor/unit/landmark/notes), delivery date, slot.
- **Items (immutable snapshot)**: product name, selling unit, quantity, unit price, product
  discount, line total `[FR-051]`.
- **Commercial summary**: product subtotal, base delivery fee, delivery discount, final delivery
  fee, final total, payment = Cash on Delivery.
- **Cancellation reason** (if cancelled) — optional stored reason `[BR-012]`.

Order data never changes when the catalog later changes `[FR-051, BR-006]`; fields are display-
only except status.

---

## 5. Order Status Actions  `[FR-049, FR-050, BR-012]`

**Highest-frequency admin operation** — must be 1 click along the allowed path.

- Forward path: **New → Confirmed → Preparing → Out for Delivery → Delivered**. The primary
  button offers the **next allowed status** (e.g. on New → "Confirm order").
- **Cancel** available from New, Confirmed, Preparing, Out for Delivery (with optional reason);
  **not** from Delivered or Cancelled `[BR-012]`.
- Disallowed transitions are not offered and are rejected server-side `[FR-050, SC-012]`.
- Each change updates the customer-visible status `[US6 #2]` and is reflected in history.

UX: a clear current-status chip + a primary "advance" action + an overflow menu for Cancel;
confirmation only for Cancel (destructive). No workflow engine / no complex branching `[C3]`.

---

## 6. Customers — Table & Details  `[FR-056]`

**Table**: business name · contact person · mobile · WhatsApp · delivery area · #orders · search
(name/mobile).

**Details**: profile/contact info + default delivery address; **order history** for that
customer (reuses the orders table scoped to the customer) → row click → Order Details.

States: loading, empty ("No customers"), error. Read-oriented (no destructive customer edits
required for MVP beyond what the spec defines).

---

## 7. Categories — Management  `[FR-057]`

**List**: name · active toggle · order/position · product count.
**Actions**: create, edit, **activate/deactivate** (inline toggle — high frequency), **arrange**
(reorder). Inactive categories are hidden from customers `[FR-017]`.

States: loading, empty ("No categories — create one"), validation (name required), error.

---

## 8. Products — Management (tabbed, NOT one giant form)  `[FR-058, FR-059, FR-060]`

**Products list**: image thumb · name · brand · category · **availability** (Available / Out of
Stock / Inactive) with quick toggle · offer indicator · search/filter by category + availability.

**Product editor — tabs** (explicitly split to avoid a monolithic form):

| Tab | Contents | Spec |
|-----|----------|------|
| **General** | Name, brand, category, description | `[FR-058, FR-013]` |
| **Media** | Product image(s), ordering, fallback note | `[FR-058]` |
| **Selling Units** | Manage one+ selling units (label e.g. "Bag 2.5 KG", "Carton ×6") | `[FR-018, FR-059]` |
| **Pricing** | Per selling unit: normal price + quantity/wholesale **tiers** | `[FR-019, FR-059]` |
| **Offers** | Temporary offers (normal/offer price, start/end date, active) | `[FR-023, FR-060]` |
| **Availability** | Available / Out of Stock / Inactive state | `[FR-014, FR-058]` |

### 8.1 Selling Units management (within Product)  `[FR-059]`
Repeatable rows per unit: unit label, (default flag). Each unit owns its own pricing (Pricing
tab). At least one unit required for an orderable product.

### 8.2 Pricing / Tier management (within Product → per unit)  `[FR-019, FR-059]`
Per selling unit: a **normal price** + an optional ordered list of **tiers** (min qty → unit
price), e.g. `1–4 = 180`, `5–9 = 170`, `10+ = 160`. Validation: non-overlapping ascending
ranges; a flat-price unit simply has no tiers. Admin sees a preview of the resulting customer
price at sample quantities.

> Note (surfaced for engineering, not decided here): the customer-facing **lower-of offer vs.
> tier** rule `[C1/BR-011]` is computed by the server at display/checkout; admins set offers and
> tiers independently — the console does not ask admins to resolve precedence.

### 8.3 Offers management (within Product, and an Offers list §9)  `[FR-023, FR-060]`
Per offer: normal price, offer price, start date, end date, active toggle, target selling
unit(s). Only active + in-range offers apply to customers `[FR-024, BR-005]`.

States (product editor): loading, validation (required name/category, at least one unit, valid
tier ranges, offer date sanity), error, disabled save until valid.

---

## 9. Offers — List (cross-product)  `[FR-060]`

A convenience list of all offers across products: product · unit · normal → offer price · start/
end · active. Create/edit/activate/deactivate. Mirrors §8.3 data; lets staff run promotions
without opening each product.

---

## 10. Delivery Areas  `[FR-061, FR-032, FR-033]`

**List**: area name · **base delivery fee** · active toggle.
**Actions**: create, edit, **activate/deactivate** (inline). Inactive areas block new orders to
that area `[FR-033]`. Validation: name + non-negative fee.

---

## 11. Delivery Slots  `[FR-061, FR-040]`

**List**: slot label/time window (e.g. "1–4 PM") · active toggle · applicable day rule (as
modeled). **Actions**: create, edit, **activate/deactivate**. **No numeric capacity field** —
MVP has no per-slot quota `[C4]`. A deactivated slot is rejected at checkout revalidation
`[FR-040]`.

---

## 12. Delivery Discount Rules  `[FR-061, FR-035, FR-036]`

**List**: rule name · type (**fixed** / **percentage of delivery fee** / **free delivery**) ·
qualifying **minimum subtotal** · value · active toggle.
**Actions**: create/edit/activate/deactivate. Qualification uses effective product subtotal
excl. delivery `[BR-003]`. Admins may create several; the **server applies only the single best
(largest saving), never stacked, never below zero, ties → higher minimum** `[BR-004, C2]` — the
console does not ask admins to resolve selection. Validation: non-negative values;
percentage 0–100.

---

## 13. Settings  `[FR-062]`

- **Minimum order amount** (used at checkout, measured on effective product subtotal excl.
  delivery) `[FR-030, C7]`.
- **Business / contact information** shown in the customer app (landing/footer/profile)
  `[FR-062, FR-065]`.

Single settings screen; validation (non-negative minimum; required contact fields); save with
confirmation.

---

## 14. High-Frequency Admin Operations (optimise for fewest clicks)

Ranked by daily frequency; these must be the fastest paths in the console:

1. **Advance / cancel order status** (§5) — the core daily loop; one-click next status from list
   or details `[FR-050, FR-054]`.
2. **Triage new orders** — Dashboard "New orders" + Orders list default (New-first) on login
   `[FR-053, FR-055]`.
3. **Toggle product availability** (Available ↔ Out of Stock) — inline on the products list
   without opening the editor `[FR-058]`.
4. **Activate/deactivate** categories, areas, slots, offers, discount rules — inline toggles in
   list views `[FR-057, FR-061]`.
5. **Create/adjust an offer** — from the Offers list or the product's Offers tab `[FR-060]`.
6. **Look up a customer / their orders** — customers search → details `[FR-056]`.
7. **Edit pricing/tiers** — product → Pricing tab `[FR-059]`.

Design implications: prominent inline toggles, a default order view that shows actionable work
first, keyboard-friendly tables, and a one-primary-action detail header for status changes.

---

## 15. Admin Screen Inventory (14 top-level destinations)

| # | Screen | Group |
|---|--------|-------|
| A01 | Admin Login | — |
| A02 | Dashboard | Operations |
| A03 | Orders List | Operations |
| A04 | Order Details (+ status actions) | Operations |
| A05 | Customers List | Operations |
| A06 | Customer Details (+ order history) | Operations |
| A07 | Categories | Catalog |
| A08 | Products List | Catalog |
| A09 | Product Editor (tabs: General/Media/Selling Units/Pricing/Offers/Availability) | Catalog |
| A10 | Offers List | Catalog |
| A11 | Delivery Areas | Delivery |
| A12 | Delivery Slots | Delivery |
| A13 | Delivery Discount Rules | Delivery |
| A14 | Settings | Settings |

Selling Units and Pricing Tiers are managed **inside** A09 (tabs), not as separate destinations,
so a product is maintained in one place `[FR-059]`. This keeps product administration off a
single giant form while avoiding scattered surfaces.

All admin operations trace to spec requirements; no admin capability beyond the approved spec is
introduced.

---

## 16. Admin Localization Readiness Checklist  `[D4, ux §21.16 / §23]`

Arabic-first, RTL, no switcher in MVP; must accept future English (LTR) with no workflow redesign.

- **Navigation groups & items**: labels are translatable presentation; group/route identity is
  language-neutral (ux §21.18) — renaming displays does not change navigation architecture.
- **Order table (A03)**: column order and numeric alignment mirror RTL↔LTR; status column uses the
  **status badge** (neutral id → localized label, ux §21.5); product/customer names may be mixed
  Arabic/Latin (bidi-safe). Table structure is reused unchanged for LTR.
- **Order filters (A03)**: status/date/area filters key off **identifiers**, not Arabic text;
  filter labels translatable.
- **Order details & status actions (A04/§5)**: the next-status action and Cancel are driven by
  neutral status identifiers; button/label text localized; timeline direction mirrors.
- **Customers (A05/A06)**: display mixed-language customer content (business/contact/address) —
  never validated Arabic-only (ux §21.23/§27).
- **Catalog (A07–A10)**: category/product/offer **managed content** may be Arabic-only in MVP, but
  the future model must allow English translations without destructive rewrite (ux §21.6/§21.24) —
  a `/speckit-plan` concern, not decided here. Selling-unit **identifiers** stay neutral
  (`bag`/`carton`/`pack`); Arabic display now, English later (ux §21.8).
- **Delivery (A11–A13)**: area/slot/rule **display names** localizable; discount **type** and
  status are neutral identifiers; amounts are Latin-digit `1,250 ج` presentation.
- **Settings (A14)**: business/contact info is managed content (Arabic MVP; English possible
  later); the minimum-order value is a neutral number.
- **Forms (all)**: logical label/field/help/validation alignment; tolerate English text expansion;
  numeric/phone fields keep LTR entry within an RTL field.
- **Validation & notifications (admin)**: rule vs localized message separation (ux §21.12/§21.13);
  admin new-order notification content localizable without changing the triggering event.
- **No duplicated bilingual labels in MVP**; English is added later via the translation layer that
  `/speckit-plan` defines (ux §23).
