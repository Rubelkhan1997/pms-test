# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Environment

**PHP binary:** `C:\Users\User\.config\herd\bin\php85\php.exe`
Always use this path when running `php artisan` or any PHP CLI command (e.g. `& "C:\Users\User\.config\herd\bin\php85\php.exe" artisan migrate`).

## Commands

```bash
# Full setup (first time)
composer run setup

# Development (runs Laravel server + queue + Vite in parallel)
composer run dev

# Run all tests
composer run test

# Run a single test file
php artisan test tests/Feature/SomeTest.php

# Run tests by filter
php artisan test --filter=SomeTestClass

# Lint (Laravel Pint)
./vendor/bin/pint

# Frontend type-check
npx vue-tsc --noEmit
```

Tests use SQLite in-memory (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`), so migrations run fresh per test suite without a real DB.

## Architecture

This is a **Hotel PMS** built on **Laravel 12 + Inertia.js v2 + Vue 3 + TypeScript + Pinia**.

### Backend Module System

All feature code lives under `app/Modules/<ModuleName>/` — never in the top-level `app/` folders. Each module mirrors this structure:

```
app/Modules/<ModuleName>/
  Actions/          # Single-responsibility action classes (called by Services)
  Controllers/
    Api/V1/         # JSON API controllers (return { status, data, message })
    Web/            # Inertia page controllers (return Inertia::render(...))
  Data/             # Spatie Laravel Data DTOs
  Events/ Listeners/ Jobs/ Notifications/ Observers/ Policies/
  Models/
  Requests/         # Form Request classes (all validation lives here)
  Resources/        # API Resources (transform model → JSON)
  Services/         # Business logic; controllers call services only
```

Current modules: `FrontDesk`, `Guest`. Enums live in `app/Enums/` (shared, not per-module).

### API Response Format

All API controllers must return:
```json
{ "status": 1, "data": { ... }, "message": "..." }
```
`status` is `1` for success, `0` for failure.

### Routing Pattern

- **Web routes** (`routes/web.php`): Inertia page rendering only (GET). Protected by `auth.token` middleware + Spatie `permission:` middleware per route.
- **API routes** (`routes/api.php`): Versioned under `/api/v1`, protected by `auth:sanctum`. Route prefix per module (e.g. `front-desk`).
- **Never use Ziggy** (`route()` helper or `import { route } from 'ziggy-js'`) in frontend code. Use hardcoded URL strings or `router.visit('/path')`.

### Auth

Authentication is token-based via Sanctum. The token is stored in a cookie (`auth_token`). `HandleInertiaRequests` middleware reads it and shares `auth.user` as Inertia shared data. The custom `auth.token` middleware guards web routes.

### Frontend Architecture

```
resources/js/
  app.ts             # Entry — Inertia app init, plugin + global component registration
  Pages/             # Inertia pages, organized by module
  Components/        # Shared UI (Table.vue, Modal.vue, Form/) + module-specific
  Composables/       # Vue reactive logic (useX.ts)
  Helpers/           # Pure JS/TS functions (non-reactive)
  Layouts/           # AppLayout, HotelLayout, MobileLayout
  Plugins/           # Vue plugins/directives (toast, confirm, directives)
  Services/          # apiClient.ts (Axios wrapper)
  Stores/            # Pinia stores, organized by module
  Types/             # TypeScript types per module + api.ts, common.ts
  Utils/             # Formatting, validation, storage; Mappers/ sub-directory
```

**Default layout**: every Inertia page gets `AppLayout` automatically unless it sets `page.layout` explicitly (see `app.ts` resolver).

**Global components**: `Head`, `Link` (Inertia), and all exports from `Components/index.ts` are registered globally — no need to import them in pages.

### Data Flow (Frontend)

Page → Composable (`useX.ts`) → Pinia Store → `apiClient.ts` → API  
Mappers in `Utils/Mappers/<module>.ts` translate between API snake_case shapes and frontend camelCase types.

### Key Reference Files

When adding a new feature, mirror these existing files:

| Layer | Reference File |
|---|---|
| API Controller | `app/Modules/FrontDesk/Controllers/Api/V1/ReservationController.php` |
| Web Controller | `app/Modules/FrontDesk/Controllers/Web/ReservationController.php` |
| Service | `app/Modules/FrontDesk/Services/ReservationService.php` |
| Action | `app/Modules/FrontDesk/Actions/CreateReservationAction.php` |
| DTO | `app/Modules/FrontDesk/Data/ReservationData.php` |
| Store | `resources/js/Stores/FrontDesk/reservationStore.ts` |
| Composable | `resources/js/Composables/FrontDesk/useReservations.ts` |
| Page | `resources/js/Pages/FrontDesk/Reservation/Index.vue` |
| Types | `resources/js/Types/FrontDesk/reservation.ts` |
| Mapper | `resources/js/Utils/Mappers/reservation.ts` |

### Templates

Scaffold templates for new modules live in `templates/backend/` and `templates/frontend/`. Use these as a starting point when creating new module files.

## Coding Rules

- PHP: `declare(strict_types=1)` at top of every file. Use PHP 8.3 features (readonly classes/properties, enums, match).
- Services are `readonly class` injected via constructor. Controllers must not contain business logic.
- Observers registered in `AppServiceProvider::boot()`. Events/listeners registered in `EventServiceProvider`.
- Vue files: always `<script setup lang="ts">`. PascalCase component names, camelCase variables/functions.
- Pinia stores: separate `loading`, `loadingList`, `loadingDetail` states for granular UI feedback.
- Always add DB indexes for foreign keys and status columns in migrations.
- Spatie Permission used for all authorization — check with `permission:` middleware on routes, not inline in controllers.
