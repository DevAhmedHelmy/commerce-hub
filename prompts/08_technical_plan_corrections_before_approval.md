Claude Prompt — Technical Plan Corrections Before Approval

Prompt Number

08

Phase

Phase 3 — Technical Planning / PM Architecture Gate Corrections

Purpose

Correct and tighten the existing /speckit-plan artifacts before the technical plan is approved and before /speckit-tasks is allowed.

Prerequisites

Read first:

.specify/memory/constitution.md

specs/001-restaurant-supplies-mvp/spec.md

specs/001-restaurant-supplies-mvp/plan.md

specs/001-restaurant-supplies-mvp/research.md

specs/001-restaurant-supplies-mvp/data-model.md

specs/001-restaurant-supplies-mvp/contracts/service-contracts.md

specs/001-restaurant-supplies-mvp/contracts/http-and-admin-surfaces.md

specs/001-restaurant-supplies-mvp/quickstart.md

all files under specs/001-restaurant-supplies-mvp/design/

Important

Do NOT run /speckit-tasks.

Do NOT run /speckit-analyze.

Do NOT implement application code.

Do NOT create Laravel application files, migrations, models, controllers, Blade views, Filament resources, or JavaScript.

This is a correction pass on planning/research artifacts only.

1. Replace Filament v3 Decision With Current Compatible Major

The current plan selected Filament v3.x.

Re-evaluate this decision using current package requirements.

The project is GREENFIELD, not an upgrade of an existing Filament v3 application.

Current verified requirements show that modern Filament 5.x supports:

PHP ^8.2

Laravel / Illuminate ^11.28 | ^12.0 | ^13.0

Therefore, unless a concrete dependency conflict exists in this project, the preferred choice should be the current supported major:

Filament 5.x

Use a Composer constraint appropriate for the current stable Filament 5 release while preserving PHP 8.2 compatibility.

Do NOT select an old major merely because it is "mature."

The project should prefer:

current supported Filament core

no unnecessary third-party Filament plugins

lowest reasonable dependency surface

Update:

plan.md

research.md

quickstart.md

relevant contracts/admin notes

ADR/decision logs

so Filament v3/v4 upgrade-path language does not remain as the authoritative plan.

Document the verified PHP and Laravel compatibility evidence.

2. Enforce PHP 8.2 During Dependency Resolution

The production server has a HARD maximum of PHP 8.2.

Technical planning must explicitly require Composer dependency resolution to emulate the production runtime even if development happens on a machine with PHP 8.3+.

Add a planning requirement to configure Composer's platform PHP version appropriately, for example conceptually:

config.platform.php = 8.2.x

The exact patch may be chosen during project foundation.

Purpose:

prevent Composer from resolving dependencies that work locally but require PHP 8.3+ in production

enforce the project's hard runtime constraint continuously

Also require:

composer check-platform-reqs or equivalent deployment verification

dependency checks before introducing significant new packages

Do NOT implement composer.json yet during this correction pass.

3. Laravel 12 Lifecycle Risk

Keep Laravel 12 because PHP 8.2 is a hard server constraint and Laravel 13 requires PHP 8.3+.

However, update the risk/upgrade documentation to acknowledge the current Laravel 12 lifecycle:

PHP compatibility remains correct for PHP 8.2.

Laravel 12 is now in its security-fixes support period.

Security support continues until its documented EOL in February 2027.

Add an explicit infrastructure upgrade trigger:

When the hosting environment can support PHP 8.3+, evaluate upgrading Laravel to the then-current supported major.

This is NOT an MVP scope addition and must not block implementation.

It is a documented maintenance risk.

4. PWA Privacy / Authenticated Content Caching

Tighten the service-worker plan.

The PWA MUST NOT cache sensitive authenticated HTML or customer-specific responses in a way that could expose:

profile information

addresses

cart content

checkout content

order history

order details

OTP/authentication responses

For MVP, prefer caching:

versioned static CSS/JS

icons

logo

safe public shell/static assets

offline fallback page

Dynamic authenticated pages should remain network-driven.

Explicitly exclude sensitive routes/responses from service-worker cache storage.

Offline order placement remains prohibited.

Update PWA planning artifacts accordingly.

5. Cart Persistence Must Use a MySQL-Enforceable Simple Model

The plan currently says:

"one active cart per authenticated customer"

Verify the data model can enforce this cleanly in MySQL without relying on unsupported partial unique indexes.

Preferred MVP approach:

one persistent cart record per customer

customer_id unique

cart items are added/updated/removed on that cart

after a successful order, cart items are cleared

the cart row may remain for reuse

This avoids an ambiguous "many carts but only one active" invariant.

If the existing data model already uses an equally simple MySQL-enforceable strategy, retain it and explain it.

Do NOT create cart-history functionality.

Order history belongs to orders, not carts.

Update data-model.md, plan, contracts, and ADR notes if required.

6. Queue Strategy Must Be Unambiguous

