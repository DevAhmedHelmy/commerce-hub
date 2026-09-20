# Wireframes — Restaurant Supplies Ordering MVP

**Feature**: `001-restaurant-supplies-mvp`
**Sources of truth**: [`../spec.md`](../spec.md), [`ux-architecture.md`](./ux-architecture.md)
**Type**: Low-fidelity structural wireframes (text/ASCII). No visual design, no code.
**Status**: Approved for Technical Planning
**Created**: 2026-09-17

> Reading notes
> - Frames are drawn **LTR for legibility**; the production UI is **RTL-first** — every frame
>   mirrors horizontally (leading edge = right). Directional icons flip; search/cart/user
>   icons do not (see ux §17).
> - **Numerals are Latin**; currency renders as `1,250 ج` (D2). Arabic CTAs are shown where
>   fixed by PM decision (`مراجعة الطلب`, `تأكيد الطلب`).
> - `[▸]` = primary CTA, `( )` = secondary/tertiary, `‹badge›` = status/label chip,
>   `�( )▸` = stepper, `▭` = image/media area, `▢` = checkbox/radio, `≈≈≈` = skeleton.
> - Each screen lists the key **states** beneath its frame. IDs match ux §4 (C01–C17).
> - **Localization readiness** `[D4, ux §21]`: Arabic is the only exposed MVP language and RTL is
>   active; **no language switcher** appears in any frame. English labels shown in these frames are
>   **structural placeholders only** (Arabic is the real MVP copy); all such copy is **translatable
>   presentation**, not business logic. Frames must be read as **direction-agnostic**: every box
>   tolerates longer/shorter future **English (LTR)** text without redesign, direction comes from
>   layout (no manually reversed Arabic), and mixed Arabic + Latin brand/number/unit content is
>   bidi-safe. Status/availability words map to **language-neutral identifiers** (ux §21.5).

---

## C01 — Landing Page (Mobile)  `[US10, FR-065]`

