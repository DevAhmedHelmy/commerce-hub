Claude Prompt — Add Product Image as Core MVP Requirement

Prompt Number

34

Phase

Catalog / Product Media Amendment

Purpose

Product image support is a CORE MVP REQUIREMENT.

Add proper product-image management to the admin dashboard and customer/public UI.

This prompt applies whether Phase D is not started yet or is partially/fully implemented.

Laravel application root:

src/

Read First

Read:

.claude/skills/restaurant-ui/SKILL.md

.specify/memory/constitution.md

specs/001-restaurant-supplies-mvp/spec.md

specs/001-restaurant-supplies-mvp/plan.md

specs/001-restaurant-supplies-mvp/data-model.md

specs/001-restaurant-supplies-mvp/contracts/

specs/001-restaurant-supplies-mvp/tasks.md

all prompt history under prompts/ in numeric order

Later prompts supersede earlier decisions.

1. MVP Product Image Model

For MVP, each product has:

one primary product image

Do NOT create a complex gallery/media-library unless already justified by approved requirements.

Future multiple-image support may be added later without changing product/business logic.

2. Data Model

Add/confirm a product image field on products.

Preferred simple approach:

image_path nullable string

Store only the relative file path in the database.

Do NOT store:

base64 images

binary image blobs in MySQL

public full URLs in the database

Use Laravel Storage abstraction.

3. Storage

Initial MVP storage:

Laravel local/public disk.

Expected behavior:

upload through admin

store under a predictable path such as:
products/

use storage:link

render through Laravel/public storage URL

Architecture must remain compatible with future object storage such as S3 without rewriting product logic.

Do NOT require S3 now.

4. Admin Dashboard

In Filament Product management, add image upload support.

Admin must be able to:

upload product image

preview current image

replace image

remove image

see placeholder if none exists

Use clear Arabic labels.

Example label:

صورة المنتج

Image input must remain responsive in admin.

Do not make the Product form unusably large.

5. Upload Validation

Validate uploads.

At minimum:

image file only

safe MIME types

reasonable maximum file size

reject malformed/non-image files

Recommended accepted formats:

JPEG

PNG

WebP

If AVIF is supported cleanly by the current environment/tooling, it may be accepted, but do not make it mandatory.

Do not allow arbitrary executable/file uploads.

6. Image Optimization

Avoid storing huge original images without control.

Implement the simplest safe MVP approach.

At minimum:

sensible maximum dimensions/file size policy

browser-friendly format support

avoid unnecessary 10–20 MB uploads

If the current stack already has safe image processing support, use it.

Do NOT add a heavy image-processing dependency unless clearly justified.

If optimization is deferred, enforce strict upload-size limits.

7. Replacement / Deletion Behavior

When product image is replaced:

update the database path

remove the old managed image when safe

do not delete unrelated/shared files

When product image is removed:

set image path to null

remove the managed file when safe

customer UI must show fallback placeholder

Never delete files outside the expected product-media directory.

8. Customer PWA

Show product image in:

Home featured/product sections

Category/product listings

Search results

Product Details

Cart where approved

Order-related live catalog references only where relevant

Historical order snapshots must NOT depend on the live image to preserve order correctness.

Image is visual-only and must not affect commerce logic.

9. Landing Page

Where products/categories are shown on landing page:

use product image

preserve aspect ratio

use consistent card image containers

use placeholder if missing

Featured products remain:

active-offer products only

Do not create separate landing-page image records for products.

10. Responsive Image Rules

Follow /restaurant-ui.

Images must:

preserve aspect ratio

never stretch/distort

scale responsively

use consistent containers

not overflow cards

work on mobile/tablet/laptop/desktop

remain suitable in PWA standalone mode

Use appropriate Tailwind classes and responsive sizing.

Do not use fixed dimensions that break smaller screens.

11. Loading / Performance

Use:

lazy loading where appropriate

sensible image dimensions

lightweight rendering

Avoid loading full-resolution source images where smaller display sizes are enough, if the current implementation can support this simply.

Do not introduce a full media CDN pipeline in MVP.

12. Accessibility

Each product image needs meaningful alt text.

Preferred:

Arabic product name.

Example:

alt="{{ product localized name }}"

If image is purely decorative in a context, use appropriate empty alt semantics.

Do not omit alt attributes.

13. Dark Mode

Product images themselves should not be altered by dark mode.

Image containers/backgrounds/borders must use semantic theme tokens so they look correct in both:

Light

System Dark

Avoid hard-coded white image-card surfaces.

14. Placeholder

Provide a reusable product-image placeholder when no image is available.

The placeholder should:

match the design system

work in light/dark mode

not break card heights

not show a broken-image icon

Use one reusable component/pattern rather than duplicating placeholder markup.

15. Security

Ensure:

admin authorization required for upload/remove/replace

validated MIME/content

safe filename/path generation

no user-controlled executable path

no path traversal

no secrets in filenames

uploads remain outside source-code directories

Do not trust original filenames as final storage paths.

16. Testing

Add/run relevant tests for:

admin can upload valid product image

invalid file type rejected

oversized file rejected

product image path stored

replacing image updates path

removal sets image to null

product page renders image

missing image renders fallback

unauthorized user cannot modify product media

Use feature tests rather than brittle pixel-level tests.

17. Spec / Plan / Tasks Update

If product image support is not already explicit, update:

spec.md

plan.md

data-model.md

relevant contracts

tasks.md

Do not duplicate tasks if equivalent tasks already exist.

If Phase D is already partially implemented:

add only missing tasks/change set

preserve valid existing work

18. Scope Guard

Do NOT add:

multi-image gallery

video uploads

product 360° images

external DAM

S3 requirement

image AI generation

advanced image editor

customer image uploads

MVP = one primary product image.

19. Final Report

Return:

result

files updated

schema change

storage path strategy

admin upload behavior

validation rules

replacement/deletion behavior

customer PWA rendering

landing-page rendering

responsive result

dark-mode result

tests added

test command/result

Phase D task impact

remaining blockers

git status --short

End with:

PRODUCT IMAGE MVP SUPPORT READY

only if implementation/planning updates are complete and tests pass.