The current summary mentions both:

sync queue default

database queue / cron draining

Clarify the exact MVP default.

Preferred lowest-cost MVP strategy:

normal business actions remain synchronous

database notifications may be written synchronously

no persistent queue worker is required

OTP sending may initially be synchronous with strict provider connection/read timeouts

if the selected OTP provider later requires asynchronous handling, the approved fallback is Laravel database queue + scheduler/cron draining

Do NOT require Redis.

Do NOT require Supervisor.

Do NOT create a database queue requirement unless the implementation actually needs it.

Document:

MVP default

fallback

upgrade path

so /speckit-tasks does not implement unnecessary queue infrastructure.

7. Future Managed-Content Localization Must Remain Additive, Not Pre-Built

Arabic is the only MVP UI/content language.

English is future scope.

The plan currently references a possible *_i18n JSON path.

Clarify that technical planning must NOT create empty bilingual/translation fields everywhere merely for future readiness.

For MVP:

business-managed content may remain Arabic scalar data

commerce/domain logic remains language-neutral

schema choices must not make later English support destructive

Preferred future path should be documented as an additive localization layer, such as translation tables or another explicitly evaluated approach.

Do NOT require unused English columns or empty translation JSON columns throughout the MVP schema unless there is a concrete current need.

Adding English later must not require rewriting pricing/order/delivery logic.

8. Delivery Slot Model Must Be Explicit

Verify the plan/data model defines a concrete MVP delivery-slot model rather than leaving "applicable date/day model" ambiguous.

The MVP needs:

customer selects a delivery date

customer selects an available slot

admin manages slots

no numeric capacity

inactive slot rejected during checkout revalidation

Choose and document the simplest concrete model.

A reasonable MVP option is recurring weekday-based slot templates, for example:

day of week

start time

end time

active state

sort order

If the existing plan uses another equally simple model, keep it and explain why.

Do NOT add:

slot capacity

driver scheduling

route planning

Document how a selected order snapshots the chosen delivery date + slot label/times so later slot edits do not mutate historical orders.

9. Order Number Strategy Must Be Concrete and Concurrency-Safe

The plan must specify an implementation-ready user-facing order-number strategy.

Requirements:

unique

human-readable

safe under concurrent order creation

enforced by a UNIQUE database constraint

not dependent on client-generated values

Do not leave only a vague "transactional sequence" statement.

Choose a pragmatic strategy suitable for this low-to-medium-volume MVP and document:

format

generation timing

uniqueness enforcement

collision/race handling

Do NOT introduce distributed ID infrastructure.

10. Money Representation Must Be Definitive

The plan summary says integer minor units + central Money.

Make this authoritative across all artifacts if that is the selected decision.

Preferred:

store money in integer minor units (e.g. piastres)

PHP calculations use integers / a central Money value object/helper

never calculate monetary values using binary floating point

display formatting converts only at presentation boundary

Arabic display remains:

444 ج

Document how values with piastres are displayed if they occur.

Do not leave both DECIMAL and integer-minor-units as equally open alternatives after this correction pass.

11. Search Strategy Must Stay Cheap and Deterministic

Keep MVP search MySQL-based.

Do not introduce Scout, Meilisearch, Elasticsearch, or external search services.

Clarify the initial implementation target:

product name

brand

category where appropriate

Arabic content

naturally English brand/product tokens

Prefer the simplest indexed SQL approach appropriate for initial catalog size.

FULLTEXT may be evaluated later if real data shows LIKE-based search is insufficient.

Do not make FULLTEXT a mandatory MVP dependency unless justified by expected data volume.

12. Final Technical Planning Audit

After applying the corrections, validate the entire planning set.

Confirm:

Filament current compatible major is selected and justified.

No selected dependency requires PHP 8.3+.

Composer production-platform emulation for PHP 8.2 is planned.

Laravel 12 lifecycle risk is documented.

PWA does not cache sensitive authenticated/customer data.

Cart uniqueness strategy is MySQL-enforceable and simple.

Queue strategy has one clear MVP default.

Future English localization remains additive and does not create unused MVP schema.

Delivery-slot model is concrete.

Order-number strategy is concrete and concurrency-safe.

Money representation is definitive.

Search remains low-cost MySQL-based.

No business requirements were changed.

No MVP scope was expanded.

All nine constitution principles still PASS.

No application code was written.

Return a PM-ready report containing:

files updated

corrected Filament decision and verified requirements

PHP 8.2 dependency-safety strategy

Laravel 12 lifecycle note

cart model

queue default

PWA cache/privacy decision

localization-content strategy

delivery-slot model

order-number strategy

definitive money strategy

search strategy

remaining blockers, if any

Constitution Check result

confirmation that no application code was written

If there are no blockers, state:

Technical Plan Ready for PM Approval

Do NOT commit.
Do NOT run /speckit-tasks.
Do NOT run /speckit-analyze.