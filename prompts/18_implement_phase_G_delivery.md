# Claude Prompt — Implement Phase G: Delivery Administration & Calculation

## Prompt Number
18

## Scope
Implement **Phase G only**.
Do not continue into Phase H.

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
- Delivery areas with active state and base fee.
- Approved concrete delivery-slot model, no numeric capacity.
- Discount types: fixed, percentage, free delivery.
- Qualification based on effective product subtotal.
- No stacking.
- If multiple qualify, choose greatest monetary saving against current base fee.
- Fee cannot go below zero.
- Tie-break = higher qualifying minimum subtotal.
- DeliveryService/result exposes base fee, selected rule, discount amount, final fee.
- Filament management for areas, slots, discounts.

## Mandatory tests
fixed, percentage, free, multiple eligible, tie-break, floor zero, inactive area, inactive slot.

End with:
`PHASE G READY FOR PM REVIEW`
