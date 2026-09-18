Create the technical implementation plan for:

`specs/001-restaurant-supplies-mvp/spec.md`

Use as authoritative supporting design documentation:

* `specs/001-restaurant-supplies-mvp/design/ux-architecture.md`
* `specs/001-restaurant-supplies-mvp/design/wireframes.md`
* `specs/001-restaurant-supplies-mvp/design/design-system.md`
* `specs/001-restaurant-supplies-mvp/design/screen-specifications.md`
* `specs/001-restaurant-supplies-mvp/design/admin-design.md`

And comply fully with:

`.specify/memory/constitution.md`

This is the TECHNICAL PLANNING PHASE.

Do NOT implement application code yet.

Do NOT create Laravel application files, migrations, models, controllers, Blade views, Filament resources, or JavaScript implementation.

Research and planning artifacts are allowed and expected.

---

# 1. Hard Runtime Constraints

These are NON-NEGOTIABLE:

* Maximum PHP version: **PHP 8.2**
* Framework: **Laravel 12.x**
* Database: **MySQL 8**
* Customer frontend: **Blade**
* Lightweight frontend interaction: **Alpine.js**
* Styling: **Tailwind CSS**
* Admin dashboard: **Filament**, but ONLY a version whose complete dependency tree supports:

  * PHP 8.2
  * Laravel 12
* Customer app must be a **PWA**
* Initial production deployment must work on relatively low-cost hosting.

Do NOT introduce any package requiring PHP 8.3+.

Before selecting an exact Filament major/minor or any significant dependency, verify actual Composer/package requirements.

Do not assume compatibility based only on memory.

Record the verified compatible choice and reasoning in the research/plan artifacts.

---

# 2. Architecture Style

Use a:

**Modular Laravel Monolith**

One repository.

One Laravel application.

One MySQL database.

Do NOT introduce microservices.

Do NOT require:

* Redis
* WebSockets
* Docker in production
* Elasticsearch
* Kubernetes
* separate frontend deployment
* separate admin application

for MVP.

The architecture must remain able to evolve later.

---

# 3. High-Level Application Surfaces

The single Laravel application contains three main presentation surfaces:

## Public

* Landing Page
* Authentication / OTP entry

## Customer PWA

* Home
* Categories
* Search
* Product Details
* Cart
* Checkout
* Orders
* Profile

## Admin

Filament-based administration.

All three consume the same business/application layer.

Business logic must not be duplicated between surfaces.

---

# 4. Recommended Application Layer

Design clear services/actions for important business workflows.

At minimum evaluate:

* `OtpService`
* `CustomerService`
* `CatalogService`
* `PricingService`
* `CartService`
* `DeliveryService`
* `PromotionService`
* `OrderService`

Do NOT create services merely as wrappers around one Eloquent call.

Use services/actions where business behavior warrants them.

Controllers and Filament Resources should orchestrate rather than own business rules.

---

# 5. Proposed Domain Modules

Plan practical boundaries for at least:

## Authentication

Customer OTP authentication.

## Customers

Customer profile and delivery address.

## Catalog

Categories, products and selling units.

## Pricing

Base prices and quantity tiers.

## Promotions

Product offers.

## Cart

Cart and cart items.

## Delivery

Areas, delivery fees, delivery slots and delivery discounts.

## Ordering

Orders, immutable order-item snapshots and status transitions.

## Administration

Filament presentation over the same domain/application logic.

## Settings

Business-wide configurable settings such as minimum order.

Do NOT turn these into separate deployable services.

---

# 6. Authentication Architecture

Admin users and customers must be separate concepts.

Plan:

`users`

for admin users.

and:

`customers`

for ordering customers.

Customer authentication:

Phone
→ OTP
→ Verification
→ Customer session

Do NOT force customers into the admin `users` table merely to reuse authentication.

Plan appropriate Laravel authentication guards/providers if required.

OTP provider must be abstracted.

MVP may later use an SMS provider, but no specific paid SMS provider has been chosen.

Design something like:

`OtpSender` / `OtpProvider` contract

with provider-specific implementation later.

OTP requirements:

* expiry
* one-time use
* secure storage
* resend cooldown
* rate limiting
* attempt limiting
* no OTP in production logs

Do not choose an SMS vendor during planning unless required.

---

# 7. Database Design

Produce a detailed data model.

At minimum evaluate tables/entities for:

