# Claude Prompt — Implement Phase F: Cart & Minimum Order

## Prompt Number
17

## Scope
Implement **Phase F only**.
Do not continue into Phase G.

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
- Persistent DB cart with approved one-cart-per-customer model.
- Cart items: product + selling unit + requested quantity.
- Cart prices are not authoritative snapshots.
- Recalculate via PricingService.
- Add/update/remove flows.
- Handle inactive product/unit and OOS.
- Configurable minimum order.
- Qualifying subtotal = effective product subtotal after tiers/offers, excluding delivery fee/discount.
- Cart UI shows products, units, qty, effective unit prices, line totals, subtotal, minimum-order progress.
- Cart MUST NOT show delivery fee/discount estimates.

## Tests
- persistence
- quantity changes
- repricing
- unavailable product/unit
- below minimum
- exact minimum
- above minimum

End with:
`PHASE F READY FOR PM REVIEW`
