Claude Prompt — Finalize Backend-Controlled Landing Page + PWA Entry Flow
Prompt Number
46
Purpose
This prompt MERGES and SUPERSEDES Prompts 46 and 47.
Do NOT run Prompts 46 and 47 separately after this prompt.
The goal is to finalize the public Landing Page so that:
1. / always opens the Landing Page.
2. Landing Page content is controlled from the Admin Dashboard.
3. Main CTA enters the customer PWA/app flow.
4. Guest/authenticated/onboarding redirect flow is correct.
5. Categories and featured products come from real domain models.
6. Landing Page is fully responsive, RTL, dark-mode compatible, and visually polished.
7. Admin can control content without editing code.
8. The implementation remains simple and maintainable, not a generic CMS/page builder.
Laravel application root:
src/
Prompt history root:
prompts/
1. Read First
Read:
- .specify/memory/constitution.md
- .claude/skills/restaurant-ui/SKILL.md
- prompts/30_implement_landing_from_emdad_food_responsive_v2.md
- prompts/36_migration_and_visual_review_gate.md
- prompts/39_add_admin_audit_log.md
- prompts/40_add_admin_roles_permissions_rbac.md
- prompts/42_autonomous_complete_all_remaining_tasks.md
- prompts/44_final_customer_delivery_profile_architecture.md
- prompts/45_final_hardening_close_remaining_tasks.md
- current route files
- current Landing Blade/Tailwind files
- current auth/onboarding redirect logic
- current PWA manifest/service worker config
- current tasks.md
Prompt 48 supersedes Prompts 46 and 47.
Do not duplicate implementation already present.
2. Verify Existing Landing First
Before modifying anything, inspect the current implementation.
Report:
- whether / currently renders a Landing Page
- current Landing view/component files
- current CTA behavior
- whether emdad-food-responsive-v2/ was already used
- whether Landing content is hard-coded or DB-driven
- whether Categories are dynamic
- whether Featured Products are dynamic
- current responsive/RTL/dark-mode quality
Do NOT rebuild valid existing work unnecessarily.
3. Root Route — Final Behavior
The root URL:
/
MUST always render the PUBLIC Landing Page.
Expected:
GET /
→ Landing Page
Do NOT:
- redirect / immediately to /home
- redirect / immediately to /login
- open Admin
- show a blank route
Authenticated users may still see the Landing Page if they manually visit /.
4. Main Landing CTA — Final Behavior
Landing must contain a clear primary CTA.
Recommended Arabic label:
ابدأ الطلب
Admin may edit the CTA label.
But the primary CTA target is SYSTEM-CONTROLLED and must not be replaced with an arbitrary URL from CMS.
Behavior:
Guest
Landing
→ CTA
→ Login
→ OTP
→ Onboarding if incomplete
→ Customer App Home
Authenticated + onboarding complete
Landing
→ CTA
→ Customer App Home
Authenticated + onboarding incomplete
Landing
→ CTA
→ Onboarding
→ Customer App Home
Use route names and Laravel intended behavior where appropriate.
No redirect loops.
5. Public Landing vs Customer PWA
Architecture must remain:
Public Landing Page
        ↓
      CTA
        ↓
Customer Auth / Onboarding
        ↓
Customer PWA / Ordering App
Do NOT create:
- a second customer frontend
- a separate PWA project
- duplicated product/catalog routes
The Landing and PWA live in the same Laravel application.
6. Backend-Controlled Landing Page
Normal Landing content must be editable from Filament/Admin.
Admin must be able to control:
- Hero title
- Hero subtitle
- Hero image
- CTA label
- contact phone
- WhatsApp
- email
- social links
- section titles/subtitles
- section visibility
- section order
- marketing cards/items
- display limits for dynamic sections where applicable
Normal content changes should NOT require code edits.
7. CMS Scope — Keep It Controlled
Do NOT build:
- WordPress clone
- arbitrary drag/drop page builder
- raw HTML editor
- raw CSS editor
- arbitrary Blade component selector
- generic page-management framework
Build a controlled Landing configuration feature.
8. Recommended Data Model
Use a simple structured architecture.
landing_page_settings
Singleton/global settings.
Suggested fields conceptually:
id

site_title_ar
site_title_en

hero_title_ar
hero_title_en

hero_subtitle_ar
hero_subtitle_en

hero_image_path

primary_cta_label_ar
primary_cta_label_en

phone
whatsapp_phone
email

facebook_url
instagram_url
tiktok_url

is_active

created_at
updated_at
Use only fields actually needed by the approved design.
Arabic remains the only exposed MVP language.
9. Landing Sections
Use:
landing_sections
Suggested fields:
id
key

title_ar
title_en

subtitle_ar
subtitle_en

content_ar
content_en

image_path

is_active
sort_order

settings JSON nullable

created_at
updated_at
Use stable language-neutral keys.
Possible approved keys:
hero
categories
featured_products
about
features
how_it_works
contact
Only create sections that fit the actual approved Landing design.
10. Repeatable Marketing Items
For repeatable non-domain content use:
landing_section_items
Suggested fields:
id
landing_section_id