* users
* customers
* customer_addresses
* categories
* products
* product_units
* product_price_tiers
* product_offers
* carts
* cart_items
* delivery_areas
* delivery_slots
* delivery_discount_rules
* orders
* order_items
* otp_verifications
* settings
* notifications if needed

Do NOT blindly create all tables if a simpler justified solution exists.

For every table document:

* purpose
* main columns
* data types conceptually
* foreign keys
* uniqueness requirements
* indexes
* important constraints
* lifecycle/soft-delete decision
* historical/audit implications

---

# 8. Product Architecture

A product can have multiple selling units.

Example:

Product:
French Fries

Units:

* Bag 2.5 KG
* Carton

A selling unit must be independently priceable.

Do NOT put one authoritative price directly on the product record if pricing belongs to selling units.

Plan clear relationships:

Product
→ Product Unit
→ Pricing Tiers

Availability behavior must support MVP:

* Available
* Out of Stock
* Inactive

Avoid implementing full inventory quantities.

---

# 9. Selling Units

Each selling unit may need data such as:

* product
* display name
* package description
* conversion information where useful
* active state
* default/display ordering

Do not overbuild inventory conversion behavior that MVP does not use.

But avoid a structure that would make future inventory conversion impossible.

---

# 10. Pricing Model

This is business-critical.

Plan a deterministic pricing service.

Inputs should conceptually include:

* product
* selected selling unit
* quantity
* current eligible pricing tiers
* currently active product offer
* relevant date/time

Approved rule:

Normal unit price

versus

eligible quantity-tier price

versus

active offer price.

When both tier price and offer price are eligible:

**the lower eligible unit price wins.**

They DO NOT stack.

Examples:

Normal = 200
Tier = 170
Offer = 160

Final = 160

Normal = 200
Tier = 150
Offer = 160

Final = 150

Plan how PricingService returns enough information to explain:

* original/base price
* tier price if eligible
* offer price if eligible
* final applied price
* applied source/reason
* savings if useful

Server is authoritative.

---

# 11. Money Handling

Define a safe monetary strategy for MySQL and PHP.

Avoid floating-point money calculations.

Choose an appropriate deterministic representation such as decimal database values and/or integer minor units where justified.

Document:

* storage
* calculation
* rounding
* serialization/display boundary

Arabic MVP display:

`444 ج`

But stored money must be locale/language neutral.

---

# 12. Product Offers

Plan simple MVP product offers.

Conceptually:

* selling unit/product target as appropriate
* offer price
* start
* end
* active state

Do NOT create a generalized promotion rules engine.

Offer validity must be server-evaluated.

---

# 13. Cart Architecture

Decide whether authenticated carts should live in:

* database
* session
* or justified hybrid

Consider:

* customers authenticate before ordering
* customers may leave and return
* future mobile/API client
* simple low-cost infrastructure

Document the decision.

Cart must not be financially authoritative.

At checkout all lines are recalculated server-side.

Cart item needs at least conceptual reference to:

* product
* selling unit
* requested quantity

Do NOT treat cached cart price as final truth.

---

# 14. Minimum Order

Admin-configurable setting.

Approved calculation basis:

effective product subtotal after applied pricing/offers.

Exclude:

* delivery fee
* delivery discount

Plan where this setting belongs and how Cart/Checkout validates it server-side.

---

# 15. Delivery Areas

Plan:

Delivery Area

* name
* active state
* base delivery fee

Customer default address references an allowed delivery area.

If an area becomes inactive:

new checkout must reject it.

---

# 16. Delivery Slots

MVP slots have no numeric capacity.

They only need availability such as:

* time interval
* active/inactive
* applicable date/day model

Research and choose the simplest model satisfying the approved specification.

Explain how admins manage slots without creating unnecessary scheduling complexity.

Checkout must revalidate slot availability.

---

# 17. Delivery Discounts

Plan deterministic delivery-discount calculation.

Supported conceptual types:

* fixed amount
* percentage
* free delivery

Qualification based on effective product subtotal.

Rules do not stack.

If multiple qualify:

calculate monetary saving against current base delivery fee and choose the single rule producing the largest saving.

Discount cannot reduce delivery below zero.

Tie-break:

higher qualifying minimum subtotal.

Plan a `DeliveryService` / calculation result that clearly exposes:

* base delivery fee
* selected rule
* discount amount
* final delivery fee

---

# 18. Checkout Architecture

Approved UI:

