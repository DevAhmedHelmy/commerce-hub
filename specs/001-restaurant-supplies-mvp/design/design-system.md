# Design System — Restaurant Supplies Ordering MVP

**Feature**: `001-restaurant-supplies-mvp`
**Sources of truth**: [`../spec.md`](../spec.md), [`ux-architecture.md`](./ux-architecture.md)
**Type**: Visual + component system (design tokens & specs). No application code.
**Status**: Approved for Technical Planning
**Created**: 2026-09-17

Design intent (per `[FR-070]`): modern, clean, trustworthy, commercial, food-distribution
appropriate, strong on mobile — **not** childish, **not** luxury, **not** visually noisy. Calm
neutrals + one confident brand green + a controlled warm accent reserved for offers/savings.

> Tokens below are a design contract (names + values) for the later implementation phase; they
> are not a technical/framework decision. RTL and Latin-digit rules (D2) are baked in.

---

## 1. Color Palette

### 1.1 Brand
| Token | HEX | Use |
|-------|-----|-----|
| `brand/700` | `#0F5A34` | Pressed states, text on light brand tint |
| `brand/600` | `#137A46` | **Primary brand green** — primary buttons, active nav, links |
| `brand/500` | `#1B9153` | Hover/lighter primary |
| `brand/100` | `#E6F2EB` | Brand tint backgrounds (selected chips, banners) |
| `brand/50`  | `#F2F8F4` | Subtle section background |

Primary brand color = **`#137A46`** (a confident, food-fresh green that reads professional and
trustworthy without being playful or luxury).

### 1.2 Accent (offers / savings only — used sparingly)
| Token | HEX | Use |
|-------|-----|-----|
| `accent/600` | `#C2610F` | Offer text on light, savings emphasis |
| `accent/500` | `#E1791F` | **Offer badge / promoted accent** |
| `accent/100` | `#FBEFE1` | Offer badge background tint |

### 1.3 Neutrals (slate)
| Token | HEX | Use |
|-------|-----|-----|
| `ink/900` | `#0F172A` | Primary text, headings |
| `ink/700` | `#334155` | Body text |
| `ink/500` | `#64748B` | Secondary text, captions |
| `ink/400` | `#94A3B8` | Placeholder, disabled text, hints |
| `line/300` | `#CBD5E1` | Strong borders |
| `line/200` | `#E2E8F0` | **Default border/divider** |
| `surface/100` | `#F1F5F9` | App background, skeletons |
| `surface/50` | `#F8FAFC` | Card alt background |
| `surface/0` | `#FFFFFF` | Cards, sheets, inputs |

### 1.4 Semantic
| Token | HEX | Use |
|-------|-----|-----|
| `success/600` | `#16A34A` | Success, "Available", confirmations |
| `success/100` | `#DCFCE7` | Success background |
| `warning/600` | `#D97706` | Warnings, changed-terms, min-order not met |
| `warning/100` | `#FEF3C7` | Warning background |
| `danger/600` | `#DC2626` | Errors, destructive (remove/cancel), OoS emphasis |
| `danger/100` | `#FEE2E2` | Error background |
| `info/600` | `#2563EB` | Informational notices, estimate hints |
| `info/100` | `#DBEAFE` | Info background |

### 1.5 Functional status mapping
- **Available** → `success/600` text on `success/100`.
- **Out of Stock** → `ink/500` text on `surface/100` (neutral, not alarming) + disabled add.
- **Inactive** → never shown to customers (`[FR-016]`).
- **Offer/Promoted** → `accent` family.
- **Order statuses** (chips): New → `info`; Confirmed → `brand`; Preparing → `warning`; Out for
  Delivery → `accent`; Delivered → `success`; Cancelled → `danger` (muted). Never color-only —
  each chip pairs an icon/label `[§18]`.

Contrast: body text `ink/700`/`ink/900` on `surface/0/50` meets ≥ 4.5:1; primary button text is
white on `brand/600` (≥ 4.5:1); status chips pair color + text/icon (never color alone).

---

## 2. Typography

### 2.1 Font family
- **Primary (Arabic + Latin): `Cairo`** — a modern, highly legible dual-script family with full
  Arabic support and matching Latin glyphs, professional (not childish/luxury), free to use.
- **Fallback stack**: `Cairo, "Noto Sans Arabic", "Segoe UI", system-ui, -apple-system,
  "Helvetica Neue", Arial, sans-serif`.
