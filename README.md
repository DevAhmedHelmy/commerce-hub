# Restaurant Supplies PWA

Mobile-first, Arabic-first **PWA** + **Filament** admin for a single-branch, delivery-only,
cash-on-delivery B2B restaurant-supplies business. Built as one modular Laravel monolith.

## Repository layout

```text
restaurant-supplies-pwa/
├── .specify/        # Spec Kit governance, templates, scripts, extensions
├── specs/           # Feature spec, plan, research, data-model, contracts, tasks, design
├── prompts/         # Prompt history (numbered)
├── src/             # ← the Laravel 12 application lives here
├── CLAUDE.md        # Agent/project instructions
└── README.md        # This file
```

> **The Laravel application root is `src/`.** Run all `composer`, `php artisan`, and `npm`
> commands from inside `src/`. Spec/design/documentation stay at the repository root, outside `src/`.

## Stack (verified)

PHP **8.2** (max) · Laravel **12.x** · MySQL **8** · Blade + Alpine.js + Tailwind CSS ·
Filament **v5** admin · PWA. Money is stored as integer minor units (EGP). Arabic is the default
and fallback locale (no language switcher in MVP). No Redis / WebSockets / Docker required.

## Local development

```bash
cd src
composer install                     # platform pinned to PHP 8.2 (config.platform.php)
cp .env.example .env                  # then set DB credentials
php artisan key:generate
php artisan migrate                   # requires a MySQL 8 database named in .env
php artisan storage:link
npm install && npm run build          # Tailwind + Alpine assets
php artisan serve                     # http://127.0.0.1:8000  (admin panel at /admin)
```

Run the test suite (Pest) and verify PHP 8.2 dependency safety:

```bash
cd src
php artisan test
composer check-platform-reqs
```

## Documentation

The authoritative planning artifacts live under `specs/001-restaurant-supplies-mvp/`:
`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/`, `quickstart.md`, `tasks.md`,
and `design/`. Governance: `.specify/memory/constitution.md`.
