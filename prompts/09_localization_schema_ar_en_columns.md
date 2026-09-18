Claude Prompt — Localization Schema Decision: Arabic + English Columns from Day One

Prompt Number

09

Phase

Phase 3 — Technical Planning / Localization Schema Decision

Purpose

Update the technical planning artifacts with the approved localization storage decision.

This prompt SUPERSEDES the earlier recommendation in prompt 08 that suggested keeping managed content Arabic-only and adding translation storage later.

Approved Decision

For selected business-managed content that is expected to be translated later, create Arabic and English fields from the beginning.

For MVP:

Arabic is the only exposed UI language.

English UI is NOT exposed yet.

No language switcher is shown.

The Arabic value is authoritative for MVP.

The English field may temporarily contain the SAME Arabic value until actual English translation is added.

This avoids a future schema migration solely to introduce the second language.

1. Where to Use _ar / _en Fields

Use bilingual fields only for managed content that is genuinely likely to need translation.

Examples:

Categories

name_ar

name_en

Optional if description exists:

description_ar

description_en

Products

name_ar

name_en

If product descriptions are managed:

description_ar

description_en

Product Selling Units

If unit display names are stored as managed content:

name_ar

name_en

Examples:

كيس / Bag

كرتونة / Carton

عبوة / Pack

Offers

If offer title/description is stored:

title_ar

title_en

description_ar

description_en

Delivery Areas

If the delivery-area display name will be localized:

name_ar

name_en

Delivery Slots

Only localize display labels if a label is stored.
Do not duplicate raw time values.

Settings / Landing Managed Content

If business-managed landing content is stored in the database later, localized textual settings should follow the same _ar / _en pattern where practical.

2. MVP Initial Values

When Arabic content is created in MVP and a corresponding English field is required:

Example:

name_ar = "بطاطس مجمدة"

Initially allow:

name_en = "بطاطس مجمدة"

until the business provides:

name_en = "Frozen Fries"

The same approach may be used for descriptions and other localized managed content.

Do NOT automatically machine-translate content.

Do NOT require English content before an admin can save an MVP record.

The MVP must remain fully operable with Arabic content only.

3. Fallback Behavior

Plan a centralized localized-content resolver.

Conceptually:

If current locale is Arabic:

use _ar

If current locale is English in the future:

use _en

if _en is empty/null, fall back to _ar

Although _en may initially contain Arabic, fallback behavior must still exist for resilience.

Do not scatter locale-selection logic across Blade, controllers, Filament resources, or domain services.

Use a centralized presentation/helper/model-accessor strategy to be decided in implementation planning.

Business logic must not depend on translated values.

4. What MUST Remain Language-Neutral

Do NOT create _ar / _en versions of language-neutral technical/domain fields.

Examples:

IDs

SKU

product codes

status codes

enum identifiers

unit technical codes

quantity

prices

discount types

dates/times

order numbers

phone numbers

WhatsApp numbers

monetary values

foreign keys

booleans

Examples of correct internal values:

new

confirmed

out_for_delivery

fixed

percentage

free

cod

These are translated only at presentation level.

5. Brand Names

Brand names usually remain a single field:

brand

Examples:

Heinz

Farm Frites

Hellmann's

Do NOT create brand_ar / brand_en by default.

Official commercial brand names remain unchanged unless a future real business requirement proves otherwise.

6. Customer-Entered Data

Do NOT create duplicate Arabic/English fields for customer-entered values such as:

business/restaurant name

contact person name

address

landmark

delivery notes

Store them once as Unicode text.

Customers may naturally enter Arabic, English, or mixed content.

Changing UI language later must not change or duplicate user-entered content.

7. Order Snapshots

Historical orders must snapshot the DISPLAYED commercial content used when the order was placed.

For MVP Arabic orders, snapshot Arabic values such as:

product name

selling unit name

delivery area name where required

Do not depend on live _ar / _en fields when rendering historical orders.

Future English orders may snapshot the localized display text used at placement time.

Order history must remain immutable.

8. Database Constraints

For MVP:

Arabic managed-content fields should be required where the content itself is required.

English fields may be nullable OR auto-populated with the Arabic value.

Choose the simpler strategy and document it consistently.

Preferred MVP approach:

required Arabic field

nullable English field at database level

admin/service layer may initially copy Arabic into English when English is not supplied

This prevents false assumptions that the English field contains a real translation.

If you choose instead to persist Arabic into both fields, clearly document that _en may contain untranslated Arabic until localization work begins.

Do not invent a translation-status workflow for MVP.

9. Admin Behavior

Admin remains Arabic-only in MVP.

When managing records:

Arabic input is required.

English input may either be hidden in MVP or shown as optional, depending on the simplest Filament UX.

Do not require the admin to enter English.

Preferred MVP UX:

expose Arabic fields prominently

keep English fields optional and collapsible/secondary if shown at all

No bilingual workflow complexity.

10. Search

MVP search should search:

Arabic product/category names

brand

naturally English commercial content where it exists

If _en currently contains Arabic duplicates, do not rely on it for meaningful English search yet.

When real English content is added later, extend search to _en fields without replacing the existing Arabic search.

Do not add external search services.

11. Slugs / URLs

Do not make business identity depend on localized names.

If slugs are used, choose a stable strategy.

Do not require separate Arabic/English route structures in MVP.

Localized URLs remain future scope.

12. Schema Scope Guard

Do NOT add _ar / _en to every textual column automatically.

Use bilingual columns only where content is genuinely translatable managed business content.

Avoid duplication for:

technical identifiers

customer-entered content

brand names

order-number data

commercial numeric data

Keep the schema understandable and maintainable.

13. Update Planning Artifacts

Update as appropriate:

specs/001-restaurant-supplies-mvp/plan.md

specs/001-restaurant-supplies-mvp/research.md

specs/001-restaurant-supplies-mvp/data-model.md

relevant contracts

ADR/decision log

quickstart.md only if needed

Record this as the authoritative localization-content schema decision.

14. Final Validation

Confirm:

Arabic remains the only exposed MVP language.

No language switcher is added.

Selected managed content uses _ar / _en readiness.

Arabic is required for MVP content.

English does not block record creation.

_en may temporarily contain Arabic or be nullable according to the documented chosen strategy.

Domain identifiers remain language-neutral.

Brand remains a single natural commercial value.

Customer-entered content is not duplicated by locale.

Order snapshots remain immutable localized snapshots.

Business logic does not depend on translated strings.

Future English can be introduced without rewriting commerce logic.

No unnecessary translation engine or translation workflow is added.

No application code is written.

Constitution Check remains PASS.

Return a concise PM-ready summary and state whether the technical plan remains ready for approval.

Do NOT run /speckit-tasks.
Do NOT run /speckit-analyze.
Do NOT implement application code.