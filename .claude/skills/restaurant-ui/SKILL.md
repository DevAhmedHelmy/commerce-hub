---
name: restaurant-ui
description: Project frontend/UI skill for the Restaurant Supplies PWA. Use for all Blade, Tailwind, Alpine, Filament UI, landing-page, responsive, PWA, RTL, dark-mode, accessibility, and visual-QA work. Enforces centralized design tokens, Emdad Food branding, responsive dashboard/customer PWA behavior, installability, and cross-device quality gates.
---

# Restaurant Supplies PWA — UI / Frontend Skill

## 1. Authority

Use this skill for every frontend/UI task.

Read and obey:
- `specs/001-restaurant-supplies-mvp/design/`
- `specs/001-restaurant-supplies-mvp/spec.md`
- `specs/001-restaurant-supplies-mvp/plan.md`
- `specs/001-restaurant-supplies-mvp/tasks.md`

This skill does not override approved business rules.

When the official `frontend-design` skill is available, use it together with this skill. This project skill has priority for brand, RTL, responsive, PWA, and approved UX constraints.

Laravel application root:
`src/`

---

# 2. Frontend Stack

Customer/public:
- Blade
- Tailwind CSS
- Alpine.js only for lightweight UI interaction

Admin:
- Filament

Do not introduce:
- React
- Vue
- Inertia SPA
- separate frontend app

Business/commercial truth remains server-side.

---

# 3. Central Design Tokens — Mandatory

All reusable visual values must come from:

`src/resources/css/theme.css`

Imported by:

`src/resources/css/app.css`

The theme file is the SINGLE source of truth for:
- brand colors
- semantic colors
- gradients
- surfaces
- text colors
- borders
- focus colors
- shadows
- radii
- reusable spacing/timing tokens where useful

Do not scatter raw HEX values throughout Blade, Alpine, Filament, JavaScript, or feature CSS.

Changing `theme.css` must update the whole application.

---

# 4. Emdad Food Brand Palette

Initial brand tokens:

```css
--brand-blue-950: #022C68;
--brand-blue-800: #03488E;
--brand-blue-600: #0264B1;
--brand-blue-400: #098FDC;

--brand-green-700: #0D7A2A;
--brand-green-500: #46A831;
```

Use semantic aliases such as:
- `--color-primary`
- `--color-primary-strong`
- `--color-success`
- `--color-bg`
- `--color-surface`
- `--color-text`
- `--color-text-muted`
- `--color-border`
- `--color-danger`
- `--color-warning`
- `--color-focus`

Do not use the previous green/orange identity as the primary brand.

---

# 5. Gradients

Provide central gradients in `theme.css`, e.g.:

```css
--gradient-brand-blue:
    linear-gradient(
        135deg,
        var(--brand-blue-950) 0%,
        var(--brand-blue-600) 58%,
        var(--brand-blue-400) 100%
    );

--gradient-brand-green:
    linear-gradient(
        135deg,
        var(--brand-green-700) 0%,
        var(--brand-green-500) 100%
    );

--gradient-brand-mixed:
    linear-gradient(
        135deg,
        var(--brand-blue-950) 0%,
        var(--brand-blue-600) 52%,
        var(--brand-green-500) 100%
    );
```

Use gradients intentionally, not everywhere.

Good uses:
- hero accents
- primary CTA
- progress/brand accents
- selected/high-value commercial highlights

Avoid gradient overload.

---

# 6. System Dark Mode — Mandatory

Dark mode follows the OS/browser automatically using:

```css
@media (prefers-color-scheme: dark) {
    :root {
        /* dark semantic token overrides */
    }
}
```

MVP:
- no manual theme toggle required
- no localStorage theme preference
- no DB theme preference

Every component must use semantic tokens so light/dark works globally.

Never hard-code light-only white/black surfaces inside feature components.

Dark mode must preserve accessible contrast.

---

# 7. Arabic / RTL

Arabic is the only exposed MVP language.

Requirements:
- `<html dir="rtl" lang="ar">`
- Cairo font
- logical CSS start/end where practical
- correct RTL navigation/icons
- no manual string reversal
- future LTR compatibility

Mixed Arabic + English brands + Latin digits + units must render correctly.

Examples:
- Heinz
- Farm Frites
- 2.5 KG
- SKU codes
- phone numbers

---

# 8. Currency

Exact Arabic MVP examples:

`444 ج`
`1,250 ج`
`12,500 ج`

