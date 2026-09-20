Claude Prompt — Implement Phase D3: Units Refactor + Inventory Base Unit Alignment

Prompt Number

38

Phase

Phase D3 — Units Module Refactor / Catalog Data Model Correction

Purpose

Implement the already-approved two-level unit architecture from Prompt 37 BEFORE completing Phase E pricing.

This is a controlled refactor of the catalog/unit/inventory foundation.

Do NOT continue into Phase E until this phase is complete and green.

Laravel application root:

src/

Prompt history root:

prompts/

Read First

Read:

prompts/37_refactor_units_module_two_level_conversion.md

prompts/36_migration_and_visual_review_gate.md

prompts/34_add_product_image_core_mvp.md

prompts/32_add_simple_inventory_core_mvp.md

.claude/skills/restaurant-ui/SKILL.md

.specify/memory/constitution.md

specs/001-restaurant-supplies-mvp/spec.md

specs/001-restaurant-supplies-mvp/plan.md

specs/001-restaurant-supplies-mvp/data-model.md

specs/001-restaurant-supplies-mvp/contracts/

specs/001-restaurant-supplies-mvp/tasks.md

Prompt 37 is the authoritative unit-model decision.

1. Stop Phase E Temporarily

Phase E is currently in progress.

Do NOT continue implementing:

PriceResult

PromotionService

PricingService

lower-of rule UI wiring

pricing Filament screens

until this Phase D3 refactor is complete.

Preserve any valid unfinished Phase E files already created, but do not build further on the old unit assumptions.

2. Units Module

Implement independent units model/table.

Required fields conceptually:

id

code

name_ar

name_en

is_active

sort_order

timestamps

Requirements:

unique language-neutral code

Arabic required

English optional

prefer inactive over destructive delete once referenced

Examples:

carton

piece

bag

bottle

pack

3. Product Units

Refactor product_units so each record references:

product_id

unit_id

level: primary or sub

conversion_to_sub_unit

is_sellable

is_active

existing pricing identity/data where already present

MVP constraint:

max one primary per product

max one sub per product

primary != sub

conversion factor > 0

sub conversion identity = 1

Do not add recursive unit trees.

4. Safe Migration

Follow Prompt 36 migration safety.

Do NOT run:

migrate

db

destructive reset

Inspect current schema/data first.

Create additive/refactor migrations that preserve current data.

If legacy product-unit records cannot be mapped safely:

stop

report exactly which records require manual mapping

Do not invent conversion factors.

5. Inventory Alignment

Inventory authoritative balance is in SUB UNIT quantity.

If existing stock is stored per product unit independently, refactor carefully.

Requirements:

authoritative stock normalized to sub-unit quantity

admin may add/remove stock using primary or sub unit

primary adjustments convert using product conversion factor

final stock cannot go below zero

cart does not reserve stock

Do NOT implement later order-deduction/cancellation logic here unless already required by updated D3 tasks.

6. Product Admin UI

Update product/unit administration.

Admin must clearly configure:

الوحدة الرئيسية

الوحدة الفرعية

عدد الوحدات الفرعية داخل الوحدة الرئيسية

Examples:

الوحدة الرئيسية: كرتونة
الوحدة الفرعية: قطعة
عدد القطع داخل الكرتونة: 12

Admin must manage generic Units from a dedicated Units section.

Use /restaurant-ui.

Ensure responsive Filament behavior.

7. Stock Admin UI

Where inventory foundation already exists:

Admin can add stock via:

primary unit

sub unit

Examples:

+10 كرتونة

normalizes to:

+120 قطعة

and:

+7 قطعة

normalizes directly.

Display exact normalized stock and optionally:

10 كرتونة + 5 قطعة

derived from authoritative sub-unit stock.

Do not store the formatted breakdown.

8. Product Image Completion

Prompt 34 is reported implemented but missing tests.

In this phase, complete ONLY the missing Product Image tests and any small fixes required by those tests.

Verify:

valid image upload

invalid file rejected

oversized file rejected

replace image

remove image

null image fallback

unauthorized media modification rejected

Do not redesign product media.

9. Pricing Compatibility

Preserve the rule that pricing belongs to the selected Product Unit.

Do NOT calculate one unit price from another.

Verify the refactor leaves clean hooks for Phase E:

primary unit has its own price

sub unit has its own price

tiers/offers can attach to selected product unit independently

If incomplete Phase E code depends on the old schema, update only what is necessary to compile after the refactor.

Do not complete Phase E business logic here.

10. Historical Safety

If orders already exist locally:

Do not mutate historical snapshot data.

Future order snapshots must preserve selected unit + conversion used at order time.

Do not retroactively reinterpret historical orders.

11. Conversion Change Safety

Implement/document a safe policy.

Preferred MVP:

If a product has non-zero stock, do not allow changing the conversion factor silently.

Require stock to be reconciled/corrected before the conversion can change, or use the approved explicit safe flow if one already exists.

Never reinterpret existing normalized stock under a new factor silently.

12. Tests

Mandatory tests:

Units

create generic unit

activate/deactivate

one primary per product

one sub per product

primary != sub

conversion > 0

Conversion

For 1 carton = 12 pieces:

1 carton = 12

10 cartons = 120

7 pieces = 7

2 cartons + 5 pieces = 29

125 pieces displays as 10 cartons + 5 pieces

Inventory foundation

add stock by primary

add stock by sub

remove by primary

remove by sub

cannot drop below zero

Product images

all missing Prompt 34 tests

Run Phase C/D regression tests.

13. Migration & Runtime Gate

Run:

php artisan migrate:status
php artisan migrate
php artisan test
npm run build

or approved equivalents.

Do not mark phase complete if migration/test/build fails.

Run current implemented screens locally and report review URLs.

14. Task Update

Update tasks.md with D3 tasks as needed.

Mark only genuinely completed D3 tasks.

Do not mark Phase E tasks complete.

15. Git

Inspect:

git status --short

Commit only if green:

feat: complete phase D3 units and inventory foundation

Do not include unrelated Phase E changes unless required solely for compilation compatibility and clearly reported.

Final Report

Return:

Phase D3 result

migrations created/applied

units schema

product_units schema

inventory normalized-stock strategy

admin unit UI

stock adjustment UI

Product Image tests completed

tests result

frontend build result

runtime URLs

Phase E compatibility notes

commit hash/message

remaining blockers

git status

End with:

PHASE D3 READY — SAFE TO RESUME PHASE E