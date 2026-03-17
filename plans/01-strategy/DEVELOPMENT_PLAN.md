# Hotel PMS - Comprehensive Development Plan

## Executive Summary

This document outlines the complete development plan for building a Property Management System (PMS) inspired by HotelRunner's capabilities, using the existing Laravel 12 + Inertia + Vue 3 + TypeScript scaffold.

**Project Vision:** Build a comprehensive, multi-tenant hotel management system with 8 core modules that rivals HotelRunner's feature set while maintaining clean architecture and scalability.

---

## Part 1: HotelRunner Analysis & Feature Mapping

### 1.1 HotelRunner Core Capabilities

Based on the support documentation analysis, HotelRunner provides:

| Category | Features | Articles |
|----------|----------|----------|
| **PMS** | Property management, room assignment, day use, folio routing, guest notifications | 68 |
| **My Property** | Property setup, configuration, profile management | 38 |
| **Calendar** | Availability, rates, restrictions management | 21 |
| **Mobile App** | Remote property management | 11 |
| **Channels** | Multi-channel distribution | 6 |
| **Reservations** | Booking modifications, cancellations, coupon codes | 3 |
| **Website** | Hotel website creation | 1 |

**Total:** 153+ support articles indicating extensive functionality

### 1.2 Feature Mapping to Our Modules

| HotelRunner Feature | Our Module | Priority |
|---------------------|------------|----------|
| Property Management | FrontDesk | P0 |
| Reservations | FrontDesk + Booking | P0 |
| Calendar (Availability/Rates) | FrontDesk + Booking | P0 |
| Channel Manager | Booking (OTA Sync) | P0 |
| Guest Management | Guest | P0 |
| Agent Management | Guest | P1 |
| Housekeeping | Housekeeping | P1 |
| Maintenance | Housekeeping | P1 |
| POS (Restaurant/Bar/Spa) | POS | P1 |
| Mobile App | Mobile | P2 |
| Reports & Analytics | Reports | P1 |
| Multi-property | Core (Hotels table) | P0 |
| User Management | HR + Spatie Permission | P1 |

---

## Part 2: Current Project State Assessment

### 2.1 What's Already Built ✅

**Architecture & Foundation:**
- ✅ Laravel 12 + Inertia + Vue 3 + TypeScript setup
- ✅ 8-module structure with consistent organization
- ✅ Multi-tenancy foundation (Hotels table)
- ✅ Spatie Permission for RBAC
- ✅ Sanctum API authentication
- ✅ Spatie Laravel Data for DTOs

**Database Schema:**
- ✅ All migrations created for 8 modules
- ✅ Proper indexes, foreign keys, soft deletes
- ✅ JSON meta fields for flexibility
- ✅ Enum-based status fields

**Models (Partial):**
- ✅ User, Reservation, Room, Agent, GuestProfile
- ✅ OtaSync, HousekeepingTask, MaintenanceRequest
- ✅ PosOrder, Employee, MobileTask, ReportSnapshot
- ❌ Missing: Hotel, Attendance, Payroll, ShiftSchedule, PosMenuItem

**Routes:**
- ✅ Web routes for all modules (index/store only)
- ✅ API v1 routes with Sanctum auth (index/store only)

**Frontend Pages:**
- ✅ Vue page components exist for all modules
- ⚠️ Likely scaffolding only, needs implementation

### 2.2 Critical Gaps ❌

1. **Missing Models:** Hotel, Attendance, Payroll, ShiftSchedule, PosMenuItem
2. **Incomplete CRUD:** Only index/store implemented (no show/update/destroy)
3. **No Business Logic:** Services/actions are minimal placeholders
4. **No Notifications:** Event listeners only log messages
5. **No Background Jobs:** Only 1 placeholder job exists
6. **No Frontend Logic:** Vue pages need data fetching, forms, state management
7. **No Search/Filter:** Services lack filtering capabilities
8. **No Factories/Seeders:** Testing infrastructure missing
9. **No Reference Generation:** Critical for reservations, guests, etc.
10. **No OTA Integration:** Actual channel manager connections missing

