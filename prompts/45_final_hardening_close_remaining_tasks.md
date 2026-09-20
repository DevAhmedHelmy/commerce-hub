Claude Prompt — Final Hardening Pass + Close Remaining Tasks
Prompt Number
45
Goal
Complete all remaining active MVP hardening tasks after the successful autonomous run.
Current green baseline:
- 165 tests / 439 assertions
- npm run build passes
- composer check-platform-reqs passes
- PHP 8.2-safe
- MySQL migrated and seeded
- D3 through P substantially complete
Laravel root: src/
Preserve Pre-Existing User Changes
These pre-existing working-tree files are NOT part of this pass:
README.md
src/config/session.php
src/app/Services/OtpService.php
Do not modify, stage, commit, revert, or reset them.
Start with:
git status --short
Record them and leave them untouched.
Read First
Read:
- .specify/memory/constitution.md
- .claude/skills/restaurant-ui/SKILL.md
- current spec/plan/data-model/contracts/tasks
- all prompts in prompts/, especially 32–44
Use CURRENT tasks.md as the source of truth. Inspect every unchecked/deferred task.
1. Formal Phase N Authorization/Security Audit
Audit all sensitive admin surfaces:
- Products
- Units
- Pricing/Tiers/Offers
- Inventory
- Orders
- Delivery Areas/Slots/Discounts
- Settings
- Customers
- Admin Users
- Roles
- Audit Log
Verify both UI visibility and server-side authorization/direct-access denial.
Re-test the RBAC matrix:
- super_admin
- manager
- orders_staff
- inventory_staff
- pricing_staff
Permissions must not bypass domain rules.
Audit:
- CSRF on state-changing web routes
- login/session rotation/logout invalidation
- customer/admin auth separation
- OTP endpoint protections already in scope
- product-image upload authorization and validation
IMPORTANT: if a session-security fix would require changing the pre-existing src/config/session.php, STOP and report it rather than overwriting unknown user work.
2. Upload/Image Performance Audit
Verify product-image:
- MIME/content validation
- file-size limits
- safe generated names
- no traversal
- safe replacement/removal
- placeholder
- lazy loading where appropriate
Review image performance. Prefer existing safe tooling; otherwise enforce strong size/dimension limits. Add WebP/thumbnails only if supported cleanly without a heavy new dependency.
3. N+1 / Pagination / Index Sweep
Audit main customer/admin pages for N+1 queries:
- Home/catalog/category/product
- Cart
- Orders
- Admin products/orders/customers/inventory/delivery
Fix confirmed N+1 issues with sensible eager loading.
Ensure pagination for growing collections:
- Products
- Customers
- Orders
- Audit Logs
- Inventory History
Review useful DB indexes for common filters/lookups. Do not add duplicate/unjustified indexes.
4. Deterministic MySQL Oversell Concurrency Test
This is mandatory.
Use MySQL-compatible locking behavior, not a fake sequential SQLite test.
Scenario:
available stock = 10 sub-units
two concurrent order attempts each request 7
Expected:
- exactly one succeeds
- exactly one fails for insufficient stock
- final stock = 3
- no negative stock
- no partial failed order
- no duplicate/partial inventory adjustments
Use separate DB connections/processes/transactions as needed.
If the normal test suite cannot exercise MySQL locking, create a dedicated MySQL integration test group and document the exact command.
Also regress:
- primary-unit normalization
- sub-unit normalization
- mixed-unit deduction
- cancellation exact restore
- double-cancel idempotency
- rollback on insufficient stock
5. Complete Delivery Audit Logging
Wire existing AdminAuditService to:
- Delivery Area create/update/activate/deactivate/fee change
- Delivery Slot create/update/activate/deactivate
- Delivery Discount Rule create/update/activate/deactivate/value/threshold/type changes
Record:
- actor
- action
- subject
- meaningful old values
- meaningful new values
- timestamp
Add tests.
Also verify existing audit coverage for:
- pricing
- offers
- inventory
- settings/minimum order
- order status
- admin cancellation
- admin users/roles
Ensure failed transactions do not create false-success audit entries.
6. Audit Log Integrity
Verify:
- append-only behavior
- no edit/delete UI
- admin-only access
- sensitive-field redaction
- useful filters/indexes
- readable old/new diff
7. Accessibility / RTL / Responsive / Dark Audit
Perform dedicated visual/accessibility review.
Representative widths:
- 390×844
- 768×1024
- 1024×768
- 1440×900
Check:
- labels and validation associations
- keyboard/focus-visible
- semantic controls
- alt text
- no required hover-only interaction
- touch targets where practical
- Arabic RTL alignment
- no horizontal overflow
- responsive tables/forms/modals
- bottom-nav/safe-area behavior
- system dark mode
- semantic theme tokens
- no hard-coded white-only surfaces
Review major customer and admin screens.
Fix meaningful issues.
8. State Coverage Audit
Ensure clear Arabic states for:
- no products/categories/search results
- out of stock
- empty cart
- minimum not reached
- inactive area/slot
- changed commercial terms
- insufficient stock
- no orders
- invalid cancellation
- empty admin tables
- validation failures
9. Optional Out-of-Stock Widget
If the current tasks.md still contains the optional out-of-stock dashboard widget as an active unchecked task, implement it to close the task.
Keep it simple:
- zero-stock count
- zero-stock product list/link
Do not invent reorder-point logic unless already approved.
Use normalized sub-unit inventory and RBAC.
10. PWA Privacy Audit
Verify the service worker does NOT cache sensitive responses:
- OTP/auth
- profile
- addresses
- cart
- checkout
- order history/details
- admin
Verify:
- no offline order submission
- no background order sync
- update flow does not silently reload during checkout/order submit
Fix/test where practical.
11. Money / Snapshot Audit
Verify:
- no float money
- centralized formatter
- server-authoritative pricing/delivery/order totals
- no duplicated client-authoritative math
Verify immutable order snapshots for:
- customer/contact
- address
- delivery area
- delivery fee
- selected unit
- conversion factor
- effective price
- line totals
12. Final Tests / Runtime Gate
Run:
php artisan migrate:status
php artisan test
composer check-platform-reqs
npm run build
Also run the dedicated MySQL concurrency suite if separate.
No destructive migration reset.
Smoke-test representative customer/admin routes and report exact actual URLs.
13. Close Remaining Tasks
Re-read current tasks.md.
For every remaining unchecked active MVP task:
- complete it, or
- explicitly document why it is intentionally deferred
Target: zero ambiguous unfinished MVP tasks.
Do not falsely mark work complete.
14. Commit Strategy
Prefer focused commits such as:
chore: complete phase N security and quality audit
test: add mysql inventory concurrency coverage
feat: complete delivery audit logging
Before each commit:
git diff --cached --stat
git diff --cached
Do not include the pre-existing user files listed above.
Final Report
Return:
1. remaining tasks found at start
2. tasks completed
3. security/authorization findings and fixes
4. upload/image findings
5. N+1/pagination/index fixes
6. MySQL concurrency test design/result
7. delivery audit integration
8. audit integrity result
9. accessibility/RTL/responsive/dark fixes
10. state-coverage fixes
11. PWA privacy result
12. optional widget result
13. tests added
14. final test/assertion count
15. migration status
16. build result
17. PHP platform result
18. runtime smoke result
19. commits created
20. remaining intentional deferrals
21. preserved pre-existing working-tree files
22. final git status --short
End with:
FINAL MVP HARDENING COMPLETE — READY FOR UAT
only if all active MVP tasks are closed and all gates pass.