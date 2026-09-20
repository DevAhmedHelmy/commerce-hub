# Claude Prompt — Implement Phase D: Catalog Administration & Customer Catalog

## Prompt Number
15

## Scope
Implement **Phase D only** from `tasks.md`.
Do not continue into Phase E.

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
- Categories, products, selling units, availability, and product images according to approved schema.
- Selective `_ar` / `_en` fields for translatable managed content.
- Brand remains one commercial field.
- Availability supports approved Available / Out of Stock / Inactive behavior.
- No full inventory quantities.
- Filament catalog resources/actions only.
- Customer Home/catalog/category/product listing/search/product details assigned to this phase.
- Product Card MUST NOT directly add to cart.
- Product Details supports unit/quantity selection UI foundation but does not implement later cart business behavior.
- MySQL search only.
- Arabic RTL UI and mixed Arabic/English brand handling.
- Local/public storage for product images.

## Tests
Catalog visibility, inactive/OOS behavior, basic search, approved admin CRUD.

## Scope guard
No pricing tiers/offers business logic, cart, delivery, checkout, or orders.

End with:
`PHASE D READY FOR PM REVIEW`
