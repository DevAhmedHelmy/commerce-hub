**Before starting any task, read `PROJECT_CONTEXT.md` (repo root)** for the latest project
decisions, architecture, implementation status, UAT state, Git workflow, and superseded
requirements. Then verify current Git state (branch/status) rather than assuming prior reports.

<!-- SPECKIT START -->
For additional context about technologies to be used, project structure, shell commands, and
other important information, read the active technical plan and its Phase 0/1 artifacts:

- Plan: `specs/001-restaurant-supplies-mvp/plan.md`
- Research/decisions (ADR): `specs/001-restaurant-supplies-mvp/research.md`
- Data model: `specs/001-restaurant-supplies-mvp/data-model.md`
- Service + HTTP/Admin contracts: `specs/001-restaurant-supplies-mvp/contracts/`
- Quickstart / deployment: `specs/001-restaurant-supplies-mvp/quickstart.md`
- Product spec: `specs/001-restaurant-supplies-mvp/spec.md`
- Approved UX/UI design: `specs/001-restaurant-supplies-mvp/design/`
- Constitution (governance): `.specify/memory/constitution.md`

Stack (verified): PHP 8.2 (max), Laravel 12.x, MySQL 8, Blade + Alpine.js + Tailwind, Filament v5.x
admin, spatie/laravel-permission ^6, PWA. Modular monolith; Arabic-first RTL, localization-ready;
money as integer minor units.
<!-- SPECKIT END -->