---

## Part 3: Development Phases

### Phase 1: Foundation & Core PMS (Weeks 1-4) **P0**

**Goal:** Make the system functional for basic hotel operations

#### Week 1: Core Models & Multi-tenancy

**Tasks:**
- [ ] 1.1.1 Create `Hotel` model with relationships
- [ ] 1.1.2 Create tenant scoping trait for multi-tenancy
- [ ] 1.1.3 Apply tenant scoping to all existing models
- [ ] 1.1.4 Create Hotel management UI (super admin only)
- [ ] 1.1.5 Implement hotel switching for staff

**Deliverables:**
- Working multi-tenancy
- Hotel CRUD
- Tenant-scoped queries

#### Week 2: FrontDesk - Reservations Core

**Tasks:**
- [ ] 1.2.1 Implement full Reservation CRUD (show/update/destroy)
- [ ] 1.2.2 Create reference number generator service
- [ ] 1.2.3 Build reservation status workflow (draft → confirmed → checked_in → checked_out)
- [ ] 1.2.4 Implement room assignment logic
- [ ] 1.2.5 Create Folio/Transaction model and tables
- [ ] 1.2.6 Build check-in/check-out workflows
- [ ] 1.2.7 Add reservation search/filter (by date, status, guest)

**Deliverables:**
- Complete reservation lifecycle
- Check-in/check-out process
- Folio management

#### Week 3: FrontDesk - Rooms & Calendar

**Tasks:**
- [ ] 1.3.1 Implement room status workflow (available → dirty → clean → occupied)
- [ ] 1.3.2 Build room grid UI with status colors
- [ ] 1.3.3 Create availability calendar (daily/weekly/monthly views)
- [ ] 1.3.4 Implement rate management (base rates, seasonal rates)
- [ ] 1.3.5 Add restrictions (min stay, max stay, closed to arrival)
- [ ] 1.3.6 Build drag-and-drop reservation calendar
- [ ] 1.3.7 Create room type management

**Deliverables:**
- Visual room calendar
- Rate management
- Availability tracking

#### Week 4: Guest Management

**Tasks:**
- [ ] 1.4.1 Implement GuestProfile CRUD
- [ ] 1.4.2 Create guest search with duplicate detection
- [ ] 1.4.3 Build guest history timeline (past stays, preferences)
- [ ] 1.4.4 Implement Agent (travel agency) management
- [ ] 1.4.5 Add guest communication templates (email/SMS)
- [ ] 1.4.6 Create guest verification (ID upload, documents)
- [ ] 1.4.7 Build VIP/guest preference tracking

**Deliverables:**
- Complete guest profiles
- Agent management
- Guest communication

---

### Phase 2: Booking Engine & Channel Manager (Weeks 5-7) **P0**

**Goal:** Enable online bookings and OTA connectivity

#### Week 5: Booking Engine Foundation

**Tasks:**
- [ ] 2.1.1 Create BookingWidget model for direct bookings
- [ ] 2.1.2 Build booking engine landing page (public)
- [ ] 2.1.3 Implement real-time availability checker
- [ ] 2.1.4 Create booking flow (search → select room → guest info → payment)
- [ ] 2.1.5 Add coupon/promo code system
- [ ] 2.1.6 Implement booking confirmation emails
- [ ] 2.1.7 Create booking modification/cancellation flows

**Deliverables:**
- Direct booking engine
- Availability search
- Coupon system

#### Week 6: OTA Integration Architecture

**Tasks:**
- [ ] 2.2.1 Create OTA Provider model (Booking.com, Expedia, etc.)
- [ ] 2.2.2 Build OTA mapping configuration UI
- [ ] 2.2.3 Implement room type mapping (our rooms → OTA rooms)
- [ ] 2.2.4 Create rate plan mapping
- [ ] 2.2.5 Build OTA sync queue system
- [ ] 2.2.6 Implement webhook handlers for OTA updates
- [ ] 2.2.7 Create OTA connection testing tools

