Claude Prompt — Cross-Artifact Analysis Before Implementation

Prompt Number

11

Phase

Phase 4.1 — Task & Architecture Consistency Gate

Purpose

Run a complete Spec Kit analysis across the approved specification, design, technical plan, and generated implementation tasks BEFORE any implementation begins.

This is the final consistency gate before /speckit-implement.

Do NOT implement application code.

Command

Run:

/speckit-analyze

for feature:

001-restaurant-supplies-mvp

Read First

Treat the following as authoritative, in this order:

.specify/memory/constitution.md

specs/001-restaurant-supplies-mvp/spec.md

specs/001-restaurant-supplies-mvp/design/

specs/001-restaurant-supplies-mvp/plan.md

specs/001-restaurant-supplies-mvp/research.md

specs/001-restaurant-supplies-mvp/data-model.md

specs/001-restaurant-supplies-mvp/contracts/

specs/001-restaurant-supplies-mvp/quickstart.md

specs/001-restaurant-supplies-mvp/tasks.md

all files under prompts/ in numeric order

Later-numbered prompts supersede earlier prompts where they explicitly changed prior decisions.

1. Hard Repository Structure Check

Verify all implementation artifacts consistently assume:

repository root/
├── .specify/
├── specs/
├── 
│   └── prompts/
└── src/
    └── Laravel application

The Laravel application MUST live in src/.

Flag as BLOCKING if:

any task installs Laravel at repository root

any application file path omits src/

plan/quickstart/contracts still instruct root-level Laravel application setup

Planning/documentation files remain outside src/.

2. Runtime & Dependency Consistency

Verify all artifacts agree on:

PHP 8.2 maximum

Laravel 12.x

MySQL 8

current approved Filament major compatible with PHP 8.2 + Laravel 12

Blade

Alpine.js

Tailwind CSS

PWA

no Redis requirement

no WebSockets requirement

no Docker production requirement

Flag any dependency or task that could require PHP 8.3+.

Verify the plan/tasks include Composer platform emulation for PHP 8.2.

3. Localization Consistency

Verify the current approved localization strategy is represented everywhere:

MVP:

Arabic only exposed

RTL-first

no language switcher

Latin digits

currency format 444 ج

Future:

English-ready

Managed business content uses selective _ar / _en fields where approved.

Verify:

categories

products

product unit display names

offer titles/descriptions where applicable

delivery area display names

are consistent with the approved schema decision.

Verify:

brand remains a single commercial value

customer-entered content is not duplicated by locale

internal statuses/enums remain language-neutral

order snapshots capture immutable localized display text

Flag any artifact still using the superseded "Arabic-only scalar now, add localization later" approach.

4. Product / Pricing Consistency

Verify all artifacts agree that:

products may have multiple selling units

authoritative prices belong to selling units, not product alone

quantity tiers are per selling unit

product offers are evaluated server-side

when both tier and offer apply, the lower eligible price wins

tier and offer do not stack

listing cards remain lightweight

Product Details handles unit + quantity + final pricing

Product Cards do NOT directly Add to Cart

Flag any conflicting task/design/contract.

5. Cart Consistency

Verify:

persistent database cart

one cart per authenticated customer using a MySQL-enforceable strategy

cart items store product/unit/quantity

cart prices are not authoritative

prices are recalculated server-side

cart shows product subtotal + minimum order progress

cart does NOT show authoritative or estimated delivery fee

delivery calculations occur in checkout

Flag any task that implements cart price snapshots as authoritative.

6. Minimum Order Consistency

Verify minimum-order qualification uses:

effective product subtotal AFTER:

tier pricing

product offers

and BEFORE:

delivery fee

delivery discount

Verify boundary tests include:

below threshold

exact threshold

above threshold

7. Delivery Consistency

Verify:

selected delivery areas only

base fee per delivery area

slots have no numeric capacity in MVP

delivery slot model is concrete

slot active/inactive state is revalidated at checkout

delivery discount types:

fixed

percentage

free

rules never stack

best rule = greatest actual monetary saving

tie-break = higher qualifying minimum subtotal

delivery fee never drops below zero

Verify checkout snapshots delivery date + selected slot display/time so historical orders do not mutate.

8. Checkout Consistency

Verify the approved UI and workflow is consistently:

Step 1:
بيانات التوصيل

Step 2:
مراجعة الطلب

Final CTA:
تأكيد الطلب

Server revalidation must cover:

product active

product available

selling unit active

pricing

tier

offer

minimum order

delivery area

delivery fee

delivery discount

delivery slot

If reviewed commercial terms changed:

order is NOT silently created

presentation receives structured changes

customer must review again

Flag any task that silently recalculates and submits.

9. Orders / Snapshot Consistency

Verify:

order creation is transactional

human-friendly order number strategy is concrete

DB uniqueness is enforced

duplicate submit is guarded

historical order items do not depend on live catalog data

order header snapshots customer/delivery/commercial data needed for history

order item snapshots product/unit/pricing data

cart is cleared only after successful order creation

No event sourcing.

10. Order Status Consistency

Internal statuses must be exactly:

new

confirmed

preparing

out_for_delivery

delivered

cancelled

Verify transition matrix is consistent.

Customer self-cancel:

only from new

Admin cancel:

new

confirmed

preparing

out_for_delivery

No cancellation from:

delivered

cancelled

Arabic labels belong to presentation only.

11. OTP Consistency

Verify:

customers separate from admin users

phone + OTP customer authentication

hashed OTP

expiry

one-time consume

resend cooldown

attempt limit

rate limiting

no production OTP logging

provider abstraction

