Claude Prompt — Refactor Customer Profile & Address Ownership
Prompt Number
43
Purpose
Correct customer/profile/address ownership before completing Checkout and Orders.
The customer's business/contact/delivery information must be stored persistently on the customer profile/address records.
The customer must NOT re-enter these fields for every order.
Orders should only store immutable SNAPSHOTS of the customer/delivery data used at order time.
Laravel application root:
src/
1. Core Rule
Canonical customer data belongs to:
- customers
- customer_addresses
NOT to orders as the primary source of truth.
Checkout should prefill from the customer's saved/default address.
The order stores snapshots only for historical integrity.
2. Customer Fields
The following belong to customers:
business_name
contact_person_name
phone
whatsapp_phone
Notes:
- phone is the customer's login/mobile phone
- whatsapp_phone is optional if approved
- business/contact data is entered during onboarding
- customer may edit profile later according to approved UI
Do not ask for these on every order.
3. Address Fields
The following belong to a saved customer address record:
customer_id
delivery_area_id
address_line
building
floor
apartment
shop
landmark
notes
is_default
Use actual existing approved fields if naming differs.
Do NOT store delivery_area_name as canonical free text.
Prefer:
delivery_area_id
linked to delivery_areas.
The delivery-area display name comes from the related Delivery Area record.
4. MVP Address UX
MVP UI may expose only ONE default address.
However, schema should remain multi-address-ready.
Customer onboarding creates or captures the default address.
At checkout:
- default address loads automatically
- customer does not re-enter business/contact/address details every time
- customer may edit/change saved address only through the approved profile/address flow
Do not build a complex address book UI unless already approved.
5. Order Ownership
orders should reference:
customer_id
customer_address_id
where practical.
But the order must also store immutable snapshots.
The relationship is for traceability.
The snapshot is the historical truth for that order.
6. Order Snapshot Fields
At order creation, snapshot the delivery/customer information needed to fulfill and review the order.
Conceptually:
business_name_snapshot
contact_person_name_snapshot
phone_snapshot
whatsapp_phone_snapshot

delivery_area_id_snapshot
delivery_area_name_snapshot
address_line_snapshot
building_snapshot
floor_snapshot
apartment_snapshot
shop_snapshot
landmark_snapshot
notes_snapshot
Exact names may follow project conventions.
Do NOT use these snapshot fields as editable customer-profile storage.
7. Why Snapshot Is Required
Example:
Customer places Order #1001 using:
Area: Nasr City
Address: Building 10, Floor 2
Later the customer updates profile/address to:
Area: Fifth Settlement
Address: Building 50, Floor 1
Order #1001 MUST still display:
Nasr City
Building 10, Floor 2
Historical orders must never change because the customer profile changed later.
8. Checkout Behavior
Checkout Step 1 — Delivery should:
1. load customer's saved/default address
2. show it to the customer
3. allow the approved address-selection/edit flow only if already in scope
4. validate the related Delivery Area is still active
5. validate delivery slot
6. continue to Review & Confirm
Do not display empty inputs for all customer data on every checkout unless data is missing and onboarding/profile completion is required.
9. Onboarding Behavior
First-time B2B onboarding must capture:
Business / Restaurant Name
Contact Person
Login Phone
WhatsApp
Delivery Area
Address
Building
Floor
Apartment / Shop as applicable
Landmark
Notes
Use the actual approved field set from spec.
After onboarding, this data persists.
10. Migration Refactor
Inspect current migrations first.
If fields such as:
business_name
contact_person_name
phone
whatsapp_phone
delivery_area_name
address_line
building
floor
were added directly to orders as canonical input fields:
refactor safely.
Do NOT use:
php artisan migrate:fresh
php artisan db:wipe
If orders already exist locally:
- preserve their values as historical snapshots
- rename/map fields safely where needed
- do not lose existing order data
If customer/profile/address records need backfill:
perform a safe deterministic backfill only where data can be trusted.
Do not invent ambiguous addresses.
11. Delivery Area Relation
Canonical customer address should reference:
delivery_area_id
not only:
delivery_area_name
Order snapshot may contain BOTH:
delivery_area_id_snapshot
delivery_area_name_snapshot
because the delivery-area record/name could later change.
12. Phone / Contact Snapshot
Even though phone/contact data lives on the Customer record, snapshot it into an order at creation if needed for fulfillment/history.
This does NOT mean asking the customer to type it again.
It is copied automatically by the server.
13. Server-Side Order Creation
OrderService should obtain profile/address data from authenticated customer + chosen saved address.
Pseudo-flow:
Authenticated Customer
→ Saved Default Address
→ Validate profile/address/area
→ Snapshot current values
→ Create Order
Do not trust client-posted business/contact/address strings as the source of truth when saved records exist.
Use IDs/references plus server-side loading.
14. Validation
Before checkout/order creation verify:
- customer profile complete
- default/chosen address exists
- delivery area exists
- delivery area active
- required address fields populated
If profile is incomplete:
send customer to complete/update profile rather than asking for duplicated checkout data.
15. Admin Views
Admin Order Detail must show SNAPSHOT values from the order.
Do not render historical order delivery details from current customer profile.
Admin Customer Detail should show current customer/profile/address data separately.
Make the distinction clear.
16. Customer Order History
Customer Order Detail should also display the historical snapshot used for that order.
Do not substitute their current saved address.
17. Audit Log
If AdminAuditService exists:
audit admin edits to customer profile/address only if admin-edit capability exists in approved scope.
Do not log sensitive auth secrets.
Normal customer self-profile editing does not need to become an admin audit event unless separately required.
18. Tests
Mandatory:
Profile persistence
- onboarding saves business/contact data
- onboarding saves default address
- later checkout reads saved data automatically
No repeated entry
- checkout can proceed without resubmitting canonical customer text fields when profile/address already exists
Snapshot integrity
- order snapshots current customer/profile/address data
- customer later changes business/contact/address
- old order still shows original snapshot
Delivery area
- customer address references delivery_area_id
- order stores delivery-area snapshot
- renamed area does not alter old order display
Security
- customer cannot submit another customer's address ID
- order creation loads address owned by authenticated customer
19. Tasks / Specs Update
Update as needed:
- spec.md
- plan.md
- data-model.md
- contracts
- tasks.md
Make explicit:
Customer profile/address = canonical current data
Order fields = immutable historical snapshots
20. Integration with Master Runner
This correction must be applied BEFORE completing:
- Phase H Checkout
- Phase I Order Placement
- Phase J Order History/Lifecycle
If autonomous Prompt 42 is currently running and has not yet reached these phases:
apply this amendment before those tasks.
If related work has already been implemented:
refactor it before marking those phases complete.
21. Migration / Runtime Gate
Follow Prompt 36.
Run:
php artisan migrate:status
php artisan migrate
php artisan test
composer check-platform-reqs
npm run build
No destructive reset.
Final Report
Return:
1. current incorrect/legacy field locations found
2. customer fields final location
3. address fields final location
4. order snapshot fields
5. migrations/refactors applied
6. checkout behavior
7. onboarding behavior
8. historical order behavior
9. tests added
10. test result
11. build result
12. remaining blockers
13. git status --short
End with:
CUSTOMER PROFILE AND ORDER SNAPSHOT MODEL READY