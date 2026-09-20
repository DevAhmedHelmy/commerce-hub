# Screen Specifications — Restaurant Supplies Ordering MVP

**Feature**: `001-restaurant-supplies-mvp`
**Sources of truth**: [`../spec.md`](../spec.md), [`ux-architecture.md`](./ux-architecture.md),
[`wireframes.md`](./wireframes.md), [`design-system.md`](./design-system.md)
**Type**: Per-screen implementation-grade design spec. No application code.
**Status**: Approved for Technical Planning
**Created**: 2026-09-17

Scope: all 17 customer-facing screens (C01–C17). Each spec is complete enough that a later
implementation phase needs to invent no missing design behavior. Global rules that apply to
**every** screen (stated once, not repeated):

- **RTL global**: document direction RTL; leading edge = right; directional icons mirror;
  content aligns to the reading (right) edge; bottom-nav order mirrors `[§17]`.
- **Numerals/currency global**: Latin digits `0-9`; money as `1,250 ج`; tabular figures for
  money/quantities/order numbers `[D2]`.
- **Accessibility global**: labels on all controls incl. icon-only; visible focus; ≥ 4.5:1 text
  contrast; status/availability never color-only; touch targets ≥ ~44px; disabled controls are
  programmatically disabled with an available reason `[§18, FR-068]`.
- **Data authority global**: all prices/fees/discounts/totals are server-computed; client shows
  estimates until checkout revalidation `[BR-001, FR-028, FR-043]`.
- **Localization-readiness global** `[D4, ux §21]`: Arabic is the **only** exposed MVP language and
  RTL is active; **no language switcher**. Every screen is designed so future **English (LTR)** can
  be added with no redesign: all visible copy (labels, buttons, validation, errors, empty states,
  statuses, notifications, checkout labels) is **translatable presentation**, never business logic;
  status/availability/type values are **language-neutral identifiers** with localized display
  (ux §21.5); layouts use **logical** start/end spacing and **tolerate English text expansion**;
  direction comes from layout only (**no manual character reversal**); mixed Arabic/Latin content
  (brand names, units, Latin numbers) is **bidi-safe**; customer-entered fields accept Arabic/
  English/mixed Unicode. Each screen's **RTL** field below implies the mirrored **LTR** future.

Field order per screen: Purpose · Layout · Components · Data · Primary · Secondary · Navigation ·
Loading · Empty · Validation · Error · Disabled · Out-of-stock · Responsive · RTL · A11y.

---

## C01 — Landing Page
- **Purpose**: Explain the business and convert a visitor to sign-in `[US10, FR-065]`.
- **Layout**: Single scroll; header → hero → category preview → advantages → offers →
  how-it-works → coverage → secondary CTA → footer (desktop: two-column hero, constrained width).
- **Components**: header (logo + Sign-In), hero + primary CTA, category preview tiles, advantages
  list, **Offers** strip (offer badge cards), steps, coverage block, secondary CTA, footer.
- **Data**: active categories (preview), active-offer products `[D1]`, business/contact info
  from Settings `[FR-062]`.
- **Primary**: Start Ordering / Sign In → C02.
- **Secondary**: tap category preview; tap offer; scroll; contact/WhatsApp.
- **Navigation**: public; → C02. No bottom nav.
- **Loading**: section skeletons for categories/offers.
- **Empty**: empty previews still render shell + CTA (no broken sections) `[US10 #3]`.
- **Validation**: n/a.
- **Error**: content load fail → static shell + CTA remain usable.
- **Disabled**: n/a.
- **Out-of-stock**: offer item sold out → labelled, not orderable from preview.
- **Responsive**: mobile single-column; desktop two-column hero + multi-up rows, max content
  width.
- **RTL**: mirrored layout; CTA arrow flips.
- **A11y**: landmark regions; CTA is a clear button; images have alt/fallback.

---

## C02 — Phone Number (Sign-In)
- **Purpose**: Capture mobile number and request an OTP `[FR-001, FR-002]`.
- **Layout**: Title + helper, phone field (country prefix + number), helper/error, primary CTA.
- **Components**: phone input (prefix + number), format helper, Send-code button.
- **Data**: none persisted pre-send; produces an OTP request `[FR-001]`.
- **Primary**: Send code → C03.
- **Secondary**: edit prefix/format; back to Landing.
- **Navigation**: → C03; ‹ → C01.
- **Loading**: "Sending…" on button.
- **Empty**: n/a.
- **Validation**: reject invalid/malformed number **before** sending, inline `[FR-002]`.
- **Error**: send failure / rate-limited → message with wait ("try again in 00:30") `[FR-005]`.
- **Disabled**: Send disabled until a valid number.
- **Out-of-stock**: n/a.
- **Responsive**: centered narrow column on larger screens.
- **RTL**: RTL field; Latin digits LTR within field.
- **A11y**: labelled input; error associated + announced; numeric keypad.

