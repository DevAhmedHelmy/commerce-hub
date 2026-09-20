Claude Prompt — Implement Landing Page From emdad-food-responsive-v2

Prompt Number

30

Phase

Landing Page Implementation / Visual Integration

Purpose

There is an existing landing-page design/reference directory at repository root:

emdad-food-responsive-v2/

Use it as the PRIMARY visual reference for the public landing page.

Implement that landing page properly inside the Laravel application using:

Laravel Blade

Tailwind CSS

Alpine.js only if lightweight interaction is actually needed

The final implementation must live under:

src/

Do NOT create a separate frontend application.

Read First

Before implementation, read:

.claude/skills/restaurant-ui/SKILL.md

.specify/memory/constitution.md

specs/001-restaurant-supplies-mvp/spec.md

specs/001-restaurant-supplies-mvp/plan.md

specs/001-restaurant-supplies-mvp/design/

specs/001-restaurant-supplies-mvp/tasks.md

all prompt history under prompts/ in numeric order

Use:

/restaurant-ui

official frontend-design skill if available

The project-specific restaurant-ui skill takes priority for:

colors

theme tokens

RTL

system dark mode

responsiveness

accessibility

approved product/business flows

PWA behavior

1. Inspect the Reference Directory First

Inspect the entire root-level directory:

emdad-food-responsive-v2/

Before changing application code, identify:

HTML files

CSS files

JavaScript files

images/assets

fonts

icons

sections

layout structure

responsive rules

breakpoints

animations/interactions

gradients

typography

desktop/mobile behavior

Do not blindly copy files.

Understand the design first.

Return/record a short implementation mapping internally:

reference component/section
→ Laravel Blade/Tailwind destination

2. Visual Fidelity

The final Laravel landing page should preserve the visual identity and composition of the reference as closely as practical.

Preserve where appropriate:

hero composition

section order

visual hierarchy

card style

image proportions

gradients

spacing rhythm

typography feel

brand treatment

CTA hierarchy

responsive intent

Do NOT replace the reference design with a generic Tailwind/SaaS landing page.

Do NOT redesign it merely because another style is easier.

3. Business / Project Rules Still Override

If the reference conflicts with an approved project requirement, preserve the approved requirement.

Examples:

Arabic-only MVP

RTL-first

no Quick Add from Product Card

featured products = active-offer products only

same Laravel application

no separate SPA

centralized money formatting

approved brand/theme tokens

approved customer navigation/flows

When a visual detail conflicts with business behavior:
keep the visual spirit but implement the approved behavior.

4. Tailwind Is Mandatory

Rebuild the landing page using Tailwind utilities/components.

Do NOT simply copy a large standalone CSS stylesheet from the reference into Laravel.

Allowed CSS:

central theme tokens in src/resources/css/theme.css

small reusable project-level component classes where Tailwind alone would create harmful repetition

unavoidable complex decorative effect definitions

Avoid:

large page-specific CSS dumps

inline style attributes with brand colors

duplicated raw HEX values

Bootstrap or another CSS framework

Prefer Tailwind-first implementation.

5. Central Theme Tokens

All brand colors and reusable visual values must come from:

src/resources/css/theme.css

The reference design must be mapped to the approved Emdad Food palette.

Use the existing brand tokens, including the approved blue/green family.

Do NOT scatter raw reference HEX codes across Blade templates.

If the reference contains a useful additional shade:

map it to an existing token where practical

or add one clearly named token to theme.css

document why it was needed

Do not create arbitrary one-off color values.

6. Gradients

The Emdad Food logo/design uses blue and green gradients.

Preserve appropriate gradients from the reference, but implement them through centralized theme tokens.

Use gradients tastefully for:

Hero

primary CTA

key accents

selected/highlight areas

Avoid gradient overload.

7. System Dark Mode

The landing page MUST work in automatic system dark mode.

Use the project's semantic theme tokens.

Do NOT create a manual theme toggle unless separately approved.

Verify:

@media (prefers-color-scheme: dark)

produces a complete usable landing page.

Fix reference elements that are light-only.

Dark mode must preserve:

readable text

sufficient contrast

visible borders

readable cards

visible CTA states

image/logo clarity

8. Arabic / RTL

Final landing page is Arabic-first and RTL.

Requirements:

lang="ar"

dir="rtl"

Cairo font

correct icon direction

logical spacing

correct section alignment

no manually reversed Arabic

correct mixed Arabic/English brands

If the reference contains LTR assumptions, adapt them correctly.

Do not mirror images/logos unnecessarily.

9. Responsive Requirements

The final landing page must be genuinely responsive.

Verify at minimum:

360 px

390 px

768 px

1024 px

1280 px

1440 px

Do not merely shrink desktop.

For each breakpoint, inspect:

header/navigation

hero

CTA positions

cards

category grid

featured-products grid

images

typography

section spacing

footer

any decorative elements

No horizontal page overflow.

10. Header / Navigation

Implement the reference header using the approved project behavior.

Requirements:

responsive

RTL

mobile menu if needed

keyboard accessible

no hover-only required navigation

appropriate touch targets

clear CTA

Use Alpine.js only if needed for mobile menu state.

Do not introduce a heavy JS dependency.

11. Hero

Preserve the visual intent of the reference hero.

Ensure:

strong Emdad Food branding

correct Arabic hierarchy

responsive text/image layout

