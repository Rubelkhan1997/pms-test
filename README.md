# Hotel Property Management System (PMS)

**A modern, enterprise-grade Hotel Property Management System built with Laravel 12, Inertia.js, Vue 3, and TypeScript.**

![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel)
![Vue](https://img.shields.io/badge/Vue-3-4FC08D?style=flat&logo=vue.js)
![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=flat&logo=typescript)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15+-336791?style=flat&logo=postgresql)
![Tests](https://img.shields.io/badge/Tests-Pest-green?style=flat)

---

## 🏨 Overview

This is a comprehensive Hotel Property Management System inspired by **HotelRunner**, featuring:

- **Multi-tenant architecture** - Manage multiple hotels from a single installation
- **8 core modules** - Complete hotel operations coverage
- **OTA Integration** - Connect with Booking.com, Expedia, and other channels
- **Modern stack** - Laravel 12 + Inertia + Vue 3 + TypeScript
- **Enterprise-ready** - Security, performance, and scalability built-in

---

## 📦 Features by Module

| Module           | Features                                                                      |
| ---------------- | ----------------------------------------------------------------------------- |
| **FrontDesk**    | Reservations, Check-in/out, Room Grid, Availability Calendar, Rate Management |
| **Booking**      | Direct Booking Engine, OTA Sync, Channel Manager, Coupon Codes                |
| **Guest**        | Guest Profiles, Agent Management, Guest History, VIP Preferences              |
| **Housekeeping** | Task Management, Room Status, Maintenance Requests, Inspections               |
| **POS**          | Restaurant/Bar/Spa Orders, Menu Management, Charge-to-Room, Kitchen Display   |
| **HR**           | Employee Management, Attendance, Payroll, Shift Scheduling                    |
| **Reports**      | Occupancy Reports, ADR, RevPAR, Revenue Analytics, Forecasts                  |
| **Mobile**       | Staff Mobile App, PWA Support, Push Notifications, Offline Mode               |

---

## 🛠️ Tech Stack

### Backend

- **PHP 8.3+** with strict types
- **Laravel 12** - Framework
- **PostgreSQL 15+** - Database (MySQL supported)
- **Redis 7+** - Cache & Queue
- **Spatie Packages** - Permission, Data, Multitenancy

### Frontend

- **Vue 3** - Composition API
- **TypeScript 5** - Type safety
- **Inertia.js 2** - Server-driven SPA
- **Pinia 3** - State management
- **Tailwind CSS 4** - Styling
- **Vite 7** - Build tool

### Testing & Quality

- **Pest PHP 3** - Testing framework
- **PHPStan 8** - Static analysis
- **Laravel Pint** - Code style
- **Rector** - Automated refactoring

### DevOps

- **Docker** - Containerization
- **GitHub Actions** - CI/CD
- **PostgreSQL extensions** - UUID, Full-text search

---

## 📁 Project Structure

```
pms-test/
├── app/
│   ├── Base/              # Base classes (Service, Repository)
│   ├── Enums/             # PHP Enums
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
│   ├── Services/          # Shared services
│   └── Traits/            # Reusable traits
├── database/
│   ├── factories/         # Model factories
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   ├── js/
│   │   ├── Components/    # Vue components
│   │   ├── Composables/   # Vue composables
│   │   ├── Layouts/       # Layout components
│   │   ├── Pages/         # Inertia pages
│   │   ├── Stores/        # Pinia stores
│   │   └── types/         # TypeScript types
│   └── views/             # Blade templates
├── routes/
│   ├── api.php            # API routes
│   ├── web.php            # Web routes
│   └── channels.php       # Broadcast channels
├── tests/
│   ├── Feature/           # Feature tests
│   ├── Unit/              # Unit tests
│   └── Pest.php           # Pest configuration
├── docker/                # Docker configuration
├── .github/workflows/     # CI/CD pipelines
└── docs/                  # Documentation
```

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.3+
- PostgreSQL 15+ (or MySQL 8.0+)
- Node.js 20+
- Composer 2.6+
- Git

### Installation

```bash
# Clone repository
git clone <repository-url> pms-test
cd pms-test

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database (PostgreSQL recommended)
# Edit .env with your database credentials

# Run migrations and seed
php artisan migrate:fresh --seed

# Build assets
npm run build

# Start development server
composer run dev
```

Visit: http://localhost:8000

### Default Credentials

| Role        | Email              | Password |
| ----------- | ------------------ | -------- |
| Super Admin | admin@pms.test     | password |
| Front Desk  | frontdesk@pms.test | password |

---

## 🐳 Docker Setup

```bash
# Start all containers
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate:fresh --seed

# Build assets
docker-compose exec app npm run build
```

Visit: http://localhost:8080

---

## ✅ Testing

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific suite
composer test:unit
composer test:feature
```

---

## 📚 Documentation

| Document                                     | Description                 |
| -------------------------------------------- | --------------------------- |
| [SETUP_GUIDE.md](./SETUP_GUIDE.md)           | Complete setup instructions |
| [BEST_PRACTICES.md](./BEST_PRACTICES.md)     | Development best practices  |
| [DEVELOPMENT_PLAN.md](./DEVELOPMENT_PLAN.md) | 20-week development roadmap |

---

## 🔧 Development Commands

```bash
# Code quality
composer lint           # Auto-fix code style
composer analyse        # Static analysis (PHPStan)
composer refactor       # Auto-refactor code

# Testing
composer test           # Run all tests
composer test:coverage  # Run with coverage

# Development
composer run dev        # Start all dev services
composer ide            # Generate IDE helpers

# Database
php artisan migrate     # Run migrations
php artisan db:seed     # Run seeders
```

---

## 🔐 Security Features

- ✅ Multi-tenancy isolation (hotel_id scoping)
- ✅ Role-based access control (Spatie Permission)
- ✅ Input validation (Form Requests)
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection (Auto-escaping)
- ✅ CSRF protection (Inertia)
- ✅ Audit logging (All changes tracked)
- ✅ API authentication (Sanctum)
- ✅ Rate limiting (API throttling)

---

## ⚡ Performance Optimizations

- ✅ PostgreSQL with proper indexing
- ✅ Redis caching (Cache tags)
- ✅ Query optimization (Eager loading)
- ✅ Queue system (Background jobs)
- ✅ Asset optimization (Vite, code splitting)
- ✅ Database connection pooling

---

## 📊 Code Quality Standards

| Tool         | Purpose               | Target        |
| ------------ | --------------------- | ------------- |
| Laravel Pint | Code style            | PSR-12        |
| PHPStan      | Static analysis       | Level 8       |
| Rector       | Automated refactoring | PHP 8.3       |
| Pest         | Testing               | 70%+ coverage |

---

## 🗓️ Development Roadmap

| Phase | Duration    | Focus                                  |
| ----- | ----------- | -------------------------------------- |
| 1     | Weeks 1-4   | Core PMS (Reservations, Rooms, Guests) |
| 2     | Weeks 5-7   | Booking Engine + Channel Manager       |
| 3     | Weeks 8-9   | Housekeeping & Maintenance             |
| 4     | Weeks 10-11 | POS System                             |
| 5     | Weeks 12-13 | HR & Staff Management                  |
| 6     | Weeks 14-15 | Reports & Analytics                    |
| 7     | Weeks 16-17 | Mobile App                             |
| 8     | Weeks 18-20 | Advanced Features & Polish             |

See [DEVELOPMENT_PLAN.md](./DEVELOPMENT_PLAN.md) for details.

---

## 🤝 Contributing

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

---

## 📝 License

This project is proprietary software. All rights reserved.

---

## 🙏 Acknowledgments

- Inspired by [HotelRunner](https://www.hotelrunner.com/)
- Built with [Laravel](https://laravel.com/)
- UI powered by [Vue.js](https://vuejs.org/)
- Testing with [Pest PHP](https://pestphp.com/)

---

## 📞 Support

For support and questions:

- 📧 Email: support@pms.test
- 📖 Documentation: See `/docs` folder
- 🐛 Issues: GitHub Issues

---

## 📈 Project Status

**Current Phase:** Foundation & Setup

- ✅ Project scaffolding complete
- ✅ Database migrations created
- ✅ Testing infrastructure ready
- ✅ CI/CD pipeline configured
- ✅ Best practices documented
- ⏳ Core features in development

---

**Built with ❤️ for the hospitality industry**
