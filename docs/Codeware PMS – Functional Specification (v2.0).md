**Codeware PMS – Functional Requirements Document (FRD)**

Version: 2.0  
Prepared By: Codeware Product Team  
System Type: Multi-Tenant SaaS Hotel Property Management System

# **1\. Document Information**

| Item | Description |
| ----- | ----- |
| Document Name | Codeware PMS – Functional Requirements Document |
| Version | 2.0 |
| Document Type | Functional Requirements Specification |
| Prepared By | Codeware Product & Engineering Team |
| Target Audience | Development Team, QA Engineers, Product Managers, Management |
| System Type | Multi-Tenant SaaS Hotel Property Management System |
| Reference Systems | eZee Absolute PMS, Oracle OPERA PMS, HotelRunner |
| Purpose | Define the functional requirements, module behaviors, workflows, and navigation structure of the Codeware PMS platform for development and testing |
| Scope | Covers Super Admin platform management and Tenant hotel operations including tenant onboarding, subscription billing, property setup, room inventory, pricing, reservations, guest management, housekeeping, maintenance, laundry, POS integration, accounting, reporting, system administration, and settings |

# **2\. System Overview**

**2.1 Overview**

Codeware PMS is a cloud-based multi-tenant hotel property management system designed to centralize and automate hotel operations under a unified SaaS platform.

The system enables hotel operators to manage daily operational activities, including reservations, room inventory, guest services, housekeeping, billing, reporting, and property administration through an integrated web-based application.  
Each hotel tenant operates independently within the platform while the system owner manages subscriptions, platform settings, and tenant access through a centralized Super Admin panel.

The platform supports scalable multi-property operations, role-based access control, financial transaction management, and third-party integrations to improve operational efficiency and service accuracy.

**2.2 User Levels**

**The system supports two operational levels:**  
Super Admin Level  
Responsible for:

* Tenant management  
* Subscription billing  
* Global platform settings  
* Platform reporting

Tenant PMS Level

Responsible for:

* Front desk operations  
* Reservations  
* Guest services  
* Housekeeping  
* Cashiering  
* Reporting  
* Property settings

# **3\. System Modules Overview**

| Module ID | Module Name | Purpose |
| :---- | :---- | :---- |
| **M01** | Tenant Management | Manage hotel partners and subscriptions |
| **M02** | Subscription & Billing | SaaS subscription plans, invoices, billing |
| **M03** | Property Setup & Onboarding | Configure hotel properties and operational settings |
| **M04** | Room & Inventory Management | Manage room types, physical rooms, and statuses |
| **M05** | Rate & Policy Management | Rate plans, occupancy pricing, restrictions |
| **M06** | Reservation Management | Booking lifecycle management |
| **M07** | Guest Management | Guest profiles, history, preferences |
| **M08** | Housekeeping | Room cleaning and housekeeping operations |
| **M09** | Maintenance | Maintenance requests and room blocks |
| **M10** | Laundry Management | Linen/laundry workflow tracking |
| **M11** | POS Integration | Post restaurant/service charges to folio |
| **M12** | Accounting & Finance | Guest folio, invoices, ledger, payments |
| **M13** | Reporting & Analytics | Operational and financial reporting |
| **M14** | System Administration | Roles, permissions, audit control |
| **M15** | Settings | Property-level and global system configurations |

# 

# 

# **4\. Super Admin Functional Modules** 

**Purpose**

The Super Admin module manages the SaaS platform at the global level, including tenant onboarding, subscription billing, property master configuration, platform reporting, and system-wide settings.

The Super Admin has full access to platform-level operations and controls all tenant accounts and global configurations.

**4.1 Super Admin Sidebar Menu**

The Super Admin panel includes the following main navigation menus:

* Dashboard  
* Tenants  
* Properties  
* Billing  
* Reports  
* Settings

**4.2 Dashboard**

**Purpose**

Provide a centralized overview of the overall SaaS platform performance and activity.

**Dashboard Widgets**

* Total Active Tenants  
* Total Active Properties  
* Monthly Revenue  
* Pending Invoices  
* Trial Expiry Alerts  
* Recent Activities

**Functions**

* Display active tenant statistics  
* Display subscription revenue summary  
* Show pending billing alerts  
* Show recent platform activities

**4.3 Tenants**

**Purpose**

Manage tenant accounts, subscriptions, and tenant-level activity across the SaaS platform.

**Submenus**

* Tenant List  
* Add Tenant  
* Tenant Profile  
* Subscription Management  
* Billing History  
* Tenant Activity Logs

**Functions**

* Create new tenant accounts  
* View and update tenant profile details  
* Assign and manage subscription plans  
* Review tenant billing history  
* Track tenant activities and status

**4.4 Properties**

**Purpose**

Manage global property-related master configurations and room setup templates used by tenant properties.

**Submenus**

* Property List  
* Property Profile  
* Categories  
* Amenities Groups  
* Amenities  
* Breakfast Plans  
* Bed Types  
* Room Types  
* Room Sub-Types

**Functions**

* View all registered properties  
* Configure property categories  
* Manage amenities and amenities groups  
* Define breakfast plans  
* Configure room type master data  
* Manage room sub-type templates

**Note: We will follow our booking engine for Properties** 

**4.5 Billing**

**Purpose**

Manage SaaS subscription plans, billing cycles, invoices, and payment collection.

**Submenus**

* Subscription Plans  
* Add-ons  
* Trial Management  
* Invoices  
* Payment Collection

**Functions**

* Create and manage subscription plans  
* Configure optional add-on services  
* Manage trial periods  
* Generate subscription invoices  
* Record and track payment collection

**4.6 Reports**

**Purpose**

Provide platform-level operational, billing, and audit reports.

**Submenus**

* Tenant Reports  
* Revenue Reports  
* Subscription Reports  
* Audit Logs

**Functions**

* Generate tenant performance reports  
* Monitor subscription revenue reports  
* Review billing reports  
* Track platform audit logs

**4.7 Settings**

**Purpose**

Manage platform-wide system configurations, security controls, and integration settings.

**Submenus**

* Roles & Permissions  
* Tax & Currency  
* OTA API Configuration  
* Email / SMS Templates  
* Maintenance Tools

**Functions**

* Manage user roles and permissions  
* Configure tax and currency settings  
* Maintain OTA integration credentials  
* Configure notification templates  
* Perform maintenance and platform utility tasks

# 

# **5\. Tenant PMS Functional Modules**

**Purpose**

The Tenant PMS module manages hotel property-level daily operations including reservations, guest services, cashiering, housekeeping, rate management, channel synchronization, night audit, reporting, and property settings.

This module is used by hotel staff such as:

* Property Admin  
* Front Desk   
* Cashiers  
* Housekeeping Staff  
* Revenue Managers  
* Account Staff

Each tenant operates independently within their own PMS environment.

**5.1 Tenant PMS Sidebar Menu**

The Tenant PMS panel includes the following main navigation menus:

* Dashboard  
* Front Desk  
* Guest Management  
* Cashiering  
* Housekeeping  
* Rate & Availability  
* Channel Manager  
* POS  
* Night Audit  
* Reports  
* Settings

**5.2 Dashboard**  
**Purpose**

Provide hotel staff with a real-time operational overview of arrivals, departures, guests, room status, housekeeping, revenue, and alerts.

**Dashboard Widgets**

**1\. Arrival Widget**

Displays today’s arrival reservations.

Example:

* Total Arrivals: 50  
* Pending: 4  
* Arrived: 46

**Logic:** Count reservations where arrival\_date \= business\_date, grouped by Pending and Arrived.

**2\. Departure Widget**

Displays today’s departures.

Example:

* Total Departures: 64  
* Pending: 36  
* Checked Out: 28

**Logic:** Count reservations where departure\_date \= business\_date, grouped by Pending and Checked Out.

**3\. Guest In-House Widget**

Displays total staying guests.

Example:

* Total Guests: 267  
* Adults: 201  
* Children: 66

Logic: Count guests from active checked-in reservations.

**4\. Room Status Widget**

Displays room inventory distribution.

Example:

* Vacant: 229  
* Sold: 100  
* Day Use: 7  
* Complimentary: 5  
* Blocked: 5

Logic:  
Count physical rooms grouped by room operational status.

**5\. Housekeeping Status Widget**

Displays housekeeping room readiness.

Example:

* Clean: 180  
* HK Assigned: 25  
* Dirty: 18  
* Blocked: 4

Logic: Count rooms grouped by housekeeping status.

**6\. Revenue Summary Widget**

Displays daily financial summary.

Example:

* Room Revenue: 25,000  
* Extra Charges: 3,500  
* Payments Collected: 20,000  
* Pending Balance: 8,500

Logic: Summarize daily folio charges and payments.

**7\. Alerts & Notifications Widget**

Displays operational alerts.

Example:

* Pending Arrivals: 4  
* Late Departures: 2  
* Dirty VIP Rooms: 1  
* Pending Payments: 6

Logic: Generate alerts based on reservation, housekeeping, and payment exceptions.

**Dashboard Layout**

**Top Row: Arrival | Departure | Guest In-House**  
**Middle Row: Room Status | Housekeeping Status**  
**Bottom Row: Revenue Summary | Alerts & Notifications**

- **Examples of Dashboards**

**![][image1]**

**![][image2]**

**![][image3]**

**5.3 Front Desk**  
**Purpose**

Manage reservations, room assignments, guest stay operations, and live room availability.

**Submenus**

* Stay View  
* Reservations  
* Arrivals  
* In-House  
* Departures  
* Room Calendar  
* Room Moves  
* Room Blocks

**Functions**

* Create and manage reservations  
* Handle check-in/check-out  
* Assign rooms  
* Move guests between rooms  
* Manage room availability  
* Block rooms for maintenance or operational reasons

**5.4 Guest Management**

**Purpose**

Maintain guest profiles, stay history, and guest-related operational records.

**Submenus**

* Guest Database  
* VIP Guests  
* Lost & Found   
* Blacklisted Guests

**Functions**

* Create and maintain guest profiles with contact and identity details  
* Identify and manage VIP guest records for priority service  
* Record, track, and manage lost & found items linked to guests or rooms  
* Maintain blacklisted guest records to restrict future reservations

**5.5 Cashiering**

5.5.1. Cashiering Center 

Purpose

Provide a centralized cashier dashboard for monitoring guest folios, pending balances, cashier collections, and quick cashier actions.

Functions

* View pending folio balances  
* Quick payment posting  
* Quick charge posting  
* View cashier totals  
* Access open folios  
  ---

  2\. Open Folios  
  Purpose

Display all active guest folios with current balances and billing details.

Functions

* View guest folio details  
* Monitor outstanding balances  
* Access folio transaction history  
* Settle folios during checkout  
  ---

  3\. Post Payment  
  Purpose

Record guest payments against open folios.

Functions

* Receive cash payment  
* Receive card payment  
* Receive bank transfer  
* Record advance payment  
* Update outstanding balance  
  ---

  4\. Post Charges  
  Purpose

Add non-room charges to the guest folio.

Functions

* Add restaurant charges  
* Add laundry charges  
* Add transport charges  
* Add minibar charges  
* Add service fees  
  ---

  5\. Refunds  
  Purpose

Process guest refunds for overpayments or cancellations.

Functions

* Refund overpayment  
* Refund deposit  
* Refund cancellation amount  
* Update folio balance  
  ---

  6\. Cashier Transactions  
  Purpose

Maintain a complete log of all cashier-related transactions.

Functions

* View payments  
* View refunds  
* View charges  
* View adjustments  
* Audit cashier activities  
  ---

  7\. Cashier Balances  
  Purpose

Track cashier opening, collection, and closing balances.

Functions

* Record opening balance  
* Monitor collected payments  
* View expected closing balance  
* Reconcile shift balance  
  ---

  8\. Cashier Transfer  
  Purpose

Transfer cashier responsibilities and balances between shifts or staff.

Functions

* Transfer cashier balance  
* Transfer active drawer  
* Shift handover logging  
  ---

  9\. Direct Sales  
  Purpose

Record direct sales transactions that are not linked to room reservations.

Functions

* Restaurant direct sale  
* Spa direct sale  
* Walk-in service sale  
* Miscellaneous service sale  
  ---

  10\. Expense Voucher  
  Purpose

Record operational expenses paid by cashier.

Functions

* Petty cash expenses  
* Maintenance expenses  
* Administrative expenses  
* Expense approval tracking  
  ---

  11\. Invoices  
  Purpose

Manage invoices for guests, companies, and vendors.

Submenus

* **Guest Invoices** – Generate guest billing invoices  
* **Company Invoices** – Generate corporate invoices  
* **Vendor Bills** – Record supplier invoices  
  Functions  
* Generate invoices  
* View invoice history  
* Track unpaid invoices  
* Export invoices  
  ---

  12\. Exchange Rates  
  Purpose

Manage currency exchange rates for multi-currency transactions.

Functions

* Set exchange rates  
* Update rates  
* Apply currency conversion to folios

**5.6 Housekeeping**  
Purpose

Manage room cleaning operations and room status updates.

Submenus

* House Status  
* Maintenance Block

Functions

