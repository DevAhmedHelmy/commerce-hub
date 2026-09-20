Claude Prompt — Refactor Units into Independent Module + Two-Level Product Unit Conversion

Prompt Number

37

Phase

Domain Model Amendment / Units + Inventory Refactor

Purpose

Refactor the current unit/selling-unit architecture.

Units must become an independent reusable module.

Products are then linked to those units through product-specific configuration.

For MVP, each product supports exactly TWO unit levels:

Primary Unit

Sub Unit

Example:

Primary Unit: Carton

Sub Unit: Piece

Product-specific conversion: 1 Carton = 12 Pieces

The customer may purchase either unit.

The admin may add inventory using either:

primary unit quantity, which is converted automatically into sub units

sub unit quantity directly

This prompt supersedes any earlier assumption that each product unit owns an independent unrelated stock balance.

Read First

Read:

.specify/memory/constitution.md

specs/001-restaurant-supplies-mvp/spec.md

specs/001-restaurant-supplies-mvp/plan.md

specs/001-restaurant-supplies-mvp/research.md

specs/001-restaurant-supplies-mvp/data-model.md

specs/001-restaurant-supplies-mvp/contracts/

specs/001-restaurant-supplies-mvp/tasks.md

all prompt history under prompts/ in numeric order

Later prompts supersede earlier decisions.

Do NOT implement unrelated features in this prompt unless explicitly instructed by the current execution workflow.

1. Independent Units Module

Create an independent units domain/entity.

Suggested fields:

id

code

name_ar

name_en

is_active

sort_order

timestamps

Examples:

carton

piece

bag

bottle

pack

Use language-neutral code.

Examples:

carton
piece
bag
bottle
pack

Managed display names may use:

name_ar

name_en

Arabic remains the only exposed MVP language.

2. Do NOT Store Product Conversion on Units Table

The conversion factor belongs to the PRODUCT relationship, not to the generic unit.

Example:

For Product A:

1 carton = 12 pieces

For Product B:

1 carton = 24 pieces

Therefore do NOT put:

carton = 12 pieces

inside the generic units table.

Conversion is product-specific.

3. Product Unit Configuration

Use a product-unit relationship model/table.

Preferred concept:

product_units

At minimum:

id

product_id

unit_id

level

conversion_to_sub_unit

is_sellable

is_active

pricing-related fields only if already approved there

timestamps

Approved levels for MVP:

primary

sub

Each product may have:

one primary unit

one sub unit

Example:

Product: Heinz Ketchup

Primary:

unit = carton

level = primary

conversion_to_sub_unit = 12

Sub:

unit = piece

level = sub

conversion_to_sub_unit = 1

Alternative schema is acceptable if it is cleaner, but must preserve the exact business rules.

4. MVP Constraint: Two Levels Only

For MVP, support exactly:

Primary Unit
→ Sub Unit

Example:

Carton
→ Piece

Do NOT implement:

Carton
→ Pack
→ Piece

Do NOT create recursive/n-level conversion trees in MVP.

Future multi-level conversion should remain possible without redesigning commerce logic.

5. Product Must Define Its Unit Pair

Each product must explicitly define:

primary unit

sub unit

conversion factor

Example:

Product: Frozen Fries

Primary Unit: Carton
Sub Unit: Bag

1 Carton = 4 Bags

Example:

Product: Sauce

Primary Unit: Carton
Sub Unit: Bottle

1 Carton = 12 Bottles

The generic Unit records are reusable.

6. Sellability

The customer may purchase BOTH units.

Example:

Customer may order:

2 cartons

5 pieces

for the same product.

Both primary and sub units are independently sellable.

Pricing remains per Product Unit.

7. Pricing

Each product unit has independent pricing.

Do NOT derive sub-unit selling price mathematically from primary-unit price.

Example:

Carton = 1,200 ج
Piece = 110 ج

Even if:

1 carton = 12 pieces

Do NOT assume:

piece = carton price / 12

Pricing tiers and offers remain evaluated per sellable product unit.

Approved pricing rules remain unchanged:

base price

quantity tier

active offer

lower eligible unit price wins

tier + offer do not stack

8. Inventory Base Unit