```
┌───────────────────────────────┐
│ ☰  Company Name        (تسجيل دخول) │  header (sticky-light)
├───────────────────────────────┤
│ ▭▭▭▭▭▭▭ HERO ▭▭▭▭▭▭▭▭▭         │
│  Supplies for your kitchen,    │  value proposition (placeholder)
│  delivered.                    │
│                                │
│      [▸  ابدأ الطلب / دخول ]     │  primary CTA → C02
├───────────────────────────────┤
│  Categories                    │  category preview (graceful if empty)
│  [🧊 Frozen][🍟 Fries][🥫 Sauces]│
│  [🧀 Dairy ][🛢 Oils ][📦 Packg ]│
├───────────────────────────────┤
│  Why order with us             │  business advantages
│  • Wholesale pricing           │
│  • Reliable delivery           │
│  • Fast repeat ordering        │
├───────────────────────────────┤
│  Offers  ‹offer›               │  FEATURED = active offers only (D1)
│  ┌─────────┐ ┌─────────┐        │
│  │▭ ‹offer›│ │▭ ‹offer›│  →     │
│  │Ketchup  │ │Fries 2.5│        │
│  │180 ج  │ │from 170 │        │
│  └─────────┘ └─────────┘        │
├───────────────────────────────┤
│  How ordering works            │
│  ①دخول ②تصفح ③طلب ④توصيل        │
├───────────────────────────────┤
│  Delivery & coverage           │  high-level; areas admin-managed
├───────────────────────────────┤
│      [▸  ابدأ الطلب / دخول ]     │  secondary CTA
├───────────────────────────────┤
│  Contact · WhatsApp · ©        │  footer (from Settings, FR-062)
└───────────────────────────────┘
```
States: **L** section skeletons · **E** empty previews still render shell + CTA (US10 #3) ·
**Er** content fail → static shell + CTA. No auth required.

---

## C01 — Landing Page (Desktop)  `[US10, FR-065, §16]`

```
┌──────────────────────────────────────────────────────────────────────┐
│ Company Name        Categories  Offers  How it works   ( تسجيل دخول )  │
├──────────────────────────────────────────────────────────────────────┤
│  ┌───────────────────────────┐   ┌──────────────────────────────────┐ │
│  │  HERO copy + value prop   │   │  ▭▭▭▭▭  HERO IMAGE  ▭▭▭▭▭▭▭▭     │ │
│  │  [▸ ابدأ الطلب / دخول ]     │   │                                  │ │
│  └───────────────────────────┘   └──────────────────────────────────┘ │
├──────────────────────────────────────────────────────────────────────┤
│  Categories   [Frozen][Fries][Sauces][Dairy][Oils][Packaging]          │
├──────────────────────────────────────────────────────────────────────┤
│  Offers ‹offer›   ┌────┐┌────┐┌────┐┌────┐   (constrained max width)   │
│                   │ ▭  ││ ▭  ││ ▭  ││ ▭  │                             │
│                   └────┘└────┘└────┘└────┘                             │
├──────────────────────────────────────────────────────────────────────┤
│  Advantages (3-up)   |  How it works (4 steps)  |  Delivery coverage   │
├──────────────────────────────────────────────────────────────────────┤
│              [▸  ابدأ الطلب / دخول ]     (centered secondary CTA)        │
├──────────────────────────────────────────────────────────────────────┤
│  Footer: Contact · WhatsApp · address · ©                              │
└──────────────────────────────────────────────────────────────────────┘
```
Same IA scaled up; centered constrained content width; hero two-column.

---

## C02 — Phone Login  `[FR-001, FR-002, US1]`

```
┌───────────────────────────────┐
│ ‹ back            Sign in       │
├───────────────────────────────┤
│  Enter your mobile number      │
│  We'll send a one-time code.   │
│                                │
│  ┌───────────────────────────┐ │
│  │ +20 │ 10 1234 5678         │ │  Latin digits (D2), RTL field
│  └───────────────────────────┘ │
│  ⓘ Format helper / error line  │  V: "Enter a valid number"
│                                │
│  [▸  Send code ]                │  D until valid
│                                │
│  By continuing you agree …     │
└───────────────────────────────┘
```
States: **V** invalid/malformed (no send, FR-002) · **L** sending · **Er** send failed /
rate-limited "try again in 00:30" (FR-005) · **D** submit until valid.

---

## C03 — OTP Verification  `[FR-003, FR-004, FR-005]`

```
┌───────────────────────────────┐
│ ‹ back        Verify code       │
├───────────────────────────────┤
│  Code sent to +20 10 1234 5678 │  (change number)
│                                │
│   [ 1 ][ 2 ][ 3 ][ 4 ][ 5 ][ 6 ]│  OTP boxes, Latin digits (D2)
│                                │
│  ‹error› Incorrect code         │  distinct from "expired" (FR-003)
│  Code expires in 04:12          │  countdown
│                                │
│  [▸  Verify ]                   │
│                                │
│  Didn't get it?  (Resend 00:28) │  D during cooldown (FR-005)
└───────────────────────────────┘
```
States: **L** verifying · **V** wrong length · **Er** incorrect / **expired → Resend** /
lockout "try again in 05:00" · **D** resend during cooldown.
→ new customer: C04 · returning: C06 (US1 #2).

---

## C04 — Profile Setup (Business Profile)  `[FR-007, C5]`

```
┌───────────────────────────────┐
│           Your business    1/2  │  onboarding progress
├───────────────────────────────┤
│  Business / Restaurant name *  │  primary commercial identity (C5)
│  [___________________________] │
│  Contact person name *         │
│  [___________________________] │
│  WhatsApp number *             │
│  [ +20 | 10 ____ ____ ]        │  Latin digits
│  Login mobile                  │
│  [ +20 10 1234 5678 ] (locked) │  read-only, from OTP
│                                │
│  (no tax / company-reg fields) │  C5: no KYC
│                                │
│  [▸  Continue ]                 │  D until required valid
└───────────────────────────────┘
```
States: **V** required + WhatsApp format · **L** save · **Er** save fail retry · **D** until
valid. Interruption resumes here (FR-009, US1 #5).

---

## C05 — Address Setup / Delivery Address Form  `[FR-008, C6, FR-033]`

```
┌───────────────────────────────┐
│        Delivery address    2/2  │  (onboarding) — also reused: Profile, Checkout
├───────────────────────────────┤
│  Delivery area *               │
│  [ Select area           ▾ ]   │  ACTIVE areas only (FR-033, BR-009)
│    → sheet: ▢ Nasr City (30 ج)│
│            ▢ Maadi   (40 ج)   │
│  Address line *                │
│  [___________________________] │
│  Building / location           │
│  [___________________________] │
│  Floor        Apt/Shop/Unit     │
│  [______]     [______________]  │
│  Landmark                      │
│  [___________________________] │
│  Delivery notes                │
│  [___________________________] │
│                                │
│  [▸  Save & continue ]          │
└───────────────────────────────┘
```
States: **L** areas / save · **E** no active areas → "Delivery isn't available yet — contact
us" · **V** required + area · **Er** save fail · **D** until valid.
Onboarding → C06 Home · Profile → back to C17 · Checkout → back to C11.

---

## C06 — Home  `[FR-011, FR-024, D1]`

```
┌───────────────────────────────┐
│  Company        🔔    (WhatsApp)│  header
│  ┌───────────────────────────┐ │
│  │ 🔍 Search products / brand │ │  persistent search → C07
│  └───────────────────────────┘ │
├───────────────────────────────┤
│  Shop by category   (See all ›)│
│  [🧊 Frozen][🍟 Fries][🥫 Sauces]│  shortcuts → C08
│  [🧀 Dairy ][🛢 Oils ][📦 Packg ]│
├───────────────────────────────┤
│  Offers ‹offer›     (See all ›) │  active-offer products only (D1)
│  ┌─────────┐┌─────────┐         │
│  │▭  ‹offer›││▭  ‹offer›│  →     │
│  │Ketchup 5L││Fries 2.5 │        │
│  │180 ج    ││from 170  │        │
│  │ (عرض ›) ││ (عرض ›) │        │  tap card → C09 (view only)
│  └─────────┘└─────────┘         │
├───────────────────────────────┤
│  Browse all products  ›         │
├───────────────────────────────┤
│ 🏠Home  ▦Categories  🛒Cart²  📦Orders  👤 │  bottom nav (Cart badge=2)
└───────────────────────────────┘
```
States: **L** skeleton shortcuts/offers · **E** no offers → hide strip; no categories →
browse-all · **Er** retry · **OoS** offer item sold out (label, view only).

---

## C07 — Search  `[FR-012, US2 #2]`

```
┌───────────────────────────────┐
│ ‹  [🔍 keto|              ] (✕) │  live query, Latin digits ok
├───────────────────────────────┤
│  Results (3)                   │
│  ┌───────────────────────────┐ │
│  │▭ ‹offer› Ketchup 5L        │ │  product card (compact, §8)
│  │ Heinz · Bottle 5L          │ │
│  │ 180 ج            (عرض ›) │ │  tap card → C09 (view only)
│  ├───────────────────────────┤ │
│  │▭ ‹out of stock› Ketchup 1L │ │  OoS labelled, view only
│  │ Heinz · Bottle 1L          │ │
│  │ 60 ج             (عرض ›) │ │
│  └───────────────────────────┘ │
├───────────────────────────────┤
│ 🏠  ▦  🛒²  📦  👤              │
└───────────────────────────────┘
```
Empty (no results): `🔍  No products match "xyz" — check spelling or browse categories.`
States: **L** searching · **E** no results · **Er** retry · **OoS** in results. No filters (§19).

---

## C08 — Product Listing (category)  `[FR-011, US2, FR-017]`

```
┌───────────────────────────────┐
│ ‹  Fries               🔍  🛒² │  category context header
├───────────────────────────────┤
│  12 products                   │
│  ┌───────────────────────────┐ │
│  │ ▭▭▭▭      ‹offer›          │ │  PRODUCT CARD (§8)
│  │ McCain                     │ │  brand
│  │ French Fries 9x9           │ │  name
│  │ Bag 2.5 KG                 │ │  default unit
│  │ from 170 ج   ~180~       │ │  applied best / prev price
│  │                   (عرض ›) │ │  tap card → C09 (view only)
│  ├───────────────────────────┤ │
│  │ ▭▭▭▭   ‹out of stock›      │ │
│  │ Farm Frites · Bag 2.5 KG   │ │
│  │ 165 ج             (عرض ›) │ │  OoS card → view only (FR-015)
│  └───────────────────────────┘ │
│         ⌄ load more            │
├───────────────────────────────┤
│ 🏠  ▦  🛒²  📦  👤              │
└───────────────────────────────┘
```
States: **L** skeleton cards · **E** empty/inactive category → "No products here yet" (not
error) · **Er** retry · **D/OoS** per card. Tap card → C09.

---

## C09 — Product Details  `[US3, FR-018..FR-025, FR-069]`

```
┌───────────────────────────────┐
│ ‹                       🔍  🛒² │
├───────────────────────────────┤
│  ▭▭▭▭▭▭ IMAGE ▭▭▭▭▭▭  ‹offer›  │  availability + offer badge
│  McCain                        │  brand
│  French Fries 9x9              │  name
│  ‹Available›                   │  availability (FR-014)
│  Crispy 9mm cut … (more ›)     │  description (collapsible)
├───────────────────────────────┤
│  Selling unit                  │  UNIT SELECTOR (FR-018/22)
│  [● Bag 2.5 KG] [ Carton ×6 ]  │  one selected; re-prices below
├───────────────────────────────┤
│  Quantity                      │  QTY STEPPER (FR-021)
│        �detected tier ↓         │
│      ( − )   [  5  ]   ( + )    │  Latin digits
├───────────────────────────────┤
│  Wholesale price (Bag 2.5 KG)  │  TIER TABLE (FR-019), active row *
│   1–4    180 ج               │
│  *5–9    170 ج  ◀ current    │
│  10+     160 ج               │
│  Offer price     175 ج ‹offer›│
│  ─────────────────────────────  │
│  Your price      170 ج        │  APPLIED BEST = lower-of (C1/BR-011)
│   ‹best price applied · tier›   │  source tag; offer & tier don't stack
│  You save        10 ج / unit  │  savings vs normal
│  ─────────────────────────────  │
│  Line total   5 × 170 = 850 ج │  live (FR-020)
├───────────────────────────────┤
│  [▸  Add to cart · 850 ج ]    │  sticky; D on OoS/inactive
└───────────────────────────────┘
```
Flat-price unit variant: no tier table, single "Price 60 ج", no tier messaging (US3 #6).
States: **L** price/detail · **V** qty bounds · **Er** load/add · **D/OoS** Add disabled with
reason.

---

## C10 — Cart  `[US3, FR-026..FR-031, C7]`

```
┌───────────────────────────────┐
│  Cart (2)                 🗑    │
│  ⓘ Prices are estimates — final │  estimate label (FR-028)
│    total confirmed at checkout │
├───────────────────────────────┤
│ ┌───────────────────────────┐  │
│ │▭ McCain French Fries       │  │
│ │  Bag 2.5 KG   ‹best·tier›  │  │  selected unit + price tag
│ │  ( − ) [ 5 ] ( + )         │  │  qty control (live re-price)
│ │  170 ج/unit   850 ج  ✕ │  │  unit price · line total · remove
│ ├───────────────────────────┤  │
│ │▭ Heinz Ketchup 5L ‹offer›  │  │
│ │  Bottle 5L                 │  │
│ │  ( − ) [ 2 ] ( + )         │  │
│ │  175 ج/unit   350 ج  ✕ │  │
│ ├───────────────────────────┤  │
│ │▭ Farm Frites  ‹unavailable›│  │  availability flag (FR-029)
│ │  excluded from checkout    │  │  (US3 #7)
│ │  (remove)                  │  │
│ └───────────────────────────┘  │
├───────────────────────────────┤
│  Product subtotal   1,200 ج    │  effective, excl. delivery (C7)
│  ▓▓▓▓▓▓▓▓▓▓░░  Min 500 ✓        │  minimum-order progress (FR-031)
│  ⓘ Delivery fee shown at checkout│  NO delivery amounts in cart
├───────────────────────────────┤
│  [▸  Continue to delivery ]     │  → C11; D below min / unresolved items
│ 🏠  ▦  🛒²  📦  👤              │
└───────────────────────────────┘
```
Below-minimum variant: `▓▓▓░░░  Add 120 ج to reach the 500 ج minimum (products only)` +
CTA disabled. Empty: friendly empty + "Browse products". States: **L E V Er D OoS**.

---

## C11 — Checkout Step 1: Delivery  `[US4, FR-038..FR-041, D3]`

```
┌───────────────────────────────┐
│ ‹ Cart      Checkout · 1 of 2   │  step indicator (nav suppressed)
├───────────────────────────────┤
│  Customer                      │
│  Al Karam Restaurant           │  business name
│  Ahmed (contact) · +20 10 …    │  contact person · mobile
│  WhatsApp +20 12 …             │
├───────────────────────────────┤
│  Delivery address     (Edit ›) │  saved default → C05 sheet
│  Nasr City · St 9, Bldg 4      │
│  Floor 2 · Shop 3 · nr Mosque  │
│  Area: Nasr City               │  (from address)
├───────────────────────────────┤
│  Delivery date                 │
│  [ Sun 20 Sep          ▾ ]     │  no past dates (FR-039)
│  Delivery slot                 │
│  ( 10–1 )  (●1–4)  ( 4–7 )     │  ACTIVE slots only (FR-040)
├───────────────────────────────┤
│  Payment                       │
│  ● Cash on Delivery            │  only method (BR-008)
├───────────────────────────────┤
│  [▸  مراجعة الطلب ]              │  → C12 ; D until date+slot valid
└───────────────────────────────┘
```
States: **L** areas/slots · **E** no slots for date → "No slots on this date — pick another"
· **V** missing date/slot · **Er** area inactive (FR-033) / slot gone (FR-039) · **D** CTA
gated · **OoS** unavailable item → back to Cart.

---

## C12 — Checkout Step 2: Review & Confirm  `[US4, FR-041..FR-047, D3]`

```
┌───────────────────────────────┐
│ ‹ Delivery   Review · 2 of 2    │  final commercial surface
├───────────────────────────────┤
│  Al Karam Restaurant · Ahmed   │  compact customer info
│  Nasr City · St 9, Bldg 4 …    │  delivery address
│  Sun 20 Sep · 1–4 PM           │  date · slot
├───────────────────────────────┤
│  Items (2)                     │
│  McCain French Fries           │
│   Bag 2.5 KG · ×5              │  unit · quantity
│   170 ج/unit        850 ج  │  final unit price · line total
│  Heinz Ketchup 5L ‹offer›      │
│   Bottle 5L · ×2               │
│   175 ج/unit        350 ج  │
├───────────────────────────────┤
│  Product subtotal     1,200 ج │
│  Base delivery fee       30 ج │
│  Delivery discount      −20 ج │  (BR-004, largest-saving rule)
│  Final delivery fee      10 ج │
│  ─────────────────────────────  │
│  Order total          1,210 ج │  final total (FR-041)
│  Payment: Cash on Delivery      │
├───────────────────────────────┤
│  [▸  تأكيد الطلب ]               │  Confirm (FR-042); D if min unmet
└───────────────────────────────┘
```
States: **L** revalidating · **V** below min (Confirm disabled + shortfall) · **Er** network →
failure/retry, **no false success** (FR-047) · **D** gated · **Changed-terms** → see next
frame. → C13 on success.

---

## Changed Commercial Terms — state of C12  `[FR-043, FR-044, US4 #3]`

```
┌───────────────────────────────┐
│ ‹ Delivery   Review · 2 of 2    │
├───────────────────────────────┤
│ ⚠ Some details changed          │  first-class changed-terms state
│  Please review before confirming│
│  ─────────────────────────────  │
│  • Ketchup 5L offer expired     │  expired offer (FR-044)
│      175 → 180 ج              │
│  • Fries unit price changed     │  price change
│      170 → 175 ج              │
│  • Delivery discount changed    │  changed delivery discount
│      −20 → −0 ج               │
│  • Slot 1–4 PM no longer avail. │  unavailable slot → back to Step 1
│  ─────────────────────────────  │
│  New order total      1,285 ج │  updated total
├───────────────────────────────┤
│  ( Back to delivery )           │  fix slot/date
│  [▸  Review updated order ]     │  must re-review; NOT auto-confirmed
└───────────────────────────────┘
```
Also covers: stock/availability change (item → out of stock), selling-unit change. Customer is
never silently confirmed; must explicitly accept updated terms.

---

## C13 — Order Success  `[FR-048]`

```
┌───────────────────────────────┐
│            ✓                    │
│      Order placed!             │
│                                │
│  Order #  ORD-100482           │  order number (Latin digits)
│  Total    1,210 ج            │
│  Delivery Sun 20 Sep · 1–4 PM  │  date · slot
│  Address  Nasr City · St 9 …   │
│  Status   ‹New›                │  current status
│                                │
│  [▸  View order details ]       │  → C15
│  ( Continue shopping )          │  → C06
└───────────────────────────────┘
```
Reached only on confirmed success (failures handled at C12; never shown as success).

---

## C14 — Orders List  `[US9, FR-052]`

```
┌───────────────────────────────┐
│  My orders                      │
├───────────────────────────────┤
│ ┌───────────────────────────┐  │
│ │ ORD-100482        ‹New›    │  │  number · status badge
│ │ Sun 20 Sep      1,210 ج  │  │  date · total
│ ├───────────────────────────┤  │
│ │ ORD-100455    ‹Delivered›  │  │
│ │ Wed 16 Sep        820 ج  │  │
│ ├───────────────────────────┤  │
│ │ ORD-100440    ‹Cancelled›  │  │
│ │ Mon 14 Sep        540 ج  │  │
│ └───────────────────────────┘  │
├───────────────────────────────┤
│ 🏠  ▦  🛒  📦Orders  👤         │
└───────────────────────────────┘
```
States: **L** skeleton rows · **E** "No orders yet — start ordering" · **Er** retry. Tap → C15.

---

## C15 — Order Details  `[US9, FR-051, FR-050, BR-012]`

```
┌───────────────────────────────┐
│ ‹ Orders     ORD-100482         │
├───────────────────────────────┤
│  Status: ‹New›                 │
│  New ─○─ Confirmed ─ Preparing  │  read-only timeline (no live track)
│   ─ Out for delivery ─ Delivered│
├───────────────────────────────┤
│  Delivery                      │
│  Sun 20 Sep · 1–4 PM           │
│  Nasr City · St 9, Bldg 4 …    │
├───────────────────────────────┤
│  Items (historical snapshot)   │  immutable (FR-051)
│  McCain Fries · Bag 2.5 KG     │
│   ×5 · 170 ج      850 ج    │
│  Heinz Ketchup 5L · Bottle 5L  │
│   ×2 · 175 ج ‹offer›  350 ج│
├───────────────────────────────┤
│  Product subtotal   1,200 ج  │
│  Base delivery         30 ج  │
│  Delivery discount    −20 ج  │
│  Final delivery        10 ج  │
│  Order total        1,210 ج  │
│  Payment: Cash on Delivery      │
├───────────────────────────────┤
│  [ Cancel order ]               │  ONLY if status = New (BR-012)
│  ⓘ To change a confirmed order, │  else: reason shown, action hidden
│    contact us on WhatsApp       │
└───────────────────────────────┘
```
States: **L** load · **V** cancel-confirm dialog · **Er** load/cancel · **D** Cancel hidden/
disabled when not allowed with reason. No Buy Again (§19).

---

## C16 — Profile  `[FR-007, FR-008, C5, C6]`

```
┌───────────────────────────────┐
│  Profile                        │
├───────────────────────────────┤
│  Al Karam Restaurant           │  business / restaurant name
│  Contact: Ahmed                │  contact person
│  Login: +20 10 1234 5678       │  read-only
│  WhatsApp: +20 12 987 6543     │
│                    (Edit ›)    │  → profile edit
├───────────────────────────────┤
│  Default delivery address      │
│  Nasr City · St 9, Bldg 4 …    │  single default (C6)
│                    (Edit ›)    │  → C17 / C05
├───────────────────────────────┤
│  Business & contact info  ›    │  from Settings
│  ( Sign out )                  │
├───────────────────────────────┤
│ 🏠  ▦  🛒  📦  👤Profile        │
└───────────────────────────────┘
```
No add-address / multi-address UI (C6). States: **L V Er D**.

---

## Cross-screen components (reference)

```
PRODUCT CARD          OFFER BADGE   AVAILABILITY        QTY STEPPER
┌──────────────┐      ‹offer›       ‹Available›         ( − ) [ 5 ] ( + )
│ ▭  ‹offer›   │      (green tag)   ‹Out of stock›(gray) UNIT SELECTOR
│ Brand        │                    ‹Inactive›(hidden)  [● Bag 2.5][ Carton ]
│ Name         │      MIN-ORDER PROGRESS               PRICE BLOCK (best)
│ Unit         │      ▓▓▓▓▓░░ Add 120 ج to reach 500  Your price 170 ج
│ from X ج     │                                      ‹best · tier›
│    (عرض ›)   │      TOAST: ✓ Added to cart · (Undo)   You save 10 ج
└──────────────┘      OFFLINE BANNER: ⚠ You're offline
(Card opens Product Details — no add-to-cart on the card. Toast/Undo belongs to Details add.)
```

All monetary values shown are illustrative and Latin-digit formatted (`1,210 ج`) per D2.
Frames are structural; visual styling is defined in `design-system.md`.