* Update room cleaning status  
* Monitor room readiness  
* Block rooms for cleaning or maintenance

---

5.7 Rate & Availability  
Purpose

Manage room pricing, rate plans, and inventory availability.

Submenus

* Rate Plans  
* Packages & Promotions  
* Availability Calendar  
* Restrictions  
* Stop Sell

Functions

* Create and manage rate plans  
* Configure promotions  
* Control room inventory availability  
* Apply stay restrictions  
* Stop sales for selected dates

---

5.8 Channel Manager  
Purpose

Synchronize rates, availability, and bookings with OTA channels.

Submenus

* OTA Connections  
* Inventory Sync  
* Rate Sync  
* Booking Logs

Functions

* Manage OTA connectivity  
* Sync room inventory  
* Sync rates  
* Monitor channel booking activity

---

5.9 POS  
Purpose

Manage outlet charges and post them to guest folios.

Submenus

* POS Orders  
* POS Posting  
* Outlet Summary

Functions

* Capture outlet sales  
* Post outlet charges to guest folio  
* Monitor outlet transactions

---

5.10 Night Audit  
Purpose

Perform end-of-day operational closure and financial posting.

Submenus

* Run Night Audit  
* Audit Review  
* No-Show Processing  
* Audit Logs

Functions

* Post daily room charges  
* Process no-shows  
* Generate daily audit records  
* Advance business date

---

5.11 Reports  
Purpose

Generate operational and financial reports for hotel management.

Submenus

* Occupancy Reports  
* Revenue Reports  
* Cashier Reports  
* Housekeeping Reports  
* Audit Reports

Functions

* Monitor hotel occupancy  
* Review revenue performance  
* Track cashier transactions  
* Review housekeeping activity  
* Generate audit reports

---

5.12 Settings  
Purpose

Manage property-specific configurations and operational settings.

Submenus

* Users & Roles  
* Room Types  
* Amenities  
* Taxes  
* Payment Methods  
* Email Templates  
* Policies  
* **Travel Agent**  
* **Business Source**  
* **Company database**

Functions

* Manage user access  
* Configure room settings  
* Setup taxes  
* Define payment methods  
* Configure communication templates  
* Manage booking policies

## 

## **1\. Tenant Management (Partner)**

### ***1.1 Tenant Account Creation (Dual-Path Entry)***

**Objective:** Establish unique identity and administrative access for new hotel partners.

#### **Path A: SaaS Self-Service Registration (Customer-Led)**

* Actors: Tenant Admin (Hotel Owner), System.  
* Inputs: Company Name, Contact Person, Email, Mobile, Password, Country.  
* Process Logic:  
  1. Validation: System checks for unique Email and Mobile.  
  2. Identity: System generates **tenant\_id** and hashes password.  
  3. Verification: System sends Activation Email. Account status \= **Pending Activation.**  
  4. Assignment Trigger: Upon email verification, system MUST redirect to Plan Selection before Onboarding.  
* **Outputs:** Tenant Record, Verification Email.

#### **Path B: Platform Admin / Sales Assisted (Internal-Led)**

* Actors: Platform Admin, Sales Team.  
* Inputs: Tenant Name, Company Reg Number, Contact Person, Email, Billing Address.  
* **Process Logic:**  
  1. Manual Setup: Admin creates Tenant and immediately assigns a Subscription Plan in the same session.  
  2. Bypass: System marks Email as verified and sets status to Active.  
  3. Credentialing: System sends Welcome Email with a secure login link/temporary password.  
* **Outputs:** Active Tenant Record, Assigned Subscription, Welcome Email.

---

### ***1.2 Manage Tenant Subscription (The Billing Engine)***

**Objective:** Define the commercial plan, limits, and deferred payment logic.

* Actors: Platform Admin (Onboarding), Tenant Admin (Management), System Billing Service.  
* Inputs: Plan ID, Billing Cycle (Monthly/Annual), Property/Room Count, Add-ons, Promo Code, Billing Country.  
* **Process Logic:**  
  1. Subscription Assignment: \* Self-Service: Assigned *after* email verification.  
     * Admin-Assisted: Assigned *during* account creation.  
  2. Calculation: Total \= (Base Plan \+ Add-ons \- Discounts) \+ Country-Specific Taxes.  
  3. Deferred Payment Logic:  
     * Payment collection is skipped during initial setup.  
     * Subscription\_Status \= Pending Payment or Trial.  
  4. Trial Logic: If trial enabled, system enforces trial\_duration\_days and limits.  
* **Outputs:** Subscription Record, Subscription Metadata (Limits/Modules), Draft Invoice.

## **2\. Property Setup & Onboarding**

### ***2.1. Step 1: Tenant First Login Validation***

**Actors:** System, Tenant Admin.  
**Inputs:** tenant\_id, property\_count, subscription\_status.  
**Process Logic:**

1. **Subscription Guard:** System verifies subscription\_status is Active or Trial.  
2. **Property Check:** SELECT COUNT(\*) FROM properties WHERE tenant\_id \= current\_tenant.  
3. **Routing:**  
   * **IF** count \== 0 → Launch **Property Onboarding Wizard**.  
   * **ELSE** → Load **Main PMS Dashboard**.  
     **Outputs:** Onboarding Wizard OR Dashboard.  
     

This ensures the **first property must be created before the system can be used**.

### ***2.2 Step 2: Property Basic Information Setup***

**Objective:** Establish the complete operational, physical, and visual identity of the hotel.

* Actors: Client Admin (Tenant), Super Admin (Support).  
* Inputs:  
  * Core Identity: Property Name, Property Type (Hotel, Resort, Apartment), Number of Rooms, Description, Branding (Logo).  
  * Address & Contact: Country, Street, City, Area, Zip/Postal Code, Geo-Location (Lat/Long), Phone, Email.  
  * General Policies: Timezone, Currency, Open/Closed Status, Check-in/Check-out Times, Child Policy, Pet Policy.  
  * Content & Media: Featured Amenities, Group Amenities, Featured Image, Video, and Photo Gallery upload.  
* **Process Logic:**  
  * Localization Engine: System auto-detects/suggests Timezone and Currency based on the selected Country.  
  * Subscription Guard (Dual-Check):  
    * Check A: IF property\_count \>= plan\_limit → Block creation & trigger Upgrade UI.  
    * Check B: IF input\_number\_of\_rooms \> plan\_room\_limit → Block and notify user of plan constraints.  
  * Entity Creation: Generate a unique property\_id and map it to the tenant\_id.  
  * Media Processing: System optimizes and stores uploaded images in the property’s cloud directory.  
* **Outputs:**  
  * Comprehensive Property Profile Record.  
  * Localized Financial Settings (Currency/Timezone).  
  * Operational Ruleset (Child/Pet/Check-in policies).

### **2.3 Step 3: Room Type & Inventory Generation**

**Objective:** Build the physical room database required for the Tape Chart and availability engine.

**Actors:** Tenant Admin, System.

**Inputs:**

* **Basic (Parent Template):** Room Type (e.g., Cottage, Room, Suite), Name (e.g., Deluxe King), Code (e.g., DKNG), **Floor** (e.g., 1, 2, G, Penthouse), Bed Type (Multi-select), Max Occupancy, Adult Occupancy, Number of Bedrooms, Number of Bathrooms, Area (m^2), Base Rate, Featured Amenities, Groupwise Amenities, Photo Gallery.  
* **Inventory Details:** Total Room Count for this type, Numbering Mode (Auto/Manual).

**Process Logic:**

1. **Template Creation:** System stores the "Room Type" as a Parent Template (Blueprint).  
2. **Subscription Guard:** System checks the requested Room Count against the remaining plan\_room\_limit in the tenant’s subscription.  
3. **Inventory Expansion (Child Records):** \* **If Auto:** System generates sequential records based on the "Room Count" (e.g., 101, 102, 103). The **Floor** value from the template is automatically assigned to these units.  
   * **If Manual:** User provides a list of specific identifiers; System validates for uniqueness within the property.  
4. **Data Linkage:** Each individual physical room record is hard-linked to room\_type\_id, property\_id, and tenant\_id.

**Outputs:**

* Room Type Parent Record.  
* Individual Physical Room Units (Inventory).

#### **2.3.1 Management of Individual Physical Rooms (Unit Attributes)**

**Objective:** Allow granular control and overrides for specific physical units created in Step 3\.

**Attributes for Developers (Database Columns):**

| Attribute | Data Type / Logic | Example / Default |
| :---- | :---- | :---- |
| **Room Identifier** | String (Unique) | 101, 202A, Cottage-1 |
| **Floor** | Integer / String | **Inherited from Template (Editable)** |
| **Status** | Enum | Vacant Clean, Vacant Dirty, Occupied, Out of Order |
| **Number of Bedrooms** | Integer | 2 |
| **Number of Bathrooms** | Integer | 1 |
| **Area ($m^2$)** | Decimal | 330.00 |
| **Bed Types** | Multi-select List | King Size, Twin Bed, Sofa Bed |
| **View** | Multi-select List | Beach View, Pool View, Garden View |
| **Usable Status** | Boolean | True (Available for Sale) / False (Block) |
| **Out of Order** | Boolean / Date Range | Maintenance / Renovation flag |

![][image4]

### **2.4 Step 4: Rate Plan & Policy Configuration**

**Objective:** Define and manage modular components for stay rules, occupancy-based pricing logic, and sellable rate products.

**Actors:** Client Admin (Hotel Property Admin)

#### **Module: Policy Management (Pillar 1\)**

**Objective:** Create a centralized library of reusable policies governing cancellations and no-show rules.

**Inputs:**

* Cancellation Policy: \* Policy Name: (e.g., Flexible – 24 Hours, Non-Refundable, Moderate – 3 Days).  
  * Cancellation Deadline: $X$ days before arrival.  
  * Penalty Type: First Night Charge, Percentage of Stay, or Full Stay Charge.  
* No-Show Policy:  
  * No-Show Penalty Type: First Night Charge, Full Stay Charge, or Fixed Amount.  
  * No-Show Deadline: (Optional).

**Process Logic:**

1. Policy Creation: System generates a unique policy\_id.  
2. Independent Storage: Policies are stored as standalone objects to be reused across multiple Rate Plans.  
3. Linking Logic: The Rate Plan references the policy\_id as a foreign key.  
4. Update Propagation: If a policy is updated, all linked Rate Plans automatically inherit the updated rules.

**Outputs:**

* Policy Repository: A reusable list of rules including policy\_id, policy\_name, cancellation\_deadline, penalty\_type, penalty\_value, and no\_show\_rule.

#### **Module: Guest-Based Pricing / OBP (Occupancy Based Pricing)**

**Objective:** Define mathematical pricing logic based on guest occupancy.

**Inputs:**

* Standard Occupancy: Number of guests included in the base price (e.g., 2 Adults).  
* Single Occupancy Discount: \* Type: Fixed Amount or Percentage.  
  * Value: (e.g., \-$10 or 10% Discount).  
* Extra Adult Charge: Fixed amount per adult above standard occupancy (e.g., $20).  
* Extra Child Charge: \* Child Age Buckets: (e.g., 0–5 Years → Free, 6–12 Years → $10, 13–17 Years → $15).

**Process Logic:**

1. Pricing Profile Creation: System generates a pricing\_profile\_id.  
2. Pricing Formula Execution:  
   $$Final Rate \= (Base Rate \\pm Occupancy Modifiers) \+ \\sum(Child Fees)$$  
3. Occupancy Validation: If Total Guests \> Room Capacity, the booking is blocked.  
4. Reusable Logic: Multiple Rate Plans can reference the same pricing\_profile\_id.

**Outputs:**

* Guest-Based Pricing Profiles: Stored structure including pricing\_profile\_id, standard\_occupancy, single\_discount\_value, extra\_adult\_fee, and child\_age\_buckets.

#### **Module: Rate Plan Management (Pillar 3 – The Connector)**

**Objective:** Create sellable products by linking policies, pricing profiles, and meal plans.

**Inputs:**

* Rate Identity: Name (e.g., Standard Rate, Early Bird, Non-Refundable), Rate Type (Base Rate or Derived Rate).  
* Meal Plan Selection: Clear board basis:  
  * Room Only (RO)  
  * Breakfast Included (BB)  
  * Half Board (HB) (Breakfast \+ 1 Meal)  
  * Full Board (FB) (Breakfast \+ Lunch \+ Dinner)  
  * All Inclusive (AI)  
* Linked Modules: Selection of policy\_id and pricing\_profile\_id.  
* Derived Rate Modifier: (For Derived Rates) e.g., Parent Rate – 10% or Parent Rate \+ $20.

**Process Logic:**

1. Rate Plan Creation: System generates a rate\_plan\_id and links all three pillars (Rate, Policy, Pricing).  
2. Derived Rate Logic: If rate\_type \= derived, the system calculates:  
   Derived Price \= Parent Rate (Plus/Minus)Modifier  
3. Automatic Updates: If the Parent Rate is updated, the Derived Rate is recalculated instantly.  
4. Product Activation: Active plans are exposed to the Booking Engine, Channel Manager, and OTA Sync.

**Outputs:**

