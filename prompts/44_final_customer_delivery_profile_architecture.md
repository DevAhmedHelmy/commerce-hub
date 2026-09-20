Claude Prompt — Final Customer Registration, Delivery Area & Persistent Address Architecture
Prompt Number
44
Purpose
Finalize the Customer + Delivery Area + Address architecture.
This prompt SUPERSEDES Prompt 43 where there is any ambiguity.
The required customer experience is:
1. Customer registers/authenticates using phone + OTP.
2. During first-time onboarding, customer enters their business/contact data.
3. Customer selects a Delivery Area.
4. Customer enters their full delivery address.
5. These details are saved permanently to the customer profile/address.
6. Every future order automatically knows the customer's delivery destination from the saved address.
7. The customer must NOT re-enter phone, WhatsApp, area, or address for every order.
8. Orders automatically copy immutable snapshots of the saved customer/address data for historical accuracy.
Architecture must remain ready for multiple customer addresses in the future, while MVP UI may use only one default address.
Laravel application root:
src/
Prompt history root:
prompts/
1. Final Domain Architecture
Use three separate concepts:
Customer
DeliveryArea
CustomerAddress
Relationships:
Customer
  hasMany CustomerAddress
  hasOne default CustomerAddress conceptually

CustomerAddress
  belongsTo Customer
  belongsTo DeliveryArea

DeliveryArea
  hasMany CustomerAddress
Orders belong to Customer and reference the chosen CustomerAddress, while storing immutable snapshots.
2. Customer Model
Canonical customer/business/contact information belongs to:
customers
Required fields conceptually:
id
business_name
contact_person_name
phone
whatsapp_phone
is_active
onboarding_completed_at
created_at
updated_at
Use current project conventions if equivalent fields already exist.
Important
phone is the customer's login/mobile number.
It must NOT be typed again during every checkout.
whatsapp_phone is stored once on the profile and may be edited later from Account/Profile.
Business data is also persistent.
3. DeliveryArea Model
Delivery areas must be an independent reusable model.
Model:
DeliveryArea
Table:
delivery_areas
Suggested fields:
id
name_ar
name_en
delivery_fee
is_active
sort_order
created_at
updated_at
Use integer money representation for delivery_fee according to the existing project money architecture.
Do NOT store canonical delivery area as free-text on the Customer.
4. Why DeliveryArea Is Separate
Delivery Area represents an admin-managed delivery zone.
Example:
التجمع الخامس
مدينة نصر
المعادي
The area may control:
- whether delivery is available
- base delivery fee
- eligibility for delivery rules
- future operational behavior
CustomerAddress references the area by ID.
Example:
delivery_area_id = 3
Do NOT make:
delivery_area_name
the canonical customer relationship.
5. CustomerAddress Model
Use:
customer_addresses
Suggested fields:
id
customer_id
delivery_area_id

label
address_line
building
floor
apartment
shop
landmark
notes

is_default
is_active

created_at
updated_at
Use only approved/needed fields.
label may be optional/future-ready, for example:
المطعم الرئيسي
الفرع الثاني
Do not expose unnecessary address-book complexity in MVP.
6. MVP Address Rule
The database architecture MUST support multiple addresses in the future.
However MVP customer UI may expose only:
one default delivery address
This means:
- schema: multi-address ready
- business services: chosen/default address aware
- MVP onboarding: creates first default address
- MVP checkout: automatically uses that default address
Future enhancement can add:
Home / Branch 1 / Branch 2 / etc.
without redesigning Orders.
7. First-Time Registration / Onboarding Flow
Required flow:
Phone
→ OTP verification
→ Customer onboarding
→ Business/contact information
→ Select Delivery Area
→ Enter Delivery Address
→ Save
→ Customer account ready
Onboarding fields should include:
Business / Restaurant Name
Contact Person Name
Phone
WhatsApp Phone

Delivery Area
Address
Building
Floor
Apartment / Shop
Landmark
Notes
Use actual approved Arabic UI labels.
8. Phone Behavior
Login phone should already be known after OTP verification.
Do NOT make the customer manually re-enter the same login phone unless confirmation/display is needed.
Server should associate the verified phone with the Customer.
Customer may enter WhatsApp separately.
Example:
Login phone:
010xxxxxxxx

WhatsApp:
010yyyyyyyy
They may also be the same number.
9. Onboarding Persistence
After onboarding:
Customer
  stores business/contact/phone/WhatsApp

CustomerAddress
  stores delivery area + address details
The customer does NOT re-enter them for every purchase.
10. Checkout Behavior
Checkout must load the authenticated Customer.
Then load their active default CustomerAddress.
Conceptually:
Authenticated Customer
→ Default CustomerAddress
→ DeliveryArea
→ Delivery slot
→ Review
→ Order
Checkout should DISPLAY the saved delivery information.
Example:
التوصيل إلى