Step 1 — Delivery

Step 2 — Review & Confirm

Server must revalidate before creating order:

* product active
* product available
* selling unit active
* price
* tier
* offer
* minimum order
* delivery area
* delivery fee
* delivery discount
* delivery slot

If the server calculation differs from what customer reviewed:

do NOT silently place the order.

Return a structured changed-commercial-terms result to presentation layer so the customer can review again.

Plan this workflow explicitly.

---

# 19. Order Creation Transaction

Order placement must be transactional.

Plan the transaction boundary.

Avoid:

* partial order creation
* order header without items
* stale browser totals
* mutable historical pricing

Order creation must capture immutable commercial snapshots.

---

# 20. Order Snapshot Model

Order items must preserve enough information that historical orders remain correct after product/catalog changes.

Plan snapshot fields such as:

* product reference where useful
* product name snapshot
* product brand snapshot where useful
* selling unit reference where useful
* unit name snapshot
* package description snapshot
* quantity
* base/list price snapshot where required
* final unit price
* applied pricing/offer description/reference where useful
* line total

Order should snapshot:

* customer/business identity as necessary
* phone
* WhatsApp
* delivery area/address
* delivery date/slot
* product subtotal
* base delivery fee
* delivery discount
* final delivery fee
* final total
* payment method

Be pragmatic; do not create a full event-sourcing architecture.

---

# 21. Order Number

Define a user-facing unique order-number strategy.

Do not expose raw database implementation unnecessarily.

The format should remain human-friendly.

Document uniqueness/concurrency requirements.

---

# 22. Order Statuses

Internal identifiers must be language-neutral.

Approved statuses:

* `new`
* `confirmed`
* `preparing`
* `out_for_delivery`
* `delivered`
* `cancelled`

Arabic labels are presentation concerns.

Plan valid state transitions.

Customer self-cancel:

only from `new`.

Admin cancellation allowed from:

* new
* confirmed
* preparing
* out_for_delivery

No cancellation from delivered/cancelled.

Avoid implementing a complex workflow engine.

A domain method/action or transition validator is sufficient.

---

# 23. Customer Address

MVP UI exposes one default address.

Architecture should support multiple customer addresses later without a destructive rewrite.

Plan:

Customer
→ addresses

with one address treated as default/active for MVP.

Do not build multi-address selection UI now.

---

# 24. Customer Type

Primarily B2B.

Plan profile data for:

* Business / Restaurant Name
* Contact Person Name
* Login Mobile
* WhatsApp

No tax/KYC/legal-company module.

---

# 25. Search

MVP search should remain inexpensive for shared hosting.

Do NOT introduce Elasticsearch/Meilisearch requirement.

Plan MySQL-based catalog search suitable for initial product volume.

Account for:

* Arabic product/category data
* naturally English brand names
* future English content

Document useful indexes/query patterns.

---

# 26. Landing Page

Landing Page belongs to same Laravel application.

Plan public rendering using Blade.

It should reuse catalog/offers data where appropriate rather than maintain a separate site/application.

Do not introduce a CMS.

Business/contact settings may be managed in Settings if practical.

---

# 27. PWA Architecture

Plan MVP PWA support.

At minimum address:

* manifest
* icons
* installability
* theme/background metadata
* service worker
* offline fallback
* asset caching

Offline order submission is NOT allowed.

The application must not indicate order success unless server creation succeeded.

Avoid complex offline data synchronization.

Plan a minimal caching strategy appropriate for low-cost hosting.

---

# 28. Product Images

Initial deployment should support local/public Laravel storage.

Plan:

* upload validation
* file naming/path strategy
* optimized web-friendly formats where practical
* size limits
* thumbnail/display strategy if needed

Avoid requiring S3 for MVP.

Architecture should allow moving media storage to object storage later.

---

# 29. Admin / Filament

Research and choose an exact Filament version compatible with:

* PHP 8.2
* Laravel 12

Verify dependency requirements.

Admin is Arabic-first.

Plan Filament Resources/pages for:

* Dashboard
* Orders
* Customers
* Categories
* Products
* Product selling units
* Pricing tiers
* Offers
* Delivery areas
* Delivery slots
* Delivery discounts
* Settings

Follow the approved `admin-design.md`.

Do not duplicate domain logic inside Filament actions.

Filament calls application/domain services for complex actions.

---

# 30. Admin Users

MVP supports multiple admins.