* Sellable Rate Plan Records: Data structure containing rate\_plan\_id, meal\_plan, policy\_id, pricing\_profile\_id, and parent\_rate\_id.

#### **2.4.1 Operational Layer: Restrictions**

**Objective:** Apply date-based restrictions controlling booking eligibility on the calendar.

**Inputs:**

* Minimum Length of Stay (MLOS): e.g., Min 2 nights.  
* Maximum Stay: e.g., Max 14 nights.  
* Closed to Arrival (CTA): TRUE/FALSE.  
* Closed to Departure (CTD): TRUE/FALSE.  
* Release Period: Minimum advance booking required (e.g., 7 days).

**Process Logic:**

1. Restriction Mapping: Stored per date, rate\_plan\_id, and room\_type\_id.  
2. Availability Control:  
   * If CTA \= TRUE, the reservation cannot start on that date.  
   * If CTD \= TRUE, the reservation cannot end on that date.  
   * If Stay Length \< MLOS, the plan is hidden from results.  
3. Overlay Mechanism: Restrictions act as a dynamic layer on top of the base rate in the grid.

**Outputs:**

* Restriction Calendar Grid: Stored as restriction\_id with associated date-level stay controls, used in the Tape Chart and Rate Grid.

#### **2.4.2 Technical Matrix (Database Design Reference)**

| Component | Data Type | Logic Level | Industry Term |
| :---- | :---- | :---- | :---- |
| Base Rate | Decimal | Rate Plan | Standard Rate |
| Meal Plan | Enum | Rate Plan | Board Basis |
| Policy Link | Foreign Key | Policy Module | Cancellation Policy |
| OBP Link | Foreign Key | Pricing Module | Occupancy Based Pricing |
| Min Stay | Integer | Restriction Layer | MLOS |
| CTA / CTD | Boolean | Restriction Layer | Stop Sell / Closed |
| Release Period | Integer | Rate Plan | Advance Booking (Early Bird) |

## **3\. Room & Inventory Management**

**Follow: 2.3 Step 3: Room Type & Inventory Generation**

## **4\. Reservation Management**

**Objective:** Serve as the central transactional hub that synchronizes room inventory, guest identity, pricing logic, and financial transactions across all booking channels while maintaining **ACID-compliant** database integrity.

### **4.1 Functional Component: Booking Engine**

**Actors:**

* **Front Desk Agent:** Internal user creating walk-ins or manual bookings.  
* **Guest:** Self-service user via the Website/Booking Engine.  
* **External Channels:** OTAs (Expedia, Booking.com) via Channel Manager API.  
* **System:** Automated services (Night Audit, automated cancellations).

**Inputs:**

* **Stay Parameters:** Check-in/Check-out dates, Adults/Children (with ages for OBP).  
* **Inventory Selection:** Room Type ID, Specific Room Identifier (Optional).  
* **Rate Selection:** Rate Plan ID (Linked to Policy and Occupancy-Based Pricing).  
* **Guest Selection (Mandatory):** \* *Existing:* guest\_id via search.  
  * *Quick Create:* First/Last Name, Mobile, Email.  
* **Source Information:**   
  * **Channel ID:** Technical source of booking (OTA, Website, Walk-in, Phone).  
  * **Market Segment:** Business classification of the guest (Leisure, Corporate, Group).  
  * **Company/Agent ID (Optional):** Link to a corporate client or travel agent profile(Optional).

**Process Logic:**

1. **Guest Selection Constraint:** The system enforces a **Search-First** workflow. A valid guest\_id is a foreign key requirement for the reservations table. If the guest is new, the system must perform an atomic "Create Guest" operation to return a guest\_id before the reservation can be saved.  
2. **Availability & Concurrency Engine:** To prevent double bookings, the system utilizes **Row-Level Locking** (e.g., SELECT ... FOR UPDATE). Availability is verified within a single database transaction to prevent race conditions between OTA API calls and manual Front Desk entries.  
3. **Restriction & Policy Validation:** Validates against the Restriction Grid: Minimum Length of Stay (MLOS), Closed to Arrival (CTA), and Max Occupancy. OTA requests failing these are rejected with an error code; Front Desk requests trigger a managerial override prompt.  
4. **Pricing Execution:** The system calculates the total stay price using the following formula:  
   Total Price \= Σ (Base Rate \+ Occupancy Modifier \+ Fees) for each night \+ Taxes  
5. **Policy Snapshot (Legal Integrity):** At the moment of confirmation, the system captures a **JSON snapshot** of the active cancellation policy (deadline and penalty). This ensures that if the hotel changes policies later, the original agreement with the guest remains legally binding.  
6. **Financial Container Initialization:** Every reservation triggers the creation of a **Guest Folio** (Financial Ledger). The reservation cannot be committed without a successful folio\_id generation.  
7. **Audit Trail Logging:** Every state change (Reserved-\>Checked-In) and attribute change (Date/Rate) is logged in a reservation\_audit\_log table, capturing the User ID, Timestamp, and "Before/After" values.

**Outputs:**

* **Reservation Record:** A record with a unique internal id and a public booking\_reference (PNR).

**Dynamic Booking Status:** The initial status is determined by the submission source and payment rules:

* **Confirmed:** For direct front-desk entries or prepaid web bookings.  
* **Waiting for Confirmation:** For "Request-only" dates, pending bank transfers, or OTA bookings requiring manual verification.  
* **Tape Chart Update:** Real-time UI refresh via WebSockets/Push notifications.  
* **Financial Ledger:** An active Folio ready to receive transactions.  
* **Confirmation Notification:** Automated dispatch via Email/SMS/WhatsApp.

### **3.2 Advanced Action Workflow (Status & Operations)**

This logic defines the "Buttons" the Front Desk will use, matching the HotelRunner standard.

| Action | Logic / Requirement | System Impact |
| :---- | :---- | :---- |
| **Check-In** | Allowed only on Arrival Date. Requires ID capture. | Room status $\\rightarrow$ **Occupied**. Folio status $\\rightarrow$ **Active**. |
| **No-Show** | Triggered if guest hasn't arrived by Night Audit time. | Releases inventory. Applies "No-Show Fee" to Folio based on policy. |
| **Cancellation** | Checks cancellation\_deadline. | Releases inventory. Calculates penalty (None, 1-night, or Full). |
| **Resend Email** | Re-triggers the SMTP service using the stored res\_id. | Logs a "Communication Sent" event in the Audit Trail. |
| **Print RegCard** | Compiles reservation \+ guest data into a PDF template. | Records "RegCard Printed" timestamp for security. |

---

### **3.3 Dynamic Folio Logic (Financial Postings)**

To handle the "Dynamic Folio" features you mentioned, the developer must implement the following **Posting Logic**:

**Inputs for Folio:**

* **Standard Posting:** Room Rent \+ VAT \+ Service Charge.  
* **Extra Add-ons:** Fixed fees (e.g., Airport Shuttle) or Variable fees (e.g., Extra Bed).  
* **Per Occupant Logic:** \* Fee\_Type\_Adult: Count $\\times$ Rate.  
  * Fee\_Type\_Child: Count $\\times$ Rate (based on age brackets).

**Process Logic:**

* **Add Payment:** Decrements the balance\_due on the Folio. Does not close the Folio until balance\_due \== 0.  
* **Add Extra:** Immediately appends a line item to the folio\_transactions table.  
* **Routing:** Allows moving a specific line item (e.g., "Dinner") from the Guest Folio to a **Company/City Ledger**.

---

### **3.4 Technical Matrix: Database Essentials**

| Table Name | Field | Data Type | Relation / Note |
| :---- | :---- | :---- | :---- |
| reservations | id | UUID (PK) | Internal ID. |
| reservations | booking\_ref | VARCHAR | Public ID (CW-101). |
| res\_guests | guest\_id | BIGINT (FK) | **Supports Multiple Guests (Many-to-One).** |
| folio\_items | type | ENUM | \[Room, Extra, Payment, Discount, Tax\]. |
| folio\_items | unit\_type | ENUM | \[Per\_Room, Per\_Adult, Per\_Child\]. |

## **4.0 Module: Guest CRM & Profile Management**

**Objective:** Maintain a single source of truth (**Golden Profile**) for guest identity, preferences, and stay history to support personalization, compliance, and loyalty tracking.

### **4.1 Functional Component: Golden Guest Profile**

**Inputs:**

* **Identity:** First/Last Name, Gender, DOB, Nationality.  
* **Contact:** Primary Email, Mobile Number, Address, Postal Code.  
* **Compliance Data:** ID Type, ID Number, Issue Country, Expiry Date, Document Scan.  
* **Operational Preferences:** Bedding (King/Twin), Smoking, Floor, Allergies.

**Process Logic:**

1. **Deduplication Engine:** Before creating a new record, the system runs a lookup on Email, Mobile, and ID Number. If a match is found, the system prompts to **Merge** or **Use Existing**, preventing fragmented guest history.  
2. **Data Security & Encryption:** Personally Identifiable Information (PII), specifically ID numbers and document scans, must be encrypted at rest using **AES-256** encryption. Decryption keys are managed via a secure vault and access is logged.  
3. **Stay History Accumulation:** Upon the Checked-Out event trigger, the system asynchronously updates the guest's lifetime stats: total\_stays, total\_revenue, and last\_stay\_date.  
4. **Preference Injection:** The system uses a **Service Loop** to push guest preferences into any new reservation created for that guest\_id. This ensures "Allergic to feathers" is visible to Housekeeping immediately upon booking.  
5. **Privacy & Compliance:** Supports "Right to be Forgotten" (GDPR/Data Privacy) by anonymizing PII in the guests table while maintaining the integrity of financial transaction records in the accounting module.

**Outputs:**

* **Golden Guest Profile:** A centralized identity with a unique guest\_id.  
* **Compliance Export:** Securely formatted data (JSON/XML) for government reporting (e.g., Police Form B).  
* **Stay History View:** A chronological timeline of all guest interactions and stays.  
* **Operational Badges:** UI indicators such as "VIP," "Repeat Guest," or "Blacklisted."

---

## **5.0 System Connectivity Matrix**

| Source Module | Target Module | Data Flow Description |
| :---- | :---- | :---- |
| **Reservation** | **Housekeeping** | Check-out event triggers room status change to **Dirty**. |
| **Reservation** | **Accounting** | **Night Audit** service posts daily room charges and taxes to the Folio. |
| **Guest CRM** | **Reservation** | **Pre-arrival** trigger auto-populates preferences into Reservation notes. |
| **POS** | **Reservation** | **Query:** Room\_ID $\\rightarrow$ Check for Checked-In status $\\rightarrow$ Post Charge to Folio. |
| **Accounting** | **Guest CRM** | Payment\_Success $\\rightarrow$ Updates total\_revenue in Guest Profile. |

1. ## **Guest Management**

2. ## **Housekeeping Management**

3. ## **Maintenance Management**

4. ## **POS (Point of Sale)**

5. ## **Laundry Management**

6. ## **Accounting & Financial Management**

7. ## **Reporting & Analytics**

8. ## **System Administration**

9. ## **Settings**

***Subscription Plan Configuration***

**Scope:** Define the base plan for tenants, including limits, core modules, pricing, billing cycles, and trial/upgrade rules.

**Actors:**

* Platform Admin / System Administrator

**Inputs:**

* Plan Name (Starter / Pro / Enterprise)  
* Property Limits  
* Room Limits  
* Base Modules (e.g., PMS)  
* Base Price  
* Billing Cycle Options (Monthly / Annual)  
* Trial Availability (Yes/No \+ Duration)  
* Upgrade / Downgrade Rules  
* Tax Applicability Rules

**Process:**

1. Admin creates a new plan and defines base limits, core modules, and pricing.  
2. Admin configures billing cycles and trial options.  
3. Admin defines upgrade/downgrade logic.  
4. System validates configuration for completeness and consistency.  
5. Plan record is saved in the database.

**Outputs:**

* Subscription Plan Created  
* Plan metadata stored  
* Plan ready for add-on assignment

***Add-on Module Configuration***

**Scope:** Define optional modules that tenants can add to plans, with pricing, dependencies, activation rules, and plan assignments.

**Actors:**

* Platform Admin / System Administrator  
* System Billing Service (for pricing, proration, tax calculation)

**Inputs:**

* Module Name (POS, Laundry, Spa, etc.)  
* Pricing (Fixed / Per Property / Per Room)  
* Billing Cycle (inherit plan / override)  
* Optional / Mandatory  
* Activation Required (Yes/No)  
* Dependency Rules (e.g., Spa requires POS)  
* Plan Assignment (which plans this module can be added to)

**Process:**

1. Admin defines add-on module attributes.  
2. Admin assigns module to one or more plans (Plan → Add-on Assignment Table).  
3. System validates dependencies and plan assignments.  
4. Module rules stored in the module object; assignment reference saved in the plan.

**Outputs:**

* Add-on Module Record created  
* Module assignments saved and linked to plans  
* Module pricing, dependencies, and activation rules stored  
* Ready for Tenant Subscription

***Trial Availability Configuration***

**Scope:** Define trial options for plans so tenants can test the platform before paying.

**Actors:**

