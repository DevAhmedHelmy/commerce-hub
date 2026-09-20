Claude Prompt — Resume and Complete Phase E Pricing
Prompt Number
41
Phase
Phase E — Pricing, Tiers & Offers
Purpose
Resume Phase E from the CURRENT repository state and complete it safely.
Do NOT restart Phase E from scratch.
Preserve valid existing Phase E work.
Phase D3 has already refactored the unit/inventory model, so all pricing logic must now use the FINAL two-level Product Unit architecture.
Laravel application root:
src/
Prompt history root:
prompts/
Read First
Read:
- prompts/37_refactor_units_module_two_level_conversion.md
- prompts/38_implement_phase_D3_units_refactor.md
- prompts/40_add_admin_roles_permissions_rbac.md
- prompts/39_add_admin_audit_log.md
- prompts/36_migration_and_visual_review_gate.md
- .claude/skills/restaurant-ui/SKILL.md
- .specify/memory/constitution.md
- specs/001-restaurant-supplies-mvp/spec.md
- specs/001-restaurant-supplies-mvp/plan.md
- specs/001-restaurant-supplies-mvp/data-model.md
- specs/001-restaurant-supplies-mvp/contracts/
- specs/001-restaurant-supplies-mvp/tasks.md
Also inspect all prompt history under prompts/ in numeric order.
Later decisions supersede earlier ones.
1. Preflight — Current State
Before changing code:
Run:
git status --short
git log --oneline -10
php artisan migrate:status
Inspect the current Phase E implementation.
Identify:
- completed Phase E files
- partial Phase E files
- unimplemented Phase E tasks
- current tests
- any stale assumptions from pre-D3 ProductUnit design
Do NOT delete valid work.
2. Mandatory Prerequisite — D3
Verify Phase D3 is fully implemented and committed.
Expected architecture:
- independent units
- product unit levels:
  - primary
  - sub
- product-specific conversion factor
- pricing attached to selected Product Unit
- no independent unrelated inventory balances per selling unit
If D3 is not green:
STOP.
Return:
PHASE E BLOCKED — PHASE D3 NOT COMPLETE
3. RBAC Integration
If Prompt 40 RBAC is already implemented:
Use the approved permissions.
Pricing actions require:
pricing.view
pricing.update_base
pricing.manage_tiers
pricing.manage_offers
Expected access:
- Super Admin
- Manager
- Pricing Staff
Inventory Staff and Orders Staff must not modify pricing.
Do not rely only on hidden Filament buttons.
Server-side authorization is mandatory.
If RBAC is not yet implemented, do not invent a second authorization system.
Report it clearly as a blocker before final completion.
4. Pricing Model — Core Rule
Each sellable Product Unit has independent pricing.
Example:
Product: Ketchup

Carton:
1,200 ج

Piece:
110 ج
Do NOT derive:
piece price = carton price / conversion factor
Unit conversion and commercial pricing are separate concerns.
5. Base Price
Each Product Unit must support an authoritative base price.
Use the project's approved money representation:
- integer minor units
- never floating point
Example display:
1,250 ج
Use the central money formatter.
Do not store formatted currency strings as authoritative values.
6. Quantity Price Tiers
Support quantity-based pricing per Product Unit.
Example:
Carton:
1–4     = 1,200 ج
5–9     = 1,150 ج
10+     = 1,100 ج
Tier requirements:
- belongs to one Product Unit
- minimum quantity required
- deterministic ordering
- no ambiguous overlapping logic
- active/inactive if approved in data model
- correct tier is selected based on quantity for THAT selected unit
Do not combine carton and piece quantities for tier thresholds.
Example:
2 cartons + 5 pieces
Carton tiers evaluate quantity 2.
Piece tiers evaluate quantity 5.
7. Offers
Support temporary offers per Product Unit.
Offer should support the approved fields in plan/data model, including conceptually:
- product_unit_id
- offer price
- starts_at
- ends_at
- active state
An offer is eligible only when:
- active
- current time is inside valid range
- Product Unit is active/sellable
- product is active/available
Use project-local time handling consistently.
8. Lower-Of Pricing Rule
If both a quantity tier and an active offer apply:
calculate BOTH.
Use the LOWER eligible unit price.
No stacking.
Example:
Base price = 1,200
Tier price = 1,100
Offer price = 1,050
Effective:
1,050
Example:
Base = 1,200
Tier = 1,050
Offer = 1,100
Effective:
1,050
Never add discounts together.
9. Pricing Service
Complete the central pricing service.
Expected concept:
PricingService
It must be the authoritative source for effective unit price.
Do NOT duplicate pricing calculations in:
- controllers
- Blade
- Alpine
- Filament resource callbacks
- cart UI
Suggested input:
- Product Unit
- quantity
- evaluation timestamp/context if needed
Suggested output/value object:
PriceResult
Conceptually includes:
- base price
- eligible tier price nullable
- eligible offer price nullable
- effective unit price
- winning source:
  - base
  - tier
  - offer