---

## C03 — OTP Verification
- **Purpose**: Verify the one-time code `[FR-001, FR-003, FR-004]`.
- **Layout**: Sent-to line (+ change number) → 6 OTP boxes → error/countdown → Verify → resend.
- **Components**: OTP input (5.3), countdown, Resend, change-number link.
- **Data**: verifies code against the request; establishes session on success.
- **Primary**: Verify → C04 (new) or C06 (returning) `[US1 #2]`.
- **Secondary**: Resend (post-cooldown); change number → C02.
- **Navigation**: → C04 / C06; ‹ → C02.
- **Loading**: verifying spinner; boxes locked.
- **Empty**: n/a.
- **Validation**: wrong length / empty.
- **Error**: **incorrect** vs **expired** distinct messaging `[FR-003, FR-004]`; lockout/backoff
  with wait `[FR-005]`.
- **Disabled**: Resend disabled during cooldown (shows countdown).
- **Out-of-stock**: n/a.
- **Responsive**: centered narrow column.
- **RTL**: OTP boxes remain left-to-right entry order; labels RTL.
- **A11y**: each box labelled (digit n of 6); paste support; announced errors/countdown.

---

## C04 — Business Profile (Onboarding)
- **Purpose**: Capture the B2B identity before ordering `[FR-006, FR-007, C5]`.
- **Layout**: Progress (1/2) → Business name → Contact person → WhatsApp → login mobile (locked)
  → Continue.
- **Components**: text inputs, locked mobile field, primary button.
- **Data**: Business/Restaurant Name, Contact Person Name, WhatsApp, login mobile (from OTP)
  `[FR-007]`. No KYC/tax `[C5]`.
- **Primary**: Continue → C05.
- **Secondary**: none (mobile locked).
- **Navigation**: → C05; abandoning resumes here next sign-in `[FR-009, US1 #5]`.
- **Loading**: save spinner.
- **Empty**: n/a.
- **Validation**: required fields; WhatsApp format.
- **Error**: save failure → retry.
- **Disabled**: Continue disabled until required valid.
- **Out-of-stock**: n/a.
- **Responsive**: single column; centered on desktop.
- **RTL**: labels/inputs RTL; phone numeric LTR.
- **A11y**: required fields marked + announced; error per field.

---

## C05 — Delivery Address Form (reusable: onboarding / profile / checkout)
- **Purpose**: Create/edit the single default delivery address `[FR-008, C6]`.
- **Layout**: Area selector → address line → building → floor + unit → landmark → notes → Save.
- **Components**: area select (sheet, active areas only), text inputs, save button.
- **Data**: area (from active Delivery Areas `[FR-032, FR-033]`), address detail fields; one
  default address per customer `[C6]`.
- **Primary**: Save (+ finish → Home in onboarding).
- **Secondary**: cancel/back (contextual).
- **Navigation**: onboarding → C06; Profile → C17; Checkout → C11.
- **Loading**: areas loading; save.
- **Empty**: no active areas → "Delivery isn't available yet — contact us" `[FR-033]`.
- **Validation**: required address line + area.
- **Error**: save failure → retry.
- **Disabled**: Save disabled until valid.
- **Out-of-stock**: n/a.
- **Responsive**: single column; sheet on mobile when launched from checkout.
- **RTL**: RTL fields; fee shown as `30 ج` per area.
- **A11y**: select is keyboard operable; labelled fields; announced errors.

---

## C06 — Home
- **Purpose**: Fast hub — search, categories, active offers `[FR-011, FR-024, D1]`.
- **Layout**: header + persistent search → category shortcuts → Offers strip → browse-all →
  bottom nav.
- **Components**: search field (5.4), category shortcuts (5.5), Offers strip (product cards),
  bottom nav (5.19).
- **Data**: active categories; active-offer products `[D1]`; cart count (badge).
- **Primary**: search or open a category.
- **Secondary**: open an offer product (→ C09, view only); go to Cart. (No quick-add.)
- **Navigation**: → C07, C08, C09, C10; tabs to C14/C16.
- **Loading**: skeleton shortcuts + offer cards.
- **Empty**: no offers → hide strip gracefully; no categories → browse-all guidance.
- **Validation**: n/a.
- **Error**: load fail → retry.
- **Disabled**: n/a (cards navigate to Details; no add control on the card).
- **Out-of-stock**: offer item sold out → labelled; card still opens Details to view `[FR-015]`.
- **Responsive**: mobile single column; tablet/desktop multi-up strips, constrained width.
- **RTL**: strips scroll right-to-left; badges positioned for RTL.
- **A11y**: search labelled; horizontal strips keyboard-scrollable; cards focusable.