- **Numerals**: Latin digits `0-9` everywhere (D2); Cairo renders them cleanly. Use
  **tabular/lining figures** for prices, quantities, order numbers, and totals so columns align.
- Mixed Arabic/Latin (brand names, "KG", sizes) uses bidi isolation so numbers/units don't
  reorder `[§17]`.

### 2.2 Type scale (mobile baseline; rem @ 16px root)
| Token | Size | Line | Weight | Use |
|-------|------|------|--------|-----|
| `display` | 28px / 1.75rem | 34px | 700 | Landing hero |
| `h1` | 24px / 1.5rem | 32px | 700 | Screen titles |
| `h2` | 20px / 1.25rem | 28px | 700 | Section headers |
| `h3` | 18px / 1.125rem | 26px | 600 | Card titles, product name |
| `body` | 16px / 1rem | 24px | 400 | Default text, inputs |
| `body-strong` | 16px | 24px | 600 | Emphasis, selected unit |
| `caption` | 14px / 0.875rem | 20px | 400/600 | Secondary, labels, brand |
| `micro` | 12px / 0.75rem | 16px | 600 | Badges, chip text, helper |
| `price-lg` | 20px | 28px | 700 | Applied "Your price", totals |
| `price-md` | 16px | 24px | 700 | Line totals, card price |
| `price-strike` | 14px | 20px | 400 | Previous price (strike, `ink/400`) |

Min body size 16px to prevent mobile zoom; never below 12px.

---

## 3. Spacing, Radii, Borders, Shadows

### 3.1 Spacing scale (4px base)
`space/1`=4 · `2`=8 · `3`=12 · `4`=16 · `5`=20 · `6`=24 · `8`=32 · `10`=40 · `12`=48.
Screen gutter (mobile) = `space/4` (16). Card padding = `space/4`. Section gap = `space/6`.

### 3.2 Radii
`radius/sm`=8 (chips, inputs, buttons) · `radius/md`=12 (cards) · `radius/lg`=16 (sheets,
modals) · `radius/pill`=999 (badges, status chips, stepper). Consistent, soft-but-serious — no
sharp corners, no oversized playful rounding.

### 3.3 Borders
- Default `1px solid line/200`. Strong/focus `1px solid line/300` or brand.
- Inputs: `1px line/300`; focus `2px brand/600` ring; error `2px danger/600`.
- Selected chips/units: `2px brand/600` + `brand/100` fill.

### 3.4 Shadows (elevation)
| Token | Value | Use |
|-------|-------|-----|
| `elev/0` | none | Flat on-surface elements |
| `elev/1` | `0 1px 2px rgba(15,23,42,.06)` | Cards, product cards |
| `elev/2` | `0 2px 8px rgba(15,23,42,.08)` | Sticky headers, bottom nav |
| `elev/3` | `0 -4px 16px rgba(15,23,42,.10)` | Bottom sheets, sticky CTA bar |
| `elev/4` | `0 8px 32px rgba(15,23,42,.16)` | Modals/dialogs |

Shadows are subtle (low alpha) to stay clean, not noisy.

---

## 4. Icons & Imagery

### 4.1 Icons
- **Line/stroke icon set** (e.g. Lucide/Feather-style), 1.5–2px stroke, 24px default (20px
  dense, 28px primary touch). Consistent metaphors: 🔍 search, 🛒 cart, 📦 orders, 👤 profile,
  ▦ categories, ✕ remove, − / + stepper, ‹ back (mirrors in RTL).
- Icon-only controls always carry accessible labels `[§18]`.
- Directional icons mirror in RTL; symbolic icons do not `[§17]`.

### 4.2 Imagery / ratios
- **Product image: 1:1 square** (card + details), `radius/md`, object-fit contain on
  `surface/50` so packaged goods aren't cropped. Graceful **fallback** (brand-tint block + name
  initial) when missing `[Assumptions]`.
- **Category tile: 1:1** (icon or photo).
- **Landing hero: 16:9** (mobile) / wide crop (desktop), lazy-loaded.
- **Offer thumbnail: 1:1**. No decorative full-bleed lifestyle imagery inside the ordering flow
  (clarity over decoration `[§1]`).

---

## 5. Component System

Each component lists structure + interaction states. States referenced: default, hover/press,
focus, disabled, loading, error/validation, selected, empty, out-of-stock where relevant.

### 5.1 Buttons
- **Variants**: Primary (`brand/600` fill, white text), Secondary (surface fill, `line/300`
  border, `ink/900` text), Tertiary/Text (brand text, no fill), Danger (`danger/600` — remove/
  cancel).