**Deliverables:**
- OTA provider framework
- Mapping configuration
- Sync infrastructure

#### Week 7: Channel Manager Implementation

**Tasks:**
- [ ] 2.3.1 Implement availability push to OTAs
- [ ] 2.3.2 Implement rate push to OTAs
- [ ] 2.3.3 Build reservation pull from OTAs
- [ ] 2.3.4 Create reservation push to OTAs
- [ ] 2.3.5 Implement booking modifications sync
- [ ] 2.3.6 Add booking cancellations sync
- [ ] 2.3.7 Build OTA sync dashboard (success/failure rates)
- [ ] 2.3.8 Create OTA error handling and retry logic

**Deliverables:**
- Two-way OTA sync
- Channel manager dashboard
- Error handling

**Note:** Start with 1-2 OTA partners (e.g., Booking.com, Expedia) using their APIs

---

### Phase 3: Housekeeping & Maintenance (Weeks 8-9) **P1**

**Goal:** Streamline room operations and maintenance

#### Week 8: Housekeeping Management

**Tasks:**
- [ ] 3.1.1 Implement housekeeping task CRUD
- [ ] 3.1.2 Create auto-task generation on checkout
- [ ] 3.1.3 Build housekeeping board (Kanban style)
- [ ] 3.1.4 Implement room inspection workflow
- [ ] 3.1.5 Add housekeeping staff assignment
- [ ] 3.1.6 Create housekeeping mobile view
- [ ] 3.1.7 Build housekeeping performance metrics

**Deliverables:**
- Task management
- Auto-generation on checkout
- Mobile-friendly interface

#### Week 9: Maintenance Management

**Tasks:**
- [ ] 3.2.1 Implement maintenance request CRUD
- [ ] 3.2.2 Create maintenance request portal (staff can report)
- [ ] 3.2.3 Build maintenance assignment workflow
- [ ] 3.2.4 Add preventive maintenance scheduling
- [ ] 3.2.5 Implement maintenance history per room
- [ ] 3.2.6 Create maintenance cost tracking
- [ ] 3.2.7 Build maintenance dashboard (open/closed/resolved)

**Deliverables:**
- Request management
- Preventive maintenance
- Cost tracking

---

### Phase 4: POS System (Weeks 10-11) **P1**

**Goal:** Enable restaurant, bar, and spa charging

#### Week 10: POS Core

**Tasks:**
- [ ] 4.1.1 Create PosMenuItem model
- [ ] 4.1.2 Implement menu item CRUD
- [ ] 4.1.3 Build POS terminal UI (grid of items)
- [ ] 4.1.4 Create order workflow (draft → submitted → served → settled)
- [ ] 4.1.5 Implement table management (if applicable)
- [ ] 4.1.6 Add order modification capabilities
- [ ] 4.1.7 Build kitchen display integration (orders go to kitchen)

**Deliverables:**
- POS terminal
- Order management
- Kitchen display

#### Week 11: POS Integration with PMS

**Tasks:**
- [ ] 4.2.1 Implement charge-to-room functionality
- [ ] 4.2.2 Add guest folio integration (POS charges appear on folio)
- [ ] 4.2.3 Create outlet management (restaurant, bar, spa)
- [ ] 4.2.4 Build POS reporting (sales by outlet, by item)
- [ ] 4.2.5 Implement inventory tracking (optional)
- [ ] 4.2.6 Add payment processing integration
- [ ] 4.2.7 Create end-of-day reconciliation

**Deliverables:**
- Room charging
- Folio integration
- Sales reporting

---

### Phase 5: HR & Staff Management (Weeks 12-13) **P1**

**Goal:** Manage hotel staff and operations

