Claude Prompt — Master Sequential Implementation Runner C→P

Prompt Number

28

Purpose

Execute the remaining approved implementation phases sequentially and autonomously from Phase C through Phase P.

The repository uses:

prompts/

at repository root for prompt history.

Do NOT look for prompts/.

Prompt Files

Read and execute these files in this exact order:

prompts/14_implement_phase_C_auth_onboarding.md

prompts/15_implement_phase_D_catalog.md

prompts/16_implement_phase_E_pricing_offers.md

prompts/17_implement_phase_F_cart_minimum.md

prompts/18_implement_phase_G_delivery.md

prompts/19_implement_phase_H_checkout.md

prompts/20_implement_phase_I_order_placement.md

prompts/21_implement_phase_J_order_lifecycle.md

prompts/22_implement_phase_K_admin_dashboard.md

prompts/23_implement_phase_L_landing.md

prompts/24_implement_phase_M_pwa.md

prompts/25_implement_phase_N_quality_security.md

prompts/26_implement_phase_O_demo_readiness.md

prompts/27_implement_phase_P_production_prep.md

If any required prompt file is missing, STOP and report exactly which file is missing.

Do NOT bypass missing prompt files by improvising from memory.

Authoritative Inputs

Before implementation, read:

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

Later-numbered prompts supersede earlier prompts when they explicitly change a previous decision.

Global Constraints

Laravel application root is src/.

PHP 8.2 maximum.

Laravel 12.x.

MySQL 8.

Use approved Filament version.

Blade + Alpine.js + Tailwind CSS.

Arabic only exposed in MVP.

RTL-first.

No language switcher.

Selective _ar / _en managed-content fields as approved.

Internal/domain identifiers remain language-neutral.

No Redis requirement.

No WebSocket requirement.

No microservices.

No Docker-in-production requirement.

No external search engine.

No dependency requiring PHP 8.3+.

Business logic belongs in application/domain services/actions.

Commerce math is server-authoritative and deterministic.

Never use binary floating point for money.

Do not silently change approved business requirements.

Do not expand MVP scope.

Execution Mode

Use Autonomous, commit per phase execution.

Run Phase C through Phase P sequentially.

For EACH phase:

Read that phase's prompt completely.

Identify the exact phase tasks in tasks.md.

Implement ONLY that phase.

Run all phase-specific mandatory tests.

Run relevant regression tests.

Verify PHP 8.2 compatibility remains valid.

Inspect git status --short.

Mark only genuinely completed tasks in tasks.md.

If tests and acceptance checks pass, create a phase checkpoint commit.

Continue to the next phase.

Phase Commit Format

Use:

feat: complete phase C authentication and onboarding

feat: complete phase D catalog

feat: complete phase E pricing and offers

and equivalent concise messages for later phases.

Do NOT squash phases together.

The Git history must remain reviewable phase-by-phase.

STOP Conditions

STOP immediately if any of these occur:

required prompt file is missing

mandatory test fails

regression test fails

PHP 8.2 compatibility is broken

dependency requires PHP 8.3+

business requirement is materially ambiguous

implementation would require changing an approved requirement

unexpected unrelated working-tree changes exist

destructive Git operation would be required

database/schema contradiction cannot be resolved from approved artifacts

security-critical uncertainty exists

When stopped:

do NOT continue to later phases

do NOT mark incomplete tasks complete

do NOT commit failing code

report exact blocker and current phase

Return:

MASTER RUN BLOCKED AT PHASE X

Git Safety

Never use:

git reset --hard

git clean -fd

force push

destructive history rewrite

Do not overwrite unrelated user changes.

Before every phase commit:

run git status --short

verify changes belong to the current phase

ensure .env, secrets, credentials, runtime files, vendor/, node_modules/, logs, uploads, and generated secrets are not staged

Phase Gates

Even though execution is autonomous, phase boundaries remain strict.

A phase is complete only when:

implementation is complete

mandatory tests pass

relevant regression tests pass

task checklist is updated honestly

PHP 8.2 constraint remains satisfied

scope guard remains satisfied

working tree contains only expected changes

phase commit succeeds

Do not enter the next phase before all gate conditions pass.

Final Validation After Phase P

After all phases C→P pass:

Run the complete automated test suite.

Run composer check-platform-reqs.

Verify application remains entirely under src/.

Verify no nested src/.git.

Verify no dependency requires PHP 8.3+.

Verify Arabic is the only exposed MVP language.

Verify no language switcher exists.

Verify approved _ar / _en content schema is respected.

Verify no prohibited scope features were introduced.

Verify sensitive customer/auth/order responses are not cached by the PWA.

Verify offline order submission is impossible.

Verify demo/test OTP cannot operate in production.

Verify all implemented MVP tasks are checked in tasks.md.

Verify no incomplete task is falsely checked.

Inspect final git status --short.

Final Report

Return:

overall result

phases completed

commit hash + message for every completed phase

phase where execution stopped, if any

tasks completed per phase

tests run per phase

final complete-suite result

PHP 8.2 compatibility result

production safety result

PWA privacy result

localization result

scope guard result

remaining unchecked tasks

warnings / technical debt discovered

final git status --short

whether MVP is ready for final PM/UAT review

If every phase passes, end with:

ALL IMPLEMENTATION PHASES C-P READY FOR FINAL PM/UAT REVIEW

Do NOT deploy to production automatically.