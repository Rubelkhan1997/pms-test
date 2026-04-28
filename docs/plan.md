# Codeware PMS — Complete Technical Build Plan

**Version:** 1.0  
**Prepared By:** Engineering Team  
**System:** Multi-Tenant SaaS Hotel Property Management System  
**Stack:** Laravel 13 + Inertia.js v2 + Vue 3 + TypeScript

---

## Table of Contents

1. [Tech Stack](#tech-stack)
2. [Architecture Decisions](#architecture-decisions)
3. [Project Structure](#project-structure)
4. [Phase 0 — Tenancy Infrastructure](#phase-0--tenancy-infrastructure)
5. [Phase 1 — Super Admin Panel](#phase-1--super-admin-panel)
6. [Phase 2 — Tenant Onboarding Wizard](#phase-2--tenant-onboarding-wizard)
7. [Phase 3 — Front Desk & Reservation Engine](#phase-3--front-desk--reservation-engine)
8. [Phase 4 — Guest CRM](#phase-4--guest-crm)
9. [Phase 5 — Cashiering & Folio](#phase-5--cashiering--folio)
10. [Phase 6 — Housekeeping & Maintenance](#phase-6--housekeeping--maintenance)
11. [Phase 7 — Rate & Availability + Channel Manager](#phase-7--rate--availability--channel-manager)
12. [Phase 8 — POS](#phase-8--pos)
13. [Phase 9 — Night Audit](#phase-9--night-audit)
14. [Phase 10 — Reports](#phase-10--reports)
15. [Phase 11 — Settings (Tenant-Level)](#phase-11--settings-tenant-level)
16. [Build Order](#build-order)
17. [Performance Architecture](#performance-architecture)
18. [Global Coding Conventions](#global-coding-conventions)

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend framework | Laravel 13 |
| Frontend bridge | Inertia.js v2 |
| Frontend framework | Vue 3 + TypeScript (`<script setup lang="ts">`) |
| State management | Pinia |
| CSS | Tailwind CSS v4 |
| Primary database | MySQL 8 (DB-per-tenant) |
| Cache & Queue | Redis + Laravel Horizon |
| WebSocket / Realtime | Laravel Reverb |
| Auth | Sanctum (cookie-based for Inertia) |
| Permissions | Spatie Laravel Permission (per tenant DB) |
| Multi-tenancy | Spatie Laravel Multitenancy |
| DTOs | Spatie Laravel Data |
| File storage | Laravel Filesystem (S3-compatible) |
| PDF generation | barryvdh/laravel-dompdf |
| Excel export | maatwebsite/laravel-excel |
| Testing | Pest PHP + Vitest |
| Code style | Laravel Pint (PSR-12) |

---

## Architecture Decisions

### Multi-Tenancy Strategy: DB-per-Tenant

Each tenant (hotel client) gets their own isolated MySQL database. The platform (landlord) has a separate DB for global data.

```
┌─────────────────────────────────────────────────┐
│  Landlord DB  (platform_db)                     │
│  tenants, subscription_plans, subscriptions     │
│  invoices, add_ons, platform_audit_logs         │
└──────────────────────┬──────────────────────────┘
                       │  SwitchTenantDatabaseTask
          ┌────────────┴────────────┐
          ▼                         ▼
┌──────────────────┐       ┌──────────────────┐
│  pms_hotel_a     │  ...  │  pms_hotel_b     │
│  (tenant DB)     │       │  (tenant DB)     │
└──────────────────┘       └──────────────────┘
```

**Domain routing:**
- `admin.pms.com` → Super Admin panel (landlord DB only)
- `hotelname.pms.com` → Tenant PMS (switches to that tenant's DB)

### Application Layers

```
HTTP Request
  → TenantFinder Middleware    (domain → Tenant → switch DB)
    → Route                   (web or api/v1)
      → FormRequest            (validation only)
        → Controller           (thin — calls service, returns response)
          → Service            (business logic, orchestrates actions)
            → Action           (single-responsibility unit of work)
              → Model          (Eloquent — data access only, no logic)
```

### Frontend Data Flow

```
Page (Vue)
  → Composable (useX.ts)      (reactive state + methods exposed to page)
    → Pinia Store             (global state + API calls)
      → apiClient.ts          (Axios wrapper)
        → API (Laravel)

Utils/Mappers/<module>.ts     (snake_case ↔ camelCase translation layer)
Types/<module>.ts             (TypeScript interfaces)
```

---

## Project Structure

### Backend

```
app/
  Models/
    Tenant.php                    ← extends Spatie\Multitenancy\Models\Tenant
    User.php
  Modules/
    SuperAdmin/                   ← Landlord-level (admin.pms.com only)
      Tenants/
        Controllers/
          Api/V1/TenantController.php
          Web/TenantController.php
        Services/TenantService.php
        Actions/
        Data/
        Requests/
        Resources/
      Billing/
      Properties/
      Reports/
      Settings/
    Auth/
    FrontDesk/                    ← Reservations, Rooms, Stay View, Tape Chart
    Guest/                        ← Golden Profile, VIP, Blacklist, Lost & Found
    Cashiering/                   ← Folio, Payments, Charges, Invoices
    Housekeeping/                 ← House Status, HK Assignment
    Maintenance/                  ← OOO/OOS Room Blocks, Requests
    Laundry/
    RateAvailability/             ← Rate Plans, OBP, Policies, Restrictions
    ChannelManager/
    POS/
    NightAudit/
    Reports/
    Settings/                     ← Tenant-level settings
  Enums/
    ReservationStatus.php
    RoomStatus.php
    HousekeepingStatus.php
    FolioItemType.php
    SubscriptionStatus.php
    PropertyType.php
    RoomType.php
    MealPlan.php
    MarketSegment.php
    BookingSource.php
  Tenancy/
    TenantFinder.php              ← domain-based lookup
  Http/
    Middleware/
      NeedsTenant.php             ← resolves tenant, switches DB
      SuperAdminOnly.php          ← blocks non-landlord domain
      EnsurePropertyOnboarded.php ← redirects to wizard if 0 properties
      EnsureSubscriptionActive.php ← blocks suspended tenants

database/
  migrations/
    landlord/                     ← run once on platform DB
      create_tenants_table.php
      create_subscription_plans_table.php
      create_subscriptions_table.php
      create_invoices_table.php
      create_add_ons_table.php
      create_plan_add_ons_table.php
    tenant/                       ← run per-tenant on provisioning
      (all feature migrations go here)
```

### Frontend

```
resources/js/
  Pages/
    SuperAdmin/
    Auth/
    Dashboard/
    FrontDesk/
      StayView/
      Reservation/
      Arrivals/
      InHouse/
      Departures/
      RoomCalendar/
      RoomMoves/
      RoomBlocks/
    Guest/
    Cashiering/
    Housekeeping/
    Maintenance/
    Laundry/
    RateAvailability/
    ChannelManager/
    POS/
    NightAudit/
    Reports/
    Settings/
    Onboarding/
  Layouts/
    SuperAdminLayout.vue
    AppLayout.vue                 ← tenant PMS layout
    OnboardingLayout.vue
  Stores/                        ← mirror Pages structure
  Composables/                   ← mirror Pages structure
  Types/                         ← mirror Pages structure
  Utils/
    Mappers/                     ← one file per module
```

---

## Phase 0 — Tenancy Infrastructure

> **This phase is a hard blocker. Nothing else can be built correctly until tenant DB switching works end-to-end.**

### 0.1 Custom Tenant Model

**File:** `app/Models/Tenant.php` — extends `Spatie\Multitenancy\Models\Tenant`

**Landlord `tenants` table:**

| Column | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| name | VARCHAR | Hotel client name |
| slug | VARCHAR unique | URL-safe identifier |
| domain | VARCHAR unique | e.g. `marriott.pms.com` |
| database | VARCHAR unique | e.g. `pms_marriott` |
| status | ENUM | `pending\|active\|trial\|suspended\|cancelled` |
| trial_ends_at | TIMESTAMP nullable | |
| plan_id | FK → subscription_plans | |
| contact_name | VARCHAR | |
| contact_email | VARCHAR | |
| contact_phone | VARCHAR | |
| email_verified_at | TIMESTAMP nullable | |
| created_at / updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP nullable | Soft delete |

### 0.2 Wire Multitenancy Config

**`config/multitenancy.php`:**
- `tenant_finder` → `App\Tenancy\TenantFinder::class`
- `switch_tenant_tasks` → enable `SwitchTenantDatabaseTask::class`
- `tenant_model` → `App\Models\Tenant::class`
- `landlord_database_connection_name` → `'landlord'`

**`config/database.php`:**
- Add `landlord` connection pointing to `platform_db`
- Default `mysql` connection remains (switched dynamically per request)

### 0.3 Tenant Finder

**File:** `app/Tenancy/TenantFinder.php`

Reads `$request->getHost()`, queries landlord DB for `Tenant::where('domain', $host)->first()`. Returns `null` if not found (middleware handles 404).

### 0.4 Middleware Stack

| Middleware | Purpose |
|---|---|
| `NeedsTenant` | Resolves domain → switches DB → aborts 404 if unknown |
| `SuperAdminOnly` | Allows only requests from the landlord domain |
| `EnsurePropertyOnboarded` | Redirects to `/onboarding` if tenant has 0 properties |
| `EnsureSubscriptionActive` | Blocks access if tenant `status = suspended` |

### 0.5 Artisan Provisioning Commands

```bash
# Create a new tenant (runs automatically on Super Admin tenant creation)
php artisan tenant:create {name} {domain}
  # 1. Creates tenant record in landlord DB
  # 2. Creates new MySQL database (pms_{slug})
  # 3. Runs all database/migrations/tenant/ on new DB
  # 4. Seeds default roles and permissions
  # 5. Sends welcome email with login link

# Run pending migrations on one tenant's DB
php artisan tenant:migrate {domain}

# Run pending migrations across ALL tenant DBs
php artisan tenants:migrate

# Seed a specific tenant
php artisan tenant:seed {domain} --class=RolePermissionSeeder
```

### 0.6 Queue & Reverb Tenant Awareness

- All queued Jobs implement `TenantAware` interface (Spatie provides this)
- Reverb broadcast channels scoped to tenant: `tenant.{tenant_id}.{event}`
- Queue workers run with tenant context automatically restored

### 0.7 Route Groups

```php
// routes/web.php
Route::domain(config('app.admin_domain'))->middleware('super.admin.only')->group(
    base_path('routes/super-admin.php')
);

Route::middleware(['needs.tenant', 'ensure.subscription.active'])->group(
    base_path('routes/tenant.php')
);
```

---

## Phase 1 — Super Admin Panel

**Module:** `app/Modules/SuperAdmin/`  
**Access:** `admin.pms.com` only  
**Middleware:** `SuperAdminOnly`  
**Layout:** `SuperAdminLayout.vue`

### 1.1 Dashboard

**Widgets:**
- Total Active Tenants
- Total Active Properties
- Monthly Recurring Revenue (MRR)
- Pending Invoices count + amount
- Trial Expiry Alerts (tenants with `trial_ends_at` < 7 days)
- Recent Platform Activities feed

### 1.2 Tenant Management

**Landlord tables:**

```sql
-- tenant_activity_logs
id, tenant_id, action VARCHAR, performed_by (FK users),
metadata JSON, ip_address, created_at
```

**Subpages:**
- **Tenant List** — filterable by status, plan, country; sortable; paginated
- **Create Tenant (Path A — Self-Service)**
  - Public registration form: Company Name, Contact Person, Email, Mobile, Password, Country
  - System generates `tenant_id`, sends activation email, status = `pending_activation`
  - On email verify → redirect to Plan Selection → trigger onboarding
- **Create Tenant (Path B — Admin-Assisted)**
  - Admin creates tenant + assigns plan in same session
  - Marks email verified automatically, status = `active`
  - Sends Welcome Email with secure login link / temp password
- **Tenant Profile** — view/edit contact, subscription, status
- **Suspend / Activate** toggle with reason
- **Impersonate** — redirect to tenant's domain as super admin
- **Subscription Management** sub-page
- **Billing History** sub-page
- **Activity Log** sub-page

### 1.3 Subscription Plans & Billing

**Landlord tables:**

```sql
-- subscription_plans
id, name VARCHAR (Starter|Pro|Enterprise),
slug VARCHAR unique,
property_limit INT,
room_limit INT,
base_price_monthly DECIMAL(10,2),
base_price_annual DECIMAL(10,2),
trial_enabled BOOL,
trial_days INT,
modules_included JSON,           -- ["pms","housekeeping","pos"]
is_active BOOL,
created_at, updated_at

-- add_ons
id, name VARCHAR, slug VARCHAR unique, description TEXT,
pricing_type ENUM(fixed|per_property|per_room),
price DECIMAL(10,2),
billing_cycle ENUM(inherit|monthly|annual),
is_mandatory BOOL,
requires_activation BOOL,
dependencies JSON,               -- ["pos"] means requires POS add-on
is_active BOOL

-- plan_add_ons (pivot)
plan_id FK, add_on_id FK

-- subscriptions
id, tenant_id FK, plan_id FK,
billing_cycle ENUM(monthly|annual),
status ENUM(trial|active|past_due|cancelled),
trial_ends_at TIMESTAMP,
current_period_start DATE,
current_period_end DATE,
property_count INT,              -- current usage
room_count INT,                  -- current usage
add_ons JSON,                    -- snapshot of subscribed add-ons
next_invoice_date DATE

-- invoices (landlord DB)
id, tenant_id FK, subscription_id FK,
amount DECIMAL(10,2),
tax_amount DECIMAL(10,2),
total_amount DECIMAL(10,2),
currency CHAR(3),
status ENUM(draft|sent|paid|overdue),
due_date DATE,
paid_at TIMESTAMP nullable,
notes TEXT
```

**Subpages:**
- Plans CRUD (name, limits, pricing, trial config, modules)
- Add-ons CRUD (pricing model, dependency rules, plan assignments)
- Trial Configuration per plan
- Assign Plan to Tenant form
- Generate Invoice
- Record Payment
- Billing History per tenant

### 1.4 Properties (Global Master Data)

Read-only cross-tenant property list. Also manages global master data used by all tenants:

- Property Categories
- Amenity Groups + Amenities
- Breakfast Plans
- Bed Types
- Room Type Templates
- Room Sub-Types

### 1.5 Reports (Platform Level)

- Tenant Report — active, trial, churned counts by plan
- Revenue Report — MRR, ARR, revenue by plan, by period
- Subscription Report — plan distribution, churn rate
- Platform Audit Logs — who did what, when

### 1.6 Settings (Global)

- Roles & Permissions (for super admin staff only)
- Tax & Currency (global defaults propagated to new tenants)
- OTA API Credentials (global keys for channel manager)
- Email/SMS Templates (platform-wide transactional templates)
- Maintenance Tools (cache clear, DB health check, queue monitor)

---

## Phase 2 — Tenant Onboarding Wizard

**Spec ref:** §2.1–2.4

**Guard:** `EnsurePropertyOnboarded` middleware on all protected tenant routes. If `SELECT COUNT(*) FROM properties WHERE tenant_id = current_tenant` = 0, redirect to `/onboarding`.

### Step 1 — Subscription Guard

On first login:
1. Verify `subscription_status` is `active` or `trial`
2. Check property count
3. If 0 → launch Onboarding Wizard
4. If > 0 → load Main PMS Dashboard

### Step 2 — Property Basic Information

**Tenant `properties` table:**

```sql
id, tenant_id,
name VARCHAR, slug VARCHAR,
type ENUM(hotel|resort|apartment|villa|hostel),
description TEXT, logo_path VARCHAR,
featured_image_path VARCHAR, gallery_paths JSON,
number_of_rooms INT,
country CHAR(2), state VARCHAR, city VARCHAR,
area VARCHAR, street VARCHAR, postal_code VARCHAR,
latitude DECIMAL(10,8), longitude DECIMAL(11,8),
phone VARCHAR, email VARCHAR,
timezone VARCHAR,                -- auto-detected from country
currency CHAR(3),                -- auto-detected from country
check_in_time TIME,              -- default 14:00
check_out_time TIME,             -- default 12:00
child_policy JSON,               -- {allowed: true, max_age: 12, free_under: 5}
pet_policy JSON,                 -- {allowed: false, charge: 0}
status ENUM(open|closed),
business_date DATE,              -- current audit business date
created_at, updated_at
```

**UI:** Multi-step form. Country selection auto-suggests Timezone and Currency. Subscription guard checks `property_count >= plan_limit` before saving.

### Step 3 — Room Types & Physical Room Inventory

**Spec ref:** §2.3

**Tenant tables:**

```sql
-- room_types (parent template / blueprint)
id, property_id,
name VARCHAR,                    -- "Deluxe King"
code VARCHAR,                    -- "DKNG" (unique per property)
type ENUM(room|suite|cottage|villa|dormitory),
sub_type_id FK nullable,
floor VARCHAR,                   -- default floor for this type (e.g. "1", "G", "Penthouse")
max_occupancy INT,
adult_occupancy INT,
num_bedrooms INT,
num_bathrooms INT,
area_sqm DECIMAL(8,2),
bed_types JSON,                  -- multi-select: ["King Size","Sofa Bed"]
base_rate DECIMAL(10,2),
amenities JSON,
gallery_paths JSON,
is_active BOOL

-- rooms (physical inventory — child records)
id, property_id, room_type_id FK,
identifier VARCHAR,              -- "101", "Cottage-3" (unique per property)
floor VARCHAR,                   -- inherited from room_type, overridable
view_types JSON,                 -- ["Beach View","Pool View"]
num_bedrooms INT,                -- inherits from type, overridable
num_bathrooms INT,
area_sqm DECIMAL(8,2),
bed_types JSON,                  -- overrides type-level if set
status ENUM(vacant_clean|vacant_dirty|occupied|out_of_order|out_of_service),
hk_status ENUM(clean|dirty|hk_assigned|inspected|blocked),
usable BOOL DEFAULT true,        -- false = blocked from sale
notes TEXT,
INDEX(property_id, status),
INDEX(property_id, room_type_id)
```

**Inventory Generation Logic:**
- User creates Room Type (blueprint)
- Enters total room count + numbering mode (Auto or Manual)
- **Auto:** System generates sequential identifiers (101, 102, 103...) linked to `room_type_id`
- **Manual:** User provides list of identifiers; system validates uniqueness
- Subscription guard: `room_count + new_count <= plan_room_limit`

### Step 4 — Rate Plans & Policies

**Spec ref:** §2.4

**Tenant tables:**

```sql
-- cancellation_policies
id, property_id,
name VARCHAR,                    -- "Flexible 24h", "Non-Refundable"
deadline_days INT,               -- cancel X days before arrival for no penalty
penalty_type ENUM(none|first_night|percentage|full_stay),
penalty_value DECIMAL(8,2),
no_show_penalty_type ENUM(none|first_night|full_stay|fixed),
no_show_penalty_value DECIMAL(8,2)

-- pricing_profiles (OBP — Occupancy Based Pricing)
id, property_id,
name VARCHAR,
standard_occupancy INT,          -- base: 2 adults
single_discount_type ENUM(fixed|percentage),
single_discount_value DECIMAL(8,2),
extra_adult_fee DECIMAL(8,2),
child_buckets JSON               -- [{min_age:0,max_age:5,fee:0},{min_age:6,max_age:12,fee:10}]

-- rate_plans
id, property_id,
name VARCHAR, code VARCHAR,
type ENUM(base|derived),
meal_plan ENUM(RO|BB|HB|FB|AI), -- Room Only, Bed&Breakfast, Half Board, Full Board, All Inclusive
cancellation_policy_id FK,
pricing_profile_id FK,
parent_rate_plan_id FK nullable, -- for derived rates
derived_modifier_type ENUM(plus|minus) nullable,
derived_modifier_value DECIMAL(8,2) nullable,
release_period_days INT DEFAULT 0,
is_active BOOL,
INDEX(property_id, is_active)

-- rate_restrictions (calendar overlay)
id, property_id, rate_plan_id FK, room_type_id FK,
date DATE,
min_stay INT DEFAULT 1,
max_stay INT nullable,
closed_to_arrival BOOL DEFAULT false,
closed_to_departure BOOL DEFAULT false,
stop_sell BOOL DEFAULT false,
UNIQUE(property_id, date, rate_plan_id, room_type_id),
INDEX(property_id, date)
```

**Derived Rate Logic:** If `type = derived`, system calculates `Derived Price = Parent Rate ± modifier_value`. Auto-recalculates when parent rate is updated.

**Update Propagation:** If a Policy is updated, all linked Rate Plans inherit the updated rules automatically.

---

## Phase 3 — Front Desk & Reservation Engine

**Spec ref:** §4.0 — core operational hub

### 3.1 Database Design

```sql
-- reservations
id CHAR(36) PK (UUID),
property_id FK,
booking_reference VARCHAR UNIQUE,  -- "CW-00001" (auto-increment padded)
room_type_id FK,
room_id FK nullable,               -- assigned at check-in
rate_plan_id FK,
check_in_date DATE,
check_out_date DATE,
nights INT GENERATED,              -- computed: checkout - checkin
adults INT,
children INT DEFAULT 0,
children_ages JSON,                -- [5, 8] for OBP child fee calc
guest_id FK,                       -- primary guest (mandatory, search-first)
source ENUM(walk_in|phone|website|ota|agent),
market_segment ENUM(leisure|corporate|group|government|airline),
agent_id FK nullable,
company_id FK nullable,
status ENUM(confirmed|waiting_confirmation|checked_in|checked_out|no_show|cancelled),
policy_snapshot JSON,              -- IMMUTABLE copy of cancellation policy at booking time
folio_id FK,                       -- created atomically with reservation
special_requests TEXT,
internal_notes TEXT,
reg_card_printed_at TIMESTAMP,
checked_in_at TIMESTAMP,
checked_out_at TIMESTAMP,
cancelled_at TIMESTAMP,
cancellation_reason TEXT,
cancellation_fee DECIMAL(10,2),
no_show_at TIMESTAMP,
no_show_fee DECIMAL(10,2),
created_by FK (users),
updated_by FK (users),
created_at, updated_at,
INDEX(property_id, check_in_date, status),
INDEX(property_id, room_id, check_in_date)

-- reservation_guests (supports multi-guest bookings)
id, reservation_id FK, guest_id FK,
is_primary BOOL DEFAULT false

-- reservation_audit_log (immutable)
id, reservation_id FK,
action VARCHAR,                    -- "status_changed", "date_modified", "room_assigned"
user_id FK,
from_state JSON,                   -- before snapshot
to_state JSON,                     -- after snapshot
changed_fields JSON,               -- {field: {before: X, after: Y}}
ip_address VARCHAR,
created_at

-- room_blocks
id, property_id FK, room_id FK,
type ENUM(maintenance|deep_clean|renovation|vip_hold|inspection),
from_date DATE, to_date DATE,
reason TEXT,
created_by FK (users),
created_at, updated_at
```

### 3.2 Booking Engine Logic

**Availability Check (ACID-safe):**
```
BEGIN TRANSACTION;
  SELECT id FROM rooms
    WHERE property_id = ? AND room_type_id = ?
    AND id NOT IN (
      SELECT room_id FROM reservations
      WHERE property_id = ?
        AND status NOT IN ('cancelled', 'no_show', 'checked_out')
        AND check_in_date < :checkout AND check_out_date > :checkin
    )
    AND id NOT IN (
      SELECT room_id FROM room_blocks
      WHERE from_date <= :checkout AND to_date >= :checkin
    )
  FOR UPDATE;
COMMIT;
```

**Pricing Execution:**
```
Total = Σ per night:
  (base_rate
    ± single_occupancy_discount (if adults = 1)
    + extra_adult_fee × max(0, adults - standard_occupancy)
    + Σ child_fees by age bracket
  )
  × nights
  + tax
```

**Policy Snapshot:** At save time, serialize current `cancellation_policy` record as JSON into `reservations.policy_snapshot`. This makes the agreement legally binding even if policy changes later.

**Folio Initialization:** Atomically create `folios` record in same DB transaction as reservation. No folio = rollback and reject booking.

**Booking Reference:** Format `CW-` + zero-padded auto-increment per property (e.g., `CW-00001`).

### 3.3 Reservation Status Machine

```
                  ┌──────────────────────┐
                  │  Waiting Confirmation │ ← OTA / bank transfer bookings
                  └──────────┬───────────┘
                             │ Admin confirms
                  ┌──────────▼───────────┐
    Walk-in ────► │      Confirmed        │
                  └──────────┬───────────┘
                             │ Check-In (arrival_date, ID captured)
                  ┌──────────▼───────────┐
                  │      Checked-In       │
                  └──────────┬───────────┘
                             │ Check-Out
                  ┌──────────▼───────────┐
                  │     Checked-Out       │
                  └──────────────────────┘

  Confirmed ────► Cancelled    (calc penalty from policy_snapshot → post to folio)
  Confirmed ────► No-Show      (Night Audit triggers → post no-show fee to folio)
```

### 3.4 Reservation Actions

| Action | Condition | System Effect |
|---|---|---|
| **Check-In** | `status = confirmed`, date = arrival_date | Room → `occupied`, Folio → `active`, capture ID, log audit |
| **Check-Out** | `status = checked_in` | Room → `vacant_dirty`, Folio closed, update guest lifetime stats |
| **Cancel** | any pre-checkin status | Calc penalty from `policy_snapshot`, post charge to folio, release room |
| **No-Show** | Night Audit run, guest not arrived | Post no-show fee, set `status = no_show`, release inventory |
| **Resend Email** | any | Re-trigger SMTP, log "Communication Sent" in audit trail |
| **Print Reg Card** | any | Generate PDF (DomPDF), log `reg_card_printed_at` |
| **Room Move** | `status = checked_in` | Update `room_id`, post any rate difference to folio, log in audit |

### 3.5 Front Desk Pages

- **Stay View (Tape Chart):** Room × Date grid. Colour-coded by status (Confirmed, Checked-In, Blocked). Drag-to-move reservation. Real-time updates via Reverb WebSocket.
- **Reservations:** Full CRUD list with filters (status, date range, source, room type, guest name)
- **Arrivals:** Today's expected arrivals (check_in_date = business_date), quick check-in CTA
- **In-House:** All currently checked-in guests, late departure alerts
- **Departures:** Today's expected checkouts (check_out_date = business_date), quick checkout CTA
- **Room Calendar:** Date-based availability view per room type (availability count + rate)
- **Room Moves:** Select checked-in reservation → choose new room → confirm
- **Room Blocks:** Create / manage OOO / OOS blocks with date range

---

## Phase 4 — Guest CRM

**Spec ref:** §4.0 — Golden Profile

### Database

```sql
-- guest_profiles
id, property_id,
first_name VARCHAR, last_name VARCHAR,
gender ENUM(male|female|other|prefer_not_to_say),
date_of_birth DATE nullable,
nationality CHAR(2),
email VARCHAR,                   -- indexed, deduplication check
phone VARCHAR,                   -- indexed, deduplication check
address TEXT, postal_code VARCHAR,
id_type ENUM(passport|nid|driving_license|other),
id_number VARCHAR,               -- AES-256 ENCRYPTED at rest
id_issue_country CHAR(2),
id_expiry_date DATE,
id_document_scan_path VARCHAR,   -- encrypted storage path
bed_preference ENUM(king|twin|single|any),
smoking_preference ENUM(smoking|non_smoking|any),
floor_preference VARCHAR,
allergies JSON,                  -- ["feathers","shellfish"]
dietary_notes TEXT,
is_vip BOOL DEFAULT false,
vip_level ENUM(silver|gold|platinum) nullable,
vip_notes TEXT,
is_blacklisted BOOL DEFAULT false,
blacklist_reason TEXT,
total_stays INT DEFAULT 0,       -- updated async on checkout
total_revenue DECIMAL(12,2) DEFAULT 0,
last_stay_date DATE,
created_by FK (users),
created_at, updated_at, deleted_at,
INDEX(property_id, email),
INDEX(property_id, phone)

-- guest_notes
id, guest_id FK, note TEXT,
category ENUM(preference|complaint|compliment|general),
created_by FK (users), created_at

-- lost_found_items
id, property_id FK,
guest_id FK nullable,
room_id FK nullable,
item_description TEXT,
found_location VARCHAR,
found_date DATE,
found_by FK (users),
status ENUM(found|claimed|returned|disposed|donated),
claimed_by VARCHAR,              -- name of person who claimed
claimed_at TIMESTAMP,
image_paths JSON,
notes TEXT
```

### Business Logic

- **Deduplication Engine:** On guest create, query `email + phone + id_number`. If match found → prompt user: Merge (consolidate records) or Use Existing
- **PII Encryption:** `id_number` uses Laravel `Encrypted` cast. Decryption access is logged.
- **GDPR Right to Forget:** Anonymize `first_name, last_name, email, phone, id_number` to `[ANONYMIZED]`. Preserve all financial records (folio items) for accounting integrity.
- **Preference Injection:** On new reservation creation for known `guest_id`, auto-populate `special_requests` with guest preferences (allergies, bed type, floor)
- **Stay History:** Async update of `total_stays`, `total_revenue`, `last_stay_date` triggered by checkout event via Observer

### Pages

- **Guest Database** — searchable, filterable, VIP/Blacklist badges
- **Guest Profile** — full details, stay history timeline, notes
- **Create / Edit Guest** — with deduplication prompt
- **VIP Guests** — filtered view by `is_vip = true`
- **Blacklisted Guests** — filtered view, reason displayed
- **Lost & Found** — board view by status

---

## Phase 5 — Cashiering & Folio

### Database

```sql
-- folios
id, reservation_id FK, property_id FK, guest_id FK,
status ENUM(open|closed|voided),
currency CHAR(3),
exchange_rate DECIMAL(10,6) DEFAULT 1,
total_charges DECIMAL(12,2) DEFAULT 0,
total_payments DECIMAL(12,2) DEFAULT 0,
balance_due DECIMAL(12,2) GENERATED,  -- total_charges - total_payments
opened_at TIMESTAMP,
closed_at TIMESTAMP,
closed_by FK (users)

-- folio_items
id, folio_id FK, property_id FK,
type ENUM(room_charge|extra_charge|payment|discount|tax|refund),
unit_type ENUM(per_room|per_adult|per_child|per_item|lump_sum),
description VARCHAR,
quantity DECIMAL(8,2) DEFAULT 1,
unit_rate DECIMAL(10,2),
amount DECIMAL(10,2),
tax_code VARCHAR nullable,
tax_amount DECIMAL(10,2) DEFAULT 0,
posted_by FK (users),
posted_at TIMESTAMP,
is_voided BOOL DEFAULT false,
voided_by FK (users) nullable,
voided_at TIMESTAMP nullable,
voided_reason VARCHAR nullable,
routed_to ENUM(guest_folio|city_ledger|company) DEFAULT guest_folio,
routed_to_id BIGINT nullable,    -- company_id or agent_id
reference_type VARCHAR nullable, -- polymorphic: "pos_order", "maintenance", etc.
reference_id BIGINT nullable,
INDEX(folio_id, type),
INDEX(folio_id, posted_at)

-- cashier_sessions
id, property_id FK, user_id FK,
opened_at TIMESTAMP,
closed_at TIMESTAMP nullable,
opening_balance DECIMAL(12,2),
closing_balance DECIMAL(12,2) nullable,
expected_balance DECIMAL(12,2) nullable,
status ENUM(open|closed)

-- cashier_transactions
id, cashier_session_id FK, folio_item_id FK,
type VARCHAR, amount DECIMAL(10,2), created_at

-- expense_vouchers
id, property_id FK, cashier_session_id FK,
category ENUM(petty_cash|maintenance|admin|food|transport|other),
description TEXT,
amount DECIMAL(10,2),
approved_by FK (users) nullable,
receipt_path VARCHAR nullable,
created_by FK (users), created_at

-- invoices (tenant DB)
id, property_id FK,
reservation_id FK nullable,
invoice_type ENUM(guest|company|vendor),
invoice_number VARCHAR unique,
to_name VARCHAR, to_address TEXT, to_email VARCHAR,
line_items JSON,
subtotal DECIMAL(12,2),
tax_amount DECIMAL(12,2),
total DECIMAL(12,2),
currency CHAR(3),
status ENUM(draft|sent|paid|overdue),
due_date DATE, paid_at TIMESTAMP,
notes TEXT, created_at, updated_at

-- exchange_rates
id, property_id FK,
from_currency CHAR(3), to_currency CHAR(3),
rate DECIMAL(14,6),
effective_date DATE,
UNIQUE(property_id, from_currency, to_currency, effective_date)
```

### Pages

- **Cashiering Center** — dashboard: pending balances, cashier totals, open sessions, quick post payment/charge
- **Open Folios** — paginated list with `balance_due`, colour-coded by overdue status
- **Folio Detail** — line-item view, inline post payment/charge, void item, route to city ledger
- **Post Payment** — form: cash, card, bank transfer, advance, cryptocurrency (future)
- **Post Charges** — form: select charge type, amount, quantity, link to folio
- **Refunds** — refund against specific folio item or full payment
- **Cashier Transactions** — log: filter by type, date, cashier
- **Cashier Balances** — opening/closing/expected per session
- **Cashier Transfer** — shift handover: transfer session to another cashier
- **Direct Sales** — non-reservation sales (walk-in restaurant, spa retail)
- **Expense Voucher** — petty cash recording with receipt upload
- **Invoices** — tabbed: Guest / Company / Vendor
- **Exchange Rates** — daily rate management

---

## Phase 6 — Housekeeping & Maintenance

### Database

```sql
-- hk_assignments
id, property_id FK, room_id FK,
assigned_to FK (users),
assigned_date DATE,
priority ENUM(normal|urgent|vip|checkout),
status ENUM(assigned|in_progress|completed|verified|skipped),
started_at TIMESTAMP, completed_at TIMESTAMP,
verified_by FK (users) nullable,
notes TEXT

-- maintenance_requests
id, property_id FK, room_id FK nullable,
category ENUM(electrical|plumbing|hvac|furniture|it|pest_control|other),
title VARCHAR, description TEXT,
priority ENUM(low|medium|high|critical),
status ENUM(open|in_progress|resolved|closed|deferred),
reported_by FK (users),
assigned_to FK (users) nullable,
opened_at TIMESTAMP,
target_resolution_date DATE nullable,
resolved_at TIMESTAMP nullable,
resolution_notes TEXT,
images JSON

-- laundry_items
id, property_id FK,
room_id FK nullable,
reservation_id FK nullable,
guest_id FK nullable,
item_type VARCHAR,               -- "bath towel", "bed sheet", "shirt"
quantity INT,
status ENUM(collected|in_laundry|cleaned|returned|damaged|lost),
collected_at TIMESTAMP,
expected_return_at TIMESTAMP nullable,
returned_at TIMESTAMP nullable,
damage_notes TEXT,
collected_by FK (users)
```

### Automated Triggers

- `Checkout event → room.hk_status = dirty` (via `ReservationObserver`)
- `HK completes room → hk_status = clean → broadcast via Reverb → Tape Chart updates`
- `Night Audit → generate HK assignments for all occupied rooms expected to checkout next day`

### Pages

- **House Status Board** — room grid colour-coded by `hk_status`. Filter by floor, room type, staff. Quick status update inline.
- **HK Assignments** — assign rooms to housekeepers by date. Drag-and-drop interface.
- **Maintenance Requests** — Kanban view (Open → In Progress → Resolved) or list view
- **Room Blocks** — Create OOO / OOS blocks (also accessible from Front Desk)
- **Laundry Tracking** — board view by status: Collected → In Laundry → Cleaned → Returned

---

## Phase 7 — Rate & Availability + Channel Manager

### Rate & Availability Pages

- **Rate Plans** — list + full CRUD (linked to Policies + OBP Profiles from Settings)
- **Packages & Promotions** — discount codes, date-limited specials, value-adds
- **Availability Calendar** — grid: room type × 31 days. Each cell shows: available units count + rate. Click cell to modify. Bulk edit mode.
- **Restrictions Grid** — same 31-day grid layout. Toggle MLOS / Max Stay / CTA / CTD / Stop Sell per cell. Bulk set by date range.
- **Stop Sell** — quick form: select room type(s) + date range → set `stop_sell = true`

### Channel Manager Tables

```sql
-- ota_connections
id, property_id FK,
ota_name ENUM(expedia|booking_com|agoda|airbnb|custom),
api_key VARCHAR,                 -- AES-256 encrypted
api_secret VARCHAR,              -- AES-256 encrypted
hotel_id_on_ota VARCHAR,
status ENUM(active|inactive|error),
last_sync_at TIMESTAMP,
last_error TEXT nullable

-- ota_booking_logs
id, property_id FK, ota_connection_id FK,
booking_ref_ota VARCHAR,
action ENUM(new|modify|cancel),
payload JSON,                    -- raw incoming OTA payload
status ENUM(pending|processed|failed),
reservation_id FK nullable,      -- created reservation if processed
error_message TEXT nullable,
created_at
```

**Channel Manager Pages:**
- OTA Connections management (API credentials, hotel ID mapping)
- Manual Inventory Push → select dates + room types → push to connected OTAs
- Manual Rate Push → select rate plans → push rates to OTAs
- Booking Logs — incoming OTA bookings, processing status, link to created reservation
- Auto-sync runs as background queued jobs via Laravel Horizon (retry on failure)

---

## Phase 8 — POS

### Database

```sql
-- pos_outlets
id, property_id FK,
name VARCHAR,
type ENUM(restaurant|bar|spa|gym|laundry|minibar|other),
is_active BOOL

-- pos_orders
id, property_id FK, outlet_id FK,
order_number VARCHAR unique,
status ENUM(open|posted|cancelled),
reservation_id FK nullable,      -- NULL for direct sales
room_id FK nullable,
guest_id FK nullable,
subtotal DECIMAL(10,2),
tax DECIMAL(10,2),
discount DECIMAL(10,2) DEFAULT 0,
total DECIMAL(10,2),
notes TEXT,
created_by FK (users),
created_at, updated_at

-- pos_order_items
id, pos_order_id FK,
item_name VARCHAR,
item_code VARCHAR nullable,
quantity DECIMAL(8,2),
unit_price DECIMAL(10,2),
discount DECIMAL(10,2) DEFAULT 0,
amount DECIMAL(10,2),
notes VARCHAR nullable
```

**Post to Folio Logic:** When "Post to Folio" is triggered, system:
1. Validates reservation is `checked_in`
2. Creates `folio_item` with `type = extra_charge`, `reference_type = pos_order`, `reference_id = pos_order.id`
3. Updates `folio.total_charges` and `balance_due`
4. Sets `pos_order.status = posted`
5. Broadcasts folio update to Reverb

**Pages:**
- Outlet Management (Settings-level)
- POS Orders — create order, add items, link to room/guest
- Post to Folio — select open order, confirm post
- Outlet Summary — daily sales by outlet

---

## Phase 9 — Night Audit

> Night Audit is a **sequential, transactional, irreversible** end-of-day process. It advances the `business_date` and must complete atomically.

### Audit Steps (enforced order)

1. **Pre-Audit Checklist** — surface unresolved items:
   - Open folios with `balance_due > 0`
   - Pending arrivals (status = `confirmed`, check_in_date = business_date)
   - Pending departures (status = `checked_in`, check_out_date = business_date)
   - Admin must acknowledge each or they must be resolved before proceeding

2. **Post Room Charges** — for every `checked_in` reservation, append one `folio_item` of type `room_charge` for `business_date` using the rate plan pricing formula

3. **Process No-Shows** — for `confirmed` reservations where `check_in_date = business_date` and not yet checked in: set `status = no_show`, calculate and post no-show fee from `policy_snapshot`

4. **Advance Business Date** — `UPDATE properties SET business_date = business_date + 1 WHERE id = ?`

5. **Generate Audit Record** — immutable snapshot saved to `night_audit_logs`

### Database

```sql
-- night_audit_logs (immutable — no updates after creation)
id, property_id FK,
business_date DATE,
run_by FK (users),
run_at TIMESTAMP,
rooms_charged INT,
no_shows_processed INT,
total_room_revenue DECIMAL(12,2),
total_extra_revenue DECIMAL(12,2),
total_payments_received DECIMAL(12,2),
total_outstanding_balance DECIMAL(12,2),
details JSON,                    -- full audit breakdown
status ENUM(completed|failed),
error_message TEXT nullable
```

**Night Audit is irreversible.** Can only be rolled back by Super Admin with a manual DB-level operation (logged in platform audit).

**Pages:**
- Run Night Audit — pre-audit checklist → step-by-step progress → completion summary
- Audit Review — view current night audit progress/status
- No-Show Processing — list of no-shows processed
- Audit Logs — history of all completed night audits per business_date

---

## Phase 10 — Reports

All reports are tenant-scoped, generated from tenant DB, cached in Redis with configurable TTL.

| Report | Key Metrics | Cache TTL |
|---|---|---|
| **Occupancy Report** | Arrivals, Departures, In-House, Occupancy %, RevPAR, ADR | 15 min |
| **Revenue Report** | Room revenue, Extra charges, Payments, Outstanding, By market segment | 15 min |
| **Reservation Report** | Bookings by source, Cancellations, No-Shows, Lead time | 30 min |
| **Cashier Report** | Collections by payment method, Refunds, Shift summaries, Cashier totals | 5 min |
| **Housekeeping Report** | Rooms cleaned, Assigned, Pending, Avg clean time, Staff performance | 30 min |
| **Guest Report** | New guests, Repeat guests, VIP count, Nationality breakdown | 1 hour |
| **Night Audit Report** | Business date history, Daily revenue posting, Discrepancies | Permanent |

**Export formats:** PDF (DomPDF) + Excel (Laravel Excel) for all reports. Generated as queued jobs for large date ranges.

---

## Phase 11 — Settings (Tenant-Level)

All settings are scoped to the current property/tenant.

| Setting | Description |
|---|---|
| **Users & Roles** | Create users, assign Spatie roles (Front Desk, Cashier, Housekeeping, etc.) |
| **Room Types** | Manage property's room type blueprints (from onboarding wizard, editable here) |
| **Amenities** | Enable/configure amenities from global master list; add property-specific ones |
| **Bed Types** | Manage bed type options for room types |
| **Taxes** | Create tax codes (VAT 15%, Service Charge 10%), assign to folio item types |
| **Payment Methods** | Cash, Visa, Mastercard, Amex, Bank Transfer, Mobile Banking |
| **Email Templates** | Booking Confirmation, Check-in, Check-out, Invoice, No-Show, Cancellation |
| **Policies** | Cancellation policies and No-Show policies library (referenced by rate plans) |
| **Travel Agents** | Agent profiles: name, contact, commission rate, payment terms |
| **Business Sources** | Market segment tracking: Walk-in, Website, Corporate, OTA, Group |
| **Company Database** | Corporate client profiles for direct billing to company account |
| **Property Profile** | Edit property basic info, logo, policies set during onboarding |

---

## Build Order

```
Phase 0 → Tenancy Infrastructure          HARD BLOCKER — must validate before proceeding
Phase 1 → Super Admin Panel               Platform management before any tenants exist
Phase 2 → Tenant Onboarding Wizard        First thing a new tenant sees
Phase 3 → Front Desk & Reservation Engine Core PMS value
Phase 4 → Guest CRM                       Depends on Phase 3 (guest_id FK on reservations)
Phase 5 → Cashiering & Folio              Depends on Phase 3 (folio_id FK on reservations)
Phase 6 → Housekeeping & Maintenance      Depends on Phase 3 (room status from checkout)
Phase 7 → Rate & Availability             Builds on Phase 2 rate plan foundation
Phase 8 → POS                             Depends on Phase 5 (post to folio)
Phase 9 → Night Audit                     Depends on Phase 3 + 5 + 6
Phase 10 → Reports                        Depends on all data-producing phases
Phase 11 → Settings                       Can be built in parallel with Phase 3+
```

---

## Performance Architecture

| Concern | Solution |
|---|---|
| **Double-booking prevention** | `SELECT ... FOR UPDATE` row-level lock inside a DB transaction |
| **Tape Chart real-time updates** | Reverb WebSocket, channel: `tenant.{id}.tape-chart` |
| **Availability query speed** | Redis cache per property per date range; invalidated on any reservation change |
| **Rate grid (31 days × N room types)** | Pre-computed cache; invalidated on restriction or rate change |
| **Night Audit bulk folio posting** | Batched queued job via Laravel Horizon; progress broadcast via Reverb |
| **OTA channel sync** | Background queued jobs with exponential backoff retry; logged in `ota_booking_logs` |
| **Report generation** | Queued jobs for large ranges; result stored in storage; notify when ready |
| **PII security** | Laravel `Encrypted` cast on `id_number`, scoped decryption, access logged |
| **Tenant data isolation** | DB-per-tenant; no cross-tenant query possible by design |
| **Session/Cache isolation** | Redis keys prefixed with `tenant_{id}_` via `PrefixCacheTask` |
| **Queue isolation** | Tenant ID attached to every queued job via `TenantAware` interface |
| **DB indexes** | All FK columns, all status columns, all date range query columns indexed |

---

## Global Coding Conventions

### PHP / Laravel

- `declare(strict_types=1)` at top of every PHP file
- PHP 8.3 features: `readonly class`, `readonly` properties, `enum`, `match`
- Services: `readonly class`, constructor-injected, never contain Eloquent directly
- Controllers: thin — one call to service, return response. No business logic.
- All validation in `FormRequest` classes. No validation in controllers or services.
- All JSON responses via `ApiResource` classes. No direct `->toArray()` in controllers.
- Observers registered in `AppServiceProvider::boot()`
- Events/Listeners registered in `EventServiceProvider`
- Enums live in `app/Enums/` — never inside module folders
- PSR-12 enforced via Laravel Pint

### API Response Format

Every API endpoint returns:
```json
{
  "status": 1,
  "data": { ... },
  "message": "Reservations fetched successfully"
}
```
`status`: `1` = success, `0` = failure.

### Vue / TypeScript

- All Vue files: `<script setup lang="ts">`
- PascalCase component names, camelCase variables and functions
- All page strings via `useI18n()` `t()` — no hardcoded UI strings
- Permission checks on every protected page via `usePermission()`
- Form pages: `useForm()` + `validateInertiaForm()` — never `reactive()`
- No Ziggy (`route()` helper or `import { route } from 'ziggy-js'`) — hardcoded URL strings only
- Navigation: `router.visit('/path')`
- Form submit: `router.post('/path', data)` / `router.put(...)` / `router.delete(...)`
- Pinia stores: separate `loading`, `loadingList`, `loadingDetail` states

### Routing

- Web routes: Inertia page rendering only (GET). Protected by `auth.token` + `permission:` per route.
- API routes: Versioned under `/api/v1`. Protected by `auth:sanctum`. Prefix per module.
- Tenant routes loaded only when tenant is resolved (in `NeedsTenant` middleware group)

### Reference Files (pattern to match)

| Layer | File |
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

---

*Last updated: April 2026*
