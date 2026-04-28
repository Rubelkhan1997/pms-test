# Codeware PMS — Master Plan Index

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement each plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a complete multi-tenant SaaS Hotel Property Management System on Laravel 13 + Inertia v2 + Vue 3 + TypeScript.

**Architecture:** DB-per-tenant via `spatie/laravel-multitenancy`. Domain-based tenant resolution (`hotel.pms.test`). Super Admin on a separate landlord domain (`admin.pms.test`). Each plan below is independently executable and produces working, testable software.

**Tech Stack:** Laravel 13, Inertia.js v2, Vue 3 + TypeScript, Pinia, Tailwind CSS v4, MySQL 8, Redis, Laravel Horizon, Laravel Reverb, Sanctum, Spatie Permission, Spatie Multitenancy, Spatie Laravel Data, Pest PHP, Vitest.

---

## Dependency Graph

```
Phase 00 (Tenancy Infrastructure)
  └── Phase 01 (Super Admin Panel)
        └── Phase 02 (Onboarding Wizard)
              └── Phase 03 (Front Desk & Reservation Engine)
                    ├── Phase 04 (Guest CRM)
                    ├── Phase 05 (Cashiering & Folio)
                    │     └── Phase 08 (POS)
                    ├── Phase 06 (Housekeeping & Maintenance)
                    │     └── Phase 09 (Night Audit)
                    └── Phase 07 (Rate & Availability + Channel Manager)

Phase 10 (Reports)   — depends on Phases 03, 05, 06
Phase 11 (Settings)  — can be built in parallel with Phase 03+
```

> **Rule:** Never start a phase until all phases it depends on are complete and all tests pass.

---

## Plan Files

| # | Phase | Plan File | Status | Depends On |
|---|---|---|---|---|
| 00 | Tenancy Infrastructure | [2026-04-27-01-tenancy-infrastructure.md](./2026-04-27-01-tenancy-infrastructure.md) | **Ready** | — |
| 01 | Super Admin Panel | [2026-04-27-02-super-admin-panel.md](./2026-04-27-02-super-admin-panel.md) | **Ready** | 00 |
| 02 | Tenant Onboarding Wizard | [2026-04-27-03-onboarding-wizard.md](./2026-04-27-03-onboarding-wizard.md) | **Ready** | 01 |
| 03 | Front Desk & Reservation Engine | [2026-04-27-04-reservation-engine.md](./2026-04-27-04-reservation-engine.md) | Pending | 02 |
| 04 | Guest CRM | [2026-04-27-05-guest-crm.md](./2026-04-27-05-guest-crm.md) | Pending | 03 |
| 05 | Cashiering & Folio | [2026-04-27-06-cashiering-folio.md](./2026-04-27-06-cashiering-folio.md) | Pending | 03 |
| 06 | Housekeeping & Maintenance | [2026-04-27-07-housekeeping-maintenance.md](./2026-04-27-07-housekeeping-maintenance.md) | Pending | 03 |
| 07 | Rate & Availability + Channel Manager | [2026-04-27-08-rate-availability-channel.md](./2026-04-27-08-rate-availability-channel.md) | Pending | 02 |
| 08 | POS | [2026-04-27-09-pos.md](./2026-04-27-09-pos.md) | Pending | 05 |
| 09 | Night Audit | [2026-04-27-10-night-audit.md](./2026-04-27-10-night-audit.md) | Pending | 03, 05, 06 |
| 10 | Reports | [2026-04-27-11-reports.md](./2026-04-27-11-reports.md) | Pending | 03, 05, 06 |
| 11 | Settings (Tenant-Level) | [2026-04-27-12-settings.md](./2026-04-27-12-settings.md) | Pending | 02 |

---

## Global Conventions (Apply to Every Plan)

### PHP / Laravel
- `declare(strict_types=1)` at the top of every PHP file
- PHP 8.3: `readonly class`, `readonly` properties, native `enum`, `match`
- Services: `readonly class`, constructor-injected — no Eloquent, no logic in controllers
- All validation in `FormRequest`. All JSON output via `ApiResource`.
- Observers → registered in `AppServiceProvider::boot()`
- Enums → `app/Enums/` only, never inside module folders
- `declare(strict_types=1)` + PSR-12 enforced via `./vendor/bin/pint`

### API Response Format
```json
{ "status": 1, "data": { ... }, "message": "Human-readable message" }
```
`status`: `1` = success, `0` = failure. No exceptions.

### Vue / TypeScript
- All Vue: `<script setup lang="ts">`
- All strings: `useI18n()` `t()` — zero hardcoded UI strings
- All protected pages: `usePermission()` check at mount
- Forms: `useForm()` + `validateInertiaForm()` — never `reactive()`
- No Ziggy. Navigation: `router.visit('/path')`. Submit: `router.post/put/delete`

### Module File Pattern
Every CRUD feature requires these files. Match the existing pattern exactly:

| Layer | Path Pattern |
|---|---|
| API Controller | `app/Modules/{Module}/Controllers/Api/V1/{Model}Controller.php` |
| Web Controller | `app/Modules/{Module}/Controllers/Web/{Model}Controller.php` |
| Service | `app/Modules/{Module}/Services/{Model}Service.php` |
| Action | `app/Modules/{Module}/Actions/Create{Model}Action.php` |
| DTO | `app/Modules/{Module}/Data/{Model}Data.php` |
| Form Request | `app/Modules/{Module}/Requests/Store{Model}Request.php` |
| API Resource | `app/Modules/{Module}/Resources/{Model}Resource.php` |
| Store | `resources/js/Stores/{Module}/{model}Store.ts` |
| Composable | `resources/js/Composables/{Module}/use{Models}.ts` |
| Page | `resources/js/Pages/{Module}/{Feature}/Index.vue` |
| Types | `resources/js/Types/{Module}/{model}.ts` |
| Mapper | `resources/js/Utils/Mappers/{module}.ts` |

### Test Commands
```bash
composer run test                        # all tests
php artisan test tests/Feature/Foo.php  # single file
php artisan test --filter=FooTest       # by class
./vendor/bin/pest --coverage            # with coverage
```

---

## Architecture Reference

### Tenant DB Switching Sequence
```
HTTP Request (hotel.pms.test)
  → TenantFinder::findForRequest()        reads domain → queries landlord DB
    → SwitchTenantDatabaseTask::makeCurrent()  switches mysql connection
      → NeedsTenant middleware             aborts 404 if no tenant
        → EnsureSubscriptionActive        403 if suspended
          → EnsurePropertyOnboarded       redirect /onboarding if 0 properties
            → Route handler              all Eloquent queries now hit tenant DB
```

### Key Config Values
```php
// config/multitenancy.php
'tenant_finder'       => App\Tenancy\TenantFinder::class,
'tenant_model'        => App\Models\Tenant::class,
'landlord_database_connection_name' => 'landlord',
'switch_tenant_tasks' => [SwitchTenantDatabaseTask::class],

// config/database.php
'landlord' => [
    'driver'   => 'mysql',
    'database' => env('LANDLORD_DB_DATABASE', 'platform_db'),
    ...
],
```

### Reverb Channel Naming
```
tenant.{tenant_id}.tape-chart       room status updates
tenant.{tenant_id}.housekeeping     HK status updates
tenant.{tenant_id}.notifications    alerts & toasts
```