#### Week 12: Employee Management

**Tasks:**
- [ ] 5.1.1 Create Attendance, Payroll, ShiftSchedule models
- [ ] 5.1.2 Implement employee CRUD
- [ ] 5.1.3 Build attendance tracking (check-in/check-out)
- [ ] 5.1.4 Create shift scheduling system
- [ ] 5.1.5 Implement leave management
- [ ] 5.1.6 Add employee document storage
- [ ] 5.1.7 Build employee directory

**Deliverables:**
- Employee profiles
- Attendance tracking
- Shift scheduling

#### Week 13: Payroll & Performance

**Tasks:**
- [ ] 5.2.1 Implement payroll calculation
- [ ] 5.2.2 Create payroll approval workflow
- [ ] 5.2.3 Build payroll reports
- [ ] 5.2.4 Add performance tracking (optional)
- [ ] 5.2.5 Implement task assignment to staff
- [ ] 5.2.6 Create staff performance dashboard
- [ ] 5.2.7 Add training/certification tracking

**Deliverables:**
- Payroll system
- Performance tracking
- Staff dashboard

---

### Phase 6: Reports & Analytics (Week 14-15) **P1**

**Goal:** Provide business intelligence

#### Week 14: Standard Reports

**Tasks:**
- [ ] 6.1.1 Create report generator service
- [ ] 6.1.2 Build occupancy report (daily/monthly/yearly)
- [ ] 6.1.3 Create ADR (Average Daily Rate) report
- [ ] 6.1.4 Implement RevPAR (Revenue Per Available Room) report
- [ ] 6.1.5 Build arrival/departure reports
- [ ] 6.1.6 Create in-house guest report
- [ ] 6.1.7 Add revenue report (by room type, by channel)

**Deliverables:**
- Core hotel metrics
- Standard reports suite

#### Week 15: Advanced Analytics

**Tasks:**
- [ ] 6.2.1 Build revenue chart (trend analysis)
- [ ] 6.2.2 Create booking pace report
- [ ] 6.2.3 Implement channel performance report
- [ ] 6.2.4 Add guest source analysis
- [ ] 6.2.5 Build forecast reports (occupancy, revenue)
- [ ] 6.2.6 Create custom report builder
- [ ] 6.2.7 Implement scheduled report delivery (email)

**Deliverables:**
- Analytics dashboard
- Forecasting
- Scheduled reports

---

### Phase 7: Mobile App (Weeks 16-17) **P2**

**Goal:** Enable staff mobility

#### Week 16: Mobile Web App

**Tasks:**
- [ ] 7.1.1 Create mobile-optimized layouts
- [ ] 7.1.2 Build mobile dashboard for staff
- [ ] 7.1.3 Implement mobile housekeeping view
- [ ] 7.1.4 Create mobile maintenance view
- [ ] 7.1.5 Add mobile check-in/out (for managers)
- [ ] 7.1.6 Implement push notifications (via PWA)
- [ ] 7.1.7 Build offline capability (service workers)

**Deliverables:**
- Mobile-responsive UI
- PWA capabilities
- Push notifications

#### Week 17: Native Mobile App (Optional)

**Tasks:**
- [ ] 7.2.1 Evaluate React Native / Flutter
- [ ] 7.2.2 Create API endpoints for mobile app
- [ ] 7.2.3 Implement mobile authentication
- [ ] 7.2.4 Build native housekeeping module
- [ ] 7.2.5 Build native maintenance module
- [ ] 7.2.6 Add biometric authentication
- [ ] 7.2.7 Implement offline sync

**Deliverables:**
- Native mobile apps (iOS/Android)
- Offline sync

---

### Phase 8: Advanced Features & Polish (Weeks 18-20) **P2**

**Goal:** Add advanced features and polish the system

#### Week 18: Notifications & Communication

