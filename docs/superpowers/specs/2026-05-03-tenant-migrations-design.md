# Tenant Migrations — Complete PMS Schema Design

**Date:** 2026-05-03  
**Status:** Approved  
**Scope:** Full clean rewrite of all tenant domain migrations for a professional Hotel PMS

---

## Context

The tenant migrations had two competing schemas (`hotels`-based from March 2026, `properties`-based from April 2026) that could not coexist — the `rooms` table was created twice and all operational tables still referenced the old `hotel_id`. This is a fresh system with no production data, so a full rewrite is the cleanest path.

---

## Approach

**Approach A — Full clean rewrite.**  
Delete all conflicting/outdated domain migrations. Rewrite all domain migrations from scratch with consistent `property_id` references, correct types, complete PMS schema, clean style (`declare(strict_types=1)`, typed closures). All new migrations use sequential `2026_04_27_000*` timestamps.

---

## File Plan

### Keep Untouched (framework)
- `0001_01_01_000000_create_users_table.php` — style fix only (add `declare(strict_types=1)`)
- `0001_01_01_000001_create_cache_table.php`
- `0001_01_01_000002_create_jobs_table.php`
- `2026_03_05_100453_create_permission_tables.php`
- `2026_03_05_100453_create_personal_access_tokens_table.php`

### Delete (3 files)
- `2026_03_05_120000_create_hotels_table.php` — superseded by properties
- `2026_03_05_120100_create_rooms_table.php` — superseded by new rooms
- `2026_03_29_120704_add_role_to_users_table.php` — redundant; Spatie handles roles

### New Domain Migrations (replace all 2026_03_05_12* and 2026_04_* domain files)

| Timestamp | File | Status |
|---|---|---|
| 2026_04_27_000100 | create_currencies_table | rewrite |
| 2026_04_27_000200 | create_properties_table | rewrite + additions |
| 2026_04_27_000300 | create_room_types_table | rewrite + additions |
| 2026_04_27_000400 | create_rooms_table | rewrite + additions |
| 2026_04_27_000500 | create_meal_plans_table | **new** |
| 2026_04_27_000600 | create_cancellation_policies_table | rewrite + bugfix |
| 2026_04_27_000700 | create_charge_codes_table | **new** |
| 2026_04_27_000800 | create_pricing_profiles_table | rewrite |
| 2026_04_27_000900 | create_rate_plans_table | rewrite + additions |
| 2026_04_27_001000 | create_rate_restrictions_table | rewrite + bugfix |
| 2026_04_27_001100 | create_companies_table | **new** |
| 2026_04_27_001200 | create_agents_table | rewrite + additions |
| 2026_04_27_001300 | create_guests_table | rewrite (from guest_profiles) |
| 2026_04_27_001400 | create_guest_documents_table | **new** |
| 2026_04_27_001500 | create_reservations_table | rewrite + major expansion |
| 2026_04_27_001600 | create_reservation_rooms_table | **new** |
| 2026_04_27_001700 | create_reservation_guests_table | **new** |
| 2026_04_27_001800 | create_folios_table | **new** |
| 2026_04_27_001900 | create_pos_outlets_table | **new** |
| 2026_04_27_002000 | create_pos_menu_categories_table | **new** |
| 2026_04_27_002100 | create_pos_menu_items_table | rewrite + additions |
| 2026_04_27_002200 | create_pos_orders_table | rewrite + additions |
| 2026_04_27_002300 | create_pos_order_items_table | **new** |
| 2026_04_27_002400 | create_folio_transactions_table | **new** |
| 2026_04_27_002500 | create_payments_table | **new** |
| 2026_04_27_002600 | create_channels_table | **new** |
| 2026_04_27_002700 | create_channel_mappings_table | **new** |
| 2026_04_27_002800 | create_ota_syncs_table | rewrite |
| 2026_04_27_002900 | create_housekeeping_tasks_table | rewrite + additions |
| 2026_04_27_003000 | create_maintenance_requests_table | rewrite + additions |
| 2026_04_27_003100 | create_departments_table | **new** |
| 2026_04_27_003200 | create_employees_table | rewrite + expand |
| 2026_04_27_003300 | create_attendances_table | rewrite |
| 2026_04_27_003400 | create_payrolls_table | rewrite |
| 2026_04_27_003500 | create_shift_schedules_table | rewrite |
| 2026_04_27_003600 | create_report_snapshots_table | rewrite |
| 2026_04_27_003700 | create_mobile_tasks_table | rewrite + expand |
| 2026_04_27_003800 | create_audit_logs_table | **new** |
| 2026_04_27_003900 | create_property_settings_table | **new** |

---

## Schema Detail

### currencies
- `id`, `code` (unique, 3-char), `name`, `symbol`, `flag`, `conversion_rate decimal(15,6)`, `is_active`, `is_default`
- `created_by` FK users nullable, `updated_by` FK users nullable
- `softDeletes`, `timestamps`

### properties
- `id`, `name`, `slug` (unique), `type` enum(hotel/resort/apartment/villa/hostel)
- `star_rating tinyint` nullable (1–5)
- `description`, `logo_path`, `featured_image_path`, `gallery_paths` json
- `number_of_rooms unsigned int`
- `country char(2)`, `state`, `city`, `area`, `street`, `postal_code`
- `latitude decimal(10,8)`, `longitude decimal(11,8)`
- `phone`, `email`, `website` nullable
- `tax_id` nullable
- `timezone` default UTC, `currency char(3)` default USD
- `check_in_time` default 14:00, `check_out_time` default 12:00
- `child_policy json`, `pet_policy json`
- `status` enum(open/closed) default open
- `business_date date` nullable
- `softDeletes`, `timestamps`

### room_types
- `id`, `property_id` FK, `name`, `code varchar(20)`
- `type` enum(room/suite/cottage/villa/dormitory)
- `description text` nullable
- `floor` nullable, `max_occupancy smallint`, `adult_occupancy smallint`, `child_occupancy smallint`
- `num_bedrooms smallint`, `num_bathrooms smallint`, `area_sqm decimal(8,2)`
- `bed_types json`, `amenities json`, `gallery_paths json`
- `base_rate decimal(10,2)`, `is_active boolean`
- `softDeletes`, `timestamps`
- unique(property_id, code)

### rooms
- `id`, `property_id` FK, `room_type_id` FK, `number varchar(10)`, `floor varchar(10)` nullable
- `status` enum(available/occupied/maintenance/dirty/blocked) default available
- `cleaning_status` enum(clean/dirty/inspecting) default clean
- `sort_order smallint`, `notes text` nullable
- `softDeletes`, `timestamps`
- unique(property_id, number), index(status), index(room_type_id)

### meal_plans
- `id`, `property_id` FK nullable (null = global template)
- `code varchar(10)` (RO/BB/HB/FB/AI/UAI), `name`, `description text` nullable
- `is_active boolean`
- `timestamps`
- unique(property_id, code)

### cancellation_policies
- `id`, `property_id` FK
- `name`, `deadline_days unsigned int`, `deadline_type` enum(days/hours)
- `cancellation_charge_percent decimal(5,2)` default 100
- `no_show_charge_percent decimal(5,2)` default 100  ← **was incorrectly boolean**
- `description text` nullable, `is_default boolean`
- `timestamps`

### charge_codes
- `id`, `property_id` FK nullable (null = system-wide)
- `code varchar(20)` (ROOM/FB/MINIBAR/LAUNDRY/PARKING/SPA/BREAKFAST…)
- `name`, `type` enum(revenue/tax/fee/discount)
- `default_amount decimal(10,2)` nullable, `tax_rate decimal(5,2)` default 0
- `is_active boolean`
- `timestamps`
- unique(property_id, code)

### pricing_profiles
- `id`, `property_id` FK, `name`, `code varchar(20)`
- `target_market` enum(business/leisure/corporate/groups/all)
- `is_active boolean`
- `timestamps`
- unique(property_id, code)

### rate_plans
- `id`, `property_id` FK, `room_type_id` FK, `pricing_profile_id` FK nullable, `cancellation_policy_id` FK nullable, `meal_plan_id` FK nullable
- `name`, `code varchar(20)`, `description text` nullable
- `valid_from date` nullable, `valid_to date` nullable
- `base_rate decimal(10,2)`, `extra_adult_rate decimal(10,2)`, `extra_child_rate decimal(10,2)`
- `weekend_factor decimal(4,2)` default 1.00, `occupancy_factor decimal(4,2)` default 1.00
- `is_dynamic boolean`, `is_direct boolean`, `is_ota boolean`, `is_active boolean`
- `timestamps`
- unique(property_id, room_type_id, code)

### rate_restrictions
- `id`, `property_id` FK, `rate_plan_id` FK, `date date`
- `min_stay smallint` nullable, `max_stay smallint` nullable
- `min_rooms smallint` default 0, `max_rooms smallint` nullable
- `closed boolean` default false  ← **was unsignedSmallInteger**
- `closed_to_arrival boolean` default false  ← **new**
- `closed_to_departure boolean` default false  ← **new**
- `rate_override decimal(10,2)` nullable, `stop_sell_reason text` nullable
- `timestamps`
- unique(rate_plan_id, date)

### companies
- `id`, `property_id` FK, `name`, `email` nullable, `phone` nullable
- `address text` nullable, `tax_id` nullable
- `credit_limit decimal(12,2)` default 0
- `contract_rate_discount decimal(5,2)` default 0
- `is_active boolean`
- `softDeletes`, `timestamps`

### agents
- `id`, `property_id` FK, `company_id` FK nullable
- `name`, `email` nullable, `phone` nullable
- `type` enum(individual/corporate/ota/gds) default individual
- `address text` nullable, `website` nullable
- `commission_rate decimal(5,2)` default 0
- `is_active boolean`
- `softDeletes`, `timestamps`

### guests  (renamed from guest_profiles)
- `id`, `property_id` FK, `agent_id` FK nullable, `company_id` FK nullable, `created_by` FK nullable
- `reference varchar` unique
- `title` enum(mr/ms/mrs/dr/prof) nullable
- `first_name`, `last_name`, `email` nullable, `phone` nullable
- `gender` enum(male/female/other/prefer_not_to_say) nullable
- `date_of_birth date` nullable
- `nationality char(2)` nullable, `language varchar(10)` nullable
- `id_type` enum(passport/national_id/driving_license/other) nullable
- `id_number` nullable, `id_expiry date` nullable
- `vip_level` enum(standard/silver/gold/platinum) default standard
- `company_id` FK nullable
- `notes text` nullable
- `status` enum(active/inactive/blacklisted) default active
- `meta json` nullable
- `softDeletes`, `timestamps`
- index(property_id, email), index(property_id, phone)

### guest_documents
- `id`, `guest_id` FK cascade
- `type` enum(passport/national_id/driving_license/visa/other)
- `document_number`, `issuing_country char(2)` nullable
- `expiry_date date` nullable, `scan_path` nullable
- `timestamps`

### reservations
- `id`, `property_id` FK, `guest_id` FK nullable, `agent_id` FK nullable, `company_id` FK nullable
- `channel_id` FK nullable, `cancellation_policy_id` FK nullable, `created_by` FK nullable
- `reference varchar` unique
- `source` enum(walk_in/phone/email/ota/website/gds/corporate) default walk_in
- `channel_reference` nullable (OTA booking ID)
- `group_name` nullable
- `status` enum(draft/confirmed/checked_in/checked_out/cancelled/no_show) default draft
- `check_in_date date`, `check_out_date date`
- `adults tinyint` default 1, `children tinyint` default 0
- `subtotal decimal(12,2)`, `discount_amount decimal(12,2)` default 0
- `tax_amount decimal(12,2)` default 0, `total_amount decimal(12,2)` default 0
- `paid_amount decimal(12,2)` default 0, `balance decimal(12,2)` default 0
- `special_requests text` nullable
- `arrival_time time` nullable, `departure_time time` nullable
- `confirmed_at timestamp` nullable, `cancelled_at timestamp` nullable
- `cancellation_reason text` nullable, `no_show_at timestamp` nullable
- `meta json` nullable
- `softDeletes`, `timestamps`
- index(property_id, status), index(property_id, check_in_date, check_out_date)

### reservation_rooms
- `id`, `reservation_id` FK cascade, `property_id` FK, `room_type_id` FK, `room_id` FK nullable
- `rate_plan_id` FK nullable, `meal_plan_id` FK nullable
- `check_in_date date`, `check_out_date date`, `nights smallint`
- `adults tinyint`, `children tinyint`
- `rate_amount decimal(10,2)`, `total_amount decimal(12,2)`
- `status` enum(pending/confirmed/checked_in/checked_out/cancelled) default pending
- `actual_check_in_at timestamp` nullable, `actual_check_out_at timestamp` nullable
- `timestamps`
- index(reservation_id), index(room_id), index(room_type_id)

### reservation_guests
- `id`, `reservation_id` FK cascade, `guest_id` FK cascade
- `is_primary boolean` default false
- `timestamps`
- unique(reservation_id, guest_id)

### folios
- `id`, `property_id` FK, `reservation_id` FK nullable, `guest_id` FK nullable
- `folio_number varchar` unique
- `type` enum(room/master/group/city_ledger) default room
- `status` enum(open/closed/invoiced) default open
- `subtotal decimal(12,2)` default 0, `tax_amount decimal(12,2)` default 0
- `total_amount decimal(12,2)` default 0, `paid_amount decimal(12,2)` default 0, `balance decimal(12,2)` default 0
- `invoice_number` nullable, `invoiced_at timestamp` nullable, `closed_at timestamp` nullable
- `timestamps`
- index(property_id, status), index(reservation_id)

### folio_transactions
- `id`, `folio_id` FK cascade, `charge_code_id` FK nullable, `created_by` FK nullable
- `reservation_room_id` FK nullable, `pos_order_id` FK nullable
- `type` enum(charge/credit/payment/transfer/adjustment/tax)
- `description`, `date date`
- `quantity decimal(8,2)` default 1, `unit_price decimal(10,2)`, `amount decimal(12,2)`
- `tax_rate decimal(5,2)` default 0, `tax_amount decimal(10,2)` default 0
- `reference` nullable
- `timestamps`
- index(folio_id, date), index(folio_id, type)

### payments
- `id`, `property_id` FK, `folio_id` FK nullable, `reservation_id` FK nullable, `created_by` FK nullable
- `method` enum(cash/credit_card/debit_card/bank_transfer/ota_collect/cheque/credit_note/complimentary)
- `amount decimal(12,2)`, `currency_id` FK nullable, `exchange_rate decimal(10,6)` default 1
- `reference` nullable, `card_last_four char(4)` nullable, `card_brand` nullable
- `status` enum(pending/completed/failed/refunded/voided) default pending
- `paid_at timestamp` nullable, `notes text` nullable
- `timestamps`
- index(property_id, status), index(folio_id)

### channels
- `id`, `property_id` FK nullable (null = global/shared)
- `name`, `code varchar(20)` unique
- `type` enum(ota/gds/booking_engine/direct/metasearch)
- `is_active boolean`
- `timestamps`

### channel_mappings
- `id`, `channel_id` FK cascade, `room_type_id` FK nullable, `rate_plan_id` FK nullable
- `channel_room_id` nullable, `channel_rate_id` nullable
- `is_active boolean`
- `timestamps`
- unique(channel_id, room_type_id, rate_plan_id)

### ota_syncs
- `id`, `property_id` FK, `channel_id` FK nullable, `created_by` FK nullable
- `reference varchar` unique
- `type` enum(availability/rates/reservations/all)
- `status` enum(pending/processing/success/failed/partial) default pending
- `sync_from date` nullable, `sync_to date` nullable
- `records_sent int` default 0, `records_updated int` default 0, `errors_count int` default 0
- `error_log json` nullable
- `started_at timestamp` nullable, `completed_at timestamp` nullable
- `meta json` nullable
- `softDeletes`, `timestamps`
- index(property_id, channel_id), index(status)

### pos_outlets
- `id`, `property_id` FK
- `name`, `code varchar(20)`
- `type` enum(restaurant/bar/room_service/spa/pool/shop/other)
- `is_active boolean`
- `timestamps`
- unique(property_id, code)

### pos_menu_categories
- `id`, `property_id` FK, `outlet_id` FK nullable
- `name`, `sort_order smallint` default 0, `is_active boolean`
- `timestamps`

### pos_menu_items
- `id`, `property_id` FK, `outlet_id` FK nullable, `category_id` FK nullable
- `name`, `code varchar(20)` nullable, `description text` nullable, `image_path` nullable
- `price decimal(10,2)`, `tax_rate decimal(5,2)` default 0
- `is_active boolean`
- `softDeletes`, `timestamps`
- index(property_id, outlet_id), index(category_id)

### pos_orders
- `id`, `property_id` FK, `outlet_id` FK nullable
- `reservation_id` FK nullable, `folio_id` FK nullable, `created_by` FK nullable
- `reference varchar` unique
- `order_type` enum(dine_in/room_service/takeaway/delivery)
- `table_identifier varchar(20)` nullable
- `status` enum(draft/ordered/preparing/ready/served/paid/cancelled/voided) default draft
- `subtotal decimal(12,2)` default 0, `discount_amount decimal(10,2)` default 0
- `tax_amount decimal(10,2)` default 0, `total_amount decimal(12,2)` default 0
- `notes text` nullable
- `softDeletes`, `timestamps`
- index(property_id, status), index(outlet_id)

### pos_order_items
- `id`, `order_id` FK cascade, `menu_item_id` FK nullable
- `name`, `quantity decimal(8,2)`, `unit_price decimal(10,2)`
- `discount_amount decimal(10,2)` default 0
- `tax_rate decimal(5,2)` default 0, `tax_amount decimal(10,2)` default 0
- `total_amount decimal(12,2)`
- `notes text` nullable
- `timestamps`

### housekeeping_tasks
- `id`, `property_id` FK, `room_id` FK nullable, `assigned_to` FK users nullable, `created_by` FK nullable
- `reference varchar` unique
- `type` enum(standard_clean/deep_clean/turndown/checkout_clean/inspection) default standard_clean
- `priority` enum(low/normal/high/urgent) default normal
- `status` enum(pending/in_progress/completed/inspected/cancelled) default pending
- `scheduled_at timestamp` nullable, `started_at timestamp` nullable
- `completed_at timestamp` nullable, `inspected_at timestamp` nullable
- `notes text` nullable, `meta json` nullable
- `softDeletes`, `timestamps`
- index(property_id, room_id), index(status), index(assigned_to)

### maintenance_requests
- `id`, `property_id` FK, `room_id` FK nullable, `assigned_to` FK users nullable, `created_by` FK nullable
- `reference varchar` unique
- `title`, `description text` nullable
- `category` enum(electrical/plumbing/hvac/furniture/equipment/safety/other)
- `priority` enum(low/normal/high/urgent) default normal
- `status` enum(open/in_progress/on_hold/completed/cancelled) default open
- `reported_at timestamp`, `started_at timestamp` nullable
- `resolved_at timestamp` nullable, `closed_at timestamp` nullable
- `estimated_cost decimal(10,2)` nullable, `actual_cost decimal(10,2)` nullable
- `notes text` nullable
- `softDeletes`, `timestamps`
- index(property_id, status), index(assigned_to)

### departments
- `id`, `property_id` FK
- `name`, `code varchar(20)`, `description text` nullable, `is_active boolean`
- `timestamps`
- unique(property_id, code)

### employees
- `id`, `property_id` FK, `department_id` FK nullable, `user_id` FK nullable, `created_by` FK nullable
- `reference varchar` unique
- `status` enum(active/inactive/terminated/on_leave) default active
- `first_name`, `last_name`, `email` nullable, `phone` nullable
- `job_title`, `position_level` enum(entry/junior/mid/senior/manager/director/executive) nullable
- `gender` enum(male/female/other/prefer_not_to_say) nullable
- `date_of_birth date` nullable, `hire_date date` nullable, `termination_date date` nullable
- `national_id` nullable
- `emergency_contact_name` nullable, `emergency_contact_phone` nullable
- `salary_amount decimal(12,2)` nullable, `salary_type` enum(hourly/daily/monthly) default monthly
- `photo_path` nullable
- `softDeletes`, `timestamps`
- index(property_id, department_id), index(status)