---

## C07 — Search
- **Purpose**: Find products by name/brand `[FR-012]`.
- **Layout**: search field (focused) → results list (product cards) → bottom nav.
- **Components**: search input with clear, product cards (compact), result count.
- **Data**: products matching name/brand `[FR-012]`; availability + offer + best price.
- **Primary**: open a result (`عرض المنتج` / tap card) → C09.
- **Secondary**: clear query. (No quick-add.)
- **Navigation**: → C09; back to origin.
- **Loading**: inline searching spinner / skeleton rows.
- **Empty**: **no results** → "No products match '…' — check spelling or browse categories"
  `[US2 #2]`.
- **Validation**: optional min query length messaging.
- **Error**: search failure → retry.
- **Disabled**: n/a (no add control on the card).
- **Out-of-stock**: OoS results labelled; card still opens Details to view `[FR-015]`.
- **Responsive**: mobile list; desktop wider rows/grid.
- **RTL**: query field RTL; Latin query allowed.
- **A11y**: results announced (count); each card labelled.

---

## C08 — Product Listing (category / results)
- **Purpose**: Browse products within a category `[FR-011, US2]`.
- **Layout**: category header (name) + count → product cards (list/2-up) → load-more → nav.
- **Components**: context header, product cards (5.6), pagination/scroll.
- **Data**: active category's products (available + OoS; inactive hidden) `[FR-016, FR-017]`.
- **Primary**: open a product (`عرض المنتج` / tap card) → C09.
- **Secondary**: scroll/paginate. (No quick-add / no add-to-cart on the card.)
- **Navigation**: → C09.
- **Loading**: skeleton cards.
- **Empty**: empty/inactive category → "No products here yet" (not error) `[US2 #4, FR-017]`.
- **Validation**: n/a.
- **Error**: load fail → retry.
- **Disabled**: n/a (no add control on the card).
- **Out-of-stock**: OoS badge + dimmed price; card still opens Details to view `[FR-015]`.
- **Responsive**: 1-up mobile / 2–3-up tablet-desktop grid.
- **RTL**: grid flows RTL; badges mirrored.
- **A11y**: heading identifies category; cards focusable; card link labelled (opens Details).

---

## C09 — Product Details
- **Purpose**: Unambiguous unit/quantity/price decision; add to cart `[US3, FR-018..FR-025,
  FR-069]`.
- **Layout**: image + badges → brand/name → availability → description → unit selector → quantity
  → pricing block (tier table, offer, applied best, savings, line total) → sticky Add.
- **Components**: unit selector (5.9), quantity selector (5.10), pricing block (5.11), sticky
  primary button.
- **Data**: product, units, per-unit tiers, active offer, availability; applied best price =
  lower-of offer vs tier `[C1/BR-011]`.
- **Primary**: **Add to Cart · {line total}** → C10 `[FR-026]`.
- **Secondary**: change unit/quantity; view tiers; go to Cart.
- **Navigation**: → C10; back to Listing/Search.
- **Loading**: skeleton price block + image.
- **Empty**: missing description degrades gracefully.
- **Validation**: quantity bounds.
- **Error**: load/add failure → retry.
- **Disabled**: Add disabled when OoS/inactive with reason `[FR-015, FR-016]`.
- **Out-of-stock**: OoS badge; Add disabled; price shown for reference `[FR-015]`.
- **Responsive**: mobile single column; desktop image-left / details-right.
- **RTL**: selector + tier table RTL; `n × price = total` reads correctly; Latin digits.
- **A11y**: unit selector = radio group; stepper announces value; applied price + source tag
  readable; "offer and tier don't stack" conveyed in text.

---

## C10 — Cart
- **Purpose**: Review/adjust; estimate + minimum-order progress `[US3, FR-026..FR-031, C7]`.
  **Products-only surface** — shows no delivery amounts.
- **Layout**: estimate notice → cart lines → product subtotal + minimum-order progress → sticky
  "Continue to delivery" → nav.
- **Components**: cart item (5.13), minimum-order progress (5.14), product subtotal, primary
  button. **No delivery fee/discount/total** in the cart (deferred to Checkout) `[FR-034]`.
- **Data**: cart lines (unit, qty, unit price, line total), effective product subtotal, minimum
  order value `[FR-030]`, availability/price-change flags `[FR-029]`. **No delivery data.**
