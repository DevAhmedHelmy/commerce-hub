# Claude Prompt — Implement Phase P: Production Deployment Preparation

## Prompt Number
27

## Scope
Implement **Phase P only**.
This is the final MVP implementation-preparation phase.

# Common Rules

Read before implementation:
- `.specify/memory/constitution.md`
- `specs/001-restaurant-supplies-mvp/spec.md`
- `specs/001-restaurant-supplies-mvp/plan.md`
- `specs/001-restaurant-supplies-mvp/research.md`
- `specs/001-restaurant-supplies-mvp/data-model.md`
- `specs/001-restaurant-supplies-mvp/contracts/`
- `specs/001-restaurant-supplies-mvp/quickstart.md`
- `specs/001-restaurant-supplies-mvp/tasks.md`
- `specs/001-restaurant-supplies-mvp/design/`
- `prompts/` in numeric order

Global constraints:
- Laravel application root is `src/`.
- PHP 8.2 maximum, Laravel 12.x, MySQL 8.
- Use the approved Filament version from the finalized technical plan.
- Arabic is the only exposed MVP language; RTL-first; no language switcher.
- Respect approved selective `_ar` / `_en` managed-content fields.
- Internal/domain identifiers remain language-neutral.
- No Redis requirement, no WebSockets requirement, no microservices, no Docker-in-production requirement, no external search engine.
- Do not add dependencies requiring PHP 8.3+.
- Business logic belongs in application/domain services/actions, not controllers, Blade, Alpine, or Filament resources.
- Commerce calculations are server-authoritative and deterministic.
- Never use binary floating point for money.
- Mark only actually completed tasks in `tasks.md`.
- Do not silently change approved business requirements.
- If a blocker appears, STOP that phase and report it.


## Required outcomes
- Shared-hosting-safe deployment documentation/process.
- Web root points to `src/public`.
- Production `.env` checklist (no secrets committed).
- Composer production install process.
- Frontend production asset build.
- writable storage/cache expectations.
- storage link.
- scheduler/cron only if actually needed.
- queue process only if actually needed; no Redis requirement.
- backup strategy.
- logging/review guidance.
- HTTPS requirement.
- production demo/test OTP prohibition.
- final smoke-test checklist.
- verify PHP 8.2 production compatibility.

## Final validation
Run complete test suite and all final approved checks.

End with:
`PHASE P READY FOR PM REVIEW`
