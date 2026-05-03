# Tenant Migrations — Complete PMS Schema Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace all tenant domain migrations with a clean, complete PMS schema anchored on `properties`.

**Architecture:** Delete 3 stale files, fix `users` table style, write 39 new domain migration files in dependency-safe timestamp order (`2026_04_27_000100`–`003900`). All tables use `property_id`. See spec: `docs/superpowers/specs/2026-05-03-tenant-migrations-design.md`.

**Tech Stack:** Laravel 12, PHP 8.3, SQLite (tests), MySQL (prod)

**Verify command (after each task):** `php artisan migrate:fresh --path=database/migrations/tenant`

---

### Task 1: Cleanup — delete stale files & fix users table

**Files:**
- Delete: `database/migrations/tenant/2026_03_05_120000_create_hotels_table.php`
- Delete: `database/migrations/tenant/2026_03_05_120100_create_rooms_table.php`
- Delete: `database/migrations/tenant/2026_03_29_120704_add_role_to_users_table.php`
- Modify: `database/migrations/tenant/0001_01_01_000000_create_users_table.php`

- [ ] Delete the 3 stale files
- [ ] Add `declare(strict_types=1);` and typed closures to `create_users_table.php`
- [ ] Run `php artisan migrate:fresh --path=database/migrations/tenant` — expect no errors
- [ ] Commit: `chore: remove stale hotel/rooms migrations, fix users table style`

---

### Task 2: Foundation — currencies, properties, room_types, rooms, meal_plans

**Files to create:**
- `database/migrations/tenant/2026_04_27_000100_create_currencies_table.php`
- `database/migrations/tenant/2026_04_27_000200_create_properties_table.php`
- `database/migrations/tenant/2026_04_27_000300_create_room_types_table.php`
- `database/migrations/tenant/2026_04_27_000400_create_rooms_table.php`
- `database/migrations/tenant/2026_04_27_000500_create_meal_plans_table.php`
- Delete: old `2026_04_27_100*` versions of properties/room_types/rooms

- [ ] Write all 5 migration files (see spec for columns)
- [ ] Delete the old `2026_04_27_100000`, `100100`, `100200` files
- [ ] Run migrate — expect no errors
- [ ] Commit: `feat: add foundation migrations — currencies, properties, room types, rooms, meal plans`

---

### Task 3: Pricing — cancellation_policies, charge_codes, pricing_profiles, rate_plans, rate_restrictions

**Files to create:**
- `database/migrations/tenant/2026_04_27_000600_create_cancellation_policies_table.php`
- `database/migrations/tenant/2026_04_27_000700_create_charge_codes_table.php`
- `database/migrations/tenant/2026_04_27_000800_create_pricing_profiles_table.php`
- `database/migrations/tenant/2026_04_27_000900_create_rate_plans_table.php`
- `database/migrations/tenant/2026_04_27_001000_create_rate_restrictions_table.php`
- Delete: old `2026_04_27_100300`, `100400`, `100500`, `100600` files

- [ ] Write all 5 migration files — fix `no_show_charge_percent` (decimal not boolean), fix `closed` (boolean not smallint), add `closed_to_arrival`/`closed_to_departure`, add `meal_plan_id` + `valid_from`/`valid_to` to rate_plans
- [ ] Delete old `2026_04_27_100*` pricing files
- [ ] Run migrate — expect no errors
- [ ] Commit: `feat: add pricing migrations — cancellation policies, charge codes, rate plans`

---

### Task 4: Guests — companies, agents, guests, guest_documents

**Files to create:**
- `database/migrations/tenant/2026_04_27_001100_create_companies_table.php`
- `database/migrations/tenant/2026_04_27_001200_create_agents_table.php`
- `database/migrations/tenant/2026_04_27_001300_create_guests_table.php`
- `database/migrations/tenant/2026_04_27_001400_create_guest_documents_table.php`
- Delete: old `2026_03_05_120400_create_agents_and_guest_profiles_table.php`

- [ ] Write all 4 migration files (agents/guests split into separate files, guests renamed from guest_profiles)
- [ ] Delete old combined agents+guest_profiles file
- [ ] Run migrate — expect no errors
- [ ] Commit: `feat: add guest migrations — companies, agents, guests, guest documents`

---

### Task 5: Reservations — reservations, reservation_rooms, reservation_guests

**Files to create:**
- `database/migrations/tenant/2026_04_27_001500_create_reservations_table.php`
- `database/migrations/tenant/2026_04_27_001600_create_reservation_rooms_table.php`
- `database/migrations/tenant/2026_04_27_001700_create_reservation_guests_table.php`
- Delete: old `2026_03_05_120450_create_reservations_table.php`

