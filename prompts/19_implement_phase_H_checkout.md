# Claude Prompt — Implement Phase H: Two-Step Checkout

## Prompt Number
19

## Scope
Implement **Phase H only**.
Do not continue into Phase I.

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


## Approved flow
Step 1: Delivery
Step 2: Review & Confirm

## Required outcomes
- Reuse saved default address with approved edit behavior.
- Select delivery area/date/slot.
- COD only.
- Server revalidates product active/available, unit active, price, tier, offer, minimum, area, fee, discount, slot.
- If terms changed, DO NOT silently submit.
- Return structured changed-commercial-terms result and force review again.
- Review screen displays authoritative recalculated values.

## Mandatory tests
price change, expired offer, OOS, inactive unit, fee change, discount change, inactive slot, minimum-order failure, changed-terms review requirement.

Do not finalize order records unless the authoritative tasks explicitly place that boundary in this phase.

End with:
`PHASE H READY FOR PM REVIEW`