**Tasks:**
- [ ] 8.1.1 Implement email notification system
- [ ] 8.1.2 Add SMS integration (Twilio, etc.)
- [ ] 8.1.3 Create notification templates
- [ ] 8.1.4 Build notification preferences
- [ ] 8.1.5 Implement in-app notifications
- [ ] 8.1.6 Add WhatsApp integration (optional)
- [ ] 8.1.7 Create automated messaging (pre-arrival, post-departure)

**Deliverables:**
- Multi-channel notifications
- Automated messaging

#### Week 19: Integration & API

**Tasks:**
- [ ] 8.2.1 Create public API documentation
- [ ] 8.2.2 Implement API rate limiting
- [ ] 8.2.3 Add API key management
- [ ] 8.2.4 Build payment gateway integration (Stripe, PayPal)
- [ ] 8.2.5 Add accounting integration (QuickBooks, Xero)
- [ ] 8.2.6 Implement door lock system integration (optional)
- [ ] 8.2.7 Add key card encoder integration

**Deliverables:**
- Public API
- Payment integration
- Third-party integrations

#### Week 20: Performance & Security

**Tasks:**
- [ ] 8.3.1 Implement query optimization
- [ ] 8.3.2 Add caching layer (Redis)
- [ ] 8.3.3 Conduct security audit
- [ ] 8.3.4 Implement audit logging
- [ ] 8.3.5 Add data backup automation
- [ ] 8.3.6 Perform load testing
- [ ] 8.3.7 Create disaster recovery plan

**Deliverables:**
- Optimized performance
- Security hardening
- Backup system

---

## Part 4: Technical Architecture Decisions

### 4.1 Multi-tenancy Strategy

**Approach:** Single database with `hotel_id` scoping

```php
// All models use HasHotel trait
trait HasHotel
{
    protected static function bootHasHotel()
    {
        static::creating(fn ($model) => $model->hotel_id = currentHotel()->id);
        static::addGlobalScope('hotel', fn ($builder) => $builder->where('hotel_id', currentHotel()->id));
    }
}
```

**Benefits:**
- Simple deployment
- Easy data sharing between hotels (if needed)
- Cost-effective

**Considerations:**
- Need strict scoping to prevent data leakage
- Large databases may need partitioning

### 4.2 Reference Number Generation

**Strategy:** Human-readable reference numbers

```php
// Format: HOTEL-YYYYMMDD-XXXX
class ReferenceGenerator
{
    public function generate(Model $model): string
    {
        $prefix = strtoupper($model->hotel->code);
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        
        return "{$prefix}-{$date}-{$random}";
    }
}
```

### 4.3 Status Workflows

Each entity has a status enum with defined transitions:

```
Reservation: draft → confirmed → checked_in → checked_out
                          ↓ cancelled

Room: available → occupied → dirty → clean → available
            ↓ out_of_order

Housekeeping: pending → in_progress → completed
```

### 4.4 Event-Driven Architecture

**Events trigger:**
- Notifications (email, SMS, push)
- External API calls (OTA sync)
- Background jobs (report generation)
- Audit logging

**Example:**
```php
Event::dispatch(new ReservationCreated($reservation));
// Listeners: SendConfirmationEmail, SyncToOTA, LogAudit
```

### 4.5 Queue Strategy

**Use queues for:**
- OTA sync operations
- Email/SMS sending
- Report generation
- Data imports/exports
- Webhook processing

**Recommended:** Redis queue driver for performance

---

## Part 5: Testing Strategy

### 5.1 Test Pyramid

```
        /\
       /  \      E2E Tests (10%)
      /----\     - Pest Browser tests
     /      \    - Critical user journeys
    /--------\   
   /          \  Integration Tests (30%)
  /------------\ - Service tests
 /              \- API tests
/----------------\ 
Unit Tests (60%)    - Action tests
                    - Model tests
```

### 5.2 Test Coverage Goals

