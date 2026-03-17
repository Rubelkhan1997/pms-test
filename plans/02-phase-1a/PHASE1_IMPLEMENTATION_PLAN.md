# Phase 1 Implementation Plan - Core PMS Features

**Priority:** Production-Ready MVP  
**Timeline:** 4-6 weeks  
**Status:** ⏳ Ready to Implement

---

## 📋 Phase 1 Features (6 Core Modules)

### 1. Tenant Management (Partner) ✅ Foundation Exists
**Goal:** Multi-tenancy with subscription & data isolation

**Features:**
- ✅ Multi-tenant architecture (hotel_id scoping)
- ⏳ Subscription plans (Basic, Pro, Enterprise)
- ⏳ Subscription billing cycle
- ⏳ Tenant status (active, suspended, cancelled)
- ⏳ Feature flags per plan
- ✅ Data isolation (already implemented)

**New Files Needed:**
- `app/Models/SubscriptionPlan.php`
- `app/Models/TenantSubscription.php`
- `app/Modules/Tenant/Services/TenantService.php`
- `database/migrations/create_subscription_tables.php`

---

### 2. Property Setup & Onboarding ⏳ Partial
**Goal:** Complete property information, tax, branding

**Features:**
- ✅ Property info (name, code, timezone, currency)
- ⏳ Tax configuration (VAT, service charge, tourism tax)
- ⏳ Branding (logo, colors, email templates)
- ⏳ Property amenities
- ⏳ Check-in/out times
- ⏳ Cancellation policies

**New Files Needed:**
- `database/migrations/add_property_settings_columns.php`
- `app/Models/PropertyTax.php`
- `app/Models/PropertyAmenity.php`
- `app/Models/PropertyPolicy.php`

---

### 3. Room & Inventory ✅ Mostly Complete
**Goal:** Room types, units, pricing

**Features:**
- ✅ Room types (Standard, Deluxe, Suite)
- ✅ Room units (individual rooms)
- ✅ Base pricing
- ⏳ Seasonal pricing
- ⏳ Rate plans (BAR, Non-refundable, Package)
- ⏳ Room amenities
- ⏳ Inventory allocation

**Enhancements Needed:**
- `database/migrations/create_room_rates_tables.php`
- `app/Models/RoomRate.php`
- `app/Models/SeasonalPricing.php`
- `app/Models/RatePlan.php`

---

### 4. Reservation (Basic) ✅ Complete
**Goal:** Booking, check-in/out, guest profile

**Features:**
- ✅ Reservation creation
- ✅ Booking modification
- ✅ Check-in workflow
- ✅ Check-out workflow
- ✅ Guest profiles
- ✅ Agent management
- ✅ Status tracking

**Status:** ✅ Already implemented!

---

### 5. Accounting ⏳ Needs Implementation
**Goal:** Invoice, payment, basic ledger

**Features:**
- ⏳ Invoice generation
- ⏳ Payment processing
- ⏳ Payment methods (cash, card, bank transfer)
- ⏳ Basic ledger (debit/credit)
- ⏳ Folio management (guest folio)
- ⏳ Receipt generation

**New Files Needed:**
- `database/migrations/create_accounting_tables.php`
- `app/Models/Invoice.php`
- `app/Models/Payment.php`
- `app/Models/LedgerEntry.php`
- `app/Models/Folio.php`
- `app/Modules/Accounting/Services/InvoiceService.php`
- `app/Modules/Accounting/Services/PaymentService.php`

---

### 6. System & Settings ⏳ Partial
**Goal:** Core database + security

**Features:**
- ✅ User management (Laravel + Spatie)
- ✅ Role-based access control
- ⏳ System settings (general, email, SMS)
- ⏳ Audit logging (already started)
- ⏳ Activity log
- ⏳ Backup configuration
- ⏳ API keys management

**Enhancements Needed:**
- `database/migrations/create_system_settings_table.php`
- `app/Models/SystemSetting.php`
- `app/Models/ActivityLog.php`
- `app/Modules/System/Services/SettingsService.php`

---

## 📊 Implementation Priority

### Week 1-2: Foundation
1. ✅ Multi-tenancy (already done)
2. ⏳ Subscription management
3. ⏳ Property settings & tax
4. ⏳ System settings

### Week 3-4: Core Operations
1. ✅ Room inventory (already done)
2. ✅ Reservations (already done)
3. ⏳ Accounting module
4. ⏳ Invoice & payment

### Week 5-6: Polish & Testing
1. ⏳ Integration testing
2. ⏳ User acceptance testing
3. ⏳ Documentation
4. ⏳ Deployment preparation

---

## 🗄️ Database Schema Updates

### New Tables Needed (8)
1. `subscription_plans` - Plan definitions
2. `tenant_subscriptions` - Active subscriptions
3. `property_taxes` - Tax configurations
4. `property_amenities` - Property features
5. `property_policies` - Cancellation policies
6. `room_rates` - Rate plans
7. `invoices` - Guest invoices
8. `payments` - Payment records
9. `ledger_entries` - Accounting entries
10. `system_settings` - System configuration

### Existing Tables to Enhance (5)
1. `hotels` - Add branding, check-in/out times
2. `rooms` - Add amenities, features
3. `reservations` - Already complete
4. `guest_profiles` - Already complete
5. `users` - Add last_login, preferences

---

## 📁 New File Structure

```
app/Modules/
├── Tenant/
│   ├── Models/
│   │   ├── SubscriptionPlan.php
│   │   └── TenantSubscription.php
│   ├── Services/
│   │   └── TenantService.php
│   └── Enums/
│       └── SubscriptionStatus.php
├── Accounting/
│   ├── Models/
│   │   ├── Invoice.php
│   │   ├── Payment.php
│   │   ├── LedgerEntry.php
│   │   └── Folio.php
│   ├── Services/
│   │   ├── InvoiceService.php
│   │   └── PaymentService.php
│   └── Enums/
│       ├── InvoiceStatus.php
│       └── PaymentMethod.php
└── System/
    ├── Models/
    │   ├── SystemSetting.php
    │   └── ActivityLog.php
    └── Services/
        └── SettingsService.php
```

---

## ✅ What's Already Ready

### From Previous Implementation:
- ✅ Multi-tenancy (HasHotel trait)
- ✅ User management + RBAC
- ✅ Hotel/Property model
- ✅ Room inventory
- ✅ Reservation system
- ✅ Guest management
- ✅ Audit logging (started)

### Reuse Existing Code:
- ✅ `HasHotel` trait → Data isolation
- ✅ `Auditable` trait → Activity tracking
- ✅ Spatie Permission → Access control
- ✅ Sanctum → API authentication

---

## 🚀 Implementation Steps

### Step 1: Subscription Management
```bash
php artisan make:model SubscriptionPlan -m
php artisan make:model TenantSubscription -m
php artisan make:service TenantService
```

### Step 2: Property Settings
```bash
php artisan make:migration add_property_settings_to_hotels_table
php artisan make:model PropertyTax -m
php artisan make:model PropertyAmenity -m
```

### Step 3: Accounting Module
```bash
php artisan make:model Invoice -m
php artisan make:model Payment -m
php artisan make:model LedgerEntry -m
php artisan make:model Folio -m
php artisan make:service InvoiceService
php artisan make:service PaymentService
```

### Step 4: System Settings
```bash
php artisan make:model SystemSetting -m
php artisan make:model ActivityLog -m
php artisan make:service SettingsService
```

---

## 📝 Next Actions

**Ready to implement?** I can:

1. **Start with Subscription Management** - Create plans & tenant subscriptions
2. **Implement Accounting Module** - Invoice, payment, ledger
3. **Enhance Property Settings** - Tax, branding, policies
4. **Create System Settings** - Configuration & security

**Which would you like me to start with?**

---

*Last Updated: March 17, 2026*  
*Phase 1 Focus: Production-Ready MVP*