- **Sizes**: lg (48px h, primary CTAs / sticky bars), md (40px), sm (32px, inline).
- **States**: hover (−1 shade), pressed (`brand/700`), **focus** (2px ring), **disabled**
  (`surface/100` fill / `ink/400` text, not interactive, reason available), **loading** (spinner
  + label retained, e.g. "Confirming…"), full-width on mobile primary actions.
- Sticky CTA bars (Add to cart, Checkout CTAs) sit at bottom with `elev/3`, showing the amount
  where relevant (`Add to cart · 850 ج`).

### 5.2 Inputs (text / number / select)
- 44–48px height, `radius/sm`, `1px line/300`, 16px text, label above, helper/error below.
- **States**: focus (2px brand ring), **error** (2px danger + danger helper text + icon),
  disabled (surface/100), filled, with trailing action (clear ✕ / dropdown ▾).
- Number/phone inputs: Latin digits, LTR entry inside RTL field, no cursor jump `[§17]`.

### 5.3 OTP input  `[C03, FR-003]`
- 6 separate boxes (48×48), auto-advance, paste-fill, Latin digits, tabular figures.
- **States**: focus (active box ring), **error** (all boxes danger border + message,
  "incorrect" vs "expired" distinct), disabled during verify, filled.
- Companion: countdown text + **Resend** button (disabled with `00:28` during cooldown).

### 5.4 Search  `[C07]`
- Pill/`radius/sm` field with leading 🔍 and trailing ✕ (clear); persistent on Home/Categories.
- **States**: idle (placeholder), typing, loading (inline spinner), **empty results**
  (illustration + guidance), error (retry).

### 5.5 Category card  `[C06, C08]`
- Compact tile: icon/thumb (1:1) + label; `radius/md`, `elev/1`; active-only `[FR-017]`.
- **States**: default, press, loading (skeleton), empty (hidden / browse-all).

