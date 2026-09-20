# Claude Prompt — Implement Phase E: Pricing & Offers

## Prompt Number
16

## Scope
Implement **Phase E only**.
Do not continue into Phase F.

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
- Selling-unit authoritative base price.
- Quantity price tiers.
- Offers according to approved schema.
- Deterministic PricingService/result DTO with base, eligible tier, eligible offer, applied price, applied source, saving, line total.
- When tier and offer both apply, LOWER eligible unit price wins.
- Never stack tier + offer.
- Offer validity evaluated server-side.
- Lightweight listing baseline pricing only.
- Full pricing evaluation on Product Details / Cart / Checkout.
- Filament tier/offer management.

## Mandatory tests
- base price
- 4→5 tier boundary
- 9→10 tier boundary
- expired offer
- inactive offer
- tier only
- offer only
- both: tier wins
- both: offer wins
- deterministic integer-money calculations

End with:
`PHASE E READY FOR PM REVIEW`