Fine-grained RBAC is outside MVP.

Do NOT introduce `spatie/laravel-permission` unless there is a concrete MVP requirement.

Standard authenticated admin access is enough initially.

Document future RBAC upgrade path if useful.

---

# 31. Notifications

MVP requirement:

Admins must be able to identify new orders.

WebSockets are NOT required.

Choose the simplest low-cost approach.

Examples to evaluate:

* admin dashboard polling
* database notification records
* Filament polling

Do not require Redis or Reverb.

Customer SMS/order notifications beyond OTP are not automatically required unless present in the approved spec.

---

# 32. Queue Strategy

Initial hosting may have limited worker support.

Do not require Redis queues.

Evaluate:

* synchronous work where safe
* database queue if asynchronous work is necessary
* cron-driven queue processing on compatible hosting

OTP sending latency/reliability must be considered.

Document the simplest MVP approach and upgrade path.

---

# 33. Caching

Do not introduce Redis as an MVP dependency.

Evaluate Laravel file/database cache for appropriate low-cost hosting.

Only cache things where it provides clear value.

Plan cache invalidation where applicable.

Do not prematurely cache dynamic pricing/cart/order calculations.

---

# 34. Localization Architecture Readiness

MVP exposed language:

Arabic only.

Future:

Arabic + English.

Arabic is fallback/default locale.

Technical plan MUST address future localization readiness.

Plan:

* Laravel translation catalog/file organization
* no hard-coded UI strings where avoidable
* Arabic default locale
* fallback locale
* language-neutral domain identifiers
* RTL direction metadata
* future LTR support
* localized validation
* localized notifications
* localized admin labels
* money/date/time presentation
* PWA metadata localization
* landing SEO metadata localization
* locale-aware caching if relevant

Do NOT expose a language switcher in MVP.

---

# 35. Future Localized Managed Content

MVP product/category content may be Arabic only.

But plan a database/content strategy that does not require destructive redesign when English content is later added.

Evaluate alternatives such as:

* translation tables
* JSON localization fields
* dedicated localized content records

Choose an approach for MVP schema or document an intentionally deferred migration path.

The key requirement:

adding English later should not require rewriting commerce/business logic.

Avoid overengineering every table with unused multilingual fields unless justified.

---

# 36. RTL Technical Requirements

Plan RTL-first customer and admin rendering.

Use direction-aware/layout-logical approaches.

Do not manually reverse Arabic strings.

Mixed text must work:

Arabic + English brands + Latin digits + package units.

Implementation later should support switching document direction to LTR when English is added.

---

# 37. Currency Presentation

MVP UI:

`444 ج`

Examples:

`1,250 ج`
`خصم التوصيل: -50 ج`

Currency rendering must be centralized.

Do not concatenate currency formatting throughout random templates.

Plan a presentation formatter/helper/component.

Future English can use a different localized format without touching stored amounts or commerce calculations.

---

# 38. Dates and Times

Plan centralized locale-aware presentation formatting.

Stored timestamps remain language-neutral.

Customer-selected delivery dates/slots need deterministic business representation separate from display localization.

---

# 39. Validation Localization

Validation rules must be separate from messages.

Arabic MVP validation copy.

Future English translation possible through Laravel localization.

---

# 40. PWA Localization Readiness

MVP manifest may be Arabic.

Plan an approach that does not prevent localized:

* app name
* short name
* description

later.

Do not build separate Arabic and English applications.

---

# 41. Security

Plan protection for:

* OTP abuse
* auth rate limiting
* session security
* CSRF
* authorization
* validation
* mass assignment
* secure file uploads
* admin protection
* sensitive data/log handling

No secrets committed.

Plan production environment configuration.

---

# 42. Database Indexing

For every important table identify expected query patterns.

Explicitly consider indexes for:

* customer phone
* category/product listing
* product search
* product availability
* product-unit lookup
* tier lookup
* active offer lookup
* cart/customer
* customer orders
* order number
* order status
* order created date
* active delivery areas
* delivery discount qualification
* OTP phone/expiry

Avoid excessive indexes.

Explain important composite index choices.

---

# 43. Concurrency / Data Integrity

Address:

* duplicate order submission
* OTP resend/verification concurrency
* order-number generation
* checkout double click
* price changing during checkout
* item becoming unavailable during checkout

Plan appropriate idempotency/locking/unique constraints where justified.

Do not overengineer distributed locking.