| Component | Target Coverage |
|-----------|----------------|
| Models | 80% (relationships, scopes, accessors) |
| Services | 90% (business logic) |
| Actions | 90% (single responsibility) |
| Controllers | 50% (request/response) |
| Frontend | 40% (critical components) |

### 5.3 Testing Tools

- **Unit/Integration:** Pest PHP
- **E2E:** Pest Browser / Playwright
- **API:** Pest + Sanctum
- **Frontend:** Vitest + Vue Test Utils

---

## Part 6: Deployment & DevOps

### 6.1 Environment Setup

**Required Services:**
- PHP 8.3+
- MySQL 8.0+ / PostgreSQL 14+
- Redis 7.0+ (cache + queue)
- Node.js 20+ (frontend build)
- Nginx/Apache

### 6.2 Deployment Checklist

- [ ] Environment variables configured
- [ ] Database migrations run
- [ ] Queue worker configured
- [ ] Scheduler configured (cron)
- [ ] Storage linked
- [ ] SSL certificate installed
- [ ] Backup automation enabled
- [ ] Monitoring setup (Sentry, Logflare)

### 6.3 CI/CD Pipeline

```yaml
# GitHub Actions example
stages:
  - test (Pest, PHPStan, Pint)
  - build (assets compilation)
  - deploy (staging → production)
```

---

## Part 7: Success Metrics

### 7.1 Technical KPIs

| Metric | Target |
|--------|--------|
| Page Load Time | < 2 seconds |
| API Response Time | < 200ms (p95) |
| Uptime | 99.9% |
| Test Coverage | > 70% |
| Bug Resolution Time | < 24 hours |

### 7.2 Business KPIs

| Metric | Target |
|--------|--------|
| Direct Bookings | > 30% of total |
| OTA Commission Savings | Track monthly |
| Check-in Time | < 3 minutes |
| Guest Satisfaction | > 4.5/5 |
| Staff Efficiency | Rooms cleaned per hour |

---

## Part 8: Risk Mitigation

### 8.1 Technical Risks

| Risk | Impact | Mitigation |
|------|--------|------------|
| OTA API changes | High | Abstraction layer, versioning |
| Data breach | Critical | Encryption, audit logs, regular security audits |
| Performance degradation | High | Caching, query optimization, monitoring |
| Multi-tenancy data leakage | Critical | Strict scoping, testing, code review |

### 8.2 Business Risks

| Risk | Impact | Mitigation |
|------|--------|------------|
| Low adoption | High | Training, intuitive UI, support |
| OTA partnership delays | Medium | Start with 1-2 partners, mock data |
| Scope creep | Medium | Strict prioritization, phased approach |
| Budget overrun | Medium | Regular reviews, MVP focus |

---

## Part 9: Resource Requirements

### 9.1 Team Composition (Ideal)

| Role | Count | Responsibilities |
|------|-------|------------------|
| Backend Developer | 2 | Laravel, API, database |
| Frontend Developer | 2 | Vue 3, Inertia, TypeScript |
| Full-stack Developer | 1 | Both backend/frontend |
| QA Engineer | 1 | Testing, quality assurance |
| DevOps Engineer | 0.5 | Deployment, infrastructure |
| Product Manager | 0.5 | Requirements, prioritization |
| UI/UX Designer | 0.5 | Design, user experience |

**Total:** ~7 FTE

### 9.2 Timeline Summary

| Phase | Duration | Weeks |
|-------|----------|-------|
| Phase 1: Foundation & Core PMS | 4 weeks | 1-4 |
| Phase 2: Booking & Channel Manager | 3 weeks | 5-7 |
| Phase 3: Housekeeping & Maintenance | 2 weeks | 8-9 |
| Phase 4: POS System | 2 weeks | 10-11 |
| Phase 5: HR & Staff | 2 weeks | 12-13 |
| Phase 6: Reports & Analytics | 2 weeks | 14-15 |
| Phase 7: Mobile App | 2 weeks | 16-17 |
| Phase 8: Advanced Features | 3 weeks | 18-20 |

