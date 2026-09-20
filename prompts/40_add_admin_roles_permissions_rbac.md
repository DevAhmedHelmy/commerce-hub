Claude Prompt — Add Dashboard Roles & Permissions (RBAC)
Prompt Number
40
Phase
Cross-Cutting Administration / Authorization & RBAC
Purpose
Add role-based access control for Admin Dashboard users.
This supersedes earlier MVP statements that advanced RBAC is out of scope.
Use spatie/laravel-permission with the project's hard runtime constraints:
- PHP 8.2 maximum
- Laravel 12.x
Use a PHP-8.2-compatible package major:
spatie/laravel-permission:^6.0
Do NOT install v7/v8 because they require PHP 8.3+.
Laravel application root:
src/
Read First
Read:
- .specify/memory/constitution.md
- .claude/skills/restaurant-ui/SKILL.md
- specs/001-restaurant-supplies-mvp/spec.md
- specs/001-restaurant-supplies-mvp/plan.md
- specs/001-restaurant-supplies-mvp/data-model.md
- specs/001-restaurant-supplies-mvp/contracts/
- specs/001-restaurant-supplies-mvp/tasks.md
- all prompt history under prompts/ in numeric order
Important related prompts:
- Prompt 38 — Units / Inventory foundation
- Prompt 39 — Admin Audit Log
- Prompt 36 — Migration + Runtime/Visual Gate
1. Authentication Boundary
RBAC applies ONLY to dashboard/admin users stored in users.
Customers remain separate in customers.
Do NOT:
- assign admin roles to customers
- merge customer/admin auth
- allow customer OTP auth into Filament admin
Use the existing admin guard/session architecture.
2. Package Integration
Install:
spatie/laravel-permission:^6.0
Before installation:
- inspect User
- ensure there are no conflicting role, roles, roles(), permission, permissions, permissions() properties/methods
Integrate HasRoles into the admin User model.
Publish/use package migrations/config according to package conventions.
Do not invent a custom RBAC engine.
3. Approved MVP Roles
Create these roles:
super_admin
Arabic label: مدير النظام
Full dashboard access.
Can:
- manage admin users
- manage roles/permissions
- view audit log
- perform all approved admin operations
manager
Arabic label: مدير
Can:
- products
- units
- pricing
- offers
- inventory
- orders
- delivery
- settings
- view audit log
Cannot:
- manage Super Admin privileges
- manage role definitions by default
- escalate own permissions
orders_staff
Arabic label: موظف الطلبات
Can:
- view orders
- view order details
- update allowed order statuses
- cancel orders according to approved cancellation rules
- view customer details required for order handling
Cannot:
- modify prices
- modify inventory
- manage catalog structure
- manage users/roles
inventory_staff
Arabic label: موظف المخزون
Can:
- view products
- view units
- view exact stock
- add stock
- remove stock
- correct stock
- view inventory adjustment history
Cannot:
- modify prices
- manage price tiers
- manage offers
- manage users/roles
- change commercial settings
pricing_staff
Arabic label: موظف الأسعار والمبيعات
Can:
- view products
- update allowed commercial product fields
- update base selling-unit prices
- manage price tiers
- manage offers
Cannot:
- adjust inventory
- manage roles/users
- perform sensitive inventory actions
4. Granular Permissions
Create granular permissions.
Products
products.view
products.create
products.update
products.activate
Units
units.view
units.create
units.update
units.activate
units.assign_to_product
units.change_conversion
Pricing
pricing.view
pricing.update_base
pricing.manage_tiers
pricing.manage_offers
Inventory
inventory.view
inventory.adjust
inventory.view_history
Orders
orders.view
orders.update_status
orders.cancel
Customers
customers.view
Delivery
delivery.view
delivery.manage_areas
delivery.manage_slots
delivery.manage_discounts
Settings
settings.view
settings.update
Audit
audit.view
Admin Users
admin_users.view
admin_users.create
admin_users.update
admin_users.deactivate
Roles
roles.view
roles.manage
Avoid one giant admin permission.
5. Role / Permission Matrix
Default matrix:
Super Admin
All permissions.
Manager
All operational permissions except:
- roles.manage
- privilege escalation over Super Admin
- destructive Super Admin management
May view audit.
Orders Staff
- orders.view
- orders.update_status
- orders.cancel
- customers.view
Inventory Staff
- products.view
- units.view
- inventory.view
- inventory.adjust
- inventory.view_history
Pricing Staff
- products.view
- products.update
- pricing.view
- pricing.update_base
- pricing.manage_tiers
- pricing.manage_offers
Document the exact final matrix.
6. Authorization Architecture
Use:
- Spatie roles/permissions = capability engine
- Laravel Policies/Gates = server-side authorization
- domain services = business-rule enforcement
Permissions never bypass business rules.
Examples:
- user may have orders.cancel, but delivered order still cannot be cancelled
- user may have inventory.adjust, but stock cannot go below zero
- user may have units.change_conversion, but conversion safety rules still apply
7. Filament Authorization
Protect all Filament:
- resources
- pages
- navigation
- create/edit actions
- table actions
- custom actions
- header actions
Do not rely only on hiding buttons.
Direct unauthorized access/action must return proper denial/403.
8. Admin Users Management
Add a Super-Admin-only Admin Users area.
Allow:
- list dashboard users
- create admin user
- edit name/email
- assign roles
- activate/deactivate if supported
- view assigned roles
Do not display password hashes.
Do not hard-code production credentials.
9. Roles Management
Keep MVP role UI simple.
Preferred:
- view predefined roles
- view their permissions
- Super Admin may edit permissions if safe/simple
Do NOT build enterprise IAM complexity.
Create an idempotent seeder:
RolesAndPermissionsSeeder
It should:
- create permissions
- create roles
- sync approved defaults
- be safe to rerun
- not delete unknown records blindly
10. First Super Admin
Provide a safe way to create the first Super Admin.
Preferred:
- Artisan command
  or