- [ ] Write all 3 migration files
- [ ] Delete old reservations file
- [ ] Run migrate — expect no errors
- [ ] Commit: `feat: add reservation migrations — reservations, rooms, guests`

---

### Task 6: Billing & POS — folios, pos_*, folio_transactions, payments

**Files to create (order matters for FKs):**
- `database/migrations/tenant/2026_04_27_001800_create_folios_table.php`
- `database/migrations/tenant/2026_04_27_001900_create_pos_outlets_table.php`
- `database/migrations/tenant/2026_04_27_002000_create_pos_menu_categories_table.php`
- `database/migrations/tenant/2026_04_27_002100_create_pos_menu_items_table.php`
- `database/migrations/tenant/2026_04_27_002200_create_pos_orders_table.php`
- `database/migrations/tenant/2026_04_27_002300_create_pos_order_items_table.php`
- `database/migrations/tenant/2026_04_27_002400_create_folio_transactions_table.php`
- `database/migrations/tenant/2026_04_27_002500_create_payments_table.php`
- Delete: old `2026_03_05_120600_create_pos_tables.php`

- [ ] Write all 8 files in order (pos_orders → folio_transactions dependency resolved by ordering)
- [ ] Delete old POS file
- [ ] Run migrate — expect no errors
- [ ] Commit: `feat: add billing and POS migrations`

---

### Task 7: Channels & OTA — channels, channel_mappings, ota_syncs

**Files to create:**
- `database/migrations/tenant/2026_04_27_002600_create_channels_table.php`
- `database/migrations/tenant/2026_04_27_002700_create_channel_mappings_table.php`
- `database/migrations/tenant/2026_04_27_002800_create_ota_syncs_table.php`
- Delete: old `2026_03_05_120300_create_ota_syncs_table.php`

- [ ] Write all 3 files
- [ ] Delete old OTA file
- [ ] Run migrate — expect no errors
- [ ] Commit: `feat: add channel and OTA sync migrations`

---

### Task 8: Operations — housekeeping_tasks, maintenance_requests

**Files to create:**
- `database/migrations/tenant/2026_04_27_002900_create_housekeeping_tasks_table.php`
- `database/migrations/tenant/2026_04_27_003000_create_maintenance_requests_table.php`
- Delete: old `2026_03_05_120500_create_housekeeping_and_maintenance_tables.php`

- [ ] Write both files — add `assigned_to`, `type`, `priority`, `started_at`/`completed_at`/`inspected_at` to housekeeping; add `reference`, `category`, `priority`, `assigned_to`, `estimated_cost`/`actual_cost` to maintenance
- [ ] Delete old combined file
- [ ] Run migrate — expect no errors
- [ ] Commit: `feat: add operations migrations — housekeeping, maintenance`

---

### Task 9: HR — departments, employees, attendances, payrolls, shift_schedules

**Files to create:**
- `database/migrations/tenant/2026_04_27_003100_create_departments_table.php`
- `database/migrations/tenant/2026_04_27_003200_create_employees_table.php`
- `database/migrations/tenant/2026_04_27_003300_create_attendances_table.php`
- `database/migrations/tenant/2026_04_27_003400_create_payrolls_table.php`
- `database/migrations/tenant/2026_04_27_003500_create_shift_schedules_table.php`
- Delete: old `2026_03_05_120900_create_hr_tables.php`

- [ ] Write all 5 files — employees replaces JSON meta with real columns (first_name, last_name, job_title, etc.)
- [ ] Delete old HR file
- [ ] Run migrate — expect no errors
- [ ] Commit: `feat: add HR migrations — departments, employees, attendance, payroll, shifts`

---

### Task 10: System — report_snapshots, mobile_tasks, audit_logs, property_settings

**Files to create:**
- `database/migrations/tenant/2026_04_27_003600_create_report_snapshots_table.php`
- `database/migrations/tenant/2026_04_27_003700_create_mobile_tasks_table.php`
- `database/migrations/tenant/2026_04_27_003800_create_audit_logs_table.php`
- `database/migrations/tenant/2026_04_27_003900_create_property_settings_table.php`
- Delete: old `2026_03_05_120700_create_report_snapshots_table.php`, `2026_03_05_120800_create_mobile_tasks_table.php`, `2026_04_02_041901_create_currencies_table.php`

- [ ] Write all 4 files — audit_logs has only `created_at` (immutable), property_settings has unique(property_id, key)
- [ ] Delete old report/mobile/currencies files
- [ ] Run final `php artisan migrate:fresh --path=database/migrations/tenant` — must succeed with all 44 files
- [ ] Commit: `feat: add system migrations — reports, mobile tasks, audit logs, settings`
