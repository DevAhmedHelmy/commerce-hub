Claude Prompt — Resume Master Implementation From Phase D After Disk-Space Block

Prompt Number

33

Purpose

Resume the autonomous implementation workflow from Phase D after the previous master run stopped because disk space was exhausted.

Phase C is already complete and must NOT be reimplemented.

The disk-space issue has been resolved.

Before resuming, the Simple Inventory MVP scope amendment from Prompt 32 must already have been applied to the planning artifacts.

Required Prerequisite

Read and confirm Prompt 32 has been applied:

prompts/32_add_simple_inventory_core_mvp.md

Confirm that the following now reflect Simple Inventory as a core MVP requirement:

specs/001-restaurant-supplies-mvp/spec.md

specs/001-restaurant-supplies-mvp/plan.md

specs/001-restaurant-supplies-mvp/data-model.md

specs/001-restaurant-supplies-mvp/contracts/

specs/001-restaurant-supplies-mvp/tasks.md

If Prompt 32 has NOT been applied, STOP and return:

RESUME BLOCKED — INVENTORY SCOPE UPDATE REQUIRED

Do not continue Phase D on the old task model.

Read Before Resuming

Read:

.specify/memory/constitution.md

.claude/skills/restaurant-ui/SKILL.md

specs/001-restaurant-supplies-mvp/spec.md

specs/001-restaurant-supplies-mvp/plan.md

specs/001-restaurant-supplies-mvp/research.md

specs/001-restaurant-supplies-mvp/data-model.md

specs/001-restaurant-supplies-mvp/contracts/

specs/001-restaurant-supplies-mvp/quickstart.md

specs/001-restaurant-supplies-mvp/tasks.md

all files under specs/001-restaurant-supplies-mvp/design/

all files under prompts/ in numeric order

Later prompts supersede earlier ones.

1. Do Not Re-run Phase C

Phase C is already complete.

Do NOT:

recreate customer auth

recreate OTP flow

rewrite onboarding

repeat Phase C migrations/services/tests

create a duplicate Phase C commit

Only run a quick regression check if needed to ensure Phase C still passes after later schema/planning changes.

2. Inspect Current Working Tree First

The previous Phase D run may have created partial files before disk space ran out.

Before changing anything:

Run:

git status --short

Inspect all uncommitted files.

Classify each Phase D change as:

complete and valid

partial but salvageable

incomplete/broken

unrelated

Do NOT discard valid work.

Do NOT use:

git reset --hard

git clean -fd

destructive checkout

force operations

Preserve unrelated user changes.

3. Validate Disk Space Is Actually Available

Before dependency/build-heavy work, verify the system drive has enough free space.

On Windows, use an appropriate command such as:

Get-PSDrive C

or equivalent.

If space is still critically low, STOP before package/build operations.

Report the available space.

Do not fill the disk again with duplicate caches or unnecessary build artifacts.

4. Resume Phase D, Not Restart the Whole Project

Resume from:

Phase D — Catalog Administration & Customer Catalog

Use:

prompts/15_implement_phase_D_catalog.md

plus all later approved amendments, especially:

Prompt 29 — frontend quality gates

Prompt 30 — landing-page reference implementation where relevant later

Prompt 32 — Simple Inventory

this Prompt 33

Continue from the first incomplete Phase D task in tasks.md.

Do not blindly rerun tasks already complete and verified.

5. Phase D Must Now Include Inventory-Aware Catalog Work

Because Simple Inventory is now core MVP, Phase D must include the inventory-related catalog/admin tasks assigned by the updated tasks.md.

At minimum, where assigned to Phase D:

stock quantity per selling unit

inventory adjustment history foundation

admin stock visibility

admin stock add/remove/correction actions

out-of-stock behavior driven by stock quantity

product-unit stock status

customer availability based on stock

no negative stock

Do NOT implement full purchasing/warehouse/ERP scope.

6. Frontend Rules

For all Phase D UI work:

Use:

/restaurant-ui

and the official frontend-design skill when available.

Mandatory:

Tailwind

Arabic RTL

