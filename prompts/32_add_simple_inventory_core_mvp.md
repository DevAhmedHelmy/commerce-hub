Claude Prompt — Add Simple Inventory as Core MVP Requirement

Prompt Number

32

Phase

Scope Amendment / Technical Planning Update Before Further Commerce Implementation

Purpose

Simple inventory quantity is now a CORE MVP REQUIREMENT.

This supersedes all earlier statements that full inventory quantities are out of MVP.

Do NOT implement warehouses, purchasing, suppliers, batch tracking, costing, or ERP features.

The required scope is a SIMPLE, RELIABLE inventory balance per selling unit.

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

1. Core Inventory Model

Inventory is maintained per:

product_unit

NOT only per product.

Example:

French Fries:

Bag 2.5 KG → stock quantity 80

Carton → stock quantity 25

Each selling unit has an independent stock balance.

2. Required MVP Inventory Capabilities

Admin must be able to:

view current stock quantity for each selling unit

add stock

reduce/correct stock

see stock status

see stock adjustment history

Customer-side availability must respect actual stock.

When stock is zero:

the selling unit cannot be ordered

UI should show Out of Stock / غير متوفر

Add to Cart / checkout must reject it

3. Stock Quantity Representation

Use integer quantity for MVP.

Examples:

25 cartons

80 bags

120 bottles

Do NOT use floating stock quantities unless a concrete selling-unit requirement proves they are necessary.

Quantity belongs to product_units or an approved dedicated inventory-balance table.

Choose the simplest safe architecture.

Preferred:

product_units.stock_quantity as current balance

dedicated stock adjustment/history table for auditability

If plan conventions strongly favor a dedicated balance table, justify it.

4. Inventory Adjustment History

Create a durable inventory adjustment history.

Suggested entity:

inventory_adjustments

Track at minimum:

id

product_unit_id

type

quantity_delta

quantity_before

quantity_after

reason

reference_type/reference_id where useful

performed_by admin user when manual

created_at

Adjustment types may include language-neutral codes such as:

initial

manual_add

manual_remove

order

order_cancel_restore

correction

Do not create a complex stock-ledger/accounting engine.

5. Admin Inventory Actions

Admin UI must provide clear actions such as:

إضافة مخزون

خصم مخزون

تصحيح المخزون

For every manual stock change:

require quantity

require/allow a reason according to UX

show current stock

preview resulting stock

prevent negative final stock unless an explicit correction policy allows it

Preferred MVP:
Never allow stock balance below zero.

6. Automatic Order Deduction

Successful order placement must atomically reduce inventory.

Required behavior:

Revalidate stock during checkout/order placement.

Lock/re-read the relevant selling-unit stock rows inside the order transaction.

Ensure requested quantities are still available.

Decrement stock.

Create corresponding inventory adjustment records.

Create order and item snapshots.

Commit all together.

If any line lacks sufficient stock:

do not create the order

do not partially deduct stock

return a structured changed/unavailable result

customer reviews cart/checkout again

No overselling.

7. Concurrency

Inventory is business-critical.

Prevent overselling under concurrent orders.

Use appropriate MySQL/Laravel transactional locking, e.g. row-level SELECT ... FOR UPDATE / lockForUpdate() as appropriate.

Do NOT rely solely on stale values loaded before transaction.

Do NOT use distributed locks/Redis.

8. Cancellation Restock

When an order is cancelled from an eligible non-delivered state:

restore its deducted inventory exactly once.

Create stock adjustment records:

order_cancel_restore

Requirements:

idempotent

no double-restock

historical order snapshots remain unchanged

Do NOT restore inventory for:

already cancelled orders

delivered orders

Follow the approved cancellation matrix.

9. Order Status Interaction

Inventory is deducted when the order is successfully CREATED, not when later confirmed/preparing.

Reason:

avoids overselling after customer successfully submits an order

provides deterministic available stock

Cancellation restores stock as defined above.

Do not introduce stock reservation expiration timers in MVP.

10. Product / Unit Availability

Keep language-neutral availability/status controls if still useful.

Differentiate:

inactive selling unit → administratively unavailable

stock quantity = 0 → out of stock

stock quantity > 0 and active → available

Do not require admins to manually toggle Out of Stock if stock naturally reaches zero.

If an existing availability enum contains out_of_stock, refactor carefully so stock-driven behavior is not contradictory.

Document one authoritative rule.

11. Cart Behavior

Cart may contain an item whose stock later changes.

Cart is not a reservation.

When displaying/recalculating cart:

show availability warning if quantity now exceeds stock

do not treat cart as stock allocation

Checkout/order placement remains authoritative.

12. Customer UI

Product Details should show stock-related availability.

Do NOT necessarily expose exact stock quantity to customers unless approved.

Preferred MVP customer behavior:

Available

Out of Stock

Admin sees exact quantity.

If customer requests quantity greater than current stock:

clearly reject/update with Arabic message

do not silently reduce requested quantity

13. Inventory + Selling Units

All inventory rules are per selling unit.

Examples:

Product:
French Fries

Unit A:
Bag 2.5 KG
stock = 80

Unit B:
Carton
stock = 25

Ordering one unit must not magically alter another unit unless explicit conversion rules exist.

MVP does NOT implement automatic unit conversion inventory.

If cartons and bags physically share stock, that is future inventory-conversion scope unless explicitly specified later.

14. Admin Dashboard Inventory Visibility

Add practical dashboard/admin visibility:

low/out-of-stock units

current stock column in product-unit management

filters for in-stock / out-of-stock where useful

Do not build advanced inventory analytics.

Optional simple dashboard widget:

عدد الأصناف غير المتوفرة

Only if consistent with current dashboard design.

15. Inventory Thresholds

Do NOT implement full reorder management unless required.

A simple low-stock threshold may be supported only if it remains trivial and useful.

If added:

per product unit or a global default

visual admin warning only

no automated purchasing

Do not block MVP on reorder thresholds.

16. Audit Requirements

Every inventory change must be traceable.

Inventory adjustments must show:

what unit changed

previous quantity

delta

resulting quantity

reason/source

admin/order reference

timestamp

This history must not be silently editable/deletable from normal admin UI.

17. Price Changes Remain Separate

Inventory quantity and pricing are separate concerns.

Do not mix stock balance with:

base prices

price tiers

offers

Price changes will be covered by the project admin audit-log requirement.

18. Data Model Update

Update data-model.md.

Expected new/updated entities:

product_units:

add/confirm stock_quantity

inventory_adjustments

Document:

columns

FKs

indexes

constraints

unsigned/non-negative behavior

transaction rules

audit/history behavior

Update the total table count accordingly.

19. Spec Update

Update spec.md so Simple Inventory is explicitly part of MVP.

Add acceptance scenarios covering:

admin adds stock

admin reduces/corrects stock

stock reaches zero

customer cannot order unavailable unit

concurrent order safety

successful order decrements inventory

cancelled order restores inventory

no double restore

Remove/supersede statements saying inventory quantities are out of MVP.

20. Plan / Research / Contracts Update

Update as needed:

plan.md

research.md

contracts/service-contracts.md

contracts/http-and-admin-surfaces.md

quickstart.md

Add an approved service/action boundary such as:

InventoryService

AdjustInventoryAction

DeductInventoryForOrder

RestoreInventoryForCancellation

Use the simplest architecture aligned with the existing service style.

Do not create unnecessary abstractions.

21. Tasks Update

Update tasks.md.

Add implementation-ready tasks in the correct phases.

Inventory tasks should integrate with:

Catalog/Admin phase

stock schema/model

admin stock display/actions

adjustment history

Cart/Checkout

availability / requested qty validation

Order placement

transactional stock lock/check/deduction

Cancellation

stock restore

Quality/tests

concurrency/idempotency/negative-stock tests

Do not place everything in one giant task.

22. Mandatory Inventory Tests

Create explicit task coverage for:

manual add stock

manual remove stock

cannot reduce below zero

stock zero => unavailable

cart does not reserve stock

checkout rejects insufficient stock

order deducts stock

multi-line order deducts all atomically

failed order rolls back stock deduction

concurrent ordering cannot oversell

cancellation restores stock

cancellation restore is idempotent

delivered order cannot restore via cancellation

manual adjustment history is recorded

order adjustment history is recorded

These are mandatory commercial/integrity tests.

23. Scope Guard

Still OUT of MVP:

suppliers

purchase orders

goods receiving workflows

warehouses

multi-warehouse stock

batch/lot tracking

expiry dates

FIFO/LIFO

stock valuation/cost accounting

barcode scanning

stock transfer

unit-conversion stock

automated procurement

ERP integration

Simple inventory only.

24. Master Runner / Prompt Integration

Update implementation execution guidance as required so inventory work is not skipped.

If the autonomous master runner has NOT yet executed affected phases:

update the relevant phase prompts/tasks before continuing.

If implementation of affected phases has already happened:

create a dedicated inventory implementation phase/change set after planning updates

do not silently patch it without tests.

Do not rewrite unrelated working code.

25. Final Planning Report

Return:

result: PASS / BLOCKED

files updated

revised table count

chosen stock-balance architecture

inventory adjustment schema

order deduction timing

cancellation restore behavior

concurrency strategy

cart behavior

admin actions

customer availability behavior

tasks added/modified

mandatory tests added

scope exclusions confirmed

Constitution Check

whether implementation can safely continue

End with:

SIMPLE INVENTORY MVP SCOPE READY FOR IMPLEMENTATION

Do NOT implement application code in this prompt unless explicitly instructed separately.