**Total:** 20 weeks (~5 months)

**Note:** Timeline can be compressed with larger team or extended with resource constraints.

---

## Part 10: Immediate Next Steps (Week 1)

### Priority Tasks

1. **Create Hotel Model**
   ```bash
   php artisan make:model Hotel
   ```

2. **Create Multi-tenancy Trait**
   ```bash
   php artisan make:trait HasHotel
   ```

3. **Create Missing HR Models**
   ```bash
   php artisan make:model Attendance
   php artisan make:model Payroll
   php artisan make:model ShiftSchedule
   ```

4. **Create Missing POS Model**
   ```bash
   php artisan make:model PosMenuItem
   ```

5. **Set up Hotel Management UI**
   - Super admin dashboard
   - Hotel CRUD
   - Hotel switching

6. **Implement Tenant Scoping**
   - Apply HasHotel trait to all models
   - Test data isolation

7. **Create Seeders**
   - Hotel seeder
   - Sample data for testing

---

## Appendix A: Database Schema Reference

See migration files in `database/migrations/` for complete schema.

**Core Tables:**
- `hotels` - Multi-tenancy
- `users` - Staff accounts
- `rooms` - Room inventory
- `reservations` - Bookings
- `guest_profiles` - Guest data
- `agents` - Travel agencies

**Module Tables:**
- `housekeeping_tasks`
- `maintenance_requests`
- `pos_orders`, `pos_menu_items`
- `employees`, `attendances`, `payrolls`, `shift_schedules`
- `report_snapshots`
- `mobile_tasks`
- `ota_syncs`

---

## Appendix B: API Endpoints Reference

### FrontDesk
- `GET/POST /api/v1/front-desk/reservations`
- `GET /api/v1/front-desk/reservations/{id}`
- `PUT /api/v1/front-desk/reservations/{id}`
- `DELETE /api/v1/front-desk/reservations/{id}`
- `POST /api/v1/front-desk/reservations/{id}/check-in`
- `POST /api/v1/front-desk/reservations/{id}/check-out`

### Booking
- `GET/POST /api/v1/booking/ota-syncs`
- `GET /api/v1/booking/availability`
- `POST /api/v1/booking/direct`

### Guest
- `GET/POST /api/v1/guests/profiles`
- `GET/POST /api/v1/guests/agents`

### Housekeeping
- `GET/POST /api/v1/housekeeping/tasks`
- `GET/POST /api/v1/housekeeping/maintenance`

### POS
- `GET/POST /api/v1/pos/orders`
- `POST /api/v1/pos/orders/{id}/charge-to-room`

### HR
- `GET/POST /api/v1/hr/employees`
- `GET/POST /api/v1/hr/attendances`
- `GET/POST /api/v1/hr/payrolls`

### Reports
- `GET /api/v1/reports/occupancy`
- `GET /api/v1/reports/revenue`
- `GET /api/v1/reports/arrivals`
- `GET /api/v1/reports/departures`

---

## Appendix C: Glossary

| Term | Definition |
|------|------------|
| **PMS** | Property Management System |
| **OTA** | Online Travel Agency (Booking.com, Expedia, etc.) |
| **ADR** | Average Daily Rate |
| **RevPAR** | Revenue Per Available Room |
| **Folio** | Guest's bill/account |
| **Channel Manager** | System that distributes inventory to OTAs |
| **Multi-tenancy** | Single instance serving multiple hotels |

---

## Document Control

**Version:** 1.0  
**Created:** March 17, 2026  
**Last Updated:** March 17, 2026  
**Author:** Development Team  
**Status:** Draft - Pending Review

---

## Next Actions

1. ✅ Review this plan with stakeholders
2. ⏳ Prioritize features for MVP
3. ⏳ Set up project management board (Jira, Linear, etc.)
4. ⏳ Begin Week 1 tasks
5. ⏳ Schedule weekly progress reviews
