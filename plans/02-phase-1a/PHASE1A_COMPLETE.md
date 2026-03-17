# ✅ Phase 1A COMPLETE: Per-Tenant Database Architecture

**Date:** March 17, 2026  
**Architecture:** Per-Tenant Database Isolation  
**Status:** ✅ Foundation Complete

---

## 🏗️ Architecture Overview

### Two-Database Structure

```
┌─────────────────────────────────────────────────────────┐
│                  CENTRAL DATABASE                        │
│  (Manages all tenants, subscriptions, billing)          │
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Tenants    │  │ Subscriptions│  │   Invoices   │  │
│  │              │  │    Plans     │  │   Payments   │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Tenant Owners│  │Usage Records │  │System Settings│  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                            │
                            │ Creates & Manages
                            ▼
┌─────────────────────────────────────────────────────────┐
│              TENANT DATABASES (Isolated)                 │
│                                                          │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐        │
│  │  Tenant 1  │  │  Tenant 2  │  │  Tenant 3  │  ...   │
│  │  Database  │  │  Database  │  │  Database  │        │
│  │            │  │            │  │            │        │
│  │ - Hotels   │  │ - Hotels   │  │ - Hotels   │        │
│  │ - Rooms    │  │ - Rooms    │  │ - Rooms    │        │
│  │ - Reservations│ │ - Reservations│ │ - Reservations│  │
│  │ - Guests   │  │ - Guests   │  │ - Guests   │        │
│  │ - Users    │  │ - Users    │  │ - Users    │        │
│  │ - Accounting│ │ - Accounting│ │ - Accounting│        │
│  └────────────┘  └────────────┘  └────────────┘        │
└─────────────────────────────────────────────────────────┘
```

---

## 📊 What Was Created

### Central Database (10 tables)

1. **`tenants`** - Master tenant registry
   - UUID primary key
   - Database name per tenant
   - Domain/subdomain routing
   - Status tracking (pending, active, suspended, cancelled)
   - Trial period management

2. **`tenant_owners`** - Tenant-user relationships
   - Multi-owner support
   - Role assignment (owner, admin, manager)

3. **`subscription_plans`** - Plan definitions
   - Starter, Professional, Enterprise
   - Monthly/yearly pricing
   - Feature flags
   - Usage limits

4. **`tenant_subscriptions`** - Active subscriptions
   - Trial/active/cancelled status
   - Billing cycle tracking
   - Stripe integration fields
   - Cancellation tracking

5. **`tenant_usage_records`** - Usage tracking
   - Properties, rooms, users count
   - API calls, storage
   - Daily recording

6. **`tenant_invoices`** - Subscription invoices
   - Invoice numbering
   - Status tracking
   - Tax calculation
   - Stripe invoice ID

7. **`tenant_invoice_items`** - Invoice line items
   - Charges, payments, adjustments
   - Tax per item

8. **`tenant_payments`** - Payment records
   - Multiple payment methods
   - Transaction tracking
   - Gateway metadata

9. **`system_settings`** - Central configuration
   - Key-value storage
   - Grouped settings
   - Type casting

10. **`activity_logs`** - Central audit trail
    - Tenant-scoped logging
    - User activity tracking
    - IP/user agent logging

---

### Tenant Database (10+ tables per tenant)

**Migrations Location:** `database/migrations/tenant/`

Each tenant gets their own database with:

1. **`hotels`** - Property information
2. **`property_taxes`** - Tax configuration
3. **`rooms`** - Room inventory
4. **`reservations`** - Bookings
5. **`guest_profiles`** - Guest data
6. **`users`** - Tenant-specific users
7. **`model_has_permissions`** - Spatie permissions (hotel-scoped)
8. **`model_has_roles`** - Spatie roles (hotel-scoped)
9. Plus all other hotel-specific tables

---

### Models Created (11)

#### Central Database Models
- ✅ `Tenant` - Main tenant model
- ✅ `TenantSubscription` - Subscription management
- ✅ `SubscriptionPlan` - Plan definitions
- ✅ `TenantInvoice` - Invoicing
- ✅ `TenantInvoiceItem` - Invoice items
- ✅ `TenantPayment` - Payment tracking
- ✅ `TenantUsageRecord` - Usage tracking
- ✅ `SystemSetting` - System configuration
- ✅ `ActivityLog` - Activity logging

#### Tenant Database Models
- ✅ All existing hotel models (Room, Reservation, Guest, etc.)

---

### Services Created (1)

#### **DatabaseProvisioningService**
The core service that manages per-tenant databases:

**Methods:**
- `createDatabaseForTenant()` - Create new tenant database
- `deleteDatabaseForTenant()` - Remove tenant database
- `setTenantConnection()` - Switch to tenant database
- `runMigrations()` - Run migrations on tenant DB
- `seedTenantData()` - Seed initial data
- `backupDatabase()` - Backup tenant database
- `restoreDatabase()` - Restore from backup
- `databaseExists()` - Check if database exists
- `getDatabaseSize()` - Get database size

**Features:**
- ✅ Automatic database creation
- ✅ User creation with restricted permissions
- ✅ Migration execution
- ✅ Initial seeding
- ✅ Backup/restore support
- ✅ Connection management

---

## 🔐 Security Features

### Database Isolation
- ✅ Each tenant has separate database
- ✅ No cross-tenant data access possible
- ✅ Tenant-specific database users
- ✅ Restricted permissions per user

### Connection Management
```php
// Switch to tenant database
$service = app(DatabaseProvisioningService::class);
$service->setTenantConnection($tenant);

// All queries now run on tenant database
$tenantsReservations = Reservation::all(); // Only this tenant's data
```

### Data Encryption
- ✅ Database passwords encrypted
- ✅ Sensitive metadata encrypted
- ✅ SSL connections supported

---

## 💰 Subscription Management

### Plan Structure
```php
Starter ($49/month)
- 1 property
- 20 rooms
- 5 users
- Basic features

Professional ($99/month)
- 5 properties
- 100 rooms
- 20 users
- Advanced features

Enterprise ($249/month)
- Unlimited properties
- Unlimited rooms
- Unlimited users
- All features
```

### Billing Flow
1. Tenant signs up → Trial starts
2. Trial ends → Subscription becomes active
3. Monthly/Yearly billing → Invoice generated
4. Payment processed → Invoice marked paid
5. Failed payment → Dunning process
6. Cancellation → Grace period → Database archived

---

## 🚀 How to Use

### Create New Tenant
```php
use App\Models\Tenant;
use App\Services\DatabaseProvisioningService;

// Create tenant
$tenant = Tenant::create([
    'name' => 'Grand Hotel',
    'email' => 'contact@grandhotel.com',
    'subdomain' => 'grandhotel',
    'status' => 'pending',
]);

// Provision database
$provisioningService = app(DatabaseProvisioningService::class);
$provisioningService->createDatabaseForTenant($tenant);

// Activate tenant
$tenant->activate();

// Create subscription
$tenant->subscription()->create([
    'subscription_plan_id' => 2, // Professional plan
    'status' => 'trial',
    'start_date' => now(),
    'trial_ends_at' => now()->addDays(14),
    'billing_cycle' => 'monthly',
    'amount' => 99.00,
]);
```

### Access Tenant Database
```php
// Get tenant
$tenant = Tenant::find($tenantId);

// Switch to tenant database
$provisioningService = app(DatabaseProvisioningService::class);
$provisioningService->setTenantConnection($tenant);

// Now all queries run on tenant database
$reservations = Reservation::all(); // Only this tenant's reservations
```

### Run Commands on Tenant Database
```php
// Run migrations
Artisan::call('migrate', [
    '--database' => 'tenant_' . $tenant->id,
    '--path' => 'database/migrations/tenant',
    '--force' => true,
]);

// Seed data
Artisan::call('db:seed', [
    '--database' => 'tenant_' . $tenant->id,
    '--class' => 'TenantDatabaseSeeder',
    '--force' => true,
]);
```

---

## 📝 Database Seeder

### Central Database Seeder
Creates:
- ✅ 3 subscription plans (Starter, Professional, Enterprise)
- ✅ Super admin user
- ✅ System settings

### Tenant Database Seeder
Creates for each tenant:
- ✅ Default hotel/property
- ✅ Roles and permissions
- ✅ Admin user

---

## 🎯 Next Steps (Phase 1B)

Now that the foundation is complete, next phase implements:

1. **Room & Inventory Management**
   - Room types with pricing
   - Rate plans
   - Seasonal pricing
   - Availability calendar

2. **Reservation Management**
   - Complete booking flow
   - Check-in/check-out
   - Guest management
   - Agent management

3. **Integration**
   - Connect tenant database to operations
   - Multi-tenant aware queries
   - Cross-database reporting

---

## ✅ Phase 1A Checklist

- [x] Central database migrations
- [x] Tenant database migrations
- [x] Tenant model
- [x] Subscription models
- [x] Invoice models
- [x] Payment models
- [x] Database provisioning service
- [x] Central database seeder
- [x] Tenant database seeder
- [x] System settings
- [x] Activity logging

---

**Status:** ✅ Phase 1A Complete  
**Next:** Phase 1B - Core Operations (Rooms, Rates, Reservations)

---

*Last Updated: March 17, 2026*  
*Architecture: Per-Tenant Database Isolation*  
*Total Tables: 20+ (central) + 10+ (per tenant)*