اسم النشاط: مطعم ...
المسؤول: ...
الهاتف: ...
واتساب: ...

المنطقة: التجمع الخامس
العنوان: ...
المبنى: ...
الدور: ...
But do NOT make these normal blank checkout form fields every time.
11. Changing Customer Information
If the customer wants to change:
- business name
- contact person
- WhatsApp
- address
- area
they should change it through:
Account / Profile / Address
not by rewriting all fields inside every order.
Checkout may provide a clear action such as:
تعديل العنوان
which routes to the approved profile/address edit flow.
After editing, checkout reloads the saved address.
12. Future Multiple Address Support
Design services so checkout can later receive:
customer_address_id
When multiple addresses are exposed in future, customer can select:
فرع التجمع
فرع مدينة نصر
فرع المعادي
For MVP:
use the active default address automatically.
Do not hard-code OrderService to assume Customer can only ever have one address row.
13. Default Address Integrity
Enforce conceptually:
- one default active address per customer
- first address becomes default automatically
- customer cannot order without an active valid address
- chosen/default address must belong to authenticated customer
Use DB/service constraints as appropriate.
14. Order Model
Order should contain references:
customer_id
customer_address_id
Use safe FK behavior.
Historical orders must NOT break if the customer later changes or disables an address.
Prefer preserving address records or allowing nullable historical FK while snapshots remain authoritative.
Do not cascade-delete historical order data.
15. Order Snapshots
At Order creation, automatically snapshot the current data.
The CUSTOMER DOES NOT ENTER THESE AGAIN.
Snapshot conceptually:
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
address_notes_snapshot
Also snapshot commercial delivery data separately where appropriate:
base_delivery_fee_snapshot
delivery_discount_snapshot
final_delivery_fee_snapshot
Exact naming may follow current conventions.
16. Snapshot Example
Customer currently has:
Business: Emdad Restaurant
Phone: 01011111111
WhatsApp: 01022222222
Area: Fifth Settlement
Address: Building 20, Floor 1
Order #1001 automatically stores those values.
Later customer changes address to Nasr City.
Order #1001 MUST STILL display:
Fifth Settlement
Building 20, Floor 1
New Order #1002 uses:
Nasr City
new address...
No historical mutation.
17. Server Authority
OrderService must NOT trust client-submitted copies such as:
business_name
phone
delivery_area_name
address_line
as canonical order input.
Client should submit only required identifiers/choices such as:
customer_address_id
delivery_slot_id
Server loads:
Customer
CustomerAddress
DeliveryArea
and creates snapshots itself.
This prevents tampering.
18. Area Validation at Checkout
At checkout/order placement verify:
- Customer exists and authenticated
- Customer onboarding complete
- CustomerAddress exists
- address belongs to Customer
- address active
- DeliveryArea exists
- DeliveryArea active
- delivery still available
- required address fields valid
If DeliveryArea became inactive:
block order and show clear Arabic message.
Do not silently deliver to an unavailable area.
19. Delivery Fee
Delivery fee comes from DeliveryArea / DeliveryService.
Do NOT store a reusable delivery fee inside CustomerAddress.
Example:
CustomerAddress
→ delivery_area_id = 3

DeliveryArea #3
→ delivery_fee = 60 ج
At Order creation:
snapshot the actual calculated fee.
If area fee later changes to:
80 ج
old order remains:
60 ج
20. Customer Account UI
Customer Account should show persistent current information.
At minimum:
Business Information
Contact Information
Delivery Address
Allow editing according to approved flow.
Do not make checkout the primary profile editor.
21. Admin Customer UI
Admin customer detail should clearly show:
Current Customer Data
- business name
- contact person
- phone
- WhatsApp
Current Delivery Address
- area
- address
- building
- floor
- apartment/shop
- landmark
- notes
Historical order pages must use Order snapshots, not these current fields.
22. Registration Completeness
Customer should not be treated as ready to order until onboarding is complete.
Required profile/address data must exist.
Suggested concept:
onboarding_completed_at
or equivalent existing project mechanism.
If incomplete:
redirect customer to onboarding/profile completion.
23. Migration Review
Inspect all existing migrations/models first.
Possible existing incorrect fields may include:
$table->string('business_name')->nullable();
$table->string('contact_person_name')->nullable();
$table->string('phone')->nullable();
$table->string('whatsapp_phone')->nullable();
$table->string('delivery_area_name')->nullable();
$table->string('address_line')->nullable();
$table->string('building')->nullable();
$table->string('floor')->nullable();
Determine WHERE they currently exist.
If these are currently on orders as the primary customer data model:
refactor safely.
Do NOT simply drop them if existing Orders depend on them.
Convert/rename them into explicit snapshot semantics where appropriate.
24. Safe Migration Rules
Do NOT run:
php artisan migrate:fresh
php artisan db:wipe
Preserve existing local data.
Use additive/rename/backfill migrations where needed.
If existing customer data can be mapped safely:
backfill.
If ambiguous:
report the exact records requiring manual decision.
Do not invent area IDs or addresses.
25. Existing Customer Backfill
If customers already exist without CustomerAddress records:
attempt deterministic migration only when enough data exists.
Example:
If old customer table contains:
delivery_area_id
address_line
building
floor
create their default CustomerAddress.
If only delivery_area_name exists and cannot map uniquely:
do not guess.
Report mapping requirement.
26. Relations
Expected relationships conceptually:
Customer
public function addresses()
{
    return $this->hasMany(CustomerAddress::class);
}
Provide a clean default-address resolver/service/query.
CustomerAddress
public function customer()
{
    return $this->belongsTo(Customer::class);
}

