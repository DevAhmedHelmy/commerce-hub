
Claude Prompt — Generate Implementation Tasks

Prompt Number

10

Phase

Phase 4 — Implementation Task Breakdown

Purpose

Generate an implementation-ready task breakdown for the approved Restaurant Supplies PWA MVP.

This step converts the approved specification, design, and technical plan into ordered, executable tasks.

DO NOT implement application code in this phase.

Command

Run:

/speckit-tasks

for feature:

001-restaurant-supplies-mvp

Read First

Before generating tasks, read and treat as authoritative:

.specify/memory/constitution.md

specs/001-restaurant-supplies-mvp/spec.md

specs/001-restaurant-supplies-mvp/plan.md

specs/001-restaurant-supplies-mvp/research.md

specs/001-restaurant-supplies-mvp/data-model.md

specs/001-restaurant-supplies-mvp/contracts/service-contracts.md

specs/001-restaurant-supplies-mvp/contracts/http-and-admin-surfaces.md

specs/001-restaurant-supplies-mvp/quickstart.md

all files under specs/001-restaurant-supplies-mvp/design/

all prompt-history files under docs/prompts/ in numeric order

Later-numbered prompts supersede earlier prompts where they explicitly change a prior decision.

1. Repository Structure — HARD CONSTRAINT

The Laravel application MUST live inside:

/src

at repository root.

Expected structure:

restaurant-supplies-pwa/
├── .specify/
├── specs/
├── docs/
│   └── prompts/
├── src/
│   └── Laravel application
└── ...

The task list MUST assume:

repository root = project/spec/documentation root

Laravel application root = src/

All implementation paths must therefore target src/....

Examples:

src/app/...

src/routes/...

src/resources/...

src/database/...

src/tests/...

src/public/...

Do NOT generate tasks that install Laravel directly into repository root.

2. Approved Stack

Tasks must comply with the approved plan:

PHP 8.2 maximum

Laravel 12.x

MySQL 8

Blade

Alpine.js

Tailwind CSS

approved Filament compatible major from the final technical plan

PWA

one Laravel modular monolith

one MySQL database

no Redis requirement

no WebSocket requirement

no Docker production requirement

low-cost hosting compatible

Do NOT add dependencies that require PHP 8.3+.

3. Task Strategy

Generate tasks in implementation order.

Prefer:

clear dependency ordering

independently testable vertical slices

small enough tasks for safe execution

explicit file paths

clear completion criteria

Avoid:

giant vague tasks such as "build checkout"

splitting every trivial line into separate tasks

architecture redesign during task generation

Every task must be concrete enough that Claude can implement it later without inventing missing requirements.

4. Required Phase Structure

Organize tasks approximately into these phases.

Phase A — Repository & Laravel Foundation

Include tasks for:

create src/

create Laravel 12 application inside src/

enforce PHP 8.2 Composer platform constraint

environment setup

MySQL configuration

Arabic default locale/fallback

timezone/config review

Tailwind/Alpine verification

install approved Filament version

configure admin panel

base test tooling

storage link

initial PWA foundation

project-level README/quickstart alignment if needed

No business features yet.

Phase B — Shared Foundations

Include:

language-neutral enums/value identifiers

centralized Money representation/calculation

money display formatter (444 ج)

date/time presentation formatting

reusable result/DTO structures where approved

shared validation/localization foundations

Arabic translation files

RTL base layout

error/empty/loading component foundations

image upload/storage support

Keep abstractions justified and minimal.

Phase C — Authentication & Customer Onboarding

Vertical slice should include:

customer model/storage

customer address storage

OTP verification storage

customer guard/session

OTP provider contract

safe local/demo OTP provider

resend cooldown

expiry

attempt/rate limiting

phone entry

OTP screen

first-time profile completion

default delivery address

returning-customer flow

automated tests

Do NOT require a real paid OTP provider yet.

Phase D — Catalog Administration + Customer Catalog

Include:

Admin:

categories

products

selling units

availability

product images

Customer:

landing/catalog data required by approved design

home

categories

product listing

search

product details

Localization fields must follow approved _ar / _en strategy for selected managed content.

Arabic remains the only exposed MVP language.

Product cards must NOT directly add to cart.

Phase E — Pricing & Offers

This is business-critical.

Include implementation and mandatory automated tests for:

selling-unit base price

quantity price tiers

product offers

lower-of tier/offer rule

price result DTO

price boundaries

active/expired offers

pricing explanation data

admin pricing management

admin offer management

Tests are NON-OPTIONAL.

Phase F — Cart & Minimum Order

Include:

persistent DB cart

one cart per customer

cart item add/update/remove

selected product unit

quantity

server-side recalculation

minimum-order setting

minimum-order progress

out-of-stock/inactive-unit handling

pricing-change handling

customer Cart UI

automated tests

Cart must NOT show authoritative delivery-fee estimates.

Phase G — Delivery Administration & Calculation

Include:

delivery areas

area base fee

delivery slots

active/inactive handling

delivery discount rules

deterministic DeliveryService

largest-saving rule

fixed/percentage/free-delivery handling

tie-break behavior

zero-floor rule

admin management

automated tests

No slot capacity.
No drivers.
No route planning.

Phase H — Checkout

Implement approved two-step checkout.

Step 1

Delivery

Step 2

Review & Confirm

Tasks must cover:

saved address reuse/edit

delivery area

delivery date

delivery slot

COD only

server recalculation

changed-commercial-terms detection

structured changes result

requiring explicit review again

minimum order revalidation

product/unit availability revalidation

delivery revalidation

Feature tests are mandatory.

Phase I — Order Placement & Snapshots

Include:

transactional order creation

concrete order-number generation

unique constraint

duplicate-submit protection

immutable order header snapshot

immutable order-item snapshots

Arabic localized display snapshots for MVP

cart clear after successful order

rollback on failure

success screen

feature tests

No event sourcing.

Phase J — Order Lifecycle

Include:

Customer:

order list

order details

customer cancel only from new

Admin:

order list

filters/search

order details

transitions

cancellation rules

status actions

Approved internal values:

new

confirmed

preparing

out_for_delivery

delivered

cancelled

Arabic UI labels remain presentation translations.

Status transition tests are mandatory.

Phase K — Admin Dashboard & Customer Directory

Include:

dashboard widgets

new orders

today's orders

today's sales

recent orders

customer directory

customer order history

efficient queries

Arabic-first admin UI

No advanced analytics.
No advanced RBAC.

Phase L — Landing Page

Include approved Arabic-first landing-page design:

header

hero

categories

offer products

benefits

ordering steps

delivery coverage

CTA

footer/contact

No CMS.

Reuse real catalog/offers/settings data where approved.

Phase M — PWA Completion

Include:

manifest

icons

theme/background

service worker

safe static asset caching

offline fallback

Explicitly prevent caching sensitive authenticated content:

OTP/auth

profile

address

cart

checkout

orders

No offline order creation.
No background sync.

Phase N — Quality / Performance / Security

Include final tasks for:

authorization review

CSRF

mass assignment

upload security

session security

OTP abuse protection

N+1 review

eager loading

pagination

indexes

image optimization

Arabic RTL review

mixed Arabic/English content

accessibility

responsive checks

PHP 8.2 dependency verification

composer check-platform-reqs

full automated test suite

Do NOT leave these as vague "polish" tasks.

Phase O — Staging / Demo Readiness

Include:

seed/demo data

categories

products

units

price tiers

offers

delivery areas

slots

sample orders where appropriate

safe demo OTP behavior

production OTP safety guard

staging environment instructions

PWA installability test

Demo content should support presenting the system to the client.

Phase P — Production Deployment Preparation

Include:

src/public web root requirement

shared-hosting-safe deployment notes

production .env

Composer production install

frontend asset build

writable directories

storage link

scheduler if required

backup strategy

log review

HTTPS

production test OTP prohibition

final smoke test

Do NOT require Docker.

5. Testing Requirements

Constitution Principle VII is NON-NEGOTIABLE.

Generate explicit test tasks for all critical business logic.

Mandatory:

Pricing

base price

quantity tiers

4→5 boundary

9→10 boundary

offer eligibility

expired offer

lower-of tier vs offer

Minimum Order

below threshold

exact threshold

above threshold

Delivery

fixed discount

percentage discount

free delivery

multiple eligible rules

tie-break

discount cannot exceed fee

inactive area

inactive slot

Orders

transactional creation

immutable snapshot

unique order number

duplicate submit

rollback

cancellation matrix

status transitions

OTP

expiry

invalid code

consumed code

resend cooldown

attempt limit

rate limiting

Checkout

price changes

expired offer

out of stock

unit inactive

delivery fee change

delivery discount change

slot inactive

changed-terms review requirement

6. Localization Tasks

MVP exposed language:

Arabic only.

Future English readiness must remain.

Task list should include:

Arabic translation catalogs

no hard-coded domain labels

language-neutral internal values

RTL-first layouts

mixed Arabic/English brand support

Latin digits

centralized 444 ج formatting

Managed translatable content uses approved _ar / _en fields selectively.

Do NOT add language switcher.

Do NOT expose English UI.

Do NOT create unnecessary translation workflow.

7. Search Tasks

Initial search stays MySQL-based.

Tasks should cover:

Arabic product names

brand

optional populated English name fields where appropriate

category context where approved

useful indexes

Do NOT add:

Scout

Meilisearch

Elasticsearch

unless the approved plan explicitly requires it, which it should not for MVP.

8. Admin Implementation Rules

Filament is a presentation layer.

Tasks must NOT place core pricing/order/delivery business logic inside Filament resources.

Filament actions should call approved services/actions.

Avoid unnecessary third-party Filament plugins.

9. Scope Guard

Do NOT create implementation tasks for:

English UI

language switcher

online payments

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

Future-readiness notes are acceptable.

Implementation tasks are not.

10. Parallelism

Mark tasks parallelizable only where they are genuinely independent.

Do not mark database migrations and dependent model/service tasks parallel when ordering matters.

Use Spec Kit's task notation correctly.

11. File Paths

Every implementation task must provide concrete target paths where practical.

Remember:

Laravel root is src/.

Examples:

src/app/...
src/database/migrations/...
src/resources/views/...
src/routes/...
src/tests/...

Design/spec files remain outside src/.

12. Task Acceptance

Every user-story/vertical-slice phase should finish with a state that can be tested independently.

Examples:

Authentication slice:
customer can OTP login and complete profile.

Catalog slice:
admin can create a product/unit and customer can browse it.

Pricing slice:
tier/offer rule is deterministic and tested.

Ordering slice:
customer can place an immutable COD order.

Avoid leaving essential integration until the very end.

13. Required Final Report

After /speckit-tasks completes, return:

path of generated tasks.md

total task count

task count by phase

P1 / P2 / P3 story mapping

tasks marked parallel

mandatory automated test task count

first executable MVP vertical slice

dependencies / critical path

any task that appears too large and should be split

any scope conflict found

Constitution Check result

confirmation that no application code was written

Do NOT run /speckit-analyze.

Do NOT implement tasks.

Do NOT create Laravel application code yet.

Stop after generating and validating the task breakdown.