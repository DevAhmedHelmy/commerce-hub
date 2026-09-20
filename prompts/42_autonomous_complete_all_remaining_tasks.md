Claude Prompt — Autonomous Runner for ALL Remaining Tasks
Prompt Number
42
Purpose
Continue implementation from the CURRENT repository state and complete ALL remaining approved MVP tasks until the project reaches final implementation readiness.
Do NOT restart completed work.
Do NOT re-run completed phases unnecessarily.
Use the current tasks.md and actual repository state as the source of truth for what remains.
This is an AUTONOMOUS EXECUTION prompt.
Do not stop for PM review between normal phases.
STOP only for a real blocker that cannot be resolved safely without user input.
Laravel application root:
src/
Prompt history root:
prompts/
1. Read Everything First
Read in this order:
1. .specify/memory/constitution.md
2. CLAUDE.md
3. .claude/skills/restaurant-ui/SKILL.md
4. specs/001-restaurant-supplies-mvp/spec.md
5. specs/001-restaurant-supplies-mvp/plan.md
6. specs/001-restaurant-supplies-mvp/research.md
7. specs/001-restaurant-supplies-mvp/data-model.md
8. specs/001-restaurant-supplies-mvp/contracts/
9. specs/001-restaurant-supplies-mvp/tasks.md
10. ALL prompt files under prompts/ in numeric order
Later prompts supersede earlier decisions.
Important latest amendments include:
- Prompt 32 — Simple Inventory becomes core MVP
- Prompt 34 — Product image support
- Prompt 36 — Migration + runtime + visual review gate
- Prompt 37/38 — Independent Units module + two-level unit conversion + D3 implementation
- Prompt 39 — Admin Audit Log
- Prompt 40 — Dashboard RBAC
- Prompt 41 — Resume/complete Phase E pricing
2. Current-State Audit Before Execution
Before coding, inspect:
git status --short
git log --oneline --decorate -20
php artisan migrate:status
Inspect:
- completed tasks [x]
- incomplete tasks [ ]
- partial code
- uncommitted changes
- existing tests
- current migrations
- current Phase status
- any valid work from prior prompts
Do not discard valid work.
Do not reset the repository.
Do not use destructive Git commands.
3. Source of Truth for Remaining Work
The runner must NOT assume old phase/task IDs are unchanged.
Use CURRENT:
specs/001-restaurant-supplies-mvp/tasks.md
Determine all remaining unchecked tasks.
For every unchecked task:
- verify whether code may already exist
- if already implemented, test/validate and mark complete
- if partial, finish it
- if missing, implement it
Never mark a task complete just because a file exists.
4. Required Execution Order
Use dependency-aware order.
Expected high-level order:
A/B/C/D/D3 already completed as applicable
↓
RBAC if not completed
↓
Phase E Pricing / Tiers / Offers
↓
Admin Audit Log framework/integrations if not completed
↓
Phase F Cart / Minimum Order
↓
Phase G Delivery
↓
Phase H Checkout
↓
Phase I Order Placement
↓
Phase J Order Lifecycle
↓
Phase K Admin Dashboard / Customer Directory
↓
Phase L Landing Page
↓
Phase M PWA
↓
Phase N Quality / Security / Performance
↓
Phase O Demo / Staging Readiness
↓
Phase P Production Preparation
↓
Final global audit
If actual task dependencies require a slightly different safe order, follow the dependency graph and document it.
Do NOT implement later tasks on top of knowingly broken prerequisites.
5. Phase E — Pricing
If incomplete, finish all Phase E work.
Must include:
- base price per Product Unit
- independent primary/sub-unit pricing
- price tiers per selected Product Unit
- temporary offers
- lower-of tier vs offer
- PriceResult
- PromotionService
- PricingService
- Filament pricing UI
- customer pricing display
- pricing tests
- RBAC authorization
- Audit Log hooks if AuditService exists
Never derive Piece price automatically from Carton price.
6. Phase F — Cart / Minimum Order
Implement all remaining cart tasks.
Required behavior:
- customer can add selected Product Unit + quantity
- same product may appear with different selected units
- cart uses PricingService
- effective price recalculated server-side
- cart shows:
  - product
  - product image
  - selected unit
  - quantity
  - effective unit price
  - line total
  - effective subtotal
  - minimum-order progress
