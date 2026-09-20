# Claude Prompt — Apply Frontend Skill & Quality Gates

## Prompt Number
29

## Purpose

Adopt the finalized project UI skill as the mandatory frontend quality standard for all remaining implementation work.

## Source

Read:

`.claude/skills/restaurant-ui/SKILL.md`

Also read the approved design artifacts under:

`specs/001-restaurant-supplies-mvp/design/`

## Actions

1. Treat `/restaurant-ui` as mandatory for every remaining task involving:
   - Blade
   - Tailwind
   - Alpine.js
   - Filament visual/UI work
   - landing page
   - customer PWA
   - responsive layout
   - PWA installability
   - frontend QA

2. When the official `frontend-design` skill is available, use it together with `/restaurant-ui`.

3. The project-specific `/restaurant-ui` skill takes precedence for:
   - brand palette
   - centralized `theme.css`
   - gradients
   - RTL
   - Arabic MVP
   - system dark mode
   - responsive behavior
   - dashboard responsiveness
   - PWA responsiveness
   - PWA installability
   - safe-area behavior
   - approved UX/business-flow constraints

4. Update the remaining implementation execution instructions / master runner if needed so every frontend-bearing phase has a UI quality gate.

5. Do NOT rewrite already-correct business logic merely to satisfy UI rules.

6. Do NOT change approved product flows.

## Mandatory Frontend Gate

Before any frontend-bearing phase is committed:

- production frontend build passes
- relevant tests pass
- RTL checked
- system light mode checked
- system dark mode checked
- mobile checked
- tablet checked
- laptop/desktop checked
- dashboard responsive if affected
- customer PWA responsive if affected
- no hover-only required behavior
- touch targets reasonable
- no horizontal page overflow
- safe areas handled for installed mobile PWA
- sensitive authenticated PWA responses not cached
- raw brand colors are not unnecessarily scattered outside central theme tokens

If browser/Playwright is available, perform visual QA at representative viewports.

## PWA Gate

For PWA phases verify:
- valid manifest
- required icons
- standalone display
- HTTPS production requirement documented
- service worker registered
- offline fallback works
- dynamic customer/order data remains network-driven
- no offline order creation
- mobile install behavior supported
- desktop installability verified where browser support exists

## Result

Update only relevant planning/execution instructions if necessary.

Do not implement unrelated features.

Return:
- files updated
- how `/restaurant-ui` is wired into remaining execution
- responsive QA strategy
- dashboard QA strategy
- PWA installability QA strategy
- dark-mode QA strategy
- blockers, if any

End with:

`FRONTEND QUALITY GATE READY`