CTA visible on mobile

CTA uses centralized brand gradient/token

no content clipped on smaller screens

no huge fixed heights causing keyboard/viewport issues

12. Categories

If the reference includes category cards:

integrate with real catalog/category data where the current implementation architecture supports it

otherwise preserve a clean data-driven Blade structure ready for real data

Use approved Arabic category names.

Cards must be responsive and accessible.

13. Featured Products

Featured products for MVP are:

currently active-offer products only

Do NOT implement popularity/best-seller ranking.

Product cards:

no Quick Add

link to Product Details

preserve approved pricing display

use centralized money formatter

show active offer styling where applicable

remain responsive

14. Benefits / Ordering Steps / Coverage / CTA

Implement the reference design for sections such as:

Benefits

How ordering works

Delivery coverage

CTA

Contact/footer

Keep copy Arabic-first.

Where project data/settings exist, use real application data rather than hard-coded duplicate business settings.

Do not add a CMS.

15. Assets

Review assets under:

emdad-food-responsive-v2/

Use only assets that are actually needed.

For reused assets:

move/copy them into appropriate Laravel public/storage/resource locations

use clear filenames

optimize where reasonable

preserve aspect ratio

add alt text

avoid oversized files

Do NOT copy:

unused files

temp files

source junk

duplicate assets

build caches

If the logo is already available elsewhere in the project, avoid creating multiple conflicting copies.

16. JavaScript / Interaction Migration

Inspect reference JavaScript.

For each interaction:

keep it only if it adds real value

migrate lightweight behavior to Alpine.js where appropriate

remove unnecessary animation libraries or dependencies

do not introduce jQuery

do not add a frontend framework

Business logic must never move into JS.

17. Animation

Preserve tasteful reference motion where useful.

Requirements:

subtle

functional

performant

reduced-motion friendly

not required to understand content

Avoid heavy entrance animations for every element.

18. Accessibility Fixes

Fix any accessibility issues found in the reference.

At minimum:

semantic headings

accessible nav

labels where needed

keyboard navigation

visible focus

button/link semantics

alt text

contrast

touch target size

no color-only communication

Do not reproduce accessibility mistakes from the source design.

19. Performance Fixes

Optimize obvious performance issues from the reference.

Check:

oversized images

duplicate CSS

unused JS

unnecessary libraries

render-blocking assets

image lazy-loading

responsive image sizing where practical

unnecessary DOM duplication

Keep implementation lightweight.

20. PWA / Standalone Compatibility

The landing page must work correctly:

in normal browser mode

in installed PWA standalone mode

on mobile

on tablet

on laptop/desktop

Respect safe areas where needed.

Do not let the Service Worker cache sensitive authenticated data.

Landing/public static assets may follow approved safe caching strategy.

21. File Architecture

Prefer reusable Blade components/partials for repeated sections when useful.

Possible examples:

src/resources/views/components/...

src/resources/views/landing/...

src/resources/views/pages/...

Follow the existing project structure rather than inventing a parallel architecture.

Do not create components for trivial one-off fragments purely for abstraction.

22. Do Not Break Existing Work

Before modifying files:

inspect current implementation

inspect git status --short

preserve unrelated changes

do not overwrite already-correct shared components blindly

If current shared components can support the reference design:
reuse/refine them.

23. Frontend Quality Gate

Before marking this implementation complete:

Build

Run the frontend production build.

It must pass.

Laravel/tests

Run relevant feature/render tests.

Responsive QA

Check:

390×844

768×1024

1024×768

1440×900

Themes

Check:

system light

system dark

RTL

Check:

Arabic text

icon direction

mixed Arabic/English brand names

numbers/units

Visual issues

Fix:

overflow

clipped text

broken grids

wrong alignment

stretched images

invisible CTA

bad contrast

broken mobile menu

hover-only behavior

unsafe fixed elements

inconsistent section spacing

Use Playwright/browser tooling if available.

24. Reference Comparison

Before finishing, compare the Laravel implementation against:

emdad-food-responsive-v2/

Report:

what was preserved exactly

what was adapted for RTL/responsiveness

what was changed for accessibility

what was changed for dark mode

what was changed for project/business requirements

what reference code/assets were intentionally not reused

25. Task / Phase Integration

If the Landing Page Phase L task(s) are still unchecked in:

specs/001-restaurant-supplies-mvp/tasks.md

mark them complete ONLY if this implementation fully satisfies those task acceptance criteria.

Do not mark unrelated tasks.

If Phase L was previously implemented:
treat this as a refinement/replacement of the landing-page visual implementation and rerun relevant regression tests.

26. Git

Do NOT automatically commit unless explicitly running under the approved autonomous master-runner workflow.

If running manually:
stop with a clean report before commit.

Required Final Report

Return:

result: PASS / PASS WITH ISSUES / BLOCKED

reference files inspected

Laravel files created/modified

Blade components created/reused

Tailwind implementation summary

theme/token changes

reference assets reused

reference assets rejected and why

RTL result

responsive result by viewport

light mode result

dark mode result

accessibility fixes

performance fixes

PWA/standalone compatibility result

frontend build command/result

Laravel/test command/result

Phase L task status

remaining visual differences from reference

git status --short

End with:

LANDING PAGE REFERENCE IMPLEMENTATION READY FOR PM REVIEW

only if the implementation/build/tests/responsive checks pass.
