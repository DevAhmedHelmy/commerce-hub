Claude Prompt — Migration + Visual Review Gate

Prompt Number

36

Purpose

Add a mandatory runtime verification step to the project workflow so we can SEE what has actually been implemented after every relevant phase.

This prompt updates the implementation workflow.

It does NOT change approved business requirements.

1. Database Migration Rule

Whenever a phase creates or modifies:

migrations

tables

columns

indexes

constraints

seeders

factories needed for demo/review

Claude MUST verify the local database state.

From Laravel root:

src/

run appropriate safe commands.

Typical local command:

php artisan migrate

Do NOT use:

php artisan migrate:fresh

or:

php artisan db:wipe

unless the user explicitly approves destructive database reset.

Preserve existing local data by default.

2. Migration Status Check

Before and after migrations, inspect:

php artisan migrate:status

Report:

pending migrations

executed migrations

failures

database connection errors

Do not mark a migration task complete if migration execution fails.

3. Database Connection Check

Before running migrations verify:

.env exists locally

DB_CONNECTION is correct

MySQL connection works

target DB exists

Do not expose passwords or secrets in the report.

If the database does not exist, report the required DB name and stop or create it only if the current environment/tooling safely allows it.

4. Seeder / Demo Data Rule

If the implemented screen requires data to be meaningfully reviewed, use approved seeders/factories.

Prefer:

php artisan db:seed

or a specific seeder:

php artisan db:seed --class=...

Only seed safe local/demo data.

Do NOT insert:

production credentials

real customer personal data

real OTP secrets

production tokens

Do not repeatedly duplicate seed data.

Seeders should be idempotent where practical.

5. Admin Review Data

For admin pages, ensure there is enough local demo data to see:

categories

products

selling units

prices

offers

stock quantities when inventory is implemented

delivery areas/slots when implemented

orders when implemented

Do not fabricate unsupported business behavior.

Use realistic demo records only.

6. Local Admin User

If Admin UI needs a user to review:

create/use a safe local development admin account

never hard-code production credentials

never commit real passwords

If a dev-only account is created, clearly report:

local-only purpose

how it was created

where to change credentials

Do not expose a default production admin account.

7. Visual Runtime Gate

After every phase that changes frontend/admin UI, Claude MUST verify that the application can actually run.

From:

src/

verify backend:

php artisan serve

and frontend assets:

npm run dev

or if the workflow uses a production-build verification:

npm run build

Do not start duplicate unnecessary processes if servers are already running.

8. Required Screen URLs

At the end of every UI-bearing phase, report exact local URLs available for manual review.

Example format:

Customer Home:
http://127.0.0.1:8000/

Admin Login:
http://127.0.0.1:8000/admin/login

Admin Dashboard:
http://127.0.0.1:8000/admin

Products:
http://127.0.0.1:8000/admin/products

Only list routes that actually exist.

Do NOT invent routes.

9. Browser / Visual Verification

When browser or Playwright tooling is available:

Open affected pages and visually verify them.

At minimum check representative viewports:

Mobile: 390×844

Tablet: 768×1024

Laptop: 1024×768

Desktop: 1440×900

For affected UI also verify:

RTL

system light mode

system dark mode

no horizontal overflow

no broken layout

no missing assets

no missing images

no unreadable text

no console-breaking frontend errors

10. Screenshot / Visual Evidence

When browser tooling supports screenshots, capture representative screenshots for internal QA.

At minimum for major new UI:

one mobile screenshot

one desktop screenshot

For Admin Dashboard work:

dashboard desktop

dashboard mobile/tablet if changed

For Customer PWA:

mobile

laptop/desktop

Do not commit temporary QA screenshots unless the project explicitly needs them.

11. UI Phase Definition of Done

A UI-bearing phase is NOT complete merely because:

files exist

tests pass

build passes

It must also be viewable locally.

Before marking the phase complete confirm:

migrations are applied

required seed/demo data exists

application boots

route loads

assets compile/load

UI is visually usable

responsive checks pass

light/dark checks pass

RTL check passes

exact review URLs are reported

12. Backend-Only Phase Definition of Done

For backend-only phases:

migrations applied if required

tests pass

affected commands/services execute

database state is valid

No fake visual review is required for backend-only work.

13. Migration Safety With Inventory

For inventory-related migrations:

do not drop existing product/unit data

do not initialize existing stock to arbitrary positive values

safe default should be documented

backfill existing records intentionally

If stock_quantity is added to existing product units:
prefer a deterministic safe default such as 0 unless an approved migration strategy specifies otherwise.

Report any data migration/backfill decision.

14. Migration Safety With Product Image

For product image schema changes:

image_path should be nullable unless the approved data model says otherwise

existing products must remain valid without images

placeholder handles null image

Do not make existing product records fail migration.

15. Failed Migration Handling

If migration fails:

STOP

inspect the actual database error

do not continue implementing dependent features

do not mark dependent tasks complete

report exact failing migration and reason

Return:

MIGRATION BLOCKED

until resolved.

16. Update Master Runner Behavior

For every remaining phase:

If DB schema changed:

implementation
→ migrate:status
→ migrate
→ tests
→ runtime verification
→ visual review if UI
→ mark tasks
→ commit

If migration or runtime verification fails:
STOP the autonomous runner.

Do not continue to the next phase.

17. Current Project Check

After adopting this prompt, perform a CURRENT STATE CHECK without destructive changes:

run git status --short

run php artisan migrate:status

apply safe pending migrations with php artisan migrate

run relevant seeders only if needed

run tests

run/build frontend

identify all currently implemented screens

report exact URLs to review

Do not implement unrelated missing features during this check.

Required Final Report

Return:

database connection result

migration status before

migrations executed

migration status after

seeders executed

test result

frontend build/dev result

application boot result

implemented screens found

exact local review URLs

responsive visual checks performed

light/dark check

RTL check

current blockers

git status --short

End with:

RUNTIME AND VISUAL REVIEW READY

only if migrations and current application runtime succeed.