### attendances
- `id`, `employee_id` FK cascade, `attendance_date date`
- `check_in time` nullable, `check_out time` nullable
- `status` enum(present/absent/half_day/late/leave) default present
- `timestamps`
- unique(employee_id, attendance_date)

### payrolls
- `id`, `employee_id` FK cascade, `period_start date`, `period_end date`
- `gross_amount decimal(12,2)`, `deduction_amount decimal(12,2)` default 0, `net_amount decimal(12,2)`
- `status` enum(pending/processed/paid/cancelled) default pending
- `paid_at timestamp` nullable
- `timestamps`
- index(employee_id, status)

### shift_schedules
- `id`, `employee_id` FK cascade, `shift_date date`
- `start_time time`, `end_time time`
- `status` enum(scheduled/confirmed/completed/cancelled) default scheduled
- `timestamps`
- unique(employee_id, shift_date)

### report_snapshots
- `id`, `property_id` FK, `created_by` FK nullable
- `reference varchar` unique
- `report_type varchar(100)`, `report_date date`
- `status` enum(pending/generating/ready/failed) default pending
- `data json` nullable  ← renamed from meta
- `softDeletes`, `timestamps`
- index(property_id, report_date), index(report_type)

### mobile_tasks
- `id`, `property_id` FK, `assigned_to` FK users nullable, `created_by` FK nullable
- `reference varchar` unique
- `title`, `description text` nullable
- `type` enum(housekeeping/maintenance/concierge/delivery/request/other) default request
- `priority` enum(low/normal/high/urgent) default normal
- `status` enum(pending/assigned/in_progress/completed/cancelled) default pending
- `scheduled_at timestamp` nullable, `completed_at timestamp` nullable
- `meta json` nullable
- `softDeletes`, `timestamps`
- index(property_id, status), index(assigned_to)

### audit_logs
- `id`, `property_id` FK nullable, `user_id` FK nullable
- `event varchar(50)` (created/updated/deleted/restored/login/logout/action)
- morph `auditable_type`/`auditable_id` nullable
- `old_values json` nullable, `new_values json` nullable
- `ip_address varchar(45)` nullable, `user_agent text` nullable
- `created_at` only (no `updated_at`)
- index(property_id, event), index(auditable_type, auditable_id), index(user_id)

### property_settings
- `id`, `property_id` FK cascade
- `group varchar(50)` (general/notifications/integrations/billing/housekeeping)
- `key varchar(100)`, `value text` nullable
- `timestamps`
- unique(property_id, key)

---

## Key Decisions

1. **Single schema anchor**: All tables use `property_id` — no `hotel_id` anywhere.
2. **No Enum class dependencies in migrations**: Use inline string arrays to avoid migration failures if Enum PHP classes are renamed/moved.
3. **Soft deletes**: All guest-facing and operational tables have `softDeletes`. System/config tables (meal_plans, channels, charge_codes) do not, as they are managed via `is_active`.
4. **Nullable FKs cascade to null**: All optional FKs use `nullOnDelete()`. Required FKs (like `folio_id` on `folio_transactions`) use `cascadeOnDelete()`.
5. **Migration order resolves billing↔POS dependency**: `pos_orders` references `folio_id` (folios come first at 001800), and `folio_transactions` references `pos_order_id` (pos_orders come before folio_transactions at 002200 → 002400). No circular dependency.
6. **No `updated_at` on `audit_logs`**: Audit records are immutable.
7. **`property_id` nullable on `meal_plans`, `channels`, `charge_codes`**: Allows system-level defaults that all properties share.
8. **`reservation.reference` is unique**: Generated booking number (e.g. RES-2026-00001).
9. **`folio_number` is unique**: Generated on folio creation (e.g. FOL-2026-00001).
