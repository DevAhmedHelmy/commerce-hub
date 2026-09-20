# Claude Prompt — Implement Phase I: Order Placement & Immutable Snapshots

## Prompt Number
20

## Scope
Implement **Phase I only**.
Do not continue into Phase J.

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
- Transactional order creation.
- Approved human-friendly concurrency-safe order number.
- UNIQUE DB enforcement and collision handling.
- Duplicate-submit/idempotency protection.
- Immutable order header snapshots: business/contact, phones, address/area, delivery date/slot, subtotal, base delivery fee, discount, final delivery fee, total, COD.
- Immutable item snapshots: localized product/unit display values, quantity, base/applied price, pricing source/savings if approved, line total.
- Historical orders never depend on live catalog.
- Clear cart only after successful commit.
- Rollback on failure.
- Success state only after confirmed server creation.

## Tests
successful placement, rollback, unique order number, duplicate submit, snapshot immutability, cart clearing only on success.

End with:
`PHASE I READY FOR PM REVIEW`
