PROJECT_CONTEXT.md
Authoritative project context for Claude.
Read this file before making architectural, implementation, UI, Git, or UAT decisions.
This summarizes the important decisions from prior conversations and prompt history.

Project purpose
- Reusable / white-label Laravel ordering platform.
- Current client/brand: Emdad Food.
- Emdad Food is a client/brand, not the application name.
- Keep the core app general and reusable for multiple clients.
Repository structure
root/
├── .claude/
├── .specify/
├── specs/
├── prompts/
├── src/
├── CLAUDE.md
├── PROJECT_CONTEXT.md
└── .gitignore
Laravel app root: src/
Prompt history root: prompts/
Stack
- Laravel 12.x
- PHP max 8.2
- MySQL 8
- Blade + Alpine.js + Tailwind CSS
- Filament Admin
- Modular monolith
- PWA supported
- Local/public storage initially
- No microservices
- No required Redis/S3/Docker
- spatie/laravel-permission:^6.0
Product scope
- B2B ordering/supplies platform
- Delivery only
- COD only
- Selected delivery areas
- One branch initially
Customer auth and onboarding
Customer login: Phone + OTP
Admin login: Email + Password
Separate concepts:
- users = dashboard/admin users
- customers = customer accounts
Persistent customer fields:
- business_name
- contact_person_name
- phone
- whatsapp_phone
- is_active
- onboarding_completed_at
Customer must not re-enter contact/address data for every order.
Delivery area and address
Standalone DeliveryArea model/table: delivery_areas
Conceptual fields:
- id
- name_ar
- name_en
- delivery_fee
- is_active
- sort_order
customer_addresses conceptually:
- id
- customer_id
- delivery_area_id
- label
- address_line
- building
- floor
- apartment
- shop
- landmark
- notes
- is_default
- is_active
MVP UI exposes one default address, but schema is multi-address ready.
Latest UAT decision:
- customer profile/address editing is ADMIN-ONLY
- customer Account may display current data but should not allow self-edit
Customer active/inactive
Admin can activate/deactivate customer.
Inactive customer:
- cannot login
- cannot access protected customer app
- cannot place orders
- data/history preserved
Arabic message:
تم إيقاف حسابك. يرجى التواصل مع الإدارة.
Orders and snapshots
Orders reference customer/address where practical, but historical truth is immutable snapshots.
Snapshots include customer/contact/address/delivery-area/delivery-fee data and product/unit/price/conversion data.
Changing current profile, address, price, or conversion later must not alter historical orders.
Order statuses
Exactly:
- new
- confirmed
- preparing
- out_for_delivery
- delivered
- cancelled
Arabic:
- جديد
- تم التأكيد
- قيد التجهيز
- خرج للتوصيل
- تم التسليم
- ملغي
Customer self-cancel: only new.
Units
Independent reusable Units module.
MVP exactly two levels:
Primary Unit -> Sub Unit
Conversion is product-specific.
Example:
- Product A: 1 Carton = 12 Pieces
- Product B: 1 Carton = 24 Pieces
Customer may buy primary or sub unit.
Inventory
Authoritative stock is normalized SUB UNIT quantity.
Example:
- Primary = Carton
- Sub = Piece
- 1 Carton = 12 Pieces
- internal stock 125 pieces
- admin may display 10 كرتونة + 5 قطعة
Admin may add stock by primary or sub unit.
Cart does not reserve stock.
Order placement:
- lock stock rows
- validate
- normalize
- deduct
- write inventory adjustment
- create order
- commit atomically
Cancellation restores exact stock once/idempotently.
Inventory history
Inventory adjustments remain separate from Admin Audit Log.
Keep before/delta/after/input unit/input qty/reference/actor/timestamp.
Pricing
Each Product Unit has independent price.
Do not derive piece price from carton price.
Money:
- integer minor units
- never float
Display:
- 444 ج
- 1,250 ج
- 12,500 ج
Tier and offer:
- evaluate both
- choose lower eligible unit price
- no stacking
Historical orders keep old snapshot price.
Minimum order
Qualifying subtotal = effective product subtotal after tiers/offers, excluding delivery fee/discount.
Delivery
Admin-managed:
- areas
- area fee
- slots
- discounts
Discount types:
- fixed
- percentage
- free delivery
No stacking.
Choose largest actual saving.
Tie-break: higher qualifying minimum subtotal.
Fee floor = 0.
Checkout
Exactly two conceptual steps:
1. Delivery
2. Review & Confirm
Server revalidates product/unit/stock/price/tier/offer/minimum/area/fee/discount/slot.
Changed commercial terms must be shown for re-review; never silently submit.
Product images
One primary image per product.
Use Laravel Storage.
Admin upload/preview/replace/remove.
Customer/Landing render image with fallback.
RBAC
Roles:
- super_admin
- manager
- orders_staff
- inventory_staff
- pricing_staff
Granular permissions and server-side enforcement.
Important newer customer permissions:
- customers.view
- customers.update
- customers.activate
Admin Audit Log
Implemented.
Tracks actor/action/entity/old/new/timestamp.
Covers products, units, pricing, offers, inventory high-level actions, delivery, orders, customers, roles, branding/landing.
Never log passwords, OTP secrets, tokens, or session secrets.
Audit UI is read-only.
Landing Page
/ must render the public Landing Page.
CTA flow:
Guest -> Login -> OTP -> Onboarding if incomplete -> Home
Authenticated complete -> Home
Authenticated incomplete -> Onboarding -> Home
Landing is intended to be backend-controlled.
Dynamic data:
- categories from real Category model
- featured products from real Product/Offer/Pricing data
- featured rule = active products with active eligible offers
No quick-add from Landing cards.
White-label branding
Backend-controlled client branding.
Conceptual fields:
- company_name_ar
- company_name_en
- logo_light_path
- logo_dark_path
- favicon_path
Logo should appear on:
- Landing
- customer auth/app
- Admin Login
- Admin panel
Fallback = company name.
Do not show Laravel/Filament as primary brand.
Theme
Latest decision supersedes system-only mode.
Theme modes:
- Light
- Dark
- System
Arabic:
- فاتح
- داكن
- حسب النظام
Persist locally, not DB.
Responsive/UI rules
Arabic-first, RTL-first.
Important widths:
- 360
- 390
- 768
- 1024
- 1280
- 1440
Use .claude/skills/restaurant-ui/SKILL.md.
PWA
Manifest/service worker/installability.
Sensitive routes must never be cached:
- OTP/auth
- profile/addresses
- cart
- checkout
- orders
- admin
No offline ordering/background order sync.
Latest Admin Products UAT requirements
Columns conceptually:
- الصورة
- المنتج
- التصنيف
- الماركة
- وحدات البيع
- المخزون
- الحالة
Actions:
- تعديل
- إضافة مخزون
- تعديل السعر
Quick stock uses InventoryService and audit.
Quick price uses Pricing architecture and must not alter historical order snapshots.
Categories
Admin Categories should be fully Arabic:
- التصنيفات
- إضافة تصنيف
- الاسم
- نشط
- الترتيب
- تعديل
Admin customer management
Admin can edit customer business/contact/address data.
Admin can activate/deactivate customer.
Customer self-edit disabled for this version.
Changing current customer data never changes historical order snapshots.
Current Git/UAT status (verified 2026-09-20)
Base branch: master @ 075b578 (PR #1 merged — contains all D3→P + prompts 43/44/45/46 work).
Other branches: 001-restaurant-supplies-mvp @ a098afd (tracks origin), tag uat-baseline-2026-09-20.
Active UAT branch (checked out, LOCAL ONLY — not pushed, NOT merged):
feature/uat-ui-branding-admin-improvements @ 59b52f6
Batch 1 commits (7): e313452, e01b336, 0e96360, 1ac76b4, 3fcced6, ccedd8b, 59b52f6.
Gate: 195 tests / 511 assertions pass; npm run build passes; PHP 8.2 platform-reqs clean.
UAT Batch 1 is IMPLEMENTED & committed on the branch (not just decided): customer
activate/deactivate + admin-only customer management, admin Products stock column + quick
Add-Stock + quick Edit-Price (historical prices immutable), backend branding + admin panel brand
+ Filament promo removed, Light/Dark/System theme switcher, product placeholder images, branded
customer home + login, Arabic admin labels. Root landing + CTA delivered earlier (prompt 46).
Remaining: cross-viewport browser visual QA (no browser here); dev-MySQL refresh; RTL stock issue (below).
Run when the environment allows (C: disk was critically ~10 MB — free it first):
php artisan migrate
php artisan db:seed --class=DemoSeeder
php artisan storage:link
Always verify Git state before assuming (branch may since have been pushed/merged).
Known UAT issue
RTL stock breakdown was visually mangled:
10 ةعطق 5 + ةنوترك
Expected meaning:
10 كرتونة + 5 قطعة
Verify/fix during UAT.
Pre-existing working tree files
Previously preserved:
- src/README.md
- src/config/session.php
- src/app/Domain/Auth/OtpService.php
Do not stage/revert/overwrite blindly.
Reference design folder
emdad-food-responsive-v2/ is a client design reference, not core app code.
Latest Git decision:
- track prompts/
- track .claude/skills/
- exclude emdad-food-responsive-v2/ unless explicitly decided otherwise
Important prompt evolution
- 12–27 original phases A–P
- 28 master runner
- 29 frontend quality gates
- 30 landing reference
- 31 admin login misunderstanding; not authoritative for Audit Log
- 32 simple inventory becomes core MVP
- 34 product images
- 36 migration/runtime/visual gate
- 37/38 two-level units + D3 implementation
- 39 Admin Audit Log
- 40 Admin RBAC
- 41 Phase E pricing
- 42 autonomous remaining tasks
- 43/44 persistent customer profile/address + snapshots
- 45 final hardening
- 48 backend-controlled Landing + PWA entry
- 51 UAT Batch 1 UI/branding/admin improvements
Later prompts supersede earlier conflicting decisions.
Superseded decisions
- Inventory: now CORE MVP
- Units: normalized sub-unit inventory, not unrelated per-unit stock
- Theme: Light/Dark/System, not system-only
- Customer editing: Admin-only for this version
- Prompt path: root prompts/, not docs/prompts/
- Prompt 31 misunderstanding superseded by real Admin Audit Log requirement
Development workflow
Before significant changes:
1. inspect git status
2. inspect current branch
3. do not work directly on master/main for UAT batches
4. create feature branch
5. preserve unrelated user changes
6. implement
7. migrate safely
8. test
9. build
10. visual review where possible
11. commit focused changes
12. do not auto-merge unless user asks
Avoid by default:
- git reset --hard
- git clean -fd
- git push --force
- php artisan migrate:fresh
- php artisan db:wipe
Current working principle
The project is in MANUAL UAT.
Workflow:
Stable baseline -> Manual testing -> collect changes -> UAT batch branch -> review -> merge when approved -> final hardening -> release
Before any new Claude session
Claude should:
1. Read this file.
2. Read CLAUDE.md.
3. Read current tasks.md.
4. Read relevant latest prompts.
5. Inspect git status.
6. Inspect current branch.
7. Verify repository state instead of assuming old reports are still current.
Maintaining this file
Update after every major:
- architecture change
- UAT batch
- merge
- release
- scope decision
Keep decisions/current state, not raw chat transcripts.