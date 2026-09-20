Claude Prompt — Add Admin Audit Log for All Critical Admin Actions

Prompt Number

39

Phase

Cross-Cutting Administration / Audit Trail

Purpose

Implement a real Admin Audit Log.

This is the clarified requirement that supersedes the earlier misunderstanding in Prompt 31.

The system must record important admin actions, especially:

price changes

pricing tier changes

offer changes

inventory changes

product changes

unit/conversion changes

order status changes

cancellations

important delivery/settings changes

The audit trail must show:

who performed the action

what entity changed

what action occurred

old values

new values

timestamp

Laravel application root:

src/

1. Audit Log Scope

Audit critical admin write actions.

At minimum:

Products

create

update

activate/inactivate

product image replace/remove

Units

create/update/activate/inactivate

primary/sub assignment changes

conversion-factor changes

Pricing

base price changes

quantity-tier create/update/delete/deactivate

offer create/update/deactivate

offer price/date changes

Inventory

manual add

manual remove

correction

Note:
Inventory adjustments already have their own business ledger/history.
The general audit log may reference them but must not replace the inventory ledger.

Orders

status change

admin cancellation

Delivery

delivery area fee changes

delivery slot changes

delivery discount rule changes

Settings

minimum-order setting changes

other critical commerce settings

2. Audit Log Table

Create a dedicated audit log table, e.g.:

admin_audit_logs

Suggested fields:

id

user_id nullable only if system action allowed

action

auditable_type

auditable_id

old_values JSON nullable

new_values JSON nullable

metadata JSON nullable

ip_address nullable

user_agent nullable

created_at

No updated_at is required if audit records are immutable.

Exact naming may follow project conventions.

3. Immutability

Normal admin UI must NOT allow:

editing audit log records

deleting audit log records

Audit history is append-only for normal application behavior.

Do not add destructive CRUD actions to the Audit Log resource.

4. Language-Neutral Action Codes

Store language-neutral codes, e.g.:

created

updated

deleted

activated

deactivated

price_changed

stock_added

stock_removed

stock_corrected

order_status_changed

order_cancelled

Arabic labels are presentation-only.

5. Price Change Detail — Mandatory

Price changes are critical.

Audit must clearly record:

product

selected selling unit

old price

new price

admin actor

timestamp

Example concept:

Product: كاتشب
Unit: كرتونة
Old Price: 1,100 ج
New Price: 1,250 ج
Changed By: Ahmed
Changed At: ...

Store monetary old/new values in the same deterministic integer-minor-unit representation used by the project.

Do not store formatted Arabic currency as the authoritative audit value.

6. Tier / Offer Changes

For price tiers record meaningful values such as:

unit

min quantity

old tier price

new tier price

active state

For offers:

target product unit

old/new offer price

start/end

active state

Avoid dumping giant irrelevant model blobs.

Store only audited business-relevant fields.

7. Inventory Audit

Keep the dedicated inventory_adjustments ledger as inventory truth.

General Audit Log should record/admin-reference the action at a higher level.

Avoid double-counting or conflicting stock history.

Example metadata may reference:

inventory_adjustment_id

8. Sensitive Data

Never audit:

passwords

password hashes

OTP codes/hashes

session tokens

API secrets

private credentials

Redact sensitive fields centrally.

9. Central Audit Service

Use one consistent mechanism.

Preferred:

AdminAuditService

or equivalent.

Responsibilities:

capture actor

action code

entity

selected old/new fields

request metadata where safe

write immutable record

Do not scatter raw audit inserts throughout Filament resources.

Filament/actions/domain services should call the centralized audit mechanism at meaningful successful transaction boundaries.

10. Transaction Semantics

For critical commerce mutations:

Audit records should only persist when the actual business change succeeds.

Where practical, write the audit event within the same transaction or after successful mutation with transaction-safe semantics.

Do not log successful price changes if the price transaction actually rolled back.

11. Admin Audit Log UI

Add read-only Filament Audit Log screen/resource.

Arabic-first.

Support useful filters:

admin/user

action type

entity type

date range

Useful table columns:

التاريخ

المستخدم

الإجراء

النوع

السجل

ملخص التغيير

Detail view should show readable old/new changes.

No edit/delete actions.

Responsive dashboard requirements from /restaurant-ui apply.

12. Human-Readable Diff

Do not show raw ugly JSON as the only detail.

Create a readable difference view.

Example:

السعر:
1,100 ج → 1,250 ج

For booleans/statuses, use localized labels.

For IDs, resolve safe display names where practical without making historical rendering depend entirely on live data.

Store enough snapshot/metadata for understandable audit history.

13. Search / Filtering

MVP does not need Elasticsearch.

Use MySQL queries/indexes.

Add useful indexes for:

user_id

action

auditable_type + auditable_id

created_at

Avoid over-indexing JSON.

14. Authorization

Only authenticated authorized admins can view audit history.

Customers must never access it.

No public route.

Advanced RBAC is still outside MVP; use current admin authorization model.

15. Retention

Do not auto-delete audit records in MVP.

No purge UI.

Future retention policy may be added later.

16. Tests

Mandatory:

product update creates audit

base price change records old/new price

tier change creates correct audit

offer change creates correct audit

inventory manual action references relevant change

order status change audited

cancellation audited

delivery fee/settings change audited

failed transaction does not create false-success audit

sensitive fields are redacted

customer cannot access audit log

admin can view audit log

audit entries cannot be edited/deleted through normal admin UI

17. Planning/Tasks Update

Update:

spec.md if requirement not explicit

plan.md

data-model.md

contracts

tasks.md

admin-design/admin surfaces if needed

Do not duplicate inventory ledger responsibilities.

18. UI Quality

Use:

/restaurant-ui

Audit Log admin UI must be:

Arabic RTL

responsive

readable on laptop/tablet

usable on mobile when necessary

system dark mode compatible

theme-token based

19. Migration Gate

Use Prompt 36 rules.

Run:

php artisan migrate:status
php artisan migrate
php artisan test
npm run build

No destructive reset.

20. Git

Commit only after green checks:

feat: add admin audit log

Final Report

Return:

result

files changed

audit table schema

actions covered

price-change audit example

inventory-ledger integration

sensitive-field redaction

Filament Audit Log UI

tests

migration result

build result

responsive/dark/RTL result

commit hash/message

remaining gaps

End with:

ADMIN AUDIT LOG READY FOR PM REVIEW