- **Primary**: Continue to delivery → C11.
- **Secondary**: change qty/unit; remove (Undo); continue shopping.
- **Navigation**: → C11; back to catalog.
- **Loading**: re-price spinners on lines / totals.
- **Empty**: empty cart → friendly empty + "Browse products".
- **Validation**: quantity limits.
- **Error**: load/re-price fail → retry.
- **Disabled**: Proceed disabled below minimum or with unresolved unavailable items.
- **Out-of-stock**: flagged item separated + excluded from checkout `[US3 #7, FR-029]`.
- **Responsive**: mobile list; desktop lines-left / summary-right.
- **RTL**: line layout mirrored; strike/prev prices RTL-safe.
- **A11y**: quantity controls labelled; flags announced; disabled Proceed states its reason.

---

## C11 — Checkout Step 1: Delivery
- **Purpose**: Confirm identity, address, area, date, slot, payment before review `[US4,
  FR-038..FR-041, D3]`.
- **Layout**: step header (1 of 2) → customer summary → address (+Edit) → area → date → slot →
  payment (COD) → sticky CTA `مراجعة الطلب`.
- **Components**: customer summary, address card (5.16), delivery slot selector (5.15), COD
  indicator, primary button.
- **Data**: customer profile, default address + area `[FR-008]`, active delivery slots for date
  `[FR-040]`.
- **Primary**: **`مراجعة الطلب`** → C12.
- **Secondary**: Edit address → C05 sheet; change date/slot; back to Cart.
- **Navigation**: → C12; edit → C05; ‹ → C10.
- **Loading**: areas/slots loading.
- **Empty**: no active slots for date → "No slots on this date — pick another" `[FR-039]`.
- **Validation**: date + slot required; date not past `[FR-039]`.
- **Error**: area inactive `[FR-033]`; selected slot gone `[FR-039]`.
- **Disabled**: CTA disabled until date + slot valid.
- **Out-of-stock**: unavailable cart item forces return to Cart.
- **Responsive**: mobile single column; desktop form-left / mini-summary-right.
- **RTL**: slot chips + date field RTL; fee `30 ج`.
- **A11y**: slot chips = radio group; COD indicated as selected method; edit labelled.

---

## C12 — Checkout Step 2: Review & Confirm
- **Purpose**: Final commercial confirmation surface `[US4, FR-041..FR-047, D3]`.
- **Layout**: step header (2 of 2) → compact customer/delivery → items (unit, qty, unit price,
  line total) → commercial summary (subtotal, base delivery, delivery discount, final delivery,
  order total) → COD → sticky CTA `تأكيد الطلب`.
- **Components**: read summary rows, pricing summary, primary button, **changed-terms** panel.
- **Data**: server-revalidated order (prices, tiers, offers, availability, selling unit, area,
  fee/discount, slot, minimum) `[FR-043]`.
- **Primary**: **`تأكيد الطلب`** → C13 `[FR-042]`.
- **Secondary**: back to Step 1; review changed-terms; edit quantities → Cart.
- **Navigation**: → C13 on success; ‹ → C11.
- **Loading**: revalidating.
- **Empty**: n/a (an order under review always has items).
- **Validation**: below minimum → Confirm disabled + shortfall `[FR-031]`.
- **Error**: network/server → explicit failure + retry, **no false success** `[FR-047, US4 #6]`;
  area inactive / slot gone → guidance.
- **Disabled**: Confirm gated (minimum unmet / unresolved changes).
- **Out-of-stock**: item became OoS → surfaced in changed-terms; return to Cart to resolve.
- **Changed-terms (first-class state)**: if price / stock-availability / selling-unit /
  expired-offer / changed delivery-discount / unavailable-slot changed → show itemised diff +
  updated total; require **Review updated order** before confirm; never auto-confirm `[FR-044,
  US4 #3]`.
- **Responsive**: mobile single column; desktop summary-right sticky.
- **RTL**: summary rows RTL; negative discount shown `−20 ج`.
- **A11y**: changed-terms announced as an alert; confirm reason available when disabled.

---

## C13 — Order Success
- **Purpose**: Confirm placement + essentials `[FR-048]`.
- **Layout**: success mark → order number → total → date/slot → address → status → actions.
- **Components**: success block, key-value summary, primary + secondary buttons.
- **Data**: order number, final total, date, slot, address, status (New) `[FR-048]`.
- **Primary**: View Order Details → C15.
- **Secondary**: Continue shopping → C06.
- **Navigation**: → C15 / C06.
- **Loading / Empty / Validation / Error / Disabled / OoS**: n/a — reached only on confirmed
  success; failures never present here.
