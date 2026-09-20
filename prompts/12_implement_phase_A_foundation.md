Claude Prompt — Implement Phase A: Repository & Laravel Foundation

Prompt Number

12

Phase

Phase 5 — Implementation / Batch 1

Scope

Implement Phase A only from:

specs/001-restaurant-supplies-mvp/tasks.md

Expected task range:

T001–T012

If the final analyzed tasks.md changed IDs, identify the tasks whose phase is exactly:

Phase A — Repository & Laravel Foundation

and implement only those tasks.

Do NOT continue into Phase B.

Command

Run:

/speckit-implement

but restrict execution to Phase A only.

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

Later-numbered prompts supersede earlier decisions where they explicitly changed them.

1. HARD Repository Structure

The Laravel application MUST be created inside:

src/

at repository root.

Required structure:

restaurant-supplies-pwa/
├── .gitignore
├── .specify/
├── specs/
├── 
│   └── prompts/
├── src/
│   └── Laravel application
└── CLAUDE.md

Do NOT install Laravel directly into repository root.

All application paths must be inside:

src/...

2. Runtime Constraints

Use:

PHP 8.2 maximum

Laravel 12.x

MySQL 8

Do NOT introduce any dependency that requires PHP 8.3+.

Composer dependency resolution must honor the production PHP 8.2 constraint even if the development machine is newer.

Configure Composer platform PHP appropriately according to the approved technical plan.

After dependency setup, verify platform compatibility.

3. Laravel Installation

Create the Laravel 12 application inside src/.

Do not create a nested duplicate repository.

Do not initialize another .git inside src/.

The root repository remains the only Git repository.

Preserve all existing root-level Spec Kit and documentation files.

4. Environment Foundation

Prepare the Laravel application for the approved environments.

Keep secrets out of Git.

Ensure:

src/.env remains ignored

src/.env.example is tracked

MySQL is the intended database

app key setup is documented/executed locally as appropriate

application environment defaults are safe

Do NOT commit credentials.

Do NOT put production secrets into .env.example.

5. Composer PHP Platform Safety

The production environment has a hard maximum of PHP 8.2.

Configure the project so Composer does not accidentally resolve PHP 8.3+-only packages.

Use an approved PHP 8.2 platform target.

Verify:

composer check-platform-reqs

or the appropriate equivalent after dependencies are installed.

Report any package whose requirements conflict with PHP 8.2.

A PHP 8.3+ package is a BLOCKER.

6. Filament

Install the exact approved Filament major from the finalized technical plan.

Use the current approved major verified compatible with:

PHP 8.2

Laravel 12

Do NOT silently downgrade to an older major.

Do NOT add third-party Filament plugins unless explicitly required by the approved plan.

Set up the base admin panel only.

Do NOT implement business resources yet.

7. Customer Frontend Foundation

Verify the Laravel frontend foundation needed for:

Blade

Tailwind CSS

Alpine.js

Do not build actual customer screens yet.

Only establish/verify the technical frontend foundation required by Phase A.

Avoid introducing Vue, React, Inertia, or a separate SPA stack.

8. Arabic / Localization Foundation

Arabic is the only exposed MVP language.

Prepare foundational Laravel configuration consistent with the approved plan:

default locale: Arabic

fallback locale: Arabic

RTL-ready direction metadata where Phase A requires it

Do not implement customer UI screens yet.

Do not expose English.

Do not add a language switcher.

Future English readiness must not be broken.

9. Testing Foundation

Set up/verify the approved testing stack.

Use the plan-approved Laravel testing approach.

Do not write all business tests yet.

Phase A should leave the application capable of running its base test suite successfully.

Run the base tests and report results.

10. PWA Foundation

Only implement the Phase-A PWA foundation tasks listed in tasks.md.

Do NOT prematurely build the full service worker behavior from Phase M.

If Phase A includes basic placeholders/foundation for:

manifest

PWA directory structure

icons placeholder strategy

implement only what the task list explicitly requires.

Do not cache authenticated routes or build offline ordering.

11. Storage Foundation

Perform only approved foundation setup such as:

storage directories

writable-path expectations

storage:link when appropriate

Do not implement product uploads yet.

Do not add S3/object storage.

12. Root .gitignore

Respect the existing root .gitignore.

Confirm it correctly ignores application runtime/generated files under src/, including as applicable:

src/vendor/

src/node_modules/

src/.env

build output

runtime logs/cache/sessions

storage symlink/runtime uploads

test output

Do NOT ignore:

.specify/

specs/



prompts/

CLAUDE.md

source code

migrations

tests

.env.example

If the existing .gitignore needs a small correction due to the actual Laravel-generated structure, update it carefully and report the change.

13. No Business Features Yet

Do NOT implement:

OTP flow

customers

catalog

products

categories

pricing

offers

cart

delivery

checkout

orders

landing-page content

admin business resources

Those belong to later phases.

This batch is FOUNDATION ONLY.

14. Task Tracking

As each Phase A task is completed:

mark only the completed task in tasks.md

do not mark later tasks

do not reorder tasks without a real reason

If a Phase A task cannot be completed, leave it unchecked and clearly report why.

Do not pretend a task is complete.

15. Validation Before Stopping

Before ending this batch, validate:

Laravel exists inside src/.

No Laravel app was created at repository root.

There is no nested src/.git.

Laravel is 12.x.

PHP dependency resolution is compatible with PHP 8.2.

Approved Filament version installs successfully.

Base Laravel application boots.

Base automated tests pass.

Frontend build foundation succeeds if Phase A requires it.

Arabic default/fallback locale is configured as planned.

.env is not tracked.

.env.example is tracked.

Root .gitignore behaves correctly.

No business feature from Phase B+ was implemented.

Only Phase A tasks are marked complete.

Required Final Report

Return:

Phase A result: PASS / PASS WITH ISSUES / BLOCKED

tasks completed

tasks not completed

files/directories created

exact Laravel version

exact PHP runtime used locally

Composer platform PHP setting

exact Filament version installed

composer check-platform-reqs result

test command + result

frontend/build verification result

localization config result

.gitignore changes, if any

any warnings or blockers

git status --short summary

confirmation that Phase B+ was NOT implemented

Stop after Phase A.

Do NOT automatically commit.

Do NOT continue to Phase B.

End with:

PHASE A READY FOR PM REVIEW

only if all Phase A acceptance checks pass.