title_ar
title_en

description_ar
description_en

image_path nullable
icon nullable
link_url nullable

is_active
sort_order

created_at
updated_at
Suitable for:
- feature cards
- benefits
- service steps
- marketing highlights
Do NOT use this table for Products, Categories, Offers, or Delivery Areas.
11. Dynamic Categories
Categories shown on Landing MUST come from the real:
categories
table/model.
Rules:
- active categories only
- limit may come from Landing section settings
- use real category image if current architecture supports it
- link to real catalog/category routes
Do NOT duplicate category content into Landing CMS tables.
12. Dynamic Featured Products
Featured Products MUST come from real application data.
Existing MVP rule remains:
Featured Products = active products with active eligible offers
Use:
- Product
- ProductUnit
- Offer/Promotion
- PricingService
- Product image
Rules:
- no duplicate Landing Product records
- no quick-add from Landing
- clicking product goes to Product Detail
- show correct effective commercial information
Do NOT duplicate pricing logic inside Landing.
13. Section-Specific Settings
Use validated configuration only.
Example:
{
  "limit": 6
}
for Categories.
Example:
{
  "limit": 8
}
for Featured Products.
Do NOT allow arbitrary executable config.
Do NOT expose raw JSON in admin if Filament form fields can represent it cleanly.
14. Admin Navigation
Create a clear Filament section such as:
إدارة الصفحة الرئيسية
or:
إعدادات الصفحة الرئيسية
Admin should have clear management screens for:
General
- Hero
- Contact
- Socials
- CTA label
Sections
- activate/deactivate
- title/subtitle
- order
- section-specific settings
Section Items
- create
- update
- activate/deactivate
- reorder
Keep UX simple.
15. Section Visibility
Every optional Landing section should support:
is_active
If inactive:
do not render it publicly.
Prefer deactivate over destructive delete.
16. Section Ordering
Use:
sort_order
Frontend should render active sections in configured order.
Do not hard-code all section order in Blade.
However, maintain safe structural constraints.
Example:
Hero should remain first if moving it would break design.
Do not let admin configuration destroy page structure.
17. Main CTA Must Remain Safe
Admin controls CTA label, but NOT arbitrary target behavior.
The primary CTA remains controlled by application logic.
Do NOT allow:
javascript:
external unsafe URL
arbitrary route
for the main customer-entry CTA.
18. Optional Secondary Links
If the design includes secondary marketing links:
- validate URLs
- use safe schemes only
- render only when configured
Do not allow XSS or unsafe protocol URLs.
19. Landing Service / Query Layer
Keep Blade clean.
Create/use:
LandingPageService
or equivalent.
Responsibilities:
- load Landing settings
- load active ordered sections
- load section items
- load dynamic Categories
- load dynamic Featured Products
- apply limits
- return presentation-ready data
Do not put database-heavy logic in Blade.
20. Cache Strategy
Landing is read-heavy.
Use simple cache where useful.
Cache:
- Landing settings
- section configuration
- marketing section items
Invalidate when Admin changes Landing configuration.
Do NOT cache private customer data.
Do NOT let cache make dynamic pricing/offers stale incorrectly.
Redis is NOT required.
Use current Laravel cache driver.
21. Hero Image Management
Admin can:
- upload
- preview
- replace
- remove
Use Laravel Storage.
Requirements:
- valid image MIME
- size limit
- safe generated filename
- responsive display
- fallback if missing
- no DB binary blobs
22. Marketing Item Image Management
Apply the same secure rules.
Do not introduce a large media library.
23. RBAC
Add permissions if not already present:
landing.view
landing.manage
Recommended role defaults:
Super Admin
- view
- manage
Manager
- view
- manage
Other roles:
- no management by default
Use current Spatie RBAC architecture.
Server-side authorization required.
24. Audit Log
Use existing:
AdminAuditService
Audit meaningful Landing actions:
- global settings changed
- Hero content changed
- Hero image changed
- section activated/deactivated
- section title/content changed
- section reordered
- marketing item created/updated/deactivated
Do not log binary image data.
Do not log giant model dumps.
25. Admin Preview
Provide an Admin action/link:
معاينة الصفحة
which opens:
/
in a new tab.
No full WYSIWYG editor needed.
26. Content Safety
Do not allow arbitrary raw HTML by default.
Use text fields / textarea / controlled rich text only if the current design truly needs it.
Sanitize any approved rich content.
Validate social/contact URLs.
Prevent XSS.
27. Empty-State Behavior
Landing must degrade gracefully.
Examples:
Missing Hero Image
Use design fallback.
No Categories
Hide Categories section or show a clean approved empty state.
No Active Offers
Hide Featured Products section or show clean approved behavior.
Missing Social Link
Do not render its icon/link.
Never show:
null
undefined
#
28. Responsive Design — Highest Priority
Landing must be visually reviewed at:
360px
390px
768px
1024px
1280px
1440px
Check:
- Header
- Logo
- Hero
- CTA
- Images
- Category cards
- Product cards
- Marketing sections
- Contact section
- Footer
No horizontal scroll.
No clipped sections.
No stretched images.
No fixed widths that break small screens.
29. Mobile-First Requirements
On mobile:
- CTA is prominent
- Hero stacks cleanly
- typography is readable
- buttons remain usable
- card layouts adapt
- images preserve aspect ratio
- no tiny controls
- touch targets ~44px where practical
- navigation remains usable
No hover-only required actions.
30. Tablet/Laptop/Desktop
Use intentional responsive layouts.
Use:
- sensible max-width containers
- balanced whitespace
- adaptive grid columns
- controlled image sizing
- readable line lengths
Do not let cards become excessively wide.
31. RTL
Arabic-first.
Verify:
dir="rtl"
Check:
- nav
- headings
- body
- card layout
- CTA
- footer
- icons where directional
- numbers/phones where LTR is appropriate
32. Theme
Use centralized theme:
src/resources/css/theme.css
Do NOT scatter raw HEX colors.
Use Emdad Food approved semantic brand tokens.
33. Dark Mode
System dark mode only.
Verify all Landing sections.
Do not apply visual filters to product images.
Ensure:
- card contrast
- button contrast
- text readability
- input/contact visibility
- image containers
34. Reference Design
If present:
emdad-food-responsive-v2/
remains the primary Landing visual reference.
Preserve its strong visual direction.
But do NOT blindly copy its standalone CSS/JS.
Use:
- Blade
- Tailwind
- semantic components
- theme tokens
35. CTA Auth Flow Tests
Mandatory tests:
Guest
GET /
→ Landing
→ CTA target/auth flow
Authenticated + onboarding complete
CTA enters Customer Home.
Authenticated + onboarding incomplete
CTA enters Onboarding first.
No redirect loop.
Landing remains publicly accessible.
36. CMS Tests
Add tests for:
- Landing settings persist
- active section renders
- inactive section hidden
- sort order respected
- Hero image validation
- unauthorized admin cannot manage Landing
- authorized Manager/Super Admin can manage
- Landing audit event created
- invalid unsafe link rejected
37. Dynamic Data Tests
Verify:
- Categories come from Category model
- inactive categories excluded
- section limit applied
Featured Products:
- come from Product/Offer models
- obey active-offer rule
- inactive offers excluded
- no duplicate CMS Product model/table used
38. Runtime / Migration Gate
Follow Prompt 36.
Run:
php artisan migrate:status
php artisan migrate
php artisan test
composer check-platform-reqs
npm run build
Do NOT use:
migrate:fresh
db:wipe
39. Visual QA
Use browser/Playwright if available.
Take/check at minimum:
- mobile Landing
- tablet Landing
- desktop Landing
- dark-mode Landing
- Landing → CTA → customer flow
Fix visible defects before completion.
40. Exact URLs
At completion report actual URLs:
Landing
Customer Login
Customer Home
Admin Landing Management
Admin Preview action destination
Do not guess routes.
41. Existing PWA Safety
Do not break:
- manifest
- service worker
- installability
- sensitive-route cache exclusions
- PWA customer routing
Landing is public.
Customer app remains PWA-capable.
42. Seed Data
Provide safe idempotent demo/default Landing data.
Seed:
- global settings
- approved sections
- useful marketing items
Do NOT overwrite real admin-edited Landing content on normal production runs.
43. Tasks / Specs Update
Update current project artifacts as needed:
- spec.md
- plan.md
- data-model.md
- contracts/admin surfaces
- tasks.md
Mark Prompts 46 and 47 as superseded by Prompt 48 in any prompt-tracking documentation if applicable.
44. Scope Guard
Do NOT:
- redesign the entire customer app
- add React/Vue SPA
- build generic CMS
- create arbitrary page builder
- duplicate Products/Categories/Offers
- add external CMS
- change unrelated business rules
Focus on:
Landing
+ Backend control
+ Root route
+ CTA/PWA entry
+ Responsive quality
45. Git
Before commit:
git status --short
git diff --cached --stat
git diff --cached
Do not include unrelated existing working-tree changes.
Commit only when migration/tests/build/visual QA pass.
Recommended commit:
feat: add backend-controlled landing and pwa entry flow
Final Report
Return:
1. existing Landing implementation found
2. root / behavior
3. guest CTA behavior
4. authenticated CTA behavior
5. incomplete-onboarding behavior
6. Landing database schema
7. LandingPageSetting implementation
8. LandingSection implementation
9. LandingSectionItem implementation
10. dynamic Categories integration
11. dynamic Featured Products integration
12. Filament Landing management UI
13. section visibility/order controls
14. image upload behavior
15. RBAC
16. Audit Log integration
17. caching behavior
18. responsive QA results
19. RTL result
20. dark-mode result
21. tests
22. build result
23. exact review URLs
24. commit hash/message
25. remaining issues
26. final git status --short
End with:
BACKEND-CONTROLLED LANDING + PWA ENTRY READY FOR REVIEW
only if all requirements are implemented and green.