public function deliveryArea()
{
    return $this->belongsTo(DeliveryArea::class);
}
DeliveryArea
public function customerAddresses()
{
    return $this->hasMany(CustomerAddress::class);
}
Follow actual project model conventions.
27. Service Responsibilities
Keep controllers thin.
Use domain/application services.
Conceptually:
CustomerProfileService
CustomerAddressService
CheckoutService
OrderService
DeliveryService
Responsibilities should remain clear.
Do not put order snapshot logic in Blade/Alpine.
28. Tests — Registration
Mandatory tests:
- OTP-authenticated customer can complete onboarding
- business data persists
- phone persists
- WhatsApp persists
- selected DeliveryArea persists through CustomerAddress
- full address persists
- first address becomes default
- onboarding becomes complete
29. Tests — Reuse on Every Order
Mandatory:
Customer completes onboarding once.
Then create first checkout/order WITHOUT resending:
business_name
contact_person_name
phone
whatsapp_phone
delivery_area_name
address_line
building
floor
Order should succeed using saved data.
Repeat second order.
It should again use saved data automatically.
This is a critical acceptance test.
30. Tests — Snapshot Integrity
Test:
1. customer creates order
2. order snapshots customer/address
3. customer edits profile/address
4. first order retains original snapshots
5. second order uses new current data
Mandatory.
31. Tests — Security
Mandatory:
- Customer A cannot order using Customer B address ID
- inactive address rejected
- inactive DeliveryArea rejected
- manipulated submitted address text is ignored/not trusted
- OrderService snapshots server-loaded records
32. Tests — Delivery Fee History
Test:
1. DeliveryArea fee = 50
2. customer places order
3. area fee changed to 70
4. old order remains 50
5. new order uses 70
Use approved delivery-discount logic when applicable.
33. Customer UX
Use /restaurant-ui.
Registration/onboarding/profile/address UI must be:
- Arabic-first
- RTL
- responsive
- mobile-first
- laptop usable
- system dark mode compatible
- clear validation
- no duplicate unnecessary fields
Customer should feel:
سجل بياناتي مرة واحدة
→ اطلب بسهولة بعد كده
34. Update Planning Artifacts
Update:
- spec.md
- plan.md
- data-model.md
- contracts
- tasks.md
- screen specs if relevant
Make explicit:
Customer = current persistent identity/business/contact data
CustomerAddress = current persistent delivery destination
DeliveryArea = admin-managed service area
Order = immutable snapshot of the values used at purchase time
35. Integration With Prompt 42
This prompt must be applied BEFORE Phase H/I/J are considered complete.
If Prompt 42 autonomous runner is active:
incorporate this requirement into the remaining tasks.
Do not continue with a checkout/order architecture that asks the customer to retype their saved delivery information every order.
36. Runtime / Migration Gate
Follow Prompt 36.
Run:
php artisan migrate:status
php artisan migrate
php artisan test
composer check-platform-reqs
npm run build
No destructive reset.
Perform visual review for:
- onboarding
- profile
- saved address display
- checkout delivery step
37. Final Report
Return:
1. previous architecture found
2. Customer final fields
3. DeliveryArea final schema
4. CustomerAddress final schema
5. relationships
6. onboarding flow
7. checkout saved-address behavior
8. order snapshot behavior
9. migration/backfill performed
10. tests added
11. test result
12. build result
13. visual review URLs
14. tasks/specs updated
15. remaining blockers
16. git status --short
End with:
PERSISTENT CUSTOMER DELIVERY PROFILE READY
only when the customer can register their data once and subsequent orders automatically use the saved delivery information.