For MVP, inventory is stored internally in the SUB UNIT.

The sub unit is the base inventory unit.

Example:

Primary: Carton
Sub: Piece
Conversion: 1 Carton = 12 Pieces

Internal stock:

125 pieces

Admin display may represent this as:

10 cartons + 5 pieces

but the authoritative stock quantity is:

125 sub-units

This prevents independent stock balances from becoming inconsistent.

9. Admin Add Stock — Primary Unit

Admin may add stock using primary units.

Example:

Current stock:

125 pieces

Admin adds:

10 cartons

Conversion:

10 × 12 = 120 pieces

New authoritative stock:

245 pieces

Create inventory adjustment:

input unit = primary

input quantity = 10

converted delta = +120 sub-units

quantity_before = 125

quantity_after = 245

10. Admin Add Stock — Sub Unit

Admin may also add sub units directly.

Example:

Admin adds:

7 pieces

Adjustment:

+7 sub-units

No conversion required.

11. Admin Remove / Correct Stock

The same principle applies to:

manual remove

correction

Admin may enter adjustment in:

primary unit

sub unit

System converts primary-unit adjustments into sub-unit quantity before changing the balance.

Never allow final stock below zero.

12. Order Stock Deduction

Orders deduct stock in authoritative SUB UNIT quantities.

Example:

Product:

1 carton = 12 pieces

Customer orders:

2 cartons
+ 5 pieces

Required stock deduction:

(2 × 12) + 5 = 29 pieces

Order placement transaction must:

lock stock row / relevant product inventory state

calculate required sub-unit quantity

verify enough stock exists

deduct authoritative sub-unit quantity

write inventory adjustment record(s)

create order snapshots

commit atomically

No overselling.

13. Cart Does Not Reserve Stock

Cart is NOT an inventory reservation.

A customer may place items in cart while stock changes later.

At checkout/order placement:

stock must be revalidated

insufficient stock must block order creation

do not silently reduce requested quantity

14. Customer Availability

Customer does NOT need exact stock quantity.

Preferred customer states:

متوفر

غير متوفر

If stock is insufficient for the selected quantity/unit:

show clear Arabic validation.

Example concept:

الكمية المطلوبة غير متوفرة حالياً.

15. Admin Stock Display

Admin should see exact inventory.

For a product with:

1 Carton = 12 Pieces
Authoritative Stock = 125 Pieces

Admin may display:

10 كرتونة + 5 قطعة

and optionally:

إجمالي المخزون: 125 قطعة

Use the sub-unit count as authoritative.

Do not store the formatted carton+piece representation.

Calculate it.

16. Inventory Adjustment History

Inventory adjustments must preserve both:

user-entered adjustment unit/quantity

normalized sub-unit delta

Recommended fields:

id

product_id

product_unit_id or relevant relation

input_unit_id

input_quantity

normalized_quantity_delta

quantity_before

quantity_after

type

reason

reference_type

reference_id

performed_by

created_at

Exact schema may be simplified if equivalent auditability is preserved.

17. Unit Conversion Service

Add a focused domain service/value object for unit conversion.

Example responsibility:

ProductUnitConverter

or equivalent.

Responsibilities:

primary → sub conversion

sub → sub identity conversion

formatting stock into primary + remainder sub units

Do NOT mix pricing into unit conversion.

Do NOT create generic arbitrary graph conversion logic.

MVP only needs two-level conversion.

18. Data Integrity Constraints

Enforce:

one primary unit per product

one sub unit per product

primary unit != sub unit

conversion factor > 0

sub-unit conversion factor = 1 conceptually

stock quantity >= 0

both unit relations belong to the same product configuration

Use DB constraints/indexes where practical and service-level validation where required.

19. Units Admin Module

Add a dedicated Units section in Admin.

Admin should be able to:

create unit

edit localized unit name

activate/deactivate unit

view units

Example units:

كرتونة

قطعة

كيس

عبوة

باكو

Do not let deleting a unit break existing product history.

Prefer disable/inactive over destructive deletion when already in use.

20. Product Admin UI

Product form/resource must allow selecting:

Primary Unit

Sub Unit

Conversion Factor

Example Arabic labels:

الوحدة الرئيسية
الوحدة الفرعية
عدد الوحدات الفرعية داخل الوحدة الرئيسية

Example:

الوحدة الرئيسية: كرتونة
الوحدة الفرعية: قطعة
عدد القطع داخل الكرتونة: 12

Avoid confusing technical wording in Admin UI.

21. Product Unit Pricing UI

Admin must configure price independently for:

primary unit

sub unit

Price tiers/offers remain associated with the appropriate Product Unit.

Do not combine unit conversion with automatic price calculation.

22. Product Image Compatibility

Prompt 34 Product Image remains valid.

Product image belongs to Product, not Unit.

Do NOT duplicate the product image per unit unless future requirements explicitly demand it.

23. Order Snapshots

Order items must snapshot enough unit information to preserve history.

Include conceptually:

product name snapshot

selected unit name snapshot

selected unit level/code

quantity ordered

conversion factor used when relevant

normalized sub-unit quantity deducted

applied unit price

line total

Historical orders must not change if product conversion factor changes later.

24. Conversion Changes After Existing Orders

Changing:

1 carton = 12 pieces

to:

1 carton = 24 pieces

must NOT alter historical orders.

Order snapshot preserves the conversion used at order time.

For current inventory, such a conversion change is potentially dangerous.

Therefore:

If stock already exists, changing the conversion factor must be controlled.

Preferred MVP rule:

block conversion-factor changes when non-zero stock or existing active data would make interpretation unsafe

require stock correction/reset workflow or explicit safe adjustment before conversion change

Document the exact policy.

Do not silently reinterpret existing stock under a new conversion factor.

25. Tests — Units

Mandatory tests:

create unit

activate/deactivate unit

one primary unit per product

one sub unit per product

primary != sub

conversion factor required and > 0

26. Tests — Conversion

Mandatory:

1 carton = 12 pieces

Test:

1 carton → 12 pieces

10 cartons → 120 pieces

7 pieces → 7 pieces

2 cartons + 5 pieces → 29 pieces

stock 125 pieces → display 10 cartons + 5 pieces

27. Tests — Inventory

Mandatory:

add 10 cartons → +120 pieces

add 7 pieces → +7 pieces

remove primary unit quantity

remove sub unit quantity

cannot go below zero

order deducts normalized sub-unit quantity

cancellation restores exact normalized quantity

double cancellation does not double restore

concurrent orders cannot oversell

28. Tests — Pricing Independence

Mandatory:

carton and piece have independent prices

changing carton price does not change piece price

tiers evaluate against selected unit

offer evaluates against selected unit

lower-of rule remains correct

29. Update Planning Artifacts

Update:

spec.md

plan.md

research.md if needed

data-model.md

contracts

tasks.md

admin surface documentation

Remove/supersede any earlier architecture that treats each Product Unit stock as an unrelated independent balance.

30. Update Prompt / Runner Integration

The autonomous implementation workflow must use this updated model.

If Phase D or inventory implementation has not completed:
integrate the new unit architecture into those tasks.

If affected code already exists:
create a controlled refactor/migration plan.

Do not silently destroy existing local data.

31. Migration Strategy

If current schema already has product_units or stock columns:

plan a safe migration.

Requirements:

preserve existing products

preserve pricing where possible

preserve existing orders

do not use destructive migrate:fresh

map existing unit names into units where practical

assign primary/sub relationships intentionally

do not invent conversion factors from ambiguous data

If conversion cannot be inferred:
report records requiring manual mapping.

32. Scope Guard

Do NOT add:

three-level units

arbitrary conversion trees

inventory by warehouse

batch/lot inventory

automated pack breaking accounting

purchasing

suppliers

unit-based costing engine

ERP integration

MVP = exactly two unit levels.

33. Final Report

Return:

result

files updated

revised schema

units-table design

product-units relationship

primary/sub constraints

stock base-unit strategy

admin stock-add flow

order stock-deduction flow

pricing independence behavior

conversion-change safety policy

migration/backfill strategy

tests added/updated

tasks added/modified

affected existing phases/code

remaining blockers

Constitution Check result

End with:

TWO-LEVEL PRODUCT UNIT MODEL READY

Do NOT proceed with destructive schema changes without safe migration handling.