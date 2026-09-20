Claude Prompt — Implement Phase B: Shared Foundations

Prompt Number

13

Phase

Phase 5 — Implementation / Batch 2

Scope

Implement Phase B only from:

specs/001-restaurant-supplies-mvp/tasks.md

Use the final analyzed task IDs for:

Phase B — Shared Foundations

Do NOT continue into Phase C.

Command

Run:

/speckit-implement

but restrict execution to Phase B only.

Read Before Implementation

Read and follow:

.specify/memory/constitution.md

specs/001-restaurant-supplies-mvp/spec.md

specs/001-restaurant-supplies-mvp/plan.md

specs/001-restaurant-supplies-mvp/research.md

specs/001-restaurant-supplies-mvp/data-model.md

specs/001-restaurant-supplies-mvp/contracts/

specs/001-restaurant-supplies-mvp/quickstart.md

specs/001-restaurant-supplies-mvp/tasks.md

all files under specs/001-restaurant-supplies-mvp/design/

all files under prompts/ in numeric order

Later-numbered prompts supersede earlier decisions where explicitly changed.

The Laravel application root is:

src/

All application code must remain inside src/....

1. Phase Boundary

Implement only Shared Foundations.

Do NOT implement:

OTP login flow

customer onboarding

categories CRUD

products CRUD

pricing tiers

offers

cart

delivery

checkout

orders

landing-page business content

business Filament Resources

Those belong to later phases.

2. Money Foundation

Implement the approved deterministic money strategy.

Requirements:

integer minor units for commerce calculations/storage boundaries

no binary floating-point money calculations

centralized Money value object/helper/service as approved by plan

explicit arithmetic operations

deterministic comparison

deterministic addition/subtraction

non-negative protection where appropriate

formatting separated from domain calculation

Arabic display format:

444 ج
1,250 ج
12,500 ج

If minor units contain piastres, follow the approved formatting decision from the plan.

Do NOT scatter currency concatenation through Blade templates.

Add focused automated tests for Money behavior where Phase B tasks require them.

3. Locale / Translation Foundation

Arabic remains the only exposed MVP language.

Implement:

Arabic translation catalog structure

Arabic validation/message readiness

language-neutral domain codes

no hard-coded translated domain statuses in business logic

Future English readiness must remain possible.

Do NOT expose:

English UI

language selector

per-user locale selection

4. RTL / Presentation Foundation

Prepare reusable RTL-first foundations required by the approved design.

Use logical CSS/layout behavior.

Do NOT manually reverse Arabic strings.

Ensure future LTR can be supported without redesign.

Mixed content must remain safe:

Arabic

English brands

Latin digits

product codes

package units

Only implement the shared layout/component foundations assigned to Phase B.

Do NOT build feature screens yet.

5. Shared Enums / Domain Identifiers

Implement approved language-neutral identifiers required by later modules.

Examples may include:

order status

availability status

discount type

payment method

pricing source

Only implement identifiers explicitly supported by the approved plan/tasks.

Do NOT invent extra generalized enums.

Do NOT translate enum values in the domain layer.

Translations belong to presentation.

6. Shared DTO / Result Foundations

Implement only reusable result/data objects that are explicitly required by the plan/tasks and genuinely shared by later business logic.

Examples may include:

Money-related result objects

structured operation result primitives

shared immutable DTO conventions

Do NOT create empty abstraction layers or speculative interfaces.

Avoid "service for every model."

7. Date / Time Presentation Foundation

Implement centralized presentation formatting only where Phase B requires it.

Requirements:

stored timestamps remain language-neutral

business date/time values remain deterministic

display formatting stays outside business rules

Arabic MVP presentation

future locale-ready behavior

Do NOT implement delivery-slot business logic yet.

8. Validation Foundation

Prepare shared validation/localization infrastructure.

Rules and translated messages must remain separate.

Arabic messages for MVP.

Do NOT create feature-specific customer/product/order validators before their phases unless Phase B explicitly requires a reusable primitive.

9. Error / Empty / Loading Foundations

Implement reusable presentation components/states required by approved UX:

generic error state

empty state

loading indicator/skeleton pattern where approved

Keep them reusable and lightweight.

Do NOT build business-specific screens prematurely.

10. Media / Image Foundation

Implement only shared image/media foundation assigned to Phase B.

Requirements:

local/public Laravel storage

safe validation foundation

file size/type constraints where approved

path/naming strategy

future object-storage portability

Do NOT implement ProductResource/product upload UI yet.

Do NOT add S3 dependency.

11. Security Foundation

Implement shared baseline protections assigned to Phase B, such as:

safe mass-assignment conventions

secure validation boundaries

no secrets in logs

safe application configuration

authorization-ready structure

Do NOT implement OTP-specific rate limiting until Phase C unless already a generic foundation task.

12. Testing

Constitution Principle VII remains mandatory.

Run:

Phase B unit tests

base Laravel tests

any shared-foundation tests

At minimum verify:

Money behavior

money formatting

shared enum/domain identifier behavior where tested

localization/fallback behavior where practical

Do NOT postpone failing Phase B tests.

13. Code Quality

Follow Laravel 12 conventions.

Prefer:

strict, readable types where useful

small cohesive classes

no premature repository pattern

no generic BaseService architecture

no unnecessary traits

no global helpers unless clearly justified

no duplicated formatting logic

Keep business logic framework-light and reusable by future API/mobile surfaces.

14. Task Tracking

Mark only completed Phase B tasks in:

specs/001-restaurant-supplies-mvp/tasks.md

Do NOT mark Phase C+ tasks.

If a Phase B task cannot be completed:

leave it unchecked

report the blocker

do not pretend completion

15. Validation Before Stopping

Verify:

Phase A remains working.

Application remains inside src/.

No PHP 8.3+ dependency was introduced.

Money calculations do not use float.

Arabic remains default/fallback.

No language switcher exists.

RTL foundations are logical-direction based.

Shared domain identifiers are language-neutral.

Currency rendering is centralized.

Date/time presentation is centralized where implemented.

Shared image/storage approach does not require S3.

Phase B tests pass.

Base test suite still passes.

No Phase C+ business functionality was implemented.

Only Phase B tasks were newly marked complete.

Required Final Report

Return:

Phase B result: PASS / PASS WITH ISSUES / BLOCKED

tasks completed

tasks not completed

files created/modified

Money implementation summary

exact money storage/calculation representation

currency formatting result/examples

localization foundation summary

RTL foundation summary

shared enums/identifiers added

shared DTO/result objects added

media/storage foundation summary

tests added

test command(s) + result(s)

PHP 8.2 compatibility status

warnings/blockers

git status --short summary

confirmation that Phase C+ was NOT implemented

Stop after Phase B.

Do NOT automatically commit.

Do NOT continue into Phase C.

End with:

PHASE B READY FOR PM REVIEW

only if all Phase B acceptance checks pass.