---

# 44. Performance

Initial target is low-cost hosting.

Plan:

* pagination
* eager loading
* avoiding N+1
* image optimization
* minimal JS
* database indexes
* efficient catalog queries

Do not preload entire product catalog.

Do not calculate every possible pricing tier for every product on listing pages.

Product listing cards should show only the approved lightweight pricing information.

Detailed price evaluation belongs on Product Details/Cart/Checkout.

---

# 45. Testing Strategy

Constitution requires tests for commercial logic.

Plan mandatory automated tests for:

## Unit / Domain Tests

* pricing tiers
* offer/tier lower-of rule
* delivery discounts
* minimum order
* money calculations
* status transitions

## Feature Tests

* OTP flow
* profile setup
* product browsing
* cart
* checkout revalidation
* order creation
* cancellation
* admin status updates

## Critical Boundary Tests

Pricing:

4 → 5
9 → 10

Minimum order:

499 → blocked
500 → allowed

Delivery discount thresholds.

Expired offer.

Inactive area.

Inactive slot.

Out-of-stock item.

Double submission.

Do not make every view require heavy browser automation.

Recommend pragmatic test levels.

---

# 46. Design Implementation Strategy

Plan how Blade/Alpine/Tailwind implementation should follow the approved design artifacts.

Avoid creating a large custom SPA.

Identify reusable customer components such as:

* Header
* Bottom Navigation
* Product Card
* Price Display
* Quantity Selector
* Unit Selector
* Cart Item
* Order Card
* Status Badge
* Form fields
* Empty/Error states

Do not implement them yet.

---

# 47. Deployment Strategy

Initial goal:

low-cost production hosting.

Plan minimum production requirements:

* PHP 8.2
* required PHP extensions
* MySQL 8
* Composer/deployment process
* Node build handling
* public directory
* HTTPS
* writable storage/cache
* storage link
* scheduler/cron
* queue approach if used
* backups
* logs

Do not require Docker.

Provide an upgrade path later to:

VPS
→ Redis
→ persistent workers
→ object storage
→ CDN

without redesigning the application.

---

# 48. Environment Separation

Plan:

* local
* staging/demo
* production

For demo/development OTP, support a safe non-production mechanism.

Production must never expose static/test OTP behavior.

---

# 49. Technical Documentation Artifacts

Generate the standard Spec Kit planning outputs as appropriate, including:

* `plan.md`
* `research.md`
* `data-model.md`
* relevant contracts/interfaces documentation
* `quickstart.md`

Use the Spec Kit workflow.

The plan should be detailed enough for `/speckit-tasks` to later create implementation-ready vertical slices.

---

# 50. Architecture Decision Records / Decisions

Record major decisions clearly, especially:

* Laravel 12 + PHP 8.2
* exact Filament-compatible version
* monolith
* customer/admin auth separation
* pricing model
* money representation
* cart persistence
* order snapshot strategy
* delivery slot model
* delivery discount resolution
* localization strategy
* future translatable content strategy
* queue strategy
* storage strategy
* deployment assumptions

If Spec Kit has no dedicated ADR mechanism at this point, include explicit decision/rationale sections in plan/research.

---

# 51. Constitution Check

Explicitly verify the plan against all nine constitution principles.

Any violation is a BLOCKER.

Do not waive constitution constraints simply to simplify implementation.

---

# 52. Scope Guard

Do NOT introduce these into MVP:

* online payment
* customer credit
* full inventory quantities
* warehouses
* multiple branches
* drivers
* route optimization
* live tracking
* loyalty
* advanced coupons
* recurring orders
* Buy Again
* customer-specific price lists
* English UI
* language switcher
* microservices
* advanced RBAC

Readiness for future growth is allowed.

Implementation of those features is not.

---

# Required Final Report

After `/speckit-plan` completes, return:

1. files created
2. exact proposed tech stack and verified versions
3. architecture summary
4. proposed domain modules
5. database table count and entity summary
6. pricing architecture summary
7. cart persistence decision
8. order snapshot approach
9. OTP approach
10. admin/Filament version decision
11. localization strategy
12. PWA strategy
13. hosting/deployment strategy
14. test strategy
15. identified technical risks
16. any unresolved technical decisions
17. Constitution Check result
18. confirmation that no application code was written

Do NOT run `/speckit-tasks`.
Do NOT run `/speckit-analyze`.
Do NOT implement the application.
