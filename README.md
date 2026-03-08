# Property Management System (PMS)

Laravel 12 + Inertia + Vue 3 + TypeScript project scaffold for a hotel PMS with 8 modules:

1. Front Desk & Reservations
2. Online Booking & OTA Integration
3. Guest & Agent Management
4. Housekeeping & Maintenance
5. POS (Restaurant, Bar, Spa)
6. Reports & Dashboard
7. Mobile App for Staff & Management
8. Human Resource (HR)

## Tech Stack

- PHP 8.3 compatible code style
- Laravel 12
- Inertia.js
- Vue 3 + TypeScript + Pinia
- Sanctum API auth
- Spatie Laravel Data
- Spatie Permission

## Module Architecture

Each module is organized under `app/Modules/<ModuleName>/` with:

- `Models/`
- `Controllers/Web/`
- `Controllers/Api/V1/`
- `Services/`
- `Actions/`
- `Data/`
- `Requests/`
- `Resources/`
- `Events/`
- `Listeners/`
- `Jobs/`
- `Notifications/`
- `Policies/`
- `Observers/`

## Frontend Structure

`resources/js/` includes:

- `Pages/` (module-based)
- `Components/Shared` and `Components/<Module>`
- `Composables/`
- `Stores/`
- `types/index.d.ts`
- `Layouts/`

## Setup

1. Install PHP dependencies:

```bash
composer install
```

2. Install JS dependencies:

```bash
npm install
```

3. Copy environment and generate key:

```bash
cp .env.example .env
php artisan key:generate
```

4. Run database migrations and seed sample data:

```bash
php artisan migrate
php artisan db:seed
```

5. Run development services:

```bash
composer run dev
```

## Seeded Roles

- `super_admin`
- `hotel_admin`
- `front_desk`
- `housekeeping`
- `pos_cashier`
- `hr_manager`
- `accountant`
- `maintenance`

## API

API routes are versioned in `routes/api.php` under `/api/v1` and protected by Sanctum.

## Notes

- Controllers call Services only (service layer pattern).
- Status fields use PHP enums where applicable.
- Module observers are registered in `AppServiceProvider`.
- Event/listener mappings are registered in `EventServiceProvider`.