- **Responsive**: centered card.
- **RTL**: mirrored; `ORD-100482`, `1,210 ج` Latin.
- **A11y**: success announced; order number selectable/readable.

---

## C14 — Orders List
- **Purpose**: The customer's own orders `[US9, FR-052]`.
- **Layout**: title → order cards (newest first) → nav.
- **Components**: order card (5.17), status chip (5.18).
- **Data**: customer's orders — number, date, total, status `[FR-052]` (own only, `[BR-010]`).
- **Primary**: open an order → C15.
- **Secondary**: scroll.
- **Navigation**: → C15.
- **Loading**: skeleton rows.
- **Empty**: "No orders yet — start ordering" → C06.
- **Validation**: n/a.
- **Error**: load fail → retry.
- **Disabled / OoS**: n/a.
- **Responsive**: mobile list; desktop table-like rows, constrained width.
- **RTL**: rows mirrored; numbers/totals Latin.
- **A11y**: each row a labelled link; status chip has text.

---

## C15 — Order Details
- **Purpose**: Immutable historical order + allowed cancellation `[US9, FR-051, FR-050,
  BR-012]`.
- **Layout**: status + read-only timeline → delivery → items (snapshot) → totals → payment →
  cancel (conditional).
- **Components**: status timeline, snapshot line items, totals, cancel button + reason note,
  cancel-confirm dialog.
- **Data**: order snapshot (names, units, unit prices, quantities, discounts, line totals,
  delivery breakdown, final total) — never changes with catalog `[FR-051]`.
- **Primary**: read; **Cancel order** only when status = New (self-cancel) `[BR-012]`.
- **Secondary**: contact business (post-Confirmed).
- **Navigation**: ‹ → C14.
- **Loading**: load spinner/skeleton.
- **Empty**: n/a (order always has content).
- **Validation**: cancel confirmation dialog.
- **Error**: load/cancel failure → retry.
- **Disabled**: Cancel hidden/disabled when not allowed, with reason ("contact us to change a
  confirmed order") `[BR-012]`.
- **Out-of-stock**: n/a (historical).
- **Responsive**: mobile single column; desktop two-column (items / summary).
- **RTL**: timeline direction mirrored; amounts Latin; `−20 ج` discount.
- **A11y**: timeline is descriptive text (not color-only); cancel dialog focus-trapped.

---

## C16 — Profile
- **Purpose**: View/edit business profile + default address; sign out `[FR-007, FR-008]`.
- **Layout**: business identity block (+Edit) → default address block (+Edit) → business info →
  sign out → nav.
- **Components**: profile summary, address card (5.16), links, sign-out button.
- **Data**: profile fields, single default address `[C6]`, business/contact info `[FR-062]`.
- **Primary**: Edit profile or Edit address → C17/C05.
- **Secondary**: sign out; WhatsApp business.
- **Navigation**: → C17 / edit forms.
- **Loading**: load.
- **Empty**: n/a.
- **Validation**: on edit forms.
- **Error**: load/save fail → retry.
- **Disabled**: Save (in edit) until valid.
- **Out-of-stock**: n/a.
- **Responsive**: mobile single column; desktop constrained.
- **RTL**: mirrored; phone/WhatsApp Latin digits.
- **A11y**: edit entry points labelled; sign-out confirmable.

---

## C17 — Delivery Address (view/edit)
- **Purpose**: Profile-context view of the single default address; edit via C05 `[FR-008, C6]`.
- **Layout**: address summary → Edit.
- **Components**: address card (5.16), edit button.
- **Data**: current default address (area + details).
- **Primary**: Edit → C05.
- **Secondary**: back.
- **Navigation**: → C05 → back to C16.
- **Loading**: load.
- **Empty**: address missing → prompt to add (→ C05).
- **Validation / Error / Disabled**: handled in C05 edit form.
- **Out-of-stock**: n/a.
- **Responsive**: mobile single column.
- **RTL**: mirrored; area fee Latin.
- **A11y**: edit labelled; summary readable.

---

## Coverage note
All 17 customer screens (C01–C17) are specified with the mandated fields. Admin screens are
specified separately in [`admin-design.md`](./admin-design.md). The two-step checkout, the
changed-terms state, lower-of pricing, minimum-order basis, delivery discounts, order statuses,
Latin-digit / `ج` currency formatting, RTL, accessibility, responsive behavior, and
**localization readiness** (Arabic-only MVP, RTL now / future English LTR, translatable copy,
language-neutral identifiers, text-expansion tolerance, bidi-safe mixed content — see the global
rule above and ux §21) are represented across these specs and audited in the design quality gate.