- environment-guarded local/demo seeder
Do not commit production passwords.
Do not ship a universal default admin password.
11. Product / Pricing / Inventory Separation
Inventory Staff
Can:
- stock view/adjust/history
Cannot:
- change base prices
- tiers
- offers
Pricing Staff
Can:
- base prices
- tiers
- offers
Cannot:
- stock adjustments
Orders Staff
Cannot mutate catalog/pricing/inventory.
12. Unit Conversion Permission
Changing product conversion such as:
1 carton = 12 pieces
requires:
units.change_conversion
Grant by default only to:
- Super Admin
- Manager
Do NOT grant this to ordinary Inventory Staff by default.
Prompt 37/38 conversion-safety rules still apply.
13. Pricing Permissions
Use:
- pricing.update_base
- pricing.manage_tiers
- pricing.manage_offers
Pricing Staff, Manager, Super Admin are allowed as appropriate.
Inventory Staff and Orders Staff are not.
14. Inventory Permissions
Use:
inventory.adjust
Allowed:
- Super Admin
- Manager
- Inventory Staff
Not allowed:
- Pricing Staff
- Orders Staff
Inventory adjustments must continue writing inventory history.
Prompt 39 should audit these actions too.
15. Order Permissions
Orders Staff gets:
- orders.view
- orders.update_status
- orders.cancel
But OrderService continues enforcing status/cancellation rules.
No role can force invalid transitions.
16. Audit Log Permissions
Prompt 39 Audit Log should require:
audit.view
Default:
- Super Admin: yes
- Manager: yes
- Orders Staff: no
- Inventory Staff: no
- Pricing Staff: no
Audit log remains read-only.
17. Privilege Escalation Protection
Only Super Admin may manage roles/users by default.
Prevent:
- user assigning a higher role without authorization
- Manager granting itself Super Admin
- non-Super-Admin editing Super Admin privileges
Protect the final active Super Admin:
- cannot deactivate the last active Super Admin
- cannot remove the last active Super Admin's role
- cannot leave the system with zero active Super Admins
18. Admin User Active State
If not already present, add/evaluate:
users.is_active
Requirements:
- existing admin users default active
- inactive admins cannot access dashboard
- no impact on customers
Use additive migration only.
19. Audit Integration
Prompt 39 should also audit:
- admin user created
- role assigned
- role removed
- admin activated/deactivated
- role permissions changed
Never audit:
- passwords
- password hashes
- OTP values
- tokens
- secrets
20. Security
All authorization is server-side.
Do not depend on:
- JavaScript permission checks
- hidden buttons
- frontend-only restrictions
Use package-supported permission cache invalidation.
Do not invent manual permission caching.
21. Tests — Role Matrix
Mandatory tests:
Super Admin
- full access
- manage admin users
- manage roles
- view audit
Manager
- products/pricing/inventory/orders/delivery/settings
- audit view
- cannot escalate privileges
Orders Staff
- order access/actions
- cannot edit pricing
- cannot adjust stock
- cannot manage roles
Inventory Staff
- stock access/adjustment/history
- cannot edit prices/offers
- cannot manage users
Pricing Staff
- base price/tier/offer actions
- cannot adjust stock
- cannot manage users/roles
22. Direct Unauthorized Access Tests
Examples:
- Inventory Staff submits price update → 403
- Pricing Staff triggers stock adjustment → 403
- Orders Staff accesses role manager → 403
- Manager attempts Super Admin privilege escalation → denied
Navigation hiding is not sufficient.
23. Business Rules Still Apply
Test:
- Super Admin still cannot cancel delivered order
- Manager cannot reduce stock below zero
- authorized pricing user must still submit valid price
- unit conversion changes still obey stock/conversion safety
24. Responsive Admin UI
Use /restaurant-ui.
Admin Users and Roles pages must be:
- Arabic-first
- RTL
- responsive
- laptop/tablet/mobile usable
- system dark-mode compatible
- theme-token based
Do not create a disconnected visual identity.
25. Planning Update
Update:
- spec.md
- plan.md
- data-model.md
- contracts/admin surfaces
- tasks.md
Supersede earlier statements that RBAC is completely out of MVP.
Keep scope simple and operational, not enterprise IAM.
26. Recommended Execution Order
Preferred order:
Prompt 38 — Units / Inventory D3
→ Prompt 40 — RBAC
→ Resume Phase E Pricing
→ Prompt 39 — Admin Audit Log
→ Continue Phase F onward
If Prompt 38 is not complete, finish it first.
27. Migration / Compatibility Gate
Follow Prompt 36.
Run:
php artisan migrate:status
php artisan migrate
php artisan test
composer check-platform-reqs
npm run build
Verify exact installed Spatie version remains PHP-8.2-compatible.
If Composer attempts v7/v8:
STOP and correct to v6.x.
No destructive DB reset.
28. Git
Commit only after all checks pass.
Recommended commit:
feat: add admin roles and permissions
Required Final Report
Return:
1. result
2. exact installed Spatie version
3. PHP 8.2 compatibility result
4. migrations applied
5. roles created
6. permissions created
7. role-permission matrix
8. Super Admin safety
9. Filament authorization result
10. Admin Users UI result
11. Roles UI result
12. inventory authorization
13. pricing authorization
14. orders authorization
15. audit-log authorization readiness
16. tests added
17. test result
18. frontend build result
19. runtime review URLs
20. commit hash/message
21. remaining gaps
22. git status --short
End with:
ADMIN RBAC READY FOR PM REVIEW
only if migrations, authorization tests, build, and PHP 8.2 checks pass.