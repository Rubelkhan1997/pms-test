# 📊 COMPLETE TENANT MIGRATION GUIDE

**Last Updated:** March 29, 2026
**Total Tables:** 150+
**Total Migrations:** 20 files

---

## 📋 MIGRATION FILE ORDER

### 1. Core & Authentication (001-010)

#### `2026_03_05_100453_create_permission_tables.php`
**Spatie Permission Tables**
- permissions
- roles
- model_has_permissions
- model_has_roles
- role_has_permissions

#### `2026_03_05_110000_create_users_table.php`
**User Management**
- users
- password_resets

#### `2026_03_05_120000_create_hotels_table.php`
**Hotel/Property**
- hotels

---

### 2. Audit & Tenant Setup (011-020)

#### `2026_03_17_000001_create_audit_logs_table.php`
**Audit Trail**
- audit_logs (central)

#### `2026_03_17_200000_create_tenant_database_schema.php`
**Tenant Management**
- tenants
- tenant_owners (pivot)

#### `2026_03_17_200001_create_tenant_database_schema.php`
**Tenant Schema Setup**
- (Schema configuration)

#### `2026_03_17_200002_add_hotel_foreign_to_users_table.php`
**User-Hotel Relationship**
- Adds foreign key to users.hotel_id

---

### 3. Room Management (030-039)

#### `2026_03_19_000000_create_room_management_tables.php`
**Rooms & Physical Inventory**
- buildings
- floors
- amenities
- room_types
- room_type_amenities (pivot)
- rooms
- room_status_logs
- room_blocks
- room_maintenance
- room_images

---

### 4. Pricing & Accounting (040-049)

#### `2026_03_19_000001_create_rate_plans_and_accounting.php`
**Pricing Strategy & Financial Tables**

*Accounting (12 tables):*
- revenue_categories
- taxes
- service_charges
- payment_methods
- stored_credit_cards
- refunds
- adjustments
- cashier_sessions
- petty_cash
- invoice_items
- folio_transactions
- ledger_entries

*Pricing (8 tables):*
- rate_plans
- rate_plan_room_types (pivot)
- rates
- rate_overrides
- occupancy_pricing
- extra_guest_charges
- derived_rate_plans (pivot)
- promotions
- promotion_uses

---

### 5. Reservations & Guests (050-059)

#### `2026_03_19_000002_create_reservations_and_guests.php`
**Reservations & Guest Management**

*Reservations (4 tables):*
- reservation_guests
- reservation_extras
- reservation_payments
- reservation_logs

*Groups & Waitlist (3 tables):*
- waitlist
- group_blocks
- group_contracts

*Guest Experience (5 tables):*
- guest_preferences
- guest_communications
- guest_documents
- vip_guestes
- blacklisted_guests

---

### 6. Availability & Inventory (060-069)

#### `2026_03_19_000003_create_availability_and_inventory.php`
**Room Availability & Pricing Calendar**
- room_availability
- daily_rates
- seasonal_pricing
- rate_restrictions
- blackout_dates

---

### 7. Inventory Management (070-079)

#### `2026_03_19_000004_create_inventory_management_tables.php`
**Extra Inventory & Features**
- room_features
- room_feature_room_type (pivot)
- housekeeping_checklists
- housekeeping_supplies

---

### 8. Housekeeping (080-089)

#### `2026_03_19_000005_create_housekeeping_management_tables.php`
**Housekeeping & Maintenance**
- housekeeping_tasks
- maintenance_requests
- housekeeping_assignments
- laundry
- linen_inventory

---

### 9. Channel Manager (090-099)

#### `2026_03_19_000006_create_channel_manager_tables.php`
**OTA Integration**

*Original (6 tables):*
- channels
- channel_room_mappings (pivot)
- channel_rate_mappings (pivot)
- rate_push_queue
- inventory_push_queue
- ota_sync_queue
- ota_sync_logs

*Extensions (7 tables):*
- ota_reservations
- ota_rate_plans (pivot)
- restrictions_push_queue
- channel_errors
- channel_statistics
- ota_provider_credentials
- channel_mapping_rules

---

### 10. Night Audit (100-109)

#### `2026_03_19_000007_create_night_audit_tables.php`
**Night Audit Operations**

*Original (4 tables):*
- night_audit_logs
- day_end_reports
- auto_postings
- auto_posting_executions

*Extensions (7 tables):*
- business_dates
- rate_verifications
- room_status_history
- room_inspections
- room_amenities
- lost_and_found
- key_cards

---

### 11. POS & Inventory (110-119)

#### `2026_03_19_000008_create_pos_integration_tables.php`
**Point of Sale & Restaurant**

*Original POS (10 tables):*
- outlets
- pos_terminals
- pos_payment_methods
- pos_mappings
- pos_checks
- pos_check_items
- pos_transactions
- pos_postings
- pos_sync_logs
- pos_revenue_summary

*POS Inventory (11 tables):*
- pos_categories
- pos_recipes
- pos_recipe_ingredients (pivot)
- pos_inventory_items
- pos_suppliers
- pos_purchase_orders
- pos_purchase_order_items (pivot)
- pos_stock_movements
- pos_waste_tracking
- pos_stock_transfers
- pos_stock_transfer_items

*Menu Items Extension:*
- Adds: category_id, description, image_path, is_available, prep_time_minutes to pos_menu_items

---

### 12. HR Management (120-129)

#### `2026_03_19_000009_create_hr_management_tables.php`
**Human Resources**

*Original HR (14 tables):*
- departments
- designations
- employees
- employee_documents
- employee_contracts
- shifts
- employee_shifts (pivot)
- attendances
- leave_types
- leave_entitlements
- leaves
- payrolls
- payroll_items
- employee_bank_accounts
- employee_logs
- overtime_records

*HR Extensions (10 tables):*
- employee_reviews
- employee_review_criteria
- employee_warnings
- employee_training
- job_postings
- job_applications
- job_interviews
- expense_claims
- expense_claim_items
- employee_benefits
- employee_separations

---

### 13. Utility & System (130-139)

#### `2026_03_17_142139_add_login_fields_to_users_table.php`
**User Login Tracking**
- Adds: last_login_at, last_login_ip to users

#### `2026_03_19_000009_add_is_active_to_users_table.php`
**User Status**
- Adds: is_active to users

#### `2026_03_19_000010_create_utility_and_tenant_tables.php`
**Laravel Utility & Tenant Management**

*Laravel Utilities (8 tables):*
- sessions
- jobs (queue)
- failed_jobs
- password_reset_tokens
- notifications
- job_batches
- rate_limits
- personal_access_tokens (Sanctum)

*Audit & Settings (3 tables):*
- activity_logs
- audit_logs (tenant)
- system_settings

*Subscription & Billing (7 tables):*
- subscription_plans
- tenant_subscriptions
- tenant_invoices
- tenant_invoice_items
- tenant_payments
- tenant_usage_records
- tenant_owners (pivot)

---

## 📊 TABLE COUNT BY CATEGORY

| Category | Tables |
|----------|--------|
| **Authentication & Users** | 15 |
| **Rooms & Physical** | 15 |
| **Pricing & Accounting** | 20 |
| **Reservations & Guests** | 18 |
| **Availability** | 5 |
| **Housekeeping** | 9 |
| **Channel Manager** | 14 |
| **Night Audit** | 11 |
| **POS & Inventory** | 22 |
| **HR Management** | 25 |
| **Utility & System** | 18 |
| **Tenant Management** | 10 |
| **TOTAL** | **182+ tables** |

---

## 🔑 KEY RELATIONSHIPS

### User → Hotel
```
users.hotel_id → hotels.id
```

### Reservation Flow
```
reservations.room_id → rooms.id
reservations.guest_profile_id → guest_profiles.id
reservation_guests.reservation_id → reservations.id
```

### Accounting Flow
```
invoices.reservation_id → reservations.id
invoice_items.invoice_id → invoices.id
folio_transactions.folio_id → folios.id
ledger_entries.hotel_id → hotels.id
payments.invoice_id → invoices.id
```

### POS → Accounting
```
pos_postings.folio_id → folios.id
pos_postings.revenue_category_id → revenue_categories.id
```

---

## 🎯 MIGRATION TIPS

### Running Migrations
```bash
# Fresh migration (development)
php artisan migrate:fresh --database=pgsql --force

# Run specific path
php artisan migrate --path=database/migrations/tenant --database=pgsql
```

### Rollback
```bash
# Rollback last batch
php artisan migrate:rollback

# Rollback all
php artisan migrate:reset
```

---

## ✅ PRE-MIGRATION CHECKLIST

- [ ] PostgreSQL database created
- [ ] `.env` configured with correct credentials
- [ ] Central database configured
- [ ] Tenant database configured
- [ ] Run `php artisan config:clear`
- [ ] Backup existing data (if any)

---

*Migration Guide: March 29, 2026*
*Total Tables: 182+*
*Total Migration Files: 20*