### 5.6 Product card  `[§8]`
- Structure (RTL-aware, top→bottom): image (1:1) with **availability**/**offer** badges → brand
  (`caption`, `ink/500`) → name (`h3`) → default unit (`caption`) → price row (**applied best**
  `price-md`, optional **previous** `price-strike`) → **View-product action** (`عرض ›` / tap the
  whole card) → opens **Product Details (C09)**.
- **No add-to-cart on the card**: the card MUST NOT add to cart, quick-add, auto-add a default
  unit, or open a unit-picker sheet. Unit + quantity + applied price are chosen on Product
  Details `[§8, FR-018..FR-025]`.
- **States**: default, press (navigates to C09), **out-of-stock** (neutral badge + dimmed price,
  still opens Details to view), offer (accent badge + strike prev price), loading (skeleton
  card).

### 5.7 Offer badge  `[D1, FR-024]`
- Pill, `accent/500` on `accent/100`, `micro` weight, label "Offer" / "عرض". Never implies
  popularity (D1). Appears on cards, details, cart, offers strip.

### 5.8 Availability indicator  `[FR-014, FR-015]`
- **Available**: `success` chip. **Out of Stock**: neutral `ink/500` chip + disables ordering.
  **Inactive**: not rendered. Paired with text/icon (not color-only).

### 5.9 Selling-unit selector  `[FR-018, FR-022]`
- Segmented control / chip row; each chip = unit label (e.g. "Bag 2.5 KG", "Carton ×6"); one
  **selected** (2px brand border + `brand/100` fill + check). Selecting re-prices the price block
  and line total.
- **States**: default, selected, disabled (unit unavailable → dimmed + reason), single-unit
  (renders as a static label, no toggle).

### 5.10 Quantity selector  `[FR-021, §18]`
- `( − ) [ n ] ( + )` pill; large tap targets (≥40px), direct numeric entry, Latin digits,
  bounds enforced, announces value changes. Optional inline "tier ↑/↓" hint when crossing a
  tier boundary.
- **States**: default, min-reached (− disabled), max/bounds (+ disabled with reason), invalid
  entry (validation), disabled (OoS).

### 5.11 Pricing block  `[§9, FR-019..FR-025, C1/BR-011]`
- Rows: Normal price → **tier table** (qty range → unit price, active row highlighted) → offer
  price (if any) → divider → **Your price** (`price-lg`, dominant) with **source tag**
  (`best · tier` / `best · offer`) → **You save X** → divider → **Line total** (`n × price =
  total`). Explicitly signals offer & tier **do not stack** (shows both candidates, marks
  winner).
- **States**: tiered, flat (single price, no table), offer-active, best-applied, loading
  (skeleton rows).

### 5.12 Wholesale-price indicator
- The tier table (5.11) + a small "wholesale" label; active tier row uses `brand/100` fill +
  `brand/600` left marker; other rows `ink/700`. Communicates quantity breaks at a glance.

### 5.13 Cart item  `[§10, FR-029]`
- Row: thumb → name + **selected unit** (`body-strong`) + price tag → quantity selector →
  unit price + **line total** → remove ✕.
- **States**: default, updating (re-price spinner on line), **flagged** (OoS/inactive/unit-
  unavailable or price/tier/offer changed → warning strip, excluded from checkout), removed
  (with Undo toast).

### 5.14 Minimum-order progress  `[FR-030, FR-031, C7]`
- Progress bar + label. Met: `success` fill + "Minimum met ✓". Not met: `warning` fill + "Add
  120 ج to reach the 500 ج minimum (products only)". Basis = effective product subtotal
  excl. delivery (C7).

### 5.15 Delivery slot selector  `[C11, FR-040]`
- Date field (no past dates) + slot chips ("10–1", "1–4", "4–7"); **active slots only**; one
  selected (brand). **States**: default, selected, **no slots for date** (empty → pick another),
  slot-became-unavailable (error → reselect), disabled.

### 5.16 Address card  `[C05, C11, C16/C17]`
- Summary block: area + full address lines; **Edit** action. Single default address (C6).
- **States**: default, editing (→ form), empty (prompt to add), area-inactive (warning + fix).

### 5.17 Order card  `[C14]`
- Row: order number (tabular) + status chip; date + total. Tap → details.
- **States**: default, press, loading (skeleton), empty-list (start ordering).

### 5.18 Order status chip  `[FR-049]`
- Pill mapped in §1.5 (New/Confirmed/Preparing/Out for Delivery/Delivered/Cancelled), icon +
  label + color; never color-only.

### 5.19 Bottom navigation  `[§3]`
- 5 items (Home · Categories · Cart · Orders · Profile), 56–64px height, `elev/2`, active =
  `brand/600` icon+label, inactive = `ink/500`; **Cart badge** (count) on the cart icon.
  Mirrored order in RTL. Suppressed during checkout (replaced by sticky CTA bar).

### 5.20 Header / app bar
- Title or logo + contextual actions (back ‹, search, cart). Sticky with `elev/2` on scroll.
  Checkout header shows step indicator ("Checkout · 1 of 2") and back.

### 5.21 Toast / snackbar
- Bottom, `elev/3`, auto-dismiss; success (✓ Added to cart, Undo), error (retry), info. Non-
  blocking; announced for a11y.

### 5.22 Modal / bottom sheet
- **Bottom sheet** (mobile default) for pickers (area, date, slot, address edit): `radius/lg`
  top, drag handle, `elev/3`, scrim. **Modal dialog** for confirmations (cancel order): `elev/4`.
  **States**: open, loading content, error, dismiss.

### 5.23 Skeleton
- `surface/100` blocks with subtle shimmer for cards, rows, price blocks, images. Matches the
  final layout's shape (cards look like cards) to reduce layout shift.

### 5.24 Empty state
- Icon/illustration + short message + primary action (e.g. empty cart → "Browse products";
  no orders → "Start ordering"; empty category → "No products here yet"). Calm, never an error
  style `[US2 #4]`.

### 5.25 Error state
- Icon + plain message + **Retry**. Distinguishes recoverable load errors from validation. The
  offline banner (⚠ You're offline) and the checkout **no-false-success** failure are error-
  family, high-clarity `[FR-047, FR-067]`.

---

## 6. Motion & Feedback
- Short, functional transitions (150–250ms) for sheets, toasts, state changes; respect
  reduced-motion. Feedback for add-to-cart, quantity change, and status change is perceivable
  without relying on color or motion alone `[§18]`.

## 7. Token summary (quick reference)
- **Primary color**: `#137A46` (brand green). **Accent (offers)**: `#E1791F`.
- **Ink**: `#0F172A` / `#334155`. **Border**: `#E2E8F0`. **App bg**: `#F1F5F9`.
- **Font**: `Cairo` (Arabic + Latin), Latin numerals, tabular figures for money.
- **Radii**: 8 / 12 / 16 / pill. **Grid gutter**: 16. **Base spacing**: 4px scale.
- All prices: `1,250 ج` (Latin grouped + `ج`), RTL-safe (D2).

---

## 8. Localization Readiness (RTL now / LTR later) `[D4, ux §21]`

Arabic is the only exposed MVP language and **RTL** is active; the system MUST be designed so
**English (LTR)** can be added later with no redesign. There is **no language switcher** in MVP.
These rules bind the whole component system.

### 8.1 Direction & spacing
- Direction comes from **document/layout direction only** — never from manually reversed Arabic
  strings. All Arabic in this system is stored in normal logical reading order.
- Use **logical** spacing/alignment (start/end, inline-start/inline-end), never hard-coded
  left/right, so components mirror automatically between RTL and LTR.
- **Text expansion**: every label, button, chip, badge, and nav item MUST tolerate longer/shorter
  future English text without truncation or breakage (no widths sized only to Arabic).

### 8.2 Bidi & mixed content
- Components rendering names/units (product card, cart item, order details, search) MUST apply
  **bidi isolation** around Latin tokens (brands like `Heinz`, units like `KG`, sizes `6 × 1 KG`,
  Latin numbers) so mixed Arabic+Latin strings render correctly in both RTL and LTR.

### 8.3 Copy & identifiers
- Every visible string in every component is **translatable presentation copy**, not a business
  value. Components receive labels; they never branch on translated text.
- Status/availability/type values are **language-neutral identifiers** with localized display
  (see ux §21.5). Order Status Badge (5.18) and Availability (5.8) map `new / out_of_delivery /
  available / out_of_stock …` → Arabic now → English later.

### 8.4 Numbers, currency, dates
- Latin digits and tabular figures everywhere (D2); stored values never change with locale.
- Price Display (5.11/5.12): `1,250 ج` in Arabic MVP; the amount + `ج` is a **formatted
  presentation**, so a future English locale can format differently without changing values.
- Any date/time chips accept a **locale-aware formatter** later (no hard-coded month/format).

### 8.5 Per-component readiness (all must handle Arabic RTL, future English LTR, text expansion,
mixed Arabic/Latin, Latin numbers — without redesign)

| Component | Localization note |
|---|---|
| Buttons (5.1) | label expands; full-width mobile absorbs longer text; icon+label order mirrors |
| Inputs (5.2) | logical label/help/error alignment; LTR digit entry in RTL field |
| Phone input | prefix + number stay LTR-numeric inside a mirrored field |
| OTP input (5.3) | boxes remain LTR digit order in both directions; messages translatable |
| Search (5.4) | accepts Arabic/Latin/mixed queries; placeholder translatable |
| Category card (5.5) | label area flexes for longer English names |
| Product card (5.6) | brand (Latin) + name (Arabic/mixed) bidi-safe; view action label translatable |
| Offer badge (5.7) | "عرض"/"Offer" swap by locale; pill sizes to text |
| Availability badge (5.8) | neutral id → localized label; never color-only |
| Selling-unit selector (5.9) | unit **id** neutral (`bag`/`carton`), display localized; chips flex |
| Quantity selector (5.10) | numeric block stays LTR; control mirrors as a block |
| Price display (5.11) | localized currency formatting; values neutral; tier table mirrors |
| Wholesale indicator (5.12) | tier ranges numeric-LTR; labels translatable |
| Cart item (5.13) | unit label + flags translatable; mixed name bidi-safe |
| Minimum-order progress (5.14) | message translatable; number/currency formatted |
| Delivery slot (5.15) | slot/date labels localizable; times formatter-driven |
| Address card (5.16) | displays mixed-language user content; not Arabic-only |
| Order status badge (5.18) | neutral id → Arabic now / English later; icon + text |
| Order card (5.17) | number LTR-numeric; status + date localized |
| Bottom navigation (5.19) | labels expand; order + badges mirror by direction |
| Header (5.20) | title/actions mirror; step indicator ("1 of 2") flips |
| Toast (5.21) | messages translatable; alignment mirrors |
| Modal (5.22) | content mirrors; localized title/actions |
| Bottom sheet (5.22) | content mirrors; drag handle unchanged |
| Skeleton (5.23) | direction-agnostic (no text) |
| Empty state (5.24) | message + CTA translatable; icon non-directional |
| Error state (5.25) | message + Retry translatable; offline banner localized |

No component may require a redesign to switch direction or language.