- relevant tier/offer IDs if applicable
Use project naming conventions.
10. PriceResult
Complete/refactor PriceResult if partially implemented.
Requirements:
- immutable/value-object style where practical
- integer money
- deterministic result
- no presentation formatting inside core pricing logic
Formatting belongs to presentation layer.
11. PromotionService / Offer Evaluation
If current architecture includes PromotionService, complete it.
Responsibility:
- determine eligible active offer
- do not calculate unrelated stock/delivery/cart rules
Do not duplicate lower-of logic in both services.
Preferred separation:
PromotionService → eligible offer
PricingService   → base + tier + offer comparison
12. Tier Resolution
Create/use one clear tier-resolution path.
Rules:
- selected Product Unit only
- selected unit quantity only
- deterministic result
- no overlap ambiguity
If multiple tiers technically match due bad data:
use explicit validation/constraints to prevent invalid configuration rather than arbitrary runtime selection.
13. Filament — Product Unit Pricing
Admin must be able to manage pricing clearly.
For each Product Unit:
- view base price
- update base price
- manage quantity tiers
- manage offers
UI must make the selected unit obvious.
Example:
المنتج: بطاطس مجمدة
الوحدة: كرتونة
السعر الأساسي: 850 ج
Do not let the admin accidentally edit Piece pricing while thinking they are editing Carton pricing.
14. Filament — Price Tiers
Tier UI should clearly show:
- unit
- minimum quantity
- unit price
- active state if supported
Prevent:
- negative quantity
- zero threshold where invalid
- zero/negative price
- invalid duplicate thresholds
Use Arabic labels.
15. Filament — Offers
Offer UI should clearly show:
- product
- selected Product Unit
- offer price
- start
- end
- active/inactive
Validate:
- end > start
- price > 0
- valid Product Unit
- no malformed date range
If overlapping offers are not approved, prevent them.
If overlapping offers are already allowed by plan, use a deterministic documented eligibility rule.
Do not invent hidden business behavior.
16. Audit Log Integration
If Prompt 39 AdminAuditService exists:
Wire Phase E pricing actions to it NOW.
Audit:
Base Price
- product
- unit
- old price
- new price
- actor
- timestamp
Tier
- created
- updated
- deactivated/deleted according to approved behavior
- old/new threshold
- old/new price
Offer
- created
- updated
- activated/deactivated
- price/date changes
Do NOT create fake audit stubs.
If Prompt 39 is not implemented yet:
add explicit follow-up tasks to wire pricing audit when it lands.
17. Historical Order Safety
Pricing changes must never mutate historical order item values.
Order snapshots later must preserve:
- selected unit
- quantity
- effective unit price used
- pricing source
- line total
- applicable commercial snapshot data
No historical recalculation.
18. Customer UI Wiring
Where current catalog/product detail UI already exists:
Show the selected unit's effective price correctly.
Flow remains:
Product Card
→ Product Details
→ choose selling unit
→ choose quantity
→ effective price
→ Add to Cart
No quick-add from product card.
When quantity changes:
effective price may update because of tier eligibility.
If an offer applies, show the effective price clearly.
Do not expose confusing internal calculation details unless useful.
19. Product Card
Product card may show a representative/start price only if already approved by UX spec.
Do not make card pricing misleading when multiple units exist.
Product image behavior from Prompt 34 remains unchanged.
20. Cart Compatibility
Phase F is not implemented yet.
Do NOT implement the full cart in Phase E.
But provide a clean pricing API/service contract Phase F can call.
No pricing math should need rewriting in Phase F.
21. Inventory Separation
Pricing must NOT modify stock.
Unit conversion must NOT affect price automatically.
Inventory stock uses normalized sub-unit quantities.
Pricing uses the customer's selected Product Unit.
Keep these concerns separate.
22. Validation
At minimum enforce:
- base price > 0 where required
- tier price > 0
- offer price > 0
- tier minimum quantity valid integer
- valid offer date range
- Product Unit belongs to Product
- Product Unit active/sellable as appropriate
Never use float money.
23. Required Tests — Base Pricing
Test:
- base price returned when no tier or offer
- correct Product Unit price selected
- carton and piece prices independent
24. Required Tests — Tiers
Test:
- below first tier → base
- exact tier threshold
- higher applicable tier
- different unit does not affect selected-unit tier
- invalid overlapping/duplicate config blocked as designed
Example:
Carton:
base = 1200
5+ = 1150
10+ = 1100
Test quantities:
- 1 → 1200
- 5 → 1150
- 9 → 1150
- 10 → 1100
- 20 → 1100
25. Required Tests — Offers
Test:
- inactive offer ignored
- future offer ignored
- expired offer ignored
- active in-range offer applied
- correct unit only
26. Required Tests — Lower-Of
Test all combinations:
- base only
- tier only
- offer only
- tier lower than offer
- offer lower than tier
- tier equal to offer
Result must be deterministic.
27. Required Tests — RBAC
If RBAC exists:
- Pricing Staff may update pricing
- Inventory Staff cannot
- Orders Staff cannot
- Manager can
- Super Admin can
- direct unauthorized request/action denied
28. Required Tests — Audit
If AuditService exists:
- base price change records old/new
- tier update audited
- offer update audited
- failed pricing transaction does not create false success audit
29. Phase E Task Completion
Review Phase E tasks in:
specs/001-restaurant-supplies-mvp/tasks.md
Based on the previous audit, Phase E was partially complete around:
- PriceResult
- PromotionService
- PricingService
- tests T048–T051
- customer UI T057
- Filament T058/T059
Use ACTUAL task IDs from current tasks.md.
Do not assume IDs if tasks changed after D3/RBAC/Audit amendments.
Mark only genuinely completed tasks.
30. Migration Gate
Follow Prompt 36.
Run:
php artisan migrate:status
php artisan migrate
No:
migrate:fresh
db:wipe
If migration fails:
STOP.
Return:
PHASE E MIGRATION BLOCKED
31. Quality Gate
Run:
php artisan test
composer check-platform-reqs
npm run build
Also run relevant Phase C/D/D3 regression tests.
Do not mark Phase E complete with failing tests.
32. Visual Review
Start/use the local application and visually inspect pricing-related screens.
Report exact routes.
Check at least:
- 390×844
- 768×1024
- 1024×768
- 1440×900
Verify:
- RTL
- Arabic labels
- system light
- system dark
- no overflow
- product/unit distinction clear
- prices use central format:
  1,250 ج
Use /restaurant-ui.
33. No Scope Creep
Do NOT implement:
- cart Phase F
- delivery Phase G
- checkout Phase H
- orders Phase I/J
- customer-specific price lists
- coupon engine
- loyalty
- credit pricing
- ERP pricing
- automatic unit price conversion
Stay in Phase E.
34. Git
Before commit:
git status --short
git diff --stat
Stage only Phase E changes plus required Phase E audit/RBAC integration.
Exclude unrelated prompt history/vendor/reference files unless intentionally tracked separately.
Commit only when green.
Recommended commit:
feat: complete phase E pricing and offers
Required Final Report
Return:
1. pre-existing Phase E work found
2. work completed in this run
3. migrations applied
4. PricingService result
5. PriceResult result
6. tier behavior
7. offer behavior
8. lower-of behavior
9. Product Unit compatibility
10. RBAC integration
11. Audit Log integration
12. Filament pricing UI
13. customer pricing UI
14. tests added
15. total test result
16. build result
17. visual review result
18. exact review URLs
19. tasks completed
20. commit hash/message
21. remaining blockers
22. git status --short
End with:
PHASE E READY FOR PM REVIEW
only when Phase E is fully green.