Do not use:
- EGP
- LE
- ج.م
- م.ج
- Arabic-Indic digits

Use centralized money formatting.

---

# 9. Responsive Design Is a Definition-of-Done Requirement

Both the CUSTOMER PWA and ADMIN DASHBOARD must be fully responsive.

Do not interpret responsive as merely shrinking elements.

Layouts must recompose intelligently per viewport.

Minimum QA widths:

- 360 px
- 390 px
- 768 px
- 1024 px
- 1280 px
- 1440 px

No page is complete until it works across these classes of viewport.

---

# 10. Customer PWA — Responsive Behavior

The Customer PWA must work as:
- installed mobile PWA
- mobile browser
- tablet browser
- laptop browser
- desktop browser

Do not build a mobile-only layout.

Mobile-first implementation is required, but desktop/laptop must use available space well.

Examples:
- product grids increase columns progressively
- desktop checkout may use content + summary columns
- mobile checkout stacks vertically
- wide screens should not stretch content indefinitely; use sensible max-width containers
- navigation behavior adapts by breakpoint

---

# 11. Admin Dashboard — Responsive Behavior

Filament/admin must remain usable from:
- laptop
- desktop
- tablet
- mobile when necessary

Dashboard rules:
- sidebar becomes collapsible/drawer at smaller widths
- dashboard cards reflow from multi-column → fewer columns → single column
- filters must remain usable on small screens
- action buttons must not overflow
- forms must reflow cleanly
- dialogs/modals must fit viewport
- charts/widgets must not force horizontal page overflow

Tables:
- prioritize key columns
- allow appropriate column hiding/toggling
- use stacked/mobile representations where practical
- controlled horizontal scrolling only when unavoidable
- never rely on a 1400px-wide table as the only usable interface

Do not treat desktop-only admin as acceptable.

---

# 12. Product Grid

Grid should adapt approximately to available width.

Conceptually:
- mobile: 1–2 cards
- tablet: 2–3 cards
- laptop: 3–4 cards
- wide desktop: 4–5 cards where appropriate

Do not hard-code fixed card widths that break smaller screens.

---

# 13. Touch Targets

Interactive mobile controls should generally provide a target around 44×44 CSS px or larger when practical.

Applies to:
- buttons
- quantity controls
- nav items
- icon buttons
- close buttons
- selectors

Do not create tiny click targets.

---

# 14. No Hover-Only Interaction

No required action or information may depend on hover.

Hover may enhance desktop UI, but the same feature must remain discoverable and usable on touch devices.

---

# 15. Safe Areas

Installed mobile PWA must account for device safe areas.

Use environment insets where required, especially for:
- bottom navigation
- sticky checkout buttons
- fullscreen/standalone layouts

Example:

```css
padding-bottom: env(safe-area-inset-bottom);
```

Do not let UI sit beneath iPhone/Android system areas.

---

# 16. Mobile Keyboards

Forms and sticky CTAs must tolerate the mobile keyboard.

Avoid:
- buttons hidden behind keyboard
- forced full-height layouts that break when keyboard opens
- input fields hidden under sticky elements

Use appropriate input types:
- `tel`
- `email`
- numeric modes where relevant

---

# 17. PWA Installability — Mandatory

The app must be installable where browser/platform support permits.

Required:
- valid `manifest.webmanifest`
- name
- short_name
- start_url
- scope
- display: standalone
- theme_color
- background_color
- 192×192 icon
- 512×512 icon
- maskable icon support where planned
- HTTPS in production
- registered service worker
- valid app shell/offline fallback

Do not claim PWA completion without installability verification.

---

# 18. Desktop PWA

Where supported, the same PWA should be installable from compatible desktop browsers such as Chromium-based desktop browsers.

Installed desktop mode must remain usable:
- proper max widths
- responsive navigation
- no mobile-only assumptions
- correct standalone window layout

---

# 19. iOS Installation

Do not assume iOS uses the same install prompt behavior as Android/Desktop.

Ensure Safari-compatible PWA metadata and document the expected Add to Home Screen path where needed.

Do not build functionality that depends on a Chromium-only install event.

---

# 20. PWA Update Strategy

Service-worker updates must not leave users indefinitely on stale assets.

Implement a simple safe update strategy appropriate to MVP.

If an update is detected, support a clear update/refresh path.

Do not silently reload during checkout/order submission.

Never risk duplicate orders because of a service-worker refresh.

---

# 21. PWA Caching Privacy

NEVER cache sensitive authenticated/customer-specific HTML/API responses such as:
- OTP/auth
- profile
- addresses
- cart
- checkout
- order history
- order details

Safe caching should focus on:
- versioned CSS/JS
- icons
- logo
- safe public assets
- offline fallback

Dynamic authenticated flows remain network-driven.

No offline order creation.
No background order sync.

---

# 22. Offline Behavior

Offline state should be honest.

The app may show:
- offline fallback
- connection unavailable message

It must never:
- fake order success
- queue order creation silently
- show stale checkout data as authoritative

---

# 23. Customer Navigation

Mobile bottom navigation:
- الرئيسية
- التصنيفات
- السلة
- طلباتي
- حسابي

Search is not a sixth tab.

Hide bottom nav during checkout.

On larger screens, navigation may adapt according to approved designs without changing information architecture.

---

# 24. Product Cards

No Quick Add.

Flow:
Product Card
→ Product Details
→ Unit
→ Quantity
→ Effective Price
→ Add to Cart

Cards show:
- image
- name
- brand
- availability
- lightweight pricing
- offer state if applicable

---

# 25. Cart

Show:
- products
- units
- quantities
- effective unit prices
- line totals
- effective subtotal
- minimum-order progress

Do NOT show delivery fee/discount estimates.

---

# 26. Checkout

Exactly two conceptual steps:
1. Delivery
2. Review & Confirm

On desktop:
- summary may be side-by-side where appropriate

On mobile:
- stack sections
- CTA may be sticky if it does not cover content/navigation/safe area

Changed commercial terms must force review again.

---

# 27. Forms

Forms require:
- visible labels
- correct input types
- Arabic validation
- visible errors
- focus states
- responsive widths
- accessible tap targets

Do not use placeholders as the only labels.

---

# 28. States

Implement and test:
- loading
- empty
- no search results
- no orders
- server/network error
- unavailable product
- inactive area
- inactive slot
- minimum order not met
- changed commercial terms
- action in progress
- disabled CTA

---

# 29. Accessibility

Must include:
- semantic HTML
- heading hierarchy
- form labels
- keyboard support
- visible focus
- adequate contrast
- alt text
- proper button/link semantics
- no color-only status communication
- reduced-motion respect where practical

---

# 30. Images

Product images:
- preserve aspect ratio
- consistent containers
- avoid distortion
- lazy load where suitable
- alt text
- graceful missing-image state

---

# 31. Blade Components

Prefer reusable components for:
- Header
- Bottom Nav
- Product Card
- Price Display
- Offer Badge
- Status Badge
- Quantity Selector
- Unit Selector
- Empty State
- Error State
- Loading State
- Form Field
- Order Card
- Section Heading
- CTA/Button

All components consume semantic theme tokens.

---

# 32. Alpine.js

Use Alpine only for lightweight UI state.

Alpine is NOT authoritative for:
- final pricing
- offer eligibility
- delivery discount
- minimum order
- checkout totals
- order creation

---

# 33. Filament

Admin is Arabic-first and uses the same brand direction.

Avoid a disconnected admin visual identity.

Business logic stays in domain/application services.

---

# 34. Browser / Visual QA

When Playwright/browser automation is available, visually inspect representative pages at:

- 390×844
- 768×1024
- 1024×768
- 1440×900

Also verify both:
- light system theme
- dark system theme

Check:
- overflow
- clipped Arabic
- bidi problems
- grid wrapping
- sidebar behavior
- table behavior
- bottom-nav overlap
- sticky CTA overlap
- safe areas
- image distortion
- dark-mode contrast
- focus states
- broken dialogs
- PWA standalone layout

Fix issues before marking tasks complete.

---

# 35. Frontend Build Gate

For every frontend phase:
- run frontend production build
- fail the phase on build error
- run relevant PHP/tests
- run responsive/visual checks where browser tooling is available

Do not commit broken frontend assets/source.

---

# 36. Definition of Done — UI

Frontend work is complete only if:

- approved UX is preserved
- central theme tokens are used
- no unnecessary raw brand colors are scattered
- light mode works
- dark system mode works
- RTL works
- mobile works
- tablet works
- laptop works
- desktop works
- Dashboard is responsive
- Customer PWA is responsive
- major UI states exist
- touch targets are usable
- hover is not required
- safe areas are respected
- relevant build/tests pass
- no obvious visual regression remains

PWA work additionally requires:
- installability checks
- manifest validity
- service-worker registration
- safe caching
- standalone mobile usability
- standalone desktop usability where supported
