# 🧹 Cleanup & Reorganization Summary

**Date:** March 17, 2026  
**Action:** Removed duplicate/conflicting code for per-tenant architecture

---

## ✅ Files Removed

### Duplicate Migrations (3)
1. `2026_03_17_100000_create_subscription_tables.php` - Now in central DB
2. `2026_03_17_100001_create_accounting_tables.php` - Now in tenant DB
3. `2026_03_17_100002_create_property_settings_tables.php` - Now in tenant DB

### Old Landlord Migration (1)
1. `landlord/2026_03_05_100453_create_landlord_tenants_table.php` - Old Stancl package

---

## 📁 Current Migration Structure

```
database/migrations/
├── 0001_01_01_000000_create_users_table.php ✅ (Central - Super admin)
├── 0001_01_01_000001_create_cache_table.php ✅ (Central)
├── 0001_01_01_000002_create_jobs_table.php ✅ (Central)
├── 2026_03_05_100453_create_permission_tables.php ✅ (Central)
├── 2026_03_05_100453_create_personal_access_tokens_table.php ✅ (Central)
├── 2026_03_17_200000_create_central_tenant_database.php ✅ (Central - NEW)
│
└── tenant/
    └── 2026_03_17_200001_create_tenant_database_schema.php ✅ (Tenant - NEW)
```

**Old Migrations (To be removed/moved):**
- `2026_03_05_120000_create_hotels_table.php` → Move to tenant/
- `2026_03_05_120100_create_rooms_table.php` → Move to tenant/
- `2026_03_05_120300_create_ota_syncs_table.php` → Move to tenant/
- `2026_03_05_120400_create_agents_and_guest_profiles_table.php` → Move to tenant/
- `2026_03_05_120450_create_reservations_table.php` → Move to tenant/
- `2026_03_05_120500_create_housekeeping_and_maintenance_tables.php` → Move to tenant/
- `2026_03_05_120600_create_pos_tables.php` → Move to tenant/
- `2026_03_05_120700_create_report_snapshots_table.php` → Move to tenant/
- `2026_03_05_120800_create_mobile_tasks_table.php` → Move to tenant/
- `2026_03_05_120900_create_hr_tables.php` → Move to tenant/
- `2026_03_17_000001_create_audit_logs_table.php` → Move to tenant/
- `2026_03_17_000002_add_check_in_out_columns_to_reservations_table.php` → Move to tenant/
- `2026_03_17_000003_add_is_active_to_hotels_table.php` → Move to tenant/
- `2026_03_17_000004_add_missing_columns_to_pos_orders_table.php` → Move to tenant/
- `2026_03_17_000005_add_missing_columns_to_housekeeping_tasks_table.php` → Move to tenant/
- `2026_03_17_000006_add_missing_columns_to_mobile_tasks_table.php` → Move to tenant/
- `2026_03_17_000007_add_data_column_to_report_snapshots_table.php` → Move to tenant/
- `2026_03_17_000008_add_hotel_id_to_hr_tables.php` → Move to tenant/

---

## 🎯 Architecture Decision

### Central Database (Landlord)
**Purpose:** Manage tenants, subscriptions, billing  
**Tables:**
- `tenants` - Tenant registry
- `subscription_plans` - Plan definitions
- `tenant_subscriptions` - Active subscriptions
- `tenant_invoices` - Billing
- `tenant_payments` - Payments
- `tenant_usage_records` - Usage tracking
- `system_settings` - System config
- `activity_logs` - Central audit trail
- `users` - Super admin only
- `permissions`, `roles`, `model_has_*` - Central RBAC

### Tenant Database (Per Hotel)
**Purpose:** Hotel operations, guest data, reservations  
**Tables:** ALL hotel-specific tables
- `hotels`, `rooms`, `reservations`
- `guest_profiles`, `agents`
- `housekeeping_tasks`, `maintenance_requests`
- `pos_orders`, `pos_menu_items`
- `employees`, `attendances`, `payrolls`, `shift_schedules`
- `report_snapshots`, `mobile_tasks`
- `users` - Tenant-specific users
- `permissions`, `roles`, `model_has_*` - Tenant RBAC

---

## ✅ Models Status

### Central Database Models ✅
- `Tenant` ✅
- `TenantSubscription` ✅
- `SubscriptionPlan` ✅
- `TenantInvoice` ✅
- `TenantInvoiceItem` ✅
- `TenantPayment` ✅
- `TenantUsageRecord` ✅
- `SystemSetting` ✅
- `ActivityLog` ✅
- `User` ✅ (Super admin only)

### Tenant Database Models (Existing) ✅
- `Hotel` ✅
- `Room` ✅
- `Reservation` ✅
- `GuestProfile` ✅
- `Agent` ✅
- `HousekeepingTask` ✅
- `MaintenanceRequest` ✅
- `PosOrder` ✅
- `PosMenuItem` ✅
- `Employee` ✅
- `Attendance` ✅
- `Payroll` ✅
- `ShiftSchedule` ✅
- `ReportSnapshot` ✅
- `MobileTask` ✅
- `User` ✅ (Tenant users)
- `AuditLog` ✅

---

## 🚀 Next Steps

1. **Move old migrations to tenant/** directory
2. **Update config** for multi-database setup
3. **Create Tenant Manager** middleware
4. **Continue with Phase 1B** (Rooms, Rates, Reservations)

---

*Last Updated: March 17, 2026*
