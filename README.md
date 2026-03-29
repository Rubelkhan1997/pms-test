# 🏨 Hotel Property Management System (PMS)

**A modern, enterprise-grade Hotel Property Management System built with Laravel 12, Inertia.js, Vue 3, and TypeScript.**

![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel)
![Vue](https://img.shields.io/badge/Vue-3-4FC08D?style=flat&logo=vue.js)
![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=flat&logo=typescript)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15+-336791?style=flat&logo=postgresql)
![Tests](https://img.shields.io/badge/Tests-Pest-green?style=flat)
![Status](https://img.shields.io/badge/Status-95%25%20Complete-blue?style=flat)

---

## 🎯 Project Status

**Current Phase:** Phase 2 - OTA/Channel Manager Integration  
**Overall Progress:** 95% Complete  
**Last Updated:** March 29, 2026

| Phase | Focus | Status | Completion |
|-------|-------|--------|------------|
| **1A** | Multi-tenancy + Subscriptions | ✅ Complete | 100% |
| **1B** | Core Operations | ✅ Complete | 100% |
| **1C** | Accounting | ✅ Complete | 100% |
| **1D** | Polish & Testing | ✅ Complete | 95% |
| **2**  | OTA/Channel Manager | 🔄 In Progress | 40% |

---

## 📖 Quick Navigation

| Documentation | Description |
|---------------|-------------|
| **[📋 Plans Overview](./plans/README.md)** | Complete project documentation index |
| **[🚀 Setup Guide](./plans/06-guides/SETUP_GUIDE.md)** | Get started in 5 minutes |
| **[📚 Developer Reference](./plans/06-guides/DEVELOPER_REFERENCE.md)** | Daily development guide |
| **[🏗️ Architecture](#-architecture)** | System design overview |
| **[📊 Features](#-features-by-module)** | Module breakdown |

---

## 🏨 Overview

This is a comprehensive Hotel Property Management System inspired by **HotelRunner**, featuring:

- **Multi-tenant SaaS architecture** - Manage multiple hotels from a single installation
- **150+ database tables** - Enterprise-grade data model
- **8 core modules** - Complete hotel operations coverage
- **OTA Integration** - Connect with Booking.com, Expedia, and other channels
- **Modern stack** - Laravel 12 + Inertia + Vue 3 + TypeScript
- **Production-ready** - Security, performance, and scalability built-in

---

## 📦 Features by Module

### Front Desk Operations
| Feature | Description |
|---------|-------------|
| Reservations | Create, modify, cancel bookings |
| Room Grid | Visual room availability calendar |
| Check-in/out | Guest arrival and departure workflows |
| Rate Management | Dynamic pricing and rate plans |
| Availability | Real-time room inventory management |

### Booking Engine
| Feature | Description |
|---------|-------------|
| Direct Booking | Commission-free bookings via hotel website |
| Channel Manager | OTA connectivity (Booking.com, Expedia) |
| Rate Parity | Consistent pricing across channels |
| Coupon Codes | Promotional discounts and packages |

### Guest Management
| Feature | Description |
|---------|-------------|
| Guest Profiles | Complete guest history and preferences |
| Agent Management | Travel agent and corporate accounts |
| VIP Tracking | Special guest recognition and amenities |
| Communication | Email and SMS notifications |

### Housekeeping
| Feature | Description |
|---------|-------------|
| Task Management | Automated room assignment |
| Room Status | Real-time cleanliness tracking |
| Maintenance | Work order management |
| Inspections | Quality assurance checklists |

### Point of Sale (POS)
| Feature | Description |
|---------|-------------|
| Restaurant/Bar | Table service and orders |
| Spa Services | Treatment bookings |
| Charge-to-Room | Guest folio integration |
| Kitchen Display | Order management screens |
| Inventory | Stock tracking and alerts |

### Human Resources
| Feature | Description |
|---------|-------------|
| Employee Management | Staff profiles and documents |
| Attendance | Time tracking and schedules |
| Payroll | Salary and benefits administration |
| Recruitment | Hiring workflow |

### Reports & Analytics
| Feature | Description |
|---------|-------------|
| Occupancy Reports | Room utilization metrics |
| ADR/RevPAR | Revenue performance indicators |
| Financial Reports | Revenue, taxes, service charges |
| Forecasts | Predictive analytics |

### Mobile App
| Feature | Description |
|---------|-------------|
| PWA Support | Installable web app |
| Push Notifications | Real-time alerts |
| Offline Mode | Work without internet |
| Mobile Check-in | Guest self-service |

---

## 🛠️ Technology Stack

### Backend
```
PHP 8.3+          → Strict types, modern features
Laravel 12        → MVC framework, ORM, queues
PostgreSQL 15+    → Primary database (MySQL supported)
Redis 7+          → Cache, sessions, queues
Spatie Packages  → Permission, Data, Multitenancy
```

### Frontend
```
Vue 3             → Composition API, reactivity
TypeScript 5      → Type safety, IDE support
Inertia.js 2      → Server-driven SPA
Pinia 3           → State management
Tailwind CSS 4    → Utility-first styling
Vite 7            → Fast build tooling
```

### Testing & Quality
```
Pest PHP 3        → Elegant testing framework
PHPStan 8         → Static analysis (Level 8)
Laravel Pint      → Code style (PSR-12)
Rector            → Automated refactoring
```

### DevOps
```
Docker            → Containerization
GitHub Actions    → CI/CD pipelines
PostgreSQL Ext    → UUID, full-text search
```

---

## 🏗️ Architecture

### Multi-Tenancy Model

```
┌─────────────────────────────────────┐
│         Central Database            │
│  ┌─────────────────────────────┐    │
│  │  Tenants                    │    │
│  │  Users                      │    │
│  │  Subscriptions              │    │
│  │  Authentication             │    │
│  └─────────────────────────────┘    │
└──────────────┬──────────────────────┘
               │
        ┌──────┴──────┐
        │             │
   ┌────▼────┐   ┌───▼────┐
   │Hotel A  │   │Hotel B │  ← Tenant Databases
   │Database │   │Database│
   └─────────┘   └────────┘
```

### Database Statistics
- **Central Tables:** 10+
- **Tenant Tables:** 140+
- **Total Tables:** 150+
- **Models:** 80+
- **Services:** 15+
- **API Endpoints:** 120+

---

## 📁 Project Structure

```
pms/
├── 📂 app/
│   ├── Base/              # Base classes (Service, Repository)
│   ├── Enums/             # PHP Enums (RoomType, Status, etc.)
│   ├── Helpers/           # Helper functions
│   ├── Http/              # Controllers, Middleware, Requests
│   ├── Models/            # Eloquent Models
│   ├── Modules/           # Feature modules
│   │   ├── Booking/       # OTA & Channel Manager
│   │   ├── FrontDesk/     # Reservations & Rooms
│   │   ├── Guest/         # Guest & Agent Management
│   │   ├── Housekeeping/  # Housekeeping & Maintenance
│   │   ├── Hr/            # Human Resources
│   │   ├── Mobile/        # Mobile App
│   │   ├── Pos/           # Point of Sale
│   │   └── Reports/       # Analytics & Reports
│   ├── Providers/         # Service Providers
│   ├── Services/          # Shared services (Pricing, Availability)
│   └── Traits/            # Reusable traits
├── 📂 database/
│   ├── factories/         # Model factories
│   ├── migrations/        # 20+ migration files
│   └── seeders/           # Database seeders
├── 📂 resources/
│   ├── js/
│   │   ├── Components/    # Vue components (shadcn/ui)
│   │   ├── Composables/   # Vue composables (VueUse)
│   │   ├── Layouts/       # Layout components
│   │   ├── Pages/         # Inertia pages
│   │   ├── Stores/        # Pinia stores
│   │   └── types/         # TypeScript types
│   └── views/             # Blade templates
├── 📂 routes/
│   ├── api.php            # API routes
│   ├── web.php            # Web routes
│   ├── central.php        # Central system routes
│   └── channels.php       # Broadcast channels
├── 📂 tests/
│   ├── Feature/           # Feature tests
│   ├── Unit/              # Unit tests
│   └── Pest.php           # Pest configuration
├── 📂 plans/              # 📚 Project documentation
│   ├── 00-PROJECT_STATUS.md
│   ├── 01-strategy/
│   ├── 02-phase-1a/
│   ├── 03-phase-1b/
│   ├── 04-phase-1c/
│   ├── 05-technical/
│   ├── 06-guides/
│   ├── 07-phase-2/
│   └── 08-ai-generated/   # AI-generated reference docs
├── 📂 docker/             # Docker configuration
├── 📂 .github/workflows/  # CI/CD pipelines
└── 📄 docker-compose.yml  # Docker Compose
```

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.3+
- PostgreSQL 15+ (or MySQL 8.0+)
- Node.js 20+
- Composer 2.6+
- Git
- Redis 7+ (optional for development)

### Installation

```bash
# Clone repository
git clone <repository-url> pms
cd pms

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database (PostgreSQL recommended)
# Edit .env with your database credentials:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=pms
# DB_USERNAME=postgres
# DB_PASSWORD=secret

# Run migrations and seed
php artisan migrate:fresh --seed

# Build assets
npm run build

# Start development server
composer run dev
```

Visit: http://localhost:8000

### Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@pms.test | password |
| Front Desk | frontdesk@pms.test | password |

---

## 🐳 Docker Setup

```bash
# Start all containers
docker-compose up -d

# Check container status
docker-compose ps

# Run migrations
docker-compose exec app php artisan migrate:fresh --seed

# Build assets
docker-compose exec app npm run build

# View logs
docker-compose logs -f app
```

Visit: http://localhost:8080

### Docker Services
- `app` - PHP/Laravel application
- `db` - PostgreSQL database
- `redis` - Redis cache
- `mailhog` - Email testing
- `pgadmin` - Database management

---

## ✅ Testing

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific test suites
composer test:unit     # Unit tests
composer test:feature  # Feature tests

# Run single test file
php artisan test tests/Feature/ReservationTest.php
```

### Test Coverage Goals
- **Unit Tests:** 80%+ coverage
- **Feature Tests:** All critical paths
- **Integration Tests:** API endpoints

---

## 📚 Documentation

### Getting Started
| Document | Description |
|----------|-------------|
| [Setup Guide](./plans/06-guides/SETUP_GUIDE.md) | Complete installation instructions |
| [Best Practices](./plans/06-guides/BEST_PRACTICES.md) | Development standards |
| [Developer Reference](./plans/06-guides/DEVELOPER_REFERENCE.md) | Daily development guide |

### Project Documentation
| Document | Description |
|----------|-------------|
| [Plans Overview](./plans/README.md) | Complete documentation index |
| [Phase 1A Complete](./plans/02-phase-1a/PHASE1A_COMPLETE.md) | Multi-tenancy implementation |
| [Phase 1B Complete](./plans/03-phase-1b/PHASE1B_SUMMARY.md) | Core operations |
| [Phase 1C Complete](./plans/04-phase-1c/PHASE1C_ACCOUNTING_COMPLETE.md) | Accounting system |
| [Phase 2 OTA](./plans/07-phase-2/PHASE2_OTA_CHANNEL_MANAGER.md) | Channel manager (WIP) |

### Technical Reference
| Document | Description |
|----------|-------------|
| [Migration Checklist](./plans/05-technical/MIGRATION_MODEL_CHECKLIST.md) | Database schema reference |
| [Testing Summary](./plans/05-technical/TESTING_SUMMARY.md) | Testing strategy |
| [Deployment Guide](./plans/08-ai-generated/04-technical-guides/DEPLOYMENT_GUIDE.md) | Production deployment |

### AI-Generated Archive
| Document | Description |
|----------|-------------|
| [Completion Reports](./plans/08-ai-generated/01-completion-reports/) | Project milestones |
| [Migration Guides](./plans/08-ai-generated/02-migration-guides/) | Database documentation |
| [Central System](./plans/08-ai-generated/03-central-system/) | Multi-tenant guides |
| [Technical Guides](./plans/08-ai-generated/04-technical-guides/) | Reference material |

---

## 🔧 Development Commands

```bash
# Code Quality
composer lint           # Auto-fix code style (Pint)
composer analyse        # Static analysis (PHPStan)
composer refactor       # Auto-refactor code (Rector)

# Testing
composer test           # Run all tests
composer test:coverage  # Run with coverage
composer test:unit      # Unit tests only
composer test:feature   # Feature tests only

# Development
composer run dev        # Start all dev services
composer ide            # Generate IDE helpers

# Database
php artisan migrate     # Run migrations
php artisan db:seed     # Run seeders
php artisan db:wipe     # Drop all tables
```

---

## 🔐 Security Features

| Feature | Implementation |
|---------|----------------|
| Multi-tenancy Isolation | Database-per-tenant architecture |
| Authentication | Laravel Sanctum + Spatie Permission |
| Authorization | Role-based access control (RBAC) |
| Input Validation | Form Request classes |
| SQL Injection | Eloquent ORM (parameterized queries) |
| XSS Protection | Auto-escaping in Blade/Vue |
| CSRF Protection | Inertia.js + Laravel tokens |
| Audit Logging | All changes tracked with user/timestamp |
| API Security | Sanctum tokens + rate limiting |
| Data Encryption | Encrypted sensitive fields |

---

## ⚡ Performance Optimizations

| Optimization | Description |
|--------------|-------------|
| Database | PostgreSQL with proper indexing |
| Caching | Redis cache tags for invalidation |
| Queries | Eager loading (N+1 prevention) |
| Queues | Background job processing |
| Assets | Vite code splitting + lazy loading |
| Connections | Database connection pooling |
| API | Response caching + throttling |

---

## 📊 Code Quality Standards

| Tool | Purpose | Target | Status |
|------|---------|--------|--------|
| Laravel Pint | Code style | PSR-12 | ✅ Enforced |
| PHPStan | Static analysis | Level 8 | ✅ Passing |
| Rector | Automated refactoring | PHP 8.3 | ✅ Configured |
| Pest | Testing | 70%+ coverage | 🔄 In Progress |

---

## 🗓️ Development Roadmap

### Phase 1: Foundation ✅ COMPLETE
| Phase | Duration | Focus | Status |
|-------|----------|-------|--------|
| 1A | Weeks 1-2 | Multi-tenancy + Subscriptions | ✅ 100% |
| 1B | Weeks 3-5 | Core Operations (Rooms, Rates, Reservations) | ✅ 100% |
| 1C | Weeks 6-7 | Accounting + Financial Controls | ✅ 100% |
| 1D | Weeks 8-9 | Polish + Testing + UI | ✅ 95% |

### Phase 2: Integration 🔄 IN PROGRESS
| Phase | Duration | Focus | Status |
|-------|----------|-------|--------|
| 2A | Weeks 10-11 | Channel Manager (Basic) | 🔄 40% |
| 2B | Weeks 12-13 | OTA Integration (Booking.com) | ⏳ Pending |
| 2C | Weeks 14-15 | OTA Integration (Expedia, Others) | ⏳ Pending |

### Future Phases
| Phase | Focus | Timeline |
|-------|-------|----------|
| 3 | Advanced Features (AI Pricing, Analytics) | Q2 2026 |
| 4 | Mobile App Enhancements | Q3 2026 |
| 5 | Enterprise Features | Q4 2026 |

---

## 🤝 Contributing

### How to Contribute

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

### Code Review Checklist

- [ ] Tests added/updated
- [ ] Code follows style guide (Pint)
- [ ] Static analysis passes (PHPStan)
- [ ] Documentation updated
- [ ] No security issues introduced
- [ ] Database migrations included (if needed)
- [ ] Frontend types defined (TypeScript)

---

## 📝 License

This project is **proprietary software**. All rights reserved.

---

## 🙏 Acknowledgments

- **Inspired by:** [HotelRunner](https://www.hotelrunner.com/)
- **Framework:** [Laravel](https://laravel.com/)
- **Frontend:** [Vue.js](https://vuejs.org/)
- **Testing:** [Pest PHP](https://pestphp.com/)
- **UI Components:** [shadcn/ui](https://ui.shadcn.com/)
- **Composables:** [VueUse](https://vueuse.org/)

---

## 📞 Support

### Getting Help

- 📖 **Documentation:** See [`/plans`](./plans/) folder
- 🐛 **Issues:** GitHub Issues
- 📧 **Email:** support@pms.test

### Quick Links

| Link | Description |
|------|-------------|
| [Project Status](./plans/00-PROJECT_STATUS.md) | Current development status |
| [Setup Guide](./plans/06-guides/SETUP_GUIDE.md) | Installation instructions |
| [Developer Guide](./plans/06-guides/DEVELOPER_REFERENCE.md) | Development workflow |
| [Deployment](./plans/08-ai-generated/04-technical-guides/DEPLOYMENT_GUIDE.md) | Production deployment |

---

## 📈 Project Metrics

### Code Statistics
- **Total Files:** 200+
- **Lines of Code:** 30,000+
- **Database Tables:** 150+
- **Models:** 80+
- **API Endpoints:** 120+

### Test Coverage
- **Unit Tests:** 50+
- **Feature Tests:** 30+
- **Overall Coverage:** 65% (Target: 70%)

---

**Built with ❤️ for the hospitality industry**

*Last Updated: March 29, 2026*