local/demo provider only outside production

No paid OTP vendor is required yet.

12. Admin / Filament Consistency

Verify:

Filament is presentation only

pricing/order/delivery business rules remain in services/actions

admin is Arabic-first

no advanced RBAC

no unnecessary Filament plugins

Products management is split logically rather than one giant form

all required admin resources/pages exist in tasks

13. PWA Safety Consistency

Verify:

manifest

icons

installability

service worker

static asset caching

offline fallback

BUT DO NOT cache sensitive authenticated/customer-specific content such as:

OTP/auth responses

profile

addresses

cart

checkout

order history

order details

No offline ordering.
No background sync.

Flag any task/service-worker plan that could cache sensitive HTML/API responses.

14. Money Consistency

Verify one authoritative money strategy exists.

Expected:

integer minor units

no binary floating point for commerce calculations

centralized Money abstraction/helper

centralized presentation formatting

Arabic display 444 ج

Flag any artifact still using ambiguous DECIMAL-vs-integer alternatives.

15. Search Consistency

Verify MVP search is MySQL-based only.

Search should support:

Arabic product names

brands

relevant category context

optional _en content if populated

Do NOT require:

Scout

Meilisearch

Elasticsearch

external search infrastructure

FULLTEXT should not be mandatory unless explicitly justified.

16. Queue / Infrastructure Consistency

Verify one clear MVP queue strategy.

Expected:

normal work synchronous

no persistent queue worker requirement

no Redis

DB queue only as optional fallback if needed

cron/scheduler only when actually required

Flag tasks that unnecessarily introduce queue infrastructure.

17. Task Dependency Review

Review all 143 generated tasks for dependency correctness.

Specifically inspect the current reported contradiction:

The report says the critical path is:

A → B → C → D → E → F → G → H → I → J

but also says:

G is parallelizable with D–F after foundational work.

Resolve this.

Produce the actual critical path.

If G can genuinely proceed in parallel, show the proper dependency graph, for example conceptually:

A → B → C
├→ D → E → F ┐
└→ G ─────────┤
↓
H → I → J

Do not change dependencies merely to maximize parallelism.

Correct tasks.md if necessary.

18. Parallel Task Audit

90 tasks were marked [P].

Audit EVERY [P] designation.

A task may be marked [P] only if:

it does not modify the same file as another concurrent task

it does not require output from the other task first

it does not rely on migrations/models/services that have not yet been created

running concurrently cannot create merge conflicts or inconsistent assumptions

Remove [P] from questionable tasks.

Correctness is more important than parallel count.

Return:

original parallel count

corrected parallel count

reasons for meaningful reductions, if any

19. Oversized Task Audit

Specifically review:

T044

T083

T093

T126

and any other large tasks.

Split tasks if they are too large for safe implementation/review.

Preferred task size:

one coherent change that can be implemented, tested, and reviewed without requiring a huge multi-file unbounded edit.

Do not over-fragment trivial work.

If splitting tasks changes IDs, keep task ordering coherent and update references.

20. Testing Coverage Audit

Verify Constitution Principle VII.

There must be explicit tests for:

Pricing

base price

tier boundaries 4→5 and 9→10

offer eligibility

expired offer

lower-of rule

Minimum Order

below

exact

above threshold

Delivery

fixed

percentage

free

multiple rules

tie-break

floor zero

inactive area

inactive slot

Checkout

price change

expired offer

out of stock

inactive unit

fee change

discount change

slot change

changed-terms review

Orders

transaction rollback

immutable snapshot

unique order number

duplicate submission

cancellations

status transitions

OTP

invalid

expired

consumed

resend cooldown

attempts

rate limit

Flag any missing mandatory test task.

21. User Story / MVP Slice Audit

Verify tasks still map to P1/P2/P3 stories and that P1 can produce a real end-to-end ordering MVP.

Identify:

first independently testable vertical slice

first complete customer-ordering slice

first client-demo-ready milestone

If useful, recommend milestone boundaries, but do NOT add scope.

22. Scope Guard Audit

Ensure no tasks implement:

English UI

language switcher

online payment

credit accounts

customer-specific pricing

full inventory quantities

warehouses

multiple branches

drivers

live tracking

route optimization

loyalty

advanced coupons

recurring orders

Buy Again

advanced analytics

advanced RBAC

microservices

Readiness only is acceptable.

23. Constitution Check

Run a full check against all nine constitution principles.

Any real violation is BLOCKING.

Do not label a blocker as "acceptable technical debt."

24. Allowed Changes

During /speckit-analyze, you MAY update planning artifacts such as:

tasks.md

plan.md

research.md

data-model.md

contracts

quickstart

ONLY when required to resolve an inconsistency.

Do NOT change approved business requirements silently.

If a true business-level contradiction is found, report it instead of inventing a resolution.

Do NOT create application code.

Required Final Report

Return:

Analysis result: PASS / PASS WITH CORRECTIONS / BLOCKED

files modified by analysis

issue count by severity:

Critical

High

Medium

Low

exact critical path after correction

original vs corrected [P] task count

oversized tasks found/split

mandatory test coverage result

spec ↔ design ↔ plan ↔ data model ↔ contracts ↔ tasks consistency result

repository src/ structure result

PHP 8.2 / dependency result

localization result

commerce/pricing result

checkout/order snapshot result

PWA privacy result

scope guard result

Constitution Check result

remaining blockers, if any

confirmation that no application code was written

If there are zero blockers and all corrections are complete, end with:

READY FOR IMPLEMENTATION GATE REVIEW

Do NOT run /speckit-implement.

Do NOT implement application code.