* Platform Admin / System Administrator

**Inputs:**

* Trial Enabled (Yes/No)  
* Trial Duration (7 / 14 / 30 days)  
* Modules available during trial (optional selection)  
* Property / Room limits during trial (optional)  
* Payment required after trial (Yes/No)  
* Retention days for trial data after expiration

**Process:**

1. Admin configures trial availability and duration for the plan.  
2. Admin specifies modules and usage limits available during trial.  
3. System validates trial settings.  
4. Trial rules stored in plan metadata for enforcement during tenant subscription.

**Outputs:**

* Trial configuration stored in plan  
* Trial rules enforced automatically during Tenant Subscription

10. ## **Rules**

**Upgrade and Downgrade Rules**

**Scope:** Define how tenants can move between plans safely without breaking billing or accounting.

**Actors:**

* Platform Admin / System Administrator

**Inputs:**

* Upgrade Rules (automatic upgrade if limits exceeded, manual option, prorated billing, approval rules)  
* Downgrade Rules (allowed Yes/No, effective immediate or end of cycle, minimum subscription period)

**Process:**

1. Admin defines upgrade and downgrade rules for each plan.  
2. System validates rules to ensure consistency with billing and trial logic.  
3. Rules stored in plan metadata.  
4. System enforces rules automatically when a tenant upgrades/downgrades mid-cycle.

**Outputs:**

* Upgrade/Downgrade rules stored  
* Rules applied automatically during Tenant Subscription lifecycle

[image1]: <data:image/png;base64,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>

[image2]: <data:image/png;base64,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>

[image3]: <data:image/png;base64,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>

[image4]: <data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnAAAAFjCAIAAAAy26FcAABQsElEQVR4Xu29i5Mcx33n6X/B4bAjHGGHlxEXtLyxlrTu0/nu7JOltR12cHWnPZ8Zg5mQBUqyZIrWyVxqjcVoCIny0Tb27NUSaxAGxDF3xgPtCRYpUhQJiQAozBBNCiTAISXSBHoAAoPhPIB5dDcamCHmgen75aOys7Oqurors6qru7+f+AVQlfXK/HVVfiqrH/NTVQAAAABY81NmAQAAAABaB0IFAADQXVy7Vv3Up6q/8zvsX5pOCwgVAABAF/Hss0ylH/mIDJpOCwgVAABAt0DjUaVS3ampjFMhVAAAAN3Cpz4lJfpXf8VmX3tNzlJ58kCoAAAAugX1sHdpqbq5WRNqKg9+IVQAAADdgv6kVxcqRfJAqAAAAOr4/ve/f/LkyZs3b5oLLPj5n//5j9Xz0Y9+dHFx0VyvRX75l395enqaJra3t48fP77y8Y8HCzXuI98TJ0585CMf+eIXv/h3f/d39C9NU4m5kkegUPMf+OCHKPpG5swlTXBq6EMfGMqbpa4RNfQfaPcHP2GUhDE70uyajpiTdf7gg+YSF/R98BOjM2ZhEtDre0qbpRbtHtfmdcYfFE0OXSGbeNU+xU8nF1mdSyMD9dk20r77gx9SE8mdhKALqFQqX/rSlz7/+c9/9rOf/bVf+7X19XVzjbjcd999ZlG1+pWvfMUsapFyufzUU0/RxB/+4R/+zM/8zNhv/mawUA8eNLdsjp/+6Z8mlapZmqYSbXkdplBH+3SPptIRtEh9DU2yLFSZzJnDupAsoVb47yoShd0w9R2WM+MP7u4L9+X4g7P8f/tUOxJbNFRVUWeBo+Omch3VZ/sDfZ/oq10L+d1DfNpbB4AwSKX67O/+7u/qs7GZmpr6i7/4i717937uc5+jfx966CExQXIS40tLDh8+XBtPK6GqePbZurWbhsajYuLDH/7wBz7wAfrXKDcwhdr3wQfrxh/UWSsB8AnWg6s73PEHR4c+tPuJw31idoZNqC5e3C9TfzTq9bmi3LJ7NWpY9aokDqGEKirJ1hx/8JSosyceWTGvGtp9fX50hN3mi3LXmELlx5X9tWiCKKd0qdp+YOgwzT50v6wSyUytLOqpKu91/XIcrHbL/Oc9bOhzMV6kHVKV9GnaoSpREwyv+xbVFm0R54Nou1iLWqqaI7ZSS1mj6BX59w+LFWaVD8bNc0Dnxo0bZlGzzNXVX1Rg/DA/OjvD9cyzs0VbpNb0KpYXY0E+LV56vSQYi5r7st13+JS60MbZVckmZg67uD8AXUuhUFhdXdVLZmdnjZJ4PPvss3s5P/uzP0v//v7v/76YuPPOO3/wgx+Ya7fCxz72Mfr33nvvrRU9++ztf/Nvaja1+DjS+973PjHxhS98Qf2rlxuYQqXrUL+HFbO8c89zV6l77bxwlX5TLPp0IVRxVVdZD/sJJloa09DF3MdW2G33uMmooUKIVlTS6xbz7FhaR0NeURWr8zq/FWBdZFB3881vfnObQxPmshaoU51XjTmZHzWYULcv4yxL3k2ArFjdYFTd3/BCkXx5Z+OJzWujSMhcg5F98/Cay/rQDr27paAaemqsW1QboMtzqXajw19Zr5Jz/FSR26oJ1VixiYJemhMnTojXqFKpGEubRpzkNWojVH1sJ5vgnS18Ua2G4pLxzlLeHNbSsLdC9LPLoua+bKtkUjlNa2f7B4LezXF0koPO5uGHHzaL+LlhFrXOxYsXz3JoSLqxsfHxj39cPDilfy9fvmyu3Tq/U2/NJ598Up+NjXrYawhVfwisYwpVjSY5sstjb5sNiXLWzdXQehk2SOIrG0IV8KHMg8Ks/ou5JepryPsR/gSyXqhatxgiVDkhljYUKvEtjlnaGjKZQnVGfjh5tsJM/Q2N1wVT3mbFIq+9jYUq9l8vVElgt948Ys90pyKG+OrlYL12feXrJBQgVHnCGELVX1y/UMXh6k4AD7qPtn6NzMcnhlClJpsRal2Sa498Z+sfKQtcnF2+bPNqsHOeXxdGu4JOP0fVAJ3MT37yk/n5eb1kcnLSyQiV+IVf+IUzZ87QxKlTp8RnkWiCjmiu1zpPPfXUL/7iL+olb7311jPPPKOXxOOLX/yimj558mRguY4pVK4WNXjyrrrxB3d7D1rrBojaNfyBIbmC7OKNh3LjD/axLoapLkxazcLvxMXkqSH9CbMSan23VS9UWiQqJpopOhq2k4ZCXVtbe++998zS1qg98mVH0fND6eU3K+Jf/YZD9cuUN/E2mGqvaIghVPWSqUeUfE68lHJXToTKxls81UqoVMO++uelZhcvjzsnJ7wMeM9++f2EV3OBX6j8fiLASQLr14jIy/sVcY9oCFXLfGOh7q57tM5e2dEheT/kr7yLs8vcs7xO6brjp5M8E/hjDz4QDxCqm2qATmZ7e/uTn/ykXvJbv/Vb+qwNv/RLv2QWcYWbRa1DqiY3+wuNkhiIR7s0pD5+/PiBAwfo383NTVXuxydUAGKh1N4qLW5lPpUFADhEfMr3/e9//6/8yq/83M/9HFnEyVCvmqRQv/zlL/+0j4ceeshcr3VOnDjx/e9/v1QqqZJyuUwlYd+cgVCBA8zn8K3QglDF24FmKQAgQUioL730klnaOvfcc4/xPdRf/dVfNVeygMaOv/d7v0cD09u3b5vLLLD/HioAAADgmFP1XLlyxVzDjo2NDf+zXyd89atf3blzJ/1rLqgHQgUAAAAcAKECAAAADoBQAQAAAAdAqAAAAIADIFQAAADAARAqAAAA4AAIFYRy++q0WdSBUCtuL1v92iUAADQDhAqCKe+8o5vCbB4AALgGQgXB+J3U0WE2DwAAXPNTy6UKAoFAIBAIy4BQEQgEAoFwEBAqAoFAIBAOAkJFIBAIBMJBQKgIBAKBQDgICBWBQCAQCAcBoSIQCAQC4SAgVAQCgUAgHASEikAgEAiEg4BQEQgEAoFwEBAqAoFAIBAOomWhlvbdUd59V2129x2lCXMdGRP3lXfvp4nyzvB1mo2jpYK/MCAuXJ71F+pB9RcT5Z33+Ze2EIX9tnuoj5n5RX9hXeQfP+dNPPhkwb/0Tx9+2iwMCrXtuSe//qcHX1TlfPrFRx74eq3kgcfl9NTTD6ppBAKBQARFLKEeOaoEWZo4x6YL+0tiBZpQ5vOEyjc5R+plP1POSs6J3yvna7JysUjtky+9j+/waOnIfbWfON+9X+yQryNWMGNhsfjtp34wf23Zv0iEEmppp7gtEJW5S1S7eOQucSyxDh2uxEruYm2h8n1H1X5klfYdpabJEraUxH+0xHcojyJazTeUrQ65t1gqVv7hH584d+Gyf5EMJdRS5cjDDz2SZxN/+sBDFCf5UibUqafZNJcl/Xvy4ENiuqbGAKEW5K58QhWba4d7URyOFxaoUE7Lg9K2bFbVDYFAIHoq4gn1nBQb8ygXqvyXCam2cm2EynRFaimKPezWx4hcqFxmQkK0h6IQG/eT5zA5QvUsWFEa88eTzzxPTvWXi6iNUNme2dHZLPMlG27WRM6bwxR45JxXGVlDGd4IldrI2yVqeFTuYULs5xyvpziKTFGYUCleeuV1cqq/XIYmVFIdeZGMKEqYC4OEKmRJjjwyVduPkKKMhkKlfYq9kY9pQviS1qG9kTW5mMW2/F+qAF/hEb5ybScIBALRGxFTqFycR7nePE/QdKE2gmThCVVuqDzqFXK3eUqTMhbDOxF8Vo53vQkxFJ5o9KyVbBotVCE8rYa8vPZgWWiyJkuxjldVFp5QhW49T9f2wNIixrU8it5otbYHX4znzzQpVDEKfKSmxq+HCJWpV20lovkRqioUPtY1XJt++GnaP/175GHyaIHWxPAUgUD0ZsQU6rI37lRCFYKpewwbIlT/CFWMXJWfxCwPn1CZ5O6q03Z9fOfZ4w2e9y5rI9QSc1vUCLUJoYp9eiNmOaT2WiFGqGIT+bhYf26sx4/O/KTR895STagkQmE4NUIVS/l7qC8Knz2ojSYfrB8ytiTU2nPd2ghVluvv45K25XEf1qqEQCAQvRQWQpWy8YRK8jBUESJU723LO8RDUfkuo3xLlYV6e7LOozvF+69MhA2e9zb/oSQ2SOU3BPxw8lbA/x5qqFBFKkST5a74mvJNX8+13ghVtVq7XaiLZj6UpD2nlYW1Eu9DSaLkJBuhsrc5ly3eQ/W29UqmnhY75zpn75jStByPeg+HdekiEAhET0XLQg2L+pFl81F75NtkmNpuf7DH1LVp7w4AgUAgED0VjoQa/zskrQm1wQivXcHGndpzXQgVgUAgejMcCRWBQCAQiN4OCBWBQCAQCAcBoSIQoTG/XL40X3pzevn1y0uvvbNIE+feXaFC/5qIZZ4uyg9liXJFGaOJSwslpAvROwGhIhBmLBWvkwlemVp86fy1lwoBQYveuLy0WLzu37YHg9JF+mycLson0oXo+oBQEYi6uHKt/KNCqBv0+FHh2ruL5R73hEyXLzn+EOny7wGB6JqAUBGIunjtnab0IOK1i4vTV0v+nfRIUNtbTddS0dwJAtE1AaEiEDLml69PXlrya6BxvFy4dnW5FweplK6XfdmIjB9fXurNdCF6ISBUBELGmQtLzTzp9cfpwrUefPDL0uVLRTPRm+lC9EJAqIjomHl39p13Lr3TJujo/io5j2sroeOtidcX/unE/D+9suBfpOLqSm8ZolG6zl39TycXvveGWd7L6UL0SECoiIggn1XbTQpOfXN62d/vf/2b878+PFcXj8/7V6N4+92VnpJEYLoeP33VSNenn5wnv/rXpHT594lAdHpAqIiIoDGi6bfUoTr4K+Y22Lc+6jv9iROeTUcXvle49r2npSTu+k7AUPVHU4tXeubTSZfm+XeKjHS9fU15lGb/3X+X2fuNfwi4BaF0Lfl2i0B0ekCoiIjoEaGe9hni64e9kRYX6nc8of76cIAhKC7OF/277co49+6KP11jZ2rDU5r9P7TBvT9XL7G3Uc3dIhCdHhAqIiJ6RKj+dwT/wPOBEOrwESXUYEO8Nd3oD/F2U7x+ecmfrgeeXdDz89ujtXQ9/XrAU1981hfRfQGhIiKiR4Tq7/H/TAl1eO6ef5r/xH9Ts8Ej1J9c7hWhBn739OvjNaHe8+T8b/xDbYT6/Z8EChU/8oDotoBQERHRI0L1D7kO/n+eQf9h7mvH5v/jmJoNFmqPj1D/6bXaI9+//uHCh2v3H8EDeoxQEd0XKQm1+MwfsT9lOnOp+I3fLO/8kH+FtsXUfvYHTQPjy/vNlTMZxSN31f9NVscRS6iTd+/YN2kWxse5UNWrXJqQ02c+/7unnzM7famE/zY/8trV//ebUg/fOWeuJiKh91CLR/629NefKA4Plr7wP5Y/cyfN+tdJOQpHn/C/h0rxfx76sUjR069f/QgX6v88PPel5wI+w/US3kNFtCuK18VFxP51/X3oNIRanLivxPssNs17/9LEnH+19gT70+jeHy2/OreyzCdmRlknuxtCZRFLqKfvvnvf6Wr1hRMvmEtikYJQz977236hjjw59xveMEvEb4wF6+Gl89dmriXyDLM0dBf9uzIzXf7aH5T/5FfJqf51Uo7Lz3/71aPPnv7K54wkPP31v9Fz9evcpqcCfyvj/DX8BiEi/VAXvh7+1WJHGkKV9eY9vuz9qSPLm6u1Jwr7pU1LrxTfZlUtjb0iyjtFqBSlnff5C11FbKE++cT9d9999/1PPLnv7jrMdZsgQaEW5PS5z/6vp0/7+n0R5xZOvHb1xJu+ci3efreYxPdQV374bSFUEaXPvI+qSoX+NdOMlRPf+ufna0I9ff8fvPydJ9jE335ZlNAINfDrp3q6/LtFIJKO4j/+P3QF0b8ixLR/tdiRolC1Earya/tDCXWKGZRVTDzp7QShlj79m6UDD7DY6U18+gH/apYRW6jeCLVUyJ86pUXJXDmaFIRavOd/ePV0wGPMJiOhj9iIa37l/Bsrb7xc+ssdsqpOu4AYUTzxrcVi5ZX/+rWXXxgnm57+/O+x+MK/VUKNjITShUA0jq4VahZHqO8urbz2V+XOGaGqgal/wmHEFWooMd5bTVCo3iNfirNHY/447asXlhL6mQIajLJKfu3/Wnn+sKpn20eoJNSVpeXX7v3t0396l7SpiOaEmly6EIjG0Q1Cle+hekNS3ovhPVTbUG+d+if8K9tEXKGyEaqffXffHVjemHSEujh+/Y3LcZw6v+T+Ya+K8mfulFV95N6Vl39Qvvf9/nVSDhJq6W8/vXTPna/qNm1OqD+eXk40XQhEg+gGoYoo/Zd/x/qFz7y/OLXkX4poNYpH2QenS0cv+Sf8K9tELKE6xrlQw2KxeP21d1pw6uSlpRT+aHbpP/9x+Y9/hWLlJ/zxSbuDVYY7fvbT/+rHf/KRmlC//El/iox0YWyKaGOsvH6qfN+/VvfQNE0l/tViR3pCRXRo9JRQKUiQ/i9ZhgWt3MuGWCq2nC7/ThCIrgkIFRERvSZUiqsr16evln/k84EKWvTm9HISH+vtxKA8UDYap2vmWhnpQnR9QKiIiOhBoYqgoef8cvnK1dLbMytvTS//5PIyTVycL1IhvkPpD5Euyg9liXJFGaMJ8ijSheidcCzU0j7vsx6+RVocLU34C90EVaDu0BP3JXcs9zFxn3yy7/qDRTbRWKhPPiA/uxvjo0bN07xQS7vvKO876i9vJtinurRPotFskXuChlZXlynYGMvh7/sUT3xLvNzLl6aWF+Zp1r9Oq1H620/7C21i5bVxeUXv+bfGIvFpDlbthXlVSPnh6SqzjK1ch0oRbQm6EMLO22Xv1E0i3AtVTLTrI7LM6Lu9b8KQn3Y3FurR0s7at+ZbC/bxYNffUWEVZnljrfA5VeU2MPzru4pIoe57hU3cH/KxXie0INSJiEQ1E7GV3HywS1rzUDaFWvrzjzLZ+8pFBAoVgchC6BeC/6IIFGpgYavhXqjyplvOMuWQeEp86MALqas6JyRXljI7quTUcFzbVLDO1NMSPy4/VmF/ifnmnKiDcE9p4pwSqhqaiJ5U1V8ELS0V+PSEMCgfYas67xbyk42yirqaU8k5cWegLCvXESKfuM/7Bi1fRzRKJpk1RHyLRhSypRNin2xvRW+fxYloczQn1Nn7n5it8m/FiPK7H3hyli+Vs3fvYys9cT+tfPoRNqIVhadr6zTScbNCVS8Qf73YC8f9KvIgshr8WtMZMqHlXwhVZvicllt5Ctm/1sxVesnCfPn//nWyF7v4yU886Apn99e+RWxWdBN8mimNlvIdir5D2LqYf9Z/3BYiyPHFxwbpX1EfXahUT3lQvkn5c/+SrX9piga4y0E9GgKRaNQJlS4NfoqyaTpRT3yrdury+0WxSAr10pS8vv78o+LsbSncC1VM6CNUkhZ1cKqvrwlVDaqYnOKOFOuDV0Bq0u856kxZeW38IdekuonVSrzPNYbXYis92J49oXryczGs8R75GuXS3Dy32uD1nNS8WEcUeg6g5jBzeGKgFqmXprSbvRzND68jhcq5X8ySR8XEvrvvJ6Pezz0qVqtyobKZV/YJv4p1lHQb0KRQa/cTPBvyhfNuQdR5GPhai3uOAKGSa8X+ma2lUO3DL1ShInYNe4NCmhZ9Qd2i+llh3GWvOxD9iBuBaQdip6VwJA/R19QJ1RvLGhWQs0ZjEYiEwxCqvJT46UqL9IuFTTzcxxZ557Pya4wxa1JCZaJSYzjekWkr+IQqw8F7q14FjpakBdmxlIR0NfKuUwm1VkNvUW1WbVXXXyuhqifM9mGMUL0xqCHUwCypZJb5Lzws82oHCrUWzd3HRApVH6EqoT75QJ1QaVTKVhJCnX3yfr6aEKq+QhhNClXd04hXSr5w3o9eeUINfq0bCNX/+trfPJnXar0mhaWaEmq9O9WsHN36jttSKFuLEbDqlQKESl2S9uC31lWJAYFvpItAJBr6dUEnKjt1uTVVibGOKhTG1ctbCvdClW8Fs1l2O8+mvZEBW8Q6LG/UqAZk/OeK/COzGOEbIvNjyQPdVdrHelithrUHgLLm4jFgiFDFQ1TSm3wGuE+sf1T8LZ1Az7UWXocu9kn/iqrK0aTnP68a3g888RANXOYuEa3gQuWJVa4VexOPfJuuc3NCZbLk4075o4OeKGf1jywFClWOcT0TB9KUUL1n2ixoWFkIFiqbCHqtdaHKP47k3dCIWVbindJ+xcaIkve5CaYiw5qXptir83BfpFDZ8PFz/5Lth48gRV/AZvlTWf9BW40i/zUZ1mT+RJcEySrmE6p4UMZW43WrdUneAzQEIs1QF1ftZo5fU6JE3c6KEnnryVdY1h7JsEvMt+fG4VioiLaHeqRZG6HaRWOhpkNTQkVkMLw3dxGIXggItauC3W150xAqor0h347CZ4ARPRMQKiIiIFQEAoFoJiBURERAqAgEAtFMQKiIiIBQEQgEopmAUBERAaEiEAhEMwGhIiICQkUgEIhmAkJFRASEikAgEM0EhIqICAgVgUAgmomWhVqurK5vbJodHgAAANAbbG9XSYV+P7YsVHPHAAAAQO9BY0vDjxAqAAAA0DI0TjX8CKECAAAAcTD8CKECAAAAcTD8CKECAAAAcTD8CKECAAAAcTD8CKECAAAAcTD8CKECAAAAcTD8CKECAAAAcTD86FKo165dM4sAAACAziRSaoYfIVQAAAAggEipGX6EUAEAAIAAIqVm+BFCBQAAAAKIlJrhRwgVAAAACCBSaoYfIVQAAAAggEipGX6EUAEAAIAAIqVm+BFCBQB0IDNjYzNmGQBuiZSa4ceEhToxlNuTp//zo2P0j5jWGcrl/IU9xdzowNCEnKZkDIzO1S1uB1QN+je/J6cq1iJ5ellrzZhgL7K21IQyIHpG0Xw6rpqtXzFLzIwNUP366axm9W/mVaN2UfvM0mjmcrkhsyyQmbGcl/YmU6dS3RIDze1cQflpsj4GLGM8w36GcvKcGWrfJSNPckp7YCX5GaIXWFxQ3Ueezmre78uJWJdGGgRIrR7Dj8kKVbeFkqtODkLNqlDp9YpbmdaEOtZf160bsxlFCJW3K2Ghsh7HLAuEC1XUpHHCFfGE2uTOPeboBR3qz8W4yAfCLweee+YnntXm8uMa7yTPQ6gxEC8fO2mz3f/7pWZg+DFZoVa1U79OqN7ZxpaxQtkFs663ny0Kvoy6EXH/TvDbNNaDjMneh9+7yV6YhinsljyXSmZEfcSLxSpHE1KKbLSU5y8TW8ZfUF7DnHbLWa0JlSvZW2GOXtk50R17is3xE0MZlDWVN782Qq2dJ7L58ghtR1SMdwdCqOLUFd2raCY1jQpZW+oSJReJdgmliYarfYvGenloRah78uL1Eq+Ifk15vXmenU5e/qmw9gJRi6SMB+jQeqo979ZOQrUoEn56D6h+k8mVS0VvvsB/tyHPKD4ElGcO13PdTYDPW6nBksiZ4w1hL5NopndWGImSLwGvsDpDxObiDGFpp1OIXTWTZjO7DnFaen0df/XrLnZxHc3xHLIzuX7r9AiUmo7hx8SFWpVnG7+M+UWlciTOKr9QxTrG6dit+EeoqoOgOT0h4vLztksQfhTZc4njyhfI6xZlP2j40idUtRpbQb8V1YRqGNSYNXolf5/bNryLX/SqQUJl7hkSdx687d69kWym0S6t95R78E6M1oQqks93bgq1pmev8voLpJ+HXKi1g4r9VGOdhKwq3lUvbp68DlTthDk+0NMyLd6ZI1bQHcyIev6RHFpaatdpVb1wPtN79zTiwlG3L2Id9qLLm1cPlrpUbqDbA3/h1GtHOfRd7LmhPUNzE0NDewaavQQSIExqCsOPyQp1bpQlgmfOu30T3aJ3m+adQ3JYxmbFedm+6yRl/EKtjVB5P8h7YamrdHIij8JvgOQL5BuhitvqxkIVQzdvBdlvVifYPbg4hGiv6iJV89Ws7Our7D14YSl5hLbjdZeiFxDVlid5I6GKRNU9bFArK8Rs7TRosjfxri+vY6q7psKE6tWc3+/ymlOqDaF628Y5CdmO+OnNGs8HYUqosvn1vtQZ4IltLFSe1eby4xpPqCylPGOsemPeo7jQEarcRLtr9K4U3k+yM9xrILuOaoLtNsSdn3zt5BmoXezs1e8fELnS7zNSxi81A8OPyQoVAABi4hvk+ZDvI3Qcql3+2wiQKSKlZvgRQgUAgLTg4zA1yIZQM06k1Aw/QqgAAABAAJFSM/wIoQIAAAABRErN8COECgAAAAQQKTXDjxAqADGJ+shM9AouifohDuOzuwCASCKlZvgRQgUgJpG+jFzBJWFC9b6kBKEC0CqRUjP8CKECEBP2VTn2LTn5oU3xTVP1PUuB+D7ogPhyrfgyu/xSHfttB7mt9yV3YojvhKuRfwOveSWTUPtFdYbUtuILr6JQfN+UTckvhg4M8N8t8o4tXCtqJb4xXNuhWIOv0ObvBQKQJpFSM/wIoQIQE28Ayn7xoKbE/rFJ78c6+AryBxbUN/3Zmv1j3s8C8C9OeINI8d1/MZQUeg4edAYiR6jaDyTxA/lGqPJHIqU16379qvaLHKJW2q/5yFZ4P08BQE8QKTXDjxAqADEZ0H7T2P9bOdV6FamfKRCa1H9+KFCo/Ait/FaO+Kkj7kK5c/6zTeq4wUKt//UrsaFcM0ioHLkHALqeSKkZfoRQAYiJeB6ak2NN8Rt+2i/t8QesdSpq+Mi3Wi9UsTfxW3RNoZ4b7+G/z070D4gf8hS/zxci1IaPfE2hyjaq3yUAoLuJlJrhRwgVAAAACCBSaoYfIVQAAAAggEipGX50KdSVynsIBAKBQHRNmJ6rx/CjS6FGyhwAAADoFCKlZvgRQgUAAAACiJSa4UcIFQAAAAggUmqGHyFUAAAAIIBIqRl+hFABAACAACKlZvgRQgUAAAACiJSa4UcIFQAAAAggUmqGHyFUANLhZGXnHeWdf2YWt5OT5Z13rJmFbUSmKEtVAj1NpNQMP0KoAKQDhBoJhAqyRaTUDD9CqAAkzOrJ8hf+rCbU1ZOVL9yxtmqulR61CnhClTVsH/Upoiq1OUUAcCKlZvgRQgUgWW5992PMW6vSFqQKmi1/+y1zvbQQ9SGBUZVkxXiVzPVSxEiRqFgbUwSAIFJqhh8hVAASR0pURXuHg1U+ItTrw2RmrpIymUsRAE1IzfAjhApAGihhtF1dEm9gyoeq5sK2UHMqbAqyQaTUDD9CqACkghBYplQhH/maxW3DS1GGqgR6m0ipGX6EUAEAAIAAIqVm+BFCBQAAAAKIlJrhRwgVAAAACCBSaoYfIVQAAAAggEipGX6EUAEAAIAAIqVm+BFCBQAAAAKIlJrhRwgVAAAACCBSaoYfXQp1pfIeAoFAIBBdE6bn6jH86FKokTIHAAAAOoVIqRl+hFABAACAACKlZvgRQgUAAAACiJSa4UcIFQAAAAggUmqGHyFUAAAAIIBIqRl+hFABAL3C6urq4uLi1taWuaB7ocZOTk6apeFQimj9nkoRnRLUarOUEyk1w48QKgCgV7h48aJZ1AOQMMyicChFLa3fHYSdGJFSM/yYuFC3NW5nG72qZjMAAJ3P2bNn9dnzs8tvzyylHwvFG3o1MkVGUtR8OEmm0WpFoNR0DD8mK1Qy08bG5uzcwuXpmUuXr2Q/qJ5UW6pzyk5dWVmZmpoq2EF7oP2Yu47F9PS0fX1SxmHzY+AqY7Qfc9exoFTQrsy9t47DrDo5yQt2KdL7TeqIyzdvVVbX04/ZpUYdaXvJSIqaDyfJ7AChkpMqlRu3bt3SCzsCqjPVPDWnFotFs8gCm+5G4LY+KWPf/Bi4zRjtzbIVlpv7sd9hRlKk95s0uPH3zqmFVqlskZ0UNR9a9WOSdaGSjZZXikvLy9ryToJqTvVP4fFvvH6hAWtrazb7tNk2C1g2PwZJHI5aYRa1guXmfiyzarNtGPHamB1baJXKFtlJUfOhVT8mHSDUKzOz8U76LEA1p/qnINSpqSmzyA6qsM0+bbbNApbNj0ESh7M86yw392OZVZttw4jXxlZtcXwwJ+kb8S/1xfSuY3UlucFx3zoytEpli9gpOu5bFB5movxBCZ/yFYaFVv2YdIBQC1PBn5vqFKj+KQi1UCiYRdbY7NNm24yQchNSPly7sGmmzbZuCbTFy6dfffTRR+evLh88eOipp5/Re2qyhZpuYEcvTE802ESrVLaInaLm7jlEmImyDK36Mcm0ULf5B3rPFy7UL+8wqP7io7/mAqck0dfY7NNm24yQchNSPly7sGmmzbZuCbQFxWOPDe/du/fRAweMnloX6q5c/3CB/tX9MU6FbPbYIJ+dzvF1KoUR9q8n1FxuUKyjD7m0SmWLGCkS8Nnp4T42Qf8yZRaEYqd3DE/TxA6Rq9VxnqjB43xblZOp4X6ZLj42Ff962R7nmWe5pdXk3jR/a9WPCYSaOBBqh5JyE1I+XLuwaabNtm4Js8XfHzxItti/f78qEeEXqhqHcZWaQlUDL9HpC0OIaSYbblkRWqWyRewUsQwURnZ4dw/66Lw+DywVMlGFkdqDYm9bmTpPq2IpFdKBaKvjg9K72t4cJLObhTqUG6jNzIwN5IZqs055/PHHDx8+fPLkSXMBRwiVMBc4JYm+xmafNttmhJSbkPLh2oVNM222dUugLU6/epY8sVS8fujQN57+7vdUN12pF6rq6MXscF9/k0INfLypVSpbxE4RSwVJURs41lxrKjBIqKssjSzEJkKo+jNzblzPrOP6hlr1Y9IRQjXfQyVTjs3wKdJk/5ixVJGOUA8dOrS5uUkTs7OzZ86cMRczoV5MWahzo0M8P/mhidoKMbDpv4xtxes1IF64iaE5Njc3MDpH0zn2CvLpjGHT/BgYhxvK5QZGx+R5ngnyVKW6ayoWNln1bZvPq8mgk2qsP6eWuyXQFg1CPc/UpDguSuSzysII+zyOEuowdfpEv1hZ+IA9zNQ34aFVKlvETpFnOJkflrFjYpkcU3qzuVChavcr8kNJPL2EGNxTJkXhDu/Buwit+jHpEqGKZPGifI71ROziotUGeDmb8YQqSmrXoTXz8/NqulIJaFf6QlVYWipwn00SuO1YP3vh8ntkN0e9Hk0L69O0VV0TILAJyRF0uHyWhMrIllBnaudM4EnlWdY9rdoiudAqlS2yk6LmQ6t+TLpCqDNjhiBFx60ufqZSLtS5UVky5G60+txzz6lp84LntEuoqouJjX+fzWOOUPm9tejd1OtCr4J6KWna4V2OE2yaH4Ogw0GodQRuy8bNE8EnVd0Q1il6v3l+dtnfNacT5ZvZ/bmbjKSo+XCSzI4UKvXO6g40t8e7ZNjdqHzIYwqVS1cXqlsOHDhQLpf37dtnLuC0Q6h5J3cMgf1XkwRuS/mnQbN6EEeDCfVSYoQadDgItY7Abemkok4g8KRKZ4Rabd8P1Tr5+dmEyEiKmg8nyexIobJLiD/pFTenEjZOzYsLXrxXNyQfAvO3VcQj35lE3pR6/vnn9+/fPzs7ay7gpC/U2iMvOwL7ryYxtvXGprxi3ttdYlq83eWkwm6xaX4Mgg4HodZhnlSj7K6xwUmVznuoIJDeTFFYqzMt1M4ifaG6wmafNttmhJSbkPLh2oVNM222dUtYvwkUvZmisFZDqM6AUDuUlJuQ8uHahU0zbbZ1S1i/CRS9maKwVmdKqDG/h5oR8D3UDiXlJqR8uHZh00ybbd0S1m8CRW+mKKzVGRKq+C3c+lU6hm3+W8S38UtJHUjKTUj5cO3Cppk227olrN8Eit5MUVirMyTUdy5N37jh4PNXbYFqTvVPQajO/xDHdvb+MEiaWDY/BkkczvKss9zcj2VWbbYNI14bw/pNoOjNFIW1OhNCrfLTfXV17fU33tSWdxJUc6r/Nsdc5pSVlRWzyI6FhQWbfdpsmwUsmx+DJA5HrTCLWsFycz+WWbXZNox4bbx4sbM/2BGPxcVFsygcSlFL63cHYSdGhoS6ubm5vr7+o9OvViqVpN+JdAXVk2pLdaaaU/1TEGqV//ll6h3sD0R7oP1sbGyYC1qE6rO2xm4mzAXZxlXzY+AwY+JPeVu2gjanVDj5a8SusurqJK9ap4i6zsnJybM9AzV2dXXVzEJDaP2eSlGYTauZEirJaXNzq1yuvHpm8sVTL41PnDo5/mKWg2pI9aTaUp2p5uJ5r5MuIBK6hZ+amirYQXtwNRSgDsu+PinjsPkxcJUx2o+561hQKmhX5t5bx2FWnZzkBXcpAiCSrAi1yp26zcepG5x1xka2Y11UNbWxKQAAgMySIaFWPadu89Fqp6DqbLQFAABAT+GXmoHhx2SFWtWcup15repVNZsBAACgxwiUmo7hx8SFCgAAAHQikVIz/AihAgAAAAFESs3wI4QKAAAABBApNcOPECoAAAAQQKTUDD9CqAAAAEAAkVIz/AihAgAAAAFESs3wI4QKAAAABBApNcOPECoAAAAQQKTUDD9CqAAAAEAAkVIz/AihAgB6CPy1mUjw12YUkVIz/AihAgB6BVLF4uLi1taWuaB7ocaSHc3ScIRNeypFdEqE3XNESs3wY0pC1X8mN+OYVQcAdAstqaVraMmOvWZTQdiJ0UBqAsOPyQqV/LSxsTk7t3B5eubS5SvZD6on1ZbqnLJZnfypSId/utLVX/dME4fNj4GrjLn6Y5/4e6iBnD17Vk0vFG+Ub96qrK6nH7NLjTrS9pKRFBmRdMb0Vuv4pWZg+DFBoYoBHynq1q1bKfspNlRPqi3VOc3RKnUQCwsL9oejPdB+NjY2zAUtQvVZW1uzr0/KuGp+DBxmjPZDe7NsBW1OqaBdmQtax1VWXZ3kVbsU6f3m+dllf8edTpCltEpli4ykyIikM5Z1oW7zsek7l+LfS7YXqnk641RXt/8K6rZs9mmzbRawbH4MkjgctcIsagXLzf1YZtVm2zDitVHvN9+eWfJ33KmFVqlskZ0UGaHV0T0dINQrM7NO7pHbAtWc6p/COHVqasossoMqbLNPm22zgGXzY5DE4SzPOsvN/Vhm1WbbMOK1sVVbHB/MSfpG/Et9Mb3rWF1JbnDct44MrVLZInaKjvsWRUZzWZWh1dE9WRfq7dvbUxcuxTvpswDrQS5cEn903FzmlEKhYBZZY7NPm20zQspNSPlw7cKmmTbbuiXMFpNvvEn/vn3+wvX6twzJFmpiuGB28b7oZqFGpqhBY52EVkf3ZFqoJKHbt28XpkK/3NMRUP1vc6OaC5ySRF9js0+bbTNCyk1I+XDtwqaZNtu6JdAWl2fm9u7du3//fvr3rbcLeieubEGxIzfIB2Hj2oBsfFeuny09NsgHWyTU6eE+tlRsIh1zTAzi+Jqp6MGGuCnybiZ4Y3cMT7PmewPQnEzdOiVHrCYmvBWmWXp4rmRJYUTcvsj0Jp+xDhDq+cKF+uUdBtUfQu1EUm5CyodrFzbNtNnWLYG2oPj7gweFMFSJCF2o1LlTL68kwft6v1DlytIoXBJimpbqY1ytUtkidor057ciV1PDPDmr47WGF0bovqSi8kObeCUsh4PjYm/HB/vrU8dCq6N7IFQHrK2tjY6OmqUeQqiEucApSfQ1Nvu02TYjpNyElA/XLmyaabOtWwJtMXXxMqnisceG6d+zk2+oHpz37OYIVT3YpDFWc0Llwy+O/kBYq1S2iJ2i4T6eisKIGI4ziRZG2MD02KBv/XExYGVJk8N3Tt8Id/D4rr6RXSzb6d2CdIRQzUe+Q7mBsRk+NTM20D9mLFXQarUZWjM3VJt1x/Xr148cObK5ufnII4+YyzhU/9SFmh/i55VWEgeb/svYVtRnYHROzIrTXi3N6a9UZrBpfgz8h5sb9c7zzGD/Svmb2Ty+bfPiRBqaYDP6SUWpYzN78vraDgm0RYU/0qR/3527pnf9Xu+/zgaXfbkpXrJLPc7lBuVaZYVilimBK0QKQzzGFCOw+tAqlS3ipsizIx+nSqGywv4d5iePxnfVDWplDr2Y3tHHtz02uEMY2gutju7pEqFq1xK7xkTHTavxqyrHZjyhihKH19n8/LyarlQC2tUOoUqUwOIRuM8mCdx2rJ+9cPk9stfLeTdDdbc+mSGwCckRdLh81oRq/0oFNbNZzG1nxtT5rZ9UNC0UW50YsroAwgmzRfqhVSpbuE2RePbrL48RWh3d0xVC1S+bCWFNtsKQUCldY3SjKoUqeyjVladA+4Q6Z9kdB+2zWQK3pRdrjmtVznrPDOy76SQIbEJyBB0OQq3D3HZmTN096yeVuG8TKzi8ddZxawub0CqVLdymyOFHf7U6uqcrhCpHqKJ3nuPTQqjyGhuiRVyo8kEQX0Huy5oXXnihVCpVeSoDvyTeJqHm5U26Bb59tkDgtgP86RyEGkjQ4SDUOoK3nRiiO+Y2CrWNv6uX9A/p2ZCRFBmRdMY6UqjMi9yjQ97bJwx28eTFBa9GqGJ1dg8rRqgzY0n0UM8///z+/ftnZ2fNBZz0hVp75GVHcP/VHMa24lGBfL3kE4U5VUn7bjoJbJofg6DDQah1mCfVKLshCzipSLGsf5gb67f9GEEYYf0mUPRmisJanWmhVrkz1NMedv2oQSefHuK3qHTxi9EoK/feQ5UDVNefVgjLY7UdQqV+RMIfscbGYd8nqqMMKmbVUvtuOglsmh+DoMNBqHX4hMou8MCTqq5/SIAG1zsQ9GaKwlqddaF2EOkL1RU2+7TZNiOk3ISUD9cubJpps61bwvpNoOjNFIW1GkJ1BoTaoaTchJQP1y5smmmzrVvC+k2g6M0UhbU6U0LN+g87NAY/7NChpNyElA/XLmyaabOtW8L6TaDozRSFtTpDQn3n0vSNGzfqV+kYqOZU/9vJ//Sg8z/EsZ29PwySJpbNj0ESh7M86yw392OZVZttw4jXxrB+Eyh6M0Vhrc6QUOcXrl65krHPZjQN1Zzqn4JQp6cd/8lY8eeXzdKmsdk2C1g2PwZJHM7y7x5abu7HMqs224YRr42Tk5NmUQ+wtbVlFoVDKWpp/e4g7MTIhFCr/M+3bWxsXru2WKl03iCV6kw1p/qn8OfbiGKxaBZZYN95ua1Pytg3PwZuM0Z7s2yF5eZ+7HeYkRStrq4uLi72lDCosWG2CIRS1GtOpVOCWm2WcrIiVDFI3dzcKpcrr56ZfPHUS+MTp06Ov5jloBpSPam2VGequRiepiBUYmVlZWpqqmAH7YH2Y+46FtRb2dcnZRw2PwauMhbPE34oFbQrc++t4zCrTk7ygrsUARBJhoRK0G3OxsbG6uoa+f/mzZs3sg3VcJWxRnWmmqdmUwAAABkkK0Ktek6lcR7JaZOx1QmxSbVNc2wKAAAgm2RIqAJhJiXXLKNX1WwGAACAHiNQajqGHxMXqkB3VcYxqw4AAKAnaSA1geHHlIQKAAAAdBaRUjP8CKECAAAAAURKzfAjhAoAAAAEECk1w48uhbpSeQ+BQCAQiK4J03P1GH50KdRImQMAAACdQqTUDD9CqAAAAEAAkVIz/AihAgAAAAFESs3wI4QKAAAABBApNcOPECoAAAAQQKTUDD9CqACAXgF/vi0S/Pk2nUipGX6EUAEAvcLFixfNoh6AhGEWhUMpamn97iDsxIiUmuHHxIWq/0yu+Wv0GUOvqtkMAEDnc/bsWX32/Ozy2zNL6cdC8YZejUyRkRQ1H06SabRaESg1HcOPCQpVmOny9MytW7c6RVFUT6ot1TlNrU5PTy8sLNgfjvZA+9nY2DAXtAjVZ21tzb4+KeOq+TFwmDHaD+3NshW0OaWCdmUuaB1XWXV1klftUqT3m6SKyup6W6J885ZWqWyRkRQ1H06S2RlCrVRukJ/0wo6A6kw1d3LxN0OxWDSLLKC+xixqEbf1SRn75sfAbcZob5atsNzcj/0OM5Iivd+kwY2/d04ttEpli+ykqPnQqh+TrAuVbLS8UlxaXtaWdxJUc6p/CuPUeP1CA8T9u1naNDbbZgHL5scgicNZDi4tN/djmVWbbcOI18bs2EKrVLbIToqaD636Mcm6UG/f3p66cClpGyUH1ZzqL95XNZc5pVAomEXW2OzTZtuMkHITUj5cu7Bpps22bgmzxeQbb9K/b5+/cP3mLb2nPj6YUxPDBbMf98X0rmN1JbnBcd86MrRKZYvYKWrQWF+YibIMrfoxybpQyUOFqeDPTXUKVP8URqhJ9DU2+7TZNiOk3ISUD9cubJpps61bAm3x8ulXH3300fmrywcPHnrq6Wf0nlrZotKUMExPNNhEq1S2iJ2iXN+Iv5khYSbKMrTqxyTTQt3mH+g9X7hQv7zDoPqLj/6aC5ySRF9js0+bbTNCyk1I+XDtwqaZNtu6JdAWFI89Nrx3795HDxwwempdqLty/TRI3ZXT/TFOhWz22CCfnc7xdSqFETGcFULN5QbFOlNOHZAQMVIk4LPTw31sgv5lyiwIxU7vGJ6miR0iV6vjPFGDx/m2KidTw/0yXX0jU96/XrbHeeZZbmk1uTfN31r1YwKhOmBtbW10dNQs9RBCJcwFTkmir7HZp822GSHlJqR8uHZh00ybbd0SaIupi5dJFUIYZyffUN10pV6oO7gD1KCTm8MUqhp4iU6frzwthZPjjnHngISInaLhvn6WBwUXnpzmedCa700XRo6buxoXJVKrisFxYdxdfSO72AsxrT+B16ofk24W6lBuoDYzMzaQG6rNOuXxxx8/fPjwyZMnzQUcCLVDSbkJKR+uXdg002ZbtwTa4vSrZ/fv379UvH7o0Dee/u73VDftdfFyWjrSGxgxfzQl1PXAx5tapbJF7BSxVBRG9IGj8faqyAmPYKFSGlmITYRQ9WfmtPPcIO2EdrvrmPSuCK36MekIoZrvoZIpx2b4FGmyf8xYqkhHqIcOHdrc3KSJ2dnZM2fOmIuZUC+mLNS50SGen/zQRG2FGNj0X8a24vUaEC/cxNAcm5sbGJ1zVdUksGl+DIzDDeVyA6Nj8jzPBHmqUt01FQubrPq2zefVpHZS0XSOdQtzY/05tdwtgbZoEOp5pibFcVEin1UWRmj6uBLqMHX6RL9YWfiADa30TXholcoWsVPkGU7mh2VMDljls1w1fg0Vqna/IoQq0kuI8ShlUhTu8B68i9CqH5OuEKq8ljgTzJqi46brX5Tn9uQ9oebFhvx6S4n0heoxZ9kdB+2zWQK3pReLXpGxftkpa7c4tlVNgsAmJEfQ4eTpmh2yJdSZsRy77WBXuX5S0bTqH2rGdUqrtkgutEpli+ykqPnQqh+TrhCq77IRV5S6+FnHzYU6NypLhtyNVp977jk1bV7wnHYJNb/H9vbcv8/mMUeo/N5a3N+o10W9CvZVTQKb5scg6HAQah2B27Jx80TdSVXrH/QhrFP0frONPwPk5Md9EiIjKWo+nCSzI4VKvbN8QjgxxEafAjZOlQ95TKFy6epCdcuBAwfK5fK+ffvMBZx2CDXv5I4hsP9qksBtKf80nlAP4vhzAjdVTYLAJiRH0OEg1DoCt6WTijoB/aTS+4fasyunGP1mu36o1snPzyZERlLUfDhJZkcKtcrHNOppD9Mqn1PTQ55QxXNzVu69hypKahp2RFgeq+0QqhgOMvgj1tgE9l9NYmwrqqPeKBWzVXdVTQKb5scg6HAQah3GtkylQSdV1egfEqDB9Q4EvZmisFZnXagdRPpCdYXNPm22zQgpNyHlw7ULm2babOuWsH4TKHozRWGthlCdAaF2KCk3IeXDtQubZtps65awfhMoejNFYa3OlFBjfg81I+B7qB1Kyk1I+XDtwqaZNtu6JazfBIreTFFYqzMkVPFbuPWrdAzb/LeIb+OnBzuQlJuQ8uHahU0zbbZ1S1i/CRS9maKwVmdIqPMLV69cydhnM5qGak71T0Gozv+yVQb/0laaWDY/BkkcLt7fJlNYbu7HMqs224YRr42Tk5NmUQ+wtbVlFoVDKWpp/e4g7MTIhFCr3Kmrq2uvv/GmtryToJpT/bc55jKnrKysmEV2LCws2OzTZtssYNn8GCRxOGqFWdQKlpv7scyqzbZhxGvjxYud/cGOeCwuLppF4VCKWlq/Owg7MTIk1M3NzfX19R+dfrVSqST9TqQrqJ5UW6oz1Zzqn4JQq/z+nXoH+wPRHmg/Gxsb5oIWofrQ7b99fVLGVfNj4DBjYixo2QranFIRbwxn4Cqrrk7yqnWKqOuk4cjZnoEau7q6amahIbR+T6UozKbVTAmV2NraovOehnr0Ct28efNGtqEarjLWqM5U83RsCgAAIJtkRahVz6k0ztvgrDM2sh3roqqpjU0BAABklgwJteo5dZt/RqlTUHU22gIAAKCn8EvNwPBjskJVKEtlH7PqAAAAepIGUhMYfkxJqAAAAEBnESk1w48QKgAAABBApNQMP0KoAAAAQACRUjP8CKECAAAAAURKzfAjhAoAAAAEECk1w48QKgAAABBApNQMP0KoAAAAQACRUjP8CKECAAAAAURKzfAjhAoAAAAEECk1w48QKgCgh8Bfm4kEf21GESk1w48QKgCgV2jQdXYxLf19U/w9VJ1IqRl+TEmo5g/mZhiz6gCAboEGXmZRD7C1tWUWhUMpamn97iDsxGggNYHhx2SFSn7a2NicnVu4PD1z6fKV7AfVk2pLdU7ZrCsrK1NTUwU7aA+0H3PXsZienravT8o4bH4MXGWM9mPuOhaUCtqVuffWcZhVJyd5wS5FZ8+eVdMLxRvlm7cqq+vOg3ZLO9cO20mkkyKHMbvUyEpNordaxy81A8OPCQqVnFSp3Lh165Ze2BFQnanmqTm1WCyaRRbYdDcCt/VJGfvmx8Btxmhvlq2w3NyP/Q4zkiK933x7ZsnfO7sK2rl22E4itRQ5DK36Mcm6UMXY9J1LcU76LEA1T2ec6ur2X7GwsGCzT5tts4Bl82OQxOGoFWZRK1hu7scyqzbbhhGvjanZAkJNM7Tqx6QDhHplZnZtbU1b3klQzan+KbyrOjU1ZRbZQRW22afNtlnAsvkxSOJwlmed5eZ+LLNqs20Y8drYqi2OD+b4xPiuHJvwZqOjd4RKOREc9y0Kj+ldx/yFdZHrG5nyFYaFVv2YdIBQC1PBn5vqFKj+KQi1UCiYRdbY7NNm24yQchNSPly7sGmmzbZuCbTFy6dfffTRR+evLh88eOipp5/Re2ph0NzguL8TbxxdJtTIFFW4Av15CIloobYUWvVjkmmhkoRu377dBUKlVkCoHUfKTUj5cO3Cppk227ol0BaXZ+b27t27f/9++vettwt6T022mBruV7NimsxxfLg/x8esLI7xMVrfiO7dLhNq4xTxCeXIaZYNmQo+rQavhRExy1dmg/5cjuVTxHBfTuxBTHgj1HFvE6+kMDJcYKvt0rbVqh+TDhDq+cKF+uUdBtUfQu1EUm5CyodrFzbNtNnWLYG2oPj7gweFMFSJCLLFDq3jVkIVTyN3DE+rf1l59wq10jBFsvk0Qi2M7MgNslm6ydCyIaZVomr2LYzUHhR724rVRJLVqJcK6UC01fHBft/eINSGDOUGajMzYwO5odqsUx5//PHDhw+fPHnSXMARQiXMBU5Joq+x2afNthkh5SakfLh2YdNMm23dEmiL06+eJU8sFa8fOvSNp7/7PdVNV9Qj39yg6PeDhDrdC0KNTFFFDBlJitqD35prTQUGCXWVaZiF2EQIVX/Yzo3rmXVc31Crfkw6QqjmI18y5dgMnyJN9o8ZSxXpCPX69etHjhzZ3Nx85JFHzGUcqn/qQs0P8ecbWkkcbPovY1tRn4HROTErHr/wSTdVTQKb5sfAf7i5Ue88zww5/ZqKhb+ZzePbNi9OpKEJNqOdVCx1bGZPXl/bIYG2qPBHmvTvu3PXat03D6EE9rkb7okgoTLdspUL3fzItxKVIpYHlqLp4b7a57a86WmRGe8B73ioUFfHd2l7m2KSru2N77yfPe89NrijT+xKhlb9mHSJULVriV1jouOm1fhVlWMznlBFicPrbH5+Xk1XKgHtaodQJUpg8QjcZ5MEbjvWz164/B7Z6+VqN0NzWTNHNaQJyRF0uHzW0lJ3kxqLoGY2i7ntzJg6v/WTiqaFYqsTQ1YXQDhhtnAS+kPI7hNqlkOrfky6QqgzY4YgRcetLn6mUi5Uum8VJUPuRqvPPfecmjYveE67hKq6mNj499k8xrZj7LMX/M5G65T5qzDHFoU/ZmgjNs2PQdDhINQ6Arcd4iNU/aSq9Q/VvMNbZx293zw/u+zvmmOEGBXon6+hoJ1rh+0kkkhRolG+6eC3g7pCqHKEKhw5x6frhMo6bk+o3llr2y8oXnjhhVKpVOWpDPySeJuEmpc36Rb49tkCgdsO8L6Pbne8We+2ZmbMvrbOCWxCcgQdDkKtI3jbiaHcnrx+Uon7aYbvVtsVer+Z3O/q4acH04zu/+nBMKEyL3KPiptTCbt48uKCH5BCFUO0OfbkUzzynRlLood6/vnn9+/fPzs7ay7gpC/U2iMvO4L7r+YwtvXGprxi8kHcHE3nR8VTOwf6d45N82MQdDgItQ7zpBplN2T+k4oplvUP7OGHvr5DwvpNoOjNFIW1OtNCrXJnsGGmeI9wQn7kRU0PeY98xWiUlXvvocoBqutPK4TlsdoOoYrnq4z+2ptMMXDY94nqKGuK2ar68Ag+lBR8OAi1Dp9Q2cnjP6mqRv+QAA2udyDozRSFtTrrQu0g0heqK2z2abNtRki5CSkfrl3YNNNmW7eE9ZtA0ZspCmt1poQa83uoGQHfQ+1QUm5CyodrFzbNtNnWLWH9JlD0ZorCWg2hOgNC7VBSbkLKh2sXNs202dYtYf0mUPRmisJanSGhvnNp+saNTv2oG9Wc6n87+Z8edP6HOLaz94dB0sSy+TFI4nCWZ53l5n4ss2qzbRjx2hjWbwJFb6YorNWZEGqVn+6rq2uvv/GmtryToJpT/bc55jKnOP9TkRn805VpYtn8GCRxuMDvcTWP5eZ+LLNqs20Y8dp48WJnf7AjHouLi2ZROJSiltbvDsJOjKwIlYZ2Gxub164tViqdN0ilOlPNqf58gJqsUIlisWgWWTA9bftH3d3WJ2Xsmx8DtxmjvVm2wnJzP/Y7zEiKVldXyRZbW1vmgu6FGjs5OWmWhkMpovV7KkV0SlCrzVJOVoQqnvpubm6Vy5VXz0y+eOql8YlTJ8dfzHJQDameVFuqM9VcPO9NQahVfgs/NTVVsIP24GooQL2VfX1SxmHzY+AqY/E84YdSQbsy9946DrPq5CQvuEsRAJFkRahV7lRic3Nzg7PO2Mh2rIuqUp1TUykAAIBskjmh0jhva2trk7HVCbFJtU1zbAoAACCbZEioAmEmJdcso1fVbAYAAIAeI1BqOoYfExcqAAAA0IlESs3wI4QKAAAABBApNcOPECoAAAAQQKTUDD9CqAAAAEAAkVIz/OhSqCuV9xAIBAKB6JowPVeP4UeXQo2UOQAAANApRErN8COECgAAAAQQKTXDjxAqAAAAEECk1Aw/QqgAAABAAJFSM/wIoQIAAAABRErN8COECgDoFfDn2yLBn2/TiZSa4ceUhKr/TG7GMasOAOgWWlJL19CSHXvNpoKwE6OB1ASGHxMXqu4q89foM4ZeVbMZAIDO5+zZs/rs+dnlt2eWnAftVj9KZ5FOimLEQvGGXjG3GK1WBEpNx/BjgkIVZro8PXPr1q1OURTVk2pLdU5Tq9PT0wsLC/aHoz3QfjY2NswFLUL1WVtbs69PyrhqfgwcZoz2Q3uzbAVtTqmgXZkLWsdVVl2d5FW7FOn9JqmisrqeUHSuU1NLUatRvnkruaxmXah05WxsbL5zaVpb3klQzan+Tq7/xqysrJhFdlC3ZbNPm22zgGXzY5DE4agVZlErWG7uxzKrNtuGEa+Ner9Jgx5/r+0qaOfaYTuJ1FIUI5LLagcIdXmluLSc1A1F0lDNqf4pjFPpXtssskPcv5ulTWOzbRawbH4Mkjic5eDScnM/llm12TaMeG1MzRbJdf1Jk1qKYkRyWc26UG/f3p66cClpGyUH1ZzqL95XNZc5pVAomEXW2OzTZtuMkHITUj5cu7Bpps22bgmzxeQbb7L++vyF6zdv6T348cEc/Tvcl9sxPO3v3xtEcl1/0rSaosrqeG5wXEwf9wqnhvtVocNILquZFuo2//xRYepi/fIOg+ovPqlkLnBKEn2NzT5tts0IKTch5cO1C5tm2mzrlkBbXJ6Z27t37/79++nft94u6D04CZXcoGbFdK5v5DgJI8dcy+LYIE1Toa6Q5Lr+pGk1RTtyg/os+XW4sM4SksvtOja965gonKabErlCYUR6l0/IpIkc5vppTbEJZV4sSierHSDU84UL9cs7DKo/hNqJpNyElA/XLmyaabOtWwJtQfHYY8OkikcPHNDE4HXrfSNqVgo1xweshRHR9eeEUQrdLNRKeIr0/PBgQlUjVJmTwojm3Zoy1Qoyh8cGp7ySXcyvrNBTMovksgqhOmBtbW10dNQs9RBCJcwFTkmir7HZp822GSHlJqR8uHZh00ybbd0SaIupi5dJFUIYZyffUN237PSPDZIh5JreCHWKz/LnwGoQltJYKmlaTVHgCFUJlRJIWRru61c5rDBZDrIhac2402JEywe1tJStTBMs28fqdp5cVjtCqOYj36HcwNgMn5oZG+gfM5YqaLXaDK2ZG6rNuuP69etHjhzZ3Nx85JFHzGUcqn/qQs0P8bNKK4mDTf9lbCvqMzA6J2bFSa+Wzo0O5NVMZrBpfgz8h6O0yPM8M+T0ayoW/mY2j2/bvDiRhibYjH5SUerYzJ6kTqtAW1T4I0369925a3r3XfFGUWqcGiTUXhmhhqWInKfeYObPcuuEyh/29uujTLEJOVU8+K0boXpLc339YtsdckJGclmFUG05dOgQ2ZQmZmdnz5w5Yy5uh1DnRod4fvKio4mNr/9qAWNb8XoNiBduYoh7dU75lXSbVM9ngU3zY+C/BRkYHcuSUNldWt01FQubrPq2zddOG/2kmhjKsW5hbqzf9p4yjDBbhIUQqngL8HiIUJluc13+Hmrj4I9nGXyWCZX+3cWHmxWeNPVhJS/GlYNF0piAOTyxbFu5tP55cnJZ7QqhymuJM8GsKTpuuv5FObtRlULNiw359ZYS6QvVY86yOw7aZ7MEbksvFr0iY/2yU5a3OPSSzYxBqEGHk6drdsiWUGfGct5jD/2komnVPyR0XsWwRbOhPRmuJNn1J43jFLF3T/vNwriRXFa7QqjyaY8YgM7xaSFUeY0N5ViXTVeafBDEV5D7suaFF14olUpVnsrAL4m3Sai2w9NqwD5bIHDbAf50LqDvS6zjsyGwCckRdDgItY7gbWk8uicfcFJVEzyv9H5zoXijbH4DxE3QbhP9nbxESSdFMWJ2qZJcVjtSqGP98l0TcS3JUjZOlQ95xBWlLn4mXU+ocmWnHDhwoFwu79u3z1zAaYdQ8+wewprg/qs5Arel/NN4Qj2Iy/WzEYa6xcmaPAKbkBxBh4NQ6wjclt0lM6HWTiq9f6g9u3KK0W8m9EO1yf1CXgqkk6IYkZxNq75WKzItVCK/h/fB4k24CfmRFzU95AlVdNWs3HsPVfberj+tEJbHajuEOibfR8jl+CPW2AT2X01ibCuqowbNYra2OLGRhA02zY9B0OEg1DqMbcUDp8CTqq5/SIAG1zsQ9GaKwlqddaF2EOkL1RU2+7TZNiOk3ISUD9cubJpps61bwvpNoOjNFIW1GkJ1BoTaoaTchJQP1y5smmmzrVvC+k2g6M0UhbU6U0LN+g87NAY/7NChpNyElA/XLmyaabOtW8L6TaDozRSFtTpDQi1MXUz6d/uSg2qO3/LtUFJuQsqHaxc2zbTZ1i1h/SZQ9GaKwlqdIaHOL1y9ciVjn81oGqo51T8FoTr/y1YZ/EtbaWLZ/Bgkcbh4f5tMYbm5H8us2mwbRrw2Tk5OmkU9wNbWllkUDqWopfW7g7ATIxNCrfI/37axsXnt2mKlkuBnnROC6kw1p/qn8OfbiGKxaBZZYN95ua1Pytg3PwZuM0Z7s2yF5eZ+7HeYkRStrq4uLi72lDCosWG2CIRS1GtOpVOCWm2WcrIiVPLQ5ubm+vr6j06/WqlUkn4n0hVUT6ot1ZlqTvVnOk1eqNQ1LCws2B+I9kD72djYMBe0CNWHbv/t65MyrpofA4cZE2NBy1bQ5pSKeGM4A1dZdXWSV61TdPHiRRLG2Z6BGhtmizCEU80ddS90Spgp8MiQUAm6zaHzfnV1jV6hmzdv3sg2VMNVxhrVmWqejk0BAABkk6wIteo5lcZ8JKdNxlYnxCbVVrx1CpsCAEAvkyGhVj2nCq12CqrORlsAAAD0FH6pGRh+TFaoCmWp7GNWHQAAQE/SQGq3njt046sfK3/yl/RISagAAABAZ+GX2tY7b9z8TwOGRyFUAAAAoBHmB4NurZY/+S/8HoVQAQAAgEboUivvbKTSRIQKAAAAdA3Cbn53BoZLoQIAAABdxnvf+iu/OwMDQgUAAACCKX/mTr84wwJCBQAAAILxWzMwbnzl46t7/whCBQAAAAKo/Pn/5nenHlvnz4g1Nyd/uJF/BkIFAAAAAvAbVMXa8JdphY3803ohhAoAAAD4WLvh96gKsYpR6F6ot44euvHV/7288w5Xcf0zd9I+zcMAAAAAibF2+Gt+j4pY3ftHt5fnK1/6cLJCdatSPWjP5sEAAACAZLj+2ff5VSpiq3CWnCqm1x7brTZxKVQaR/pF6DAwTgUAAJAODX4aiYR6/d4P+stbFmq5srq+sWkemXP9M3f6LegwaP/mIQEAAIAEaCDUzckfVv7DR8S01Qi1QfgV6Dz8B0UgEAgEwnmU//iX/SoVUfnSh7dXKw7eQ20Qfv85D/9BEQgEAoFwHqX/8FG/SlXQeHTr/BkIFYFAIBCIiCg+97jfo3psvHbi1ncfTVuo2zdL1Y1bGy99Z+O1Y/6lLYX/oAgEAoFAJBF+ieqxuvePNid/eP3eD9767v7t5fmU3kOtbm5snP6evzxG+A+KQCAQCEQS4ZeoHu998y9pkKo+kUROTUOom6+/UN3a2rr4upw9/8r6+LdozHpz70D19tb6iX9878je7fX3bv7nT22vVW597wAtuv4n/8q/nzKEikAgEIi0ojj+hN+jKmhsulU4e/Ov5RdSy+k88hXx3nf+y+2ld2889PGtSz+ubq6Tz1cPPbA1/db29WUqv73wzuY/v6RUT4v8eyhDqAgEAoFIMUqfTvLPtzUIv//0oEEnG4A+d5AMuvp3n789d4Gsuf7DwyTX7VurNLF+8r+vn/o2FVJU/v3/4t9DGUJFIBAIRIqxcpIGqaFfSG2DULdvFKu3t9gz3r/uJ61WNze23j1/+9oVMQzdLLxKIda8Xbpa3d7evln270SE/6AIBAKBQCQafncGRhpCdRj+gyIQCAQCkWgUv/Ef/fr0B4SKQCAQCERErJw9Wf5Czi9RCBWBQCAQiJZj5Y2XS3/Z51epe6Gm8OP4/oMiEAgEApFmlP5mp9+mjoVafPK/+i3oMGj//oMiEAgEAtGWWHnhSPFv7in/2f9UvucOx0KlWLnwz34ROgnas/9wCAQCgUBkJBwLdZmPU0sP3uU3Yuy4/pk7MTZFIBAIRMbDvVARCAQCgejBgFARCAQCgXAQ/z/oy0cbEoAMjAAAAABJRU5ErkJggg==>