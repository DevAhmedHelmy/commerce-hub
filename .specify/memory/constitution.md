<!--
SYNC IMPACT REPORT
==================
Version change: (template / unversioned) → 1.0.0
Bump rationale: Initial ratification. The prior file contained only unfilled
  template placeholders; this is the first concrete, enforceable constitution.

Modified principles: N/A (initial adoption)
Added principles:
  I.    Modular Monolith, API-Ready Core
  II.   Server-Authoritative & Deterministic Commerce Math
  III.  Business Logic Lives in Services
  IV.   Growth-Ready, Data-Driven Domain Model
  V.    Immutable Order Snapshots
  VI.   Security by Default
  VII.  Tests Mandatory for Business-Critical Logic
  VIII. Mobile-First, Accessible, RTL-Ready UX
  IX.   Spec-Driven Development & Controlled MVP Scope
Added sections:
  - Technology & Infrastructure Constraints
  - Code Quality Standards
  - Governance

Templates requiring updates:
  ✅ .specify/templates/plan-template.md   — Constitution Check gate is generic
       and defers its gate content to this constitution file; no edit required.
       The nine principles below are the gates it must enforce.
  ✅ .specify/templates/spec-template.md   — No constitution-driven mandatory
       sections changed; RTL/accessibility/success-criteria slots already exist.
  ✅ .specify/templates/tasks-template.md  — Test-task and phase structure is
       compatible; Principle VII makes business-logic test tasks non-optional
       (see note below), which /speckit-tasks must honor.
  ⚠  Runtime guidance (README.md / quickstart.md) — none present yet;
       create when the first feature plan is written (no action now).

Follow-up TODOs: none. All placeholders resolved.
-->

# Restaurant Supplies PWA Constitution

Mobile-first Progressive Web App for a restaurant-supplies business (frozen foods,
condiments, sauces, dairy, packaging, and related supplies). Starts as an MVP but is
engineered to grow without rewrites. This constitution defines non-negotiable rules.
Every word "MUST" is a hard gate; "SHOULD" is a strong default that requires a
documented justification to deviate from.

## Core Principles

### I. Modular Monolith, API-Ready Core

The system MUST be a single, modular Laravel monolith for the MVP. Microservices,
service meshes, and network-boundary decomposition are PROHIBITED for the MVP.

- Code MUST be organized into cohesive modules/domains (e.g. Catalog, Pricing, Cart,
  Ordering, Delivery, Auth) with explicit boundaries; cross-module calls go through a
  module's public services, not by reaching into another module's internals.
- All business behavior MUST be reachable from a transport-agnostic service layer so a
  future Flutter/mobile app or JSON API can consume it without reimplementing logic.
- Presentation (Blade/Alpine) and the admin panel (Filament) are consumers of that
  service layer, not owners of business rules.

**Rationale**: A well-modularized monolith delivers MVP speed while preserving a clean
extraction path to APIs and, if ever needed, services — without a rewrite.

**Testable gate**: Plans that introduce a second deployable service, or place business
rules where only Blade/HTTP can reach them, FAIL this gate.

### II. Server-Authoritative & Deterministic Commerce Math

All money and quantity calculations — pricing, unit conversion, quantity/wholesale
tiers, promotions, cart totals, minimum-order rules, delivery fees, delivery discounts,
and order totals — MUST be computed server-side and MUST be deterministic.

- The server MUST NOT trust any price, subtotal, discount, fee, or total sent from the
  browser; client-supplied totals are recomputed and, if mismatched, rejected.
- Given the same inputs and the same catalog/promotion state, a calculation MUST always
  produce the same result (no reliance on wall-clock beyond explicit validity windows,
  no locale-dependent rounding). Monetary rounding rules MUST be defined once and reused.

**Rationale**: Financial correctness and auditability are the core trust of a commerce
platform; determinism is what makes it testable.

**Testable gate**: Any feature that finalizes a total using a browser-provided amount,
or whose calculation is not covered by a deterministic unit test, FAILS.

### III. Business Logic Lives in Services

Complex business logic MUST NOT live in controllers, Blade views, Alpine components, or
Filament resources. It MUST live in dedicated application/domain services (or actions).

- Controllers/resources orchestrate: validate input, call a service, return a response.
- The following MUST each have a named service/action as the single source of truth:
  pricing, product units, quantity/wholesale tiers, promotions, cart calculation,
  delivery fees, delivery discounts, order creation, and OTP authentication.

**Rationale**: Centralizing rules keeps them testable, reusable across web/API, and safe
to change.

**Testable gate**: A plan/task that embeds pricing, tier, discount, fee, or order-
creation logic directly in a controller/view/resource FAILS.

### IV. Growth-Ready, Data-Driven Domain Model

The schema MUST avoid MVP-only shortcuts that would block known future growth. From day
one the data model MUST allow (even if the UI exposes only a subset):

- A product having multiple selling units.
- Prices depending on unit and on quantity tier.
- A customer having multiple delivery addresses.
- Future customer-specific price lists (structure MUST NOT preclude them).
- Delivery areas, fees, minimums, and discounts expressed as data, NOT hard-coded in
  code or views.
- Migrations MUST declare indexes and foreign keys aligned with expected query patterns;
  index choices MUST be justifiable against the reads a feature performs.

**Rationale**: These growth vectors are stated business intent; retrofitting them into a
flat schema later is a rewrite.

**Testable gate**: A data-model design that stores a single price/unit per product,
hard-codes delivery rules, or assumes one address per customer FAILS.

### V. Immutable Order Snapshots

A placed order MUST capture immutable snapshots of the commercial facts at purchase time,
including at minimum: product name, selling unit, unit price, quantity, and applied
discounts (plus resulting line and order totals).

- Historical orders MUST render identically after products, prices, units, or promotions
  change later. Order line records MUST NOT depend on live catalog values for displayed
  historical amounts.

**Rationale**: Legal, financial, and customer-trust integrity require that history not
mutate.

**Testable gate**: A design where changing a product/price alters an already-placed
order's stored figures FAILS.

### VI. Security by Default

Every feature MUST enforce, server-side:

- Authorization on every protected action (no client-only gating).
- Validation of all input on the server.
- Safe OTP handling (hashed/one-time, expiring codes; no OTP or secret leaked in
  responses or logs) and rate limiting on authentication/OTP endpoints.
- CSRF protection on state-changing web requests.
- Mass-assignment protection (explicit fillable/guarded or form-request DTOs).
- No secrets/credentials committed to Git; configuration via environment.

**Rationale**: Auth, payments-adjacent flows, and OTP are the highest-risk surfaces;
these controls are baseline, not features.

**Testable gate**: A plan lacking server-side authorization/validation, unbounded
OTP/auth endpoints, or that commits secrets FAILS.

### VII. Tests Mandatory for Business-Critical Logic

Automated tests are MANDATORY for business-critical logic and MUST exist before that
logic is considered done. Critical areas that MUST be tested:

- Pricing and quantity/wholesale tiers.
- Offers/promotions.
- Cart totals.
- Minimum-order rules.
- Delivery fees and delivery discounts.
- Order creation and order status transitions.

Tests for these areas MUST cover boundary conditions (tier edges, minimum thresholds,
zero/empty carts, out-of-stock). This overrides the "tests optional" default in the task
template for these areas specifically.

**Rationale**: These rules are where money and correctness live; untested changes here
are unacceptable risk.

**Testable gate**: A tasks list that implements any listed area without accompanying
test tasks FAILS.

### VIII. Mobile-First, Accessible, RTL-Ready UX

The customer app MUST be mobile-first and installable as a PWA.

- Layouts MUST be designed mobile-first and MUST be Arabic/RTL-ready (logical
  properties, no LTR-only assumptions).
- Product browsing MUST be fast; controls MUST be accessible (labels, focus, contrast,
  keyboard/touch targets).
- Pricing and unit selection MUST be presented clearly and unambiguously.
- Every data-driven screen MUST define its loading, empty, validation-error, error, and
  out-of-stock states.

**Rationale**: The audience is on phones, often in Arabic; clarity of price/unit and of
states is core usability, not polish.

**Testable gate**: A UI spec missing RTL readiness, or missing loading/empty/error/
out-of-stock state definitions for a data-driven screen, FAILS.

### IX. Spec-Driven Development & Controlled MVP Scope

No implementation MUST begin before the Spec Kit quality gates for that feature are
complete: specification, clarification, technical plan, task breakdown, and analysis.

- MVP scope MUST remain controlled. Ideas beyond agreed MVP scope MUST be recorded
  separately (e.g. a backlog/"Future" note) and MUST NOT silently enter MVP work.
- Scope additions require an explicit decision recorded in the spec, not ad-hoc code.

**Rationale**: Discipline here is what keeps a growth-ready MVP from becoming an
uncontrolled, over-built one.

**Testable gate**: Implementation tasks generated without a completed plan+tasks+analyze
chain, or scope creep absent from the spec, FAIL.

## Technology & Infrastructure Constraints

These are hard constraints. Deviations require a constitution amendment, not a plan-level
exception.

**Stack (maximum/pinned versions):**

- PHP **8.2 maximum** — dependencies requiring PHP 8.3+ are PROHIBITED.
- Laravel **12.x**.
- MySQL **8**.
- **Blade** for the customer-facing app; **Alpine.js** for lightweight interactivity;
  **Tailwind CSS** for styling.
- **Filament** for the admin dashboard — only a Filament version and dependency set fully
  compatible with PHP 8.2 and Laravel 12 may be used.
- The customer app MUST be installable as a PWA.

**Infrastructure (MVP):**

- One Laravel application, one MySQL database. No microservices.
- No Redis requirement, no WebSocket requirement, no Docker production dependency for the
  initial release.
- Local/public storage MAY be used for product images initially.
- The first production deployment MUST be able to run on low-cost hosting.
- Architecture MUST remain upgradeable later to VPS, Redis, queues, object storage, CDN,
  and horizontally scaled infrastructure — MVP choices MUST NOT hard-block these.

## Code Quality Standards

- Follow idiomatic, maintainable Laravel conventions; match surrounding code.
- Clear, intention-revealing naming; small, focused classes with a single responsibility.
- Avoid unnecessary abstraction, premature optimization, and overengineering — solve the
  MVP problem, but do not violate the growth-readiness principles (IV, I) to do so.
- Migrations and indexes MUST be reviewed against expected query patterns before merge.

## Governance

This constitution supersedes other practices and conventions where they conflict. It is
the authority against which `/speckit-plan`, `/speckit-tasks`, and `/speckit-analyze`
evaluate features.

**Compliance:**

- Every plan MUST pass the Constitution Check (the nine principles as gates) before
  Phase 0 research and again after Phase 1 design.
- `/speckit-analyze` MUST flag any spec/plan/tasks artifact that violates a principle as
  a blocking finding.
- Any complexity or deviation MUST be justified in the plan's Complexity Tracking table;
  an unjustified violation blocks the feature.

**Amendments:**

- Amendments MUST be made by editing this file, with a Sync Impact Report and a version
  bump, and MUST propagate to dependent templates.
- Versioning (semantic): **MAJOR** = removing/redefining a principle or governance rule
  in a backward-incompatible way; **MINOR** = adding a principle/section or materially
  expanding guidance; **PATCH** = clarifications and non-semantic refinements.
- Changing a pinned technology constraint (PHP/Laravel/MySQL/Filament/PWA) is at minimum
  a MINOR amendment and requires explicit rationale.

**Version**: 1.0.0 | **Ratified**: 2026-09-17 | **Last Amended**: 2026-09-17
