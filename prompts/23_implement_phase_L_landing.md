# Claude Prompt — Implement Phase L: Landing Page

## Prompt Number
23

## Scope
Implement **Phase L only**.
Do not continue into Phase M.

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
Build the approved Arabic-first public landing page in the same Laravel app:
- Header
- Hero
- Categories
- featured products = active-offer products only
- benefits
- ordering steps
- delivery coverage
- CTA
- footer/contact

Requirements:
- Reuse real catalog/offers/settings data where approved.
- Responsive RTL-first Blade/Tailwind implementation.
- No CMS.
- No separate SPA.
- No quick-add bypass of Product Details.
- Preserve exact approved design-system behavior where documented.

Run feature/render tests assigned by tasks.

End with:
`PHASE L READY FOR PM REVIEW`
