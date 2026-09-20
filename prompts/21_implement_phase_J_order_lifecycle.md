# Claude Prompt — Implement Phase J: Order Lifecycle & History

## Prompt Number
21

## Scope
Implement **Phase J only**.
Do not continue into Phase K.

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


## Internal statuses
- `new`
- `confirmed`
- `preparing`
- `out_for_delivery`
- `delivered`
- `cancelled`

## Required outcomes
- Valid status transition enforcement.
- Customer order list/details using immutable snapshots.
- Customer self-cancel only from `new`.
- Admin cancellation allowed from new/confirmed/preparing/out_for_delivery.
- No cancellation from delivered/cancelled.
- Filament order list/detail/actions.
- Arabic labels are presentation only.

## Tests
valid/invalid transitions, customer cancellation, admin cancellation matrix, historical rendering from snapshots.

End with:
`PHASE J READY FOR PM REVIEW`