Cart MUST NOT show estimated:
- delivery fee
- delivery discount
Cart does NOT reserve stock.
Minimum-order qualifying subtotal:
- effective product subtotal
- after tiers/offers
- excluding delivery fee
- excluding delivery discount
7. Phase G — Delivery
Implement:
- delivery areas
- per-area fee
- active/inactive
- delivery slots
- active/inactive
- delivery discount rules:
  - fixed
  - percentage
  - free delivery
No stacking.
If multiple delivery discounts are eligible:
choose the one producing the largest actual saving against the base delivery fee.
Tie-break:
higher qualifying minimum subtotal.
Final delivery fee cannot go below zero.
Add admin UI, tests, RBAC, audit hooks.
8. Phase H — Checkout
Checkout is exactly TWO conceptual steps:
Step 1 — Delivery
Customer chooses/confirms:
- saved/default address
- delivery area
- delivery slot
Step 2 — Review & Confirm
Show final commercial terms.
Server MUST revalidate:
- product active
- product available
- selected Product Unit active
- selected Product Unit sellable
- current stock sufficiency
- current price
- tier
- offer
- minimum order
- delivery area
- delivery fee
- delivery discount
- delivery slot
If terms changed:
DO NOT silently submit.
Return structured changed-commercial-terms result.
Customer must review again before confirmation.
9. Phase I — Order Placement
Implement transactional order placement.
Required:
- human-friendly unique order number
- immutable commercial snapshots
- server-authoritative totals
- COD only
- delivery only
- no pickup
- idempotency / duplicate-submit protection
- stock lock and deduction
Inventory rules:
authoritative stock is normalized SUB UNIT quantity.
For an order:
2 cartons + 5 pieces
with:
1 carton = 12 pieces
deduct:
29 sub-units
Order creation transaction must:
1. lock required inventory rows/state
2. validate stock
3. normalize required quantity
4. deduct stock
5. write inventory adjustment
6. create order + order items + snapshots
7. commit atomically
No overselling under concurrency.
10. Phase J — Order Lifecycle
Internal statuses exactly:
new
confirmed
preparing
out_for_delivery
delivered
cancelled
Arabic labels exactly:
جديد
تم التأكيد
قيد التجهيز
خرج للتوصيل
تم التسليم
ملغي
Customer may self-cancel ONLY:
new
Admin may cancel:
- new
- confirmed
- preparing
- out_for_delivery
Admin may NOT cancel:
- delivered
- cancelled
Cancellation restores stock exactly once.
Restoration must be idempotent.
No double restoration.
Audit:
- status changes
- admin cancellation
- relevant actor/context
RBAC still applies.
11. Phase K — Admin Dashboard
Complete responsive Filament dashboard.
Include relevant operational visibility:
- orders
- customers
- products
- units
- pricing
- inventory
- delivery
- admin users/roles
- audit log if implemented
Dashboard must be fully responsive.
Check:
- sidebar behavior
- cards/widgets reflow
- forms
- filters
- tables
- modals
- mobile/tablet/laptop
Do not make dashboard desktop-only.
12. Phase L — Landing Page
Use:
emdad-food-responsive-v2/
as primary visual reference if present.
Use Prompt 30 rules.
Rebuild/refine inside Laravel using:
- Blade
- Tailwind
- centralized theme tokens
Do NOT blindly copy giant standalone CSS/JS.
Landing must be:
- Arabic
- RTL
- responsive
- light/dark compatible
- performance-conscious
Featured products:
active-offer products only.
No quick-add from product card.
Product image support from Prompt 34 must be used.
13. Phase M — PWA
Complete PWA.
Required:
- manifest
- start_url
- scope
- standalone display
- theme/background colors
- 192 icon
- 512 icon
- maskable icon if supported
- service worker
- offline fallback
- safe update handling
- HTTPS production requirement documented
Sensitive responses MUST NEVER be cached:
- OTP
- auth
- profile
- addresses
- cart
- checkout
- order history/details
- admin
No offline ordering.
No background order synchronization.
Do not silently reload/update service worker during checkout/order submit.
Support desktop-installable PWA where browser permits.
Document iOS Add to Home Screen path.
14. Phase N — Quality / Security / Performance
Run full quality pass.
Check:
- authorization
- validation
- CSRF
- file-upload security
- OTP security
- inventory race conditions
- pricing correctness
- order idempotency
- PWA cache privacy
- Audit Log sensitive-field redaction
- RBAC direct-access denial
- SQL/query performance
- N+1 issues
- indexes
- accessibility
- responsive UI
- dark mode
- RTL
- semantic HTML
- keyboard usability where relevant
Fix issues found.
15. Phase O — Demo / Staging Readiness
Create/verify safe demo data.
Need enough data to review:
- units
- categories
- products
- product images/placeholders
- primary/sub units
- conversion factors
- stock
- prices
- tiers
- offers
- delivery areas
- slots
- delivery discounts
- admin roles/users
- customers
- orders in representative statuses
Use idempotent seeders where practical.
No production credentials.
No real customer personal data.
Any demo OTP behavior must be safe and clearly development-only.
16. Phase P — Production Preparation
Complete production-readiness tasks.
Verify/document:
- environment setup
- PHP 8.2 platform constraint
- Composer production install
- composer check-platform-reqs
- migrations
- storage link
- permissions
- queue strategy
- cron/scheduler if needed
- APP_DEBUG off
- secure APP_KEY
- session/cookie security
- trusted proxy/HTTPS considerations
- public storage
- PWA HTTPS
- backup considerations
- rollback/deploy notes
- health checks
Do NOT introduce Docker/Redis/S3 unless required by existing approved scope.
17. RBAC Requirement
If Prompt 40 is not fully implemented, implement it before sensitive later phases.
Approved roles:
- Super Admin
- Manager
- Orders Staff
- Inventory Staff
- Pricing Staff
Use:
spatie/laravel-permission:^6.0
Hard constraint:
PHP 8.2.
Do NOT install Spatie v7/v8 if they require PHP 8.3+.
Protect Filament server-side.
Do not rely only on hidden navigation/actions.
18. Audit Log Requirement
If Prompt 39 is not fully implemented:
Implement foundation now and wire existing modules.
As future modules are implemented during this run, immediately wire their audit actions.
Audit critical admin mutations including:
- product changes
- unit/conversion changes
- base price
- tiers
- offers
- inventory adjustments
- delivery configuration
- order status
- cancellations
- minimum-order/settings
- admin-user role changes
Audit must record:
- actor
- action
- entity
- old values
- new values
- timestamp
Audit Log UI:
- read-only
- no edit
- no delete
- Arabic RTL
- responsive
Never log passwords, OTPs, tokens, secrets.
19. Product Images
Ensure Prompt 34 is complete.
MVP:
one primary image per product.
Verify:
- upload
- preview
- replace
- remove
- validation
- placeholder
- customer display
- landing display
- tests
No gallery required.
20. Unit / Inventory Model
Prompt 37/38 is authoritative.
MVP exactly:
Primary Unit
→ Sub Unit
Example:
Carton
→ Piece
1 Carton = 12 Pieces
Generic units are reusable.
Conversion is product-specific.
Customer may purchase either unit.
Admin may add stock using:
- primary quantity
- sub quantity
Authoritative inventory balance:
SUB UNIT quantity.
Do not revert to unrelated stock balances per Product Unit.
21. Migration Rule — Mandatory
For every schema-changing phase:
migrate:status
→ migrate
→ tests
→ runtime check
→ visual check if UI
→ commit
Never use:
php artisan migrate:fresh
php artisan db:wipe
unless explicitly approved by user.
Preserve local data.
If migration fails and cannot be safely fixed:
STOP.
22. Tests — Mandatory
After each logical phase:
run relevant targeted tests.
Before phase commit:
run regression tests needed to ensure previous phases remain green.
At major gates run:
php artisan test
composer check-platform-reqs
npm run build
Do not commit a phase with failing tests.
If a flaky/non-deterministic test is discovered:
fix root cause where practical.
Do not just disable critical tests.
23. Runtime + Visual Verification
For every UI-bearing phase:
ensure app can run locally.
Verify exact routes.
When browser/Playwright tooling is available, inspect at:
- 390×844
- 768×1024
- 1024×768
- 1440×900
Verify:
- Arabic
- RTL
- system light mode
- system dark mode
- no horizontal overflow
- no broken images/assets
- no console-breaking errors
- responsive layout
- touch-friendly interaction
- customer PWA
- admin dashboard
Use /restaurant-ui.
24. Theme Rule
All UI must use centralized theme tokens.
Primary file:
src/resources/css/theme.css
Do not scatter raw HEX colors across components.
Use Emdad Food-derived brand palette already approved.
System dark mode only.
No manual theme toggle required in MVP.
25. Money Rule
Use centralized money formatting.
Exact examples:
444 ج
1,250 ج
12,500 ج
Rules:
- Latin digits
- comma thousands separator
- one space before ج
- no EGP
- no LE
- no ج.م
Authoritative calculations use integer minor units.
Never float money.
26. Customer Language
MVP exposed language:
Arabic only.
RTL-first.
No language selector.
Managed translatable content may keep _ar / _en fields according to current schema.
Do not expose incomplete English UI.
27. Commit Strategy
Make one clean commit per completed logical phase.
Recommended pattern:
feat: complete phase E pricing and offers
feat: complete phase F cart and minimum order
feat: complete phase G delivery
feat: complete phase H checkout
feat: complete phase I order placement
feat: complete phase J order lifecycle
feat: complete phase K admin dashboard
feat: complete phase L landing page
feat: complete phase M pwa
chore: complete phase N quality and security
chore: complete phase O demo readiness
chore: complete phase P production preparation
Use actual scope if phase names differ.
Before every commit inspect:
git status --short
git diff --cached --stat
git diff --cached
Do not accidentally commit:
- unrelated temp files
- logs
- secrets
- .env
- build artifacts that should be ignored
- random vendor/reference folders unless intentionally part of repo
Prompt files and skills may remain uncommitted unless project policy explicitly tracks them.
28. Do Not Stop for Normal Questions
This is autonomous mode.
Do NOT ask the user:
- whether to continue to the next phase
- whether to run tests
- whether to commit a green completed phase
- whether to proceed after a normal passing gate
Make the safe implementation decision and continue.
29. Stop Conditions
STOP only for a real blocker such as:
- unresolved destructive migration ambiguity
- production-like data at risk
- incompatible dependency requiring architectural decision
- missing secret/external provider required for implementation
- corrupted repository state
- repeated migration failure not safely resolvable
- failing critical test requiring product decision
- ambiguous business rule not covered by spec/prompts
When blocked:
return:
MASTER RUN BLOCKED
and report:
1. exact phase/task
2. exact error/problem
3. what was attempted
4. what remains safe
5. minimum user decision required
Do not continue into dependent phases.
30. Final Global Verification
After ALL remaining tasks are complete:
run:
php artisan migrate:status
php artisan test
composer check-platform-reqs
npm run build
git status --short
Verify:
- no pending application migrations
- all critical tests green
- frontend builds
- PHP 8.2 compatibility
- current app boots
- major customer routes work
- major admin routes work
- RBAC works
- Audit Log works
- inventory works
- pricing works
- cart works
- delivery works
- checkout works
- order lifecycle works
- PWA works
- responsive UI works
31. Final Task Audit
Re-read:
specs/001-restaurant-supplies-mvp/tasks.md
Every approved MVP implementation task should be either:
[x]
or explicitly documented as:
- intentionally deferred
- superseded
- blocked
Do not leave ambiguous unchecked tasks.
32. Final URLs
Return exact local routes for review, including as applicable:
- Landing
- Customer Home
- Categories
- Product Detail
- Cart
- Checkout
- Orders
- Account/Profile
- Admin Login
- Admin Dashboard
- Products
- Units
- Inventory
- Pricing
- Offers
- Orders
- Delivery
- Customers
- Admin Users
- Roles
- Audit Log
Only report routes that actually exist.
33. Final Report
At the very end return:
Implementation Summary
1. starting state
2. phases completed in this run
3. tasks completed
4. migrations applied
5. final test count/result
6. build result
7. PHP 8.2 compatibility
8. RBAC status
9. Audit Log status
10. Units status
11. Inventory status
12. Product Images status
13. Pricing status
14. Cart status
15. Delivery status
16. Checkout status
17. Orders status
18. Admin Dashboard status
19. Landing status
20. PWA status
21. responsive/RTL/dark-mode status
22. demo readiness
23. production-prep status
24. commits created
25. remaining intentional deferrals
26. remaining blockers
27. exact review URLs
28. final git status --short
Only when all approved remaining tasks are complete and all final gates pass, end with:
ALL REMAINING MVP TASKS COMPLETE — READY FOR FINAL PM/UAT REVIEW