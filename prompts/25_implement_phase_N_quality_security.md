# Claude Prompt — Implement Phase N: Quality, Performance & Security

## Prompt Number
25

## Scope
Implement **Phase N only**.
Do not continue into Phase O.

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


## Required audits/fixes
- authorization
- CSRF
- mass assignment
- upload security
- session security
- OTP abuse protection
- logging/sensitive data
- N+1
- eager loading
- pagination
- DB indexes
- image optimization
- responsive behavior
- accessibility
- RTL correctness
- mixed Arabic/English text
- all empty/error/loading states
- PHP 8.2 dependency safety
- `composer check-platform-reqs`
- complete automated suite

Do not add new product scope under the label "polish".

All failures found in approved MVP behavior must be fixed and retested.

End with:
`PHASE N READY FOR PM REVIEW`