responsive mobile/tablet/laptop/desktop

system dark mode

centralized theme.css

no scattered hard-coded brand colors

no Product Card Quick Add

customer PWA works on desktop too

admin dashboard/resources remain responsive

Run visual QA if browser/Playwright tooling is available.

7. Inventory Concurrency Scope

Do NOT prematurely implement order-time stock locking in Phase D unless the updated tasks.md explicitly assigns it here.

Phase D should establish the catalog/admin inventory foundation.

Order-time:

locking

atomic deduction

rollback

cancellation restore

belong to their updated later phases.

Do not duplicate future implementation early.

8. Phase D Completion Gate

Before committing Phase D:

all Phase D tasks are complete

relevant migrations/models/resources/services compile

catalog tests pass

inventory-admin tests assigned to Phase D pass

search tests pass

frontend production build passes

Phase C regression tests still pass

PHP 8.2 compatibility remains valid

responsive/RTL/dark-mode checks pass for affected UI

git status --short contains only intended Phase D changes

Then commit:

feat: complete phase D catalog and inventory foundation

Do not continue if Phase D is red.

9. Resume Remaining Master Run After D

After Phase D passes and commits successfully, continue autonomously through the remaining phases:

Phase E — Pricing & Offers

Phase F — Cart & Minimum Order

Phase G — Delivery

Phase H — Checkout

Phase I — Order Placement & Snapshots

Phase J — Order Lifecycle

Phase K — Admin Dashboard

Phase L — Landing Page

Phase M — PWA

Phase N — Quality/Security

Phase O — Demo Readiness

Phase P — Production Preparation

Use the corresponding prompt files under prompts/.

Respect all updated inventory tasks in those phases.

10. Inventory Requirements in Later Phases

The resumed run must now include, in the appropriate updated phases:

Cart

cart does not reserve stock

warn/reject when requested quantity exceeds available stock

Checkout

revalidate stock

insufficient stock becomes changed/unavailable commercial state

Order Placement

transactional row locking

atomic stock check

atomic stock deduction

inventory adjustment records

no overselling

rollback on failed order

Cancellation

restore stock exactly once

idempotent restoration

no restore for delivered/cancelled invalid paths

Admin

exact stock visible

adjustment history visible

low/out-of-stock visibility if approved

Tests

concurrent order safety

atomic multi-line deduction

rollback

cancellation restore

no double restore

manual adjustment history

order adjustment history

11. Audit Log Awareness

A broader admin Audit Log is planned separately.

Do NOT invent a second competing audit architecture inside Phase D.

Inventory adjustment history is required now because it is part of stock integrity.

General admin action audit logging, including price-change history, will be handled by its dedicated prompt/change set unless already included by an approved later artifact.

12. Git Safety

Per phase:

inspect git status --short

run tests

run build where relevant

verify PHP 8.2 compatibility

mark tasks honestly

commit only green phase changes

Never commit:

.env

credentials

secrets

vendor/

node_modules/

logs

temp/cache files

uploaded runtime content

Do not squash completed phase history.

13. Stop Conditions

STOP if:

inventory planning update is missing

disk space becomes critically low again

tests fail

frontend build fails

PHP 8.2 compatibility breaks

a required phase prompt is missing

business requirements conflict materially

unrelated working-tree changes make safe commit impossible

schema mismatch cannot be reconciled from approved artifacts

Return:

MASTER RUN BLOCKED AT PHASE X

with exact reason.

Required Final Report

Return:

resume result

free disk space confirmed

Phase C regression result

partial Phase D files found from previous run

partial work reused vs repaired

updated Phase D task completion

Phase D tests

Phase D commit hash/message

inventory foundation implemented in D

subsequent phases completed

per-phase commit hashes/messages

final full-suite result

PHP 8.2 compatibility result

remaining unchecked tasks

remaining blockers/warnings

final git status --short

whether project is ready for PM/UAT review

If all remaining phases pass, end with:

ALL IMPLEMENTATION PHASES D-P READY FOR FINAL PM/UAT REVIEW

Do NOT deploy to production automatically.