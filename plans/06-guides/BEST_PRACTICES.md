# PMS Best Practices Guide

## Property Management System - Enterprise Standards

This document outlines industry best practices for building a secure, performant, and maintainable Hotel Property Management System.

---

## Table of Contents

1. [Architecture Best Practices](#1-architecture-best-practices)
2. [Database & PostgreSQL Optimization](#2-database--postgresql-optimization)
3. [Security Best Practices](#3-security-best-practices)
4. [Performance Optimization](#4-performance-optimization)
5. [Testing Strategy](#5-testing-strategy)
6. [Code Quality & Developer Experience](#6-code-quality--developer-experience)
7. [API Design Standards](#7-api-design-standards)
8. [Frontend Best Practices](#8-frontend-best-practices)
9. [Monitoring & Observability](#9-monitoring--observability)
10. [Deployment & DevOps](#10-deployment--devops)

---

## 1. Architecture Best Practices

### 1.1 Domain-Driven Design (DDD) Principles

**Module Organization:**
```
app/Modules/FrontDesk/
├── Actions/           # Single-responsibility business actions
├── Controllers/
│   ├── Web/          # Inertia controllers
│   └── Api/V1/       # API controllers
├── Data/             # DTOs (Spatie Laravel Data)
├── Events/           # Domain events
├── Exceptions/       # Module-specific exceptions
├── Jobs/             # Queueable jobs
├── Listeners/        # Event listeners
├── Models/           # Eloquent models
├── Notifications/    # Notification classes
├── Observers/        # Model observers
├── Policies/         # Authorization policies
├── Requests/         # Form requests
├── Resources/        # API resources
├── Rules/            # Custom validation rules
├── Services/         # Business logic services
└── ValueObjects/     # Immutable value objects
```

**Why:** Clear separation of concerns, testability, maintainability

### 1.2 Service Layer Pattern

```php
<?php

namespace App\Modules\FrontDesk\Services;

use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Data\ReservationData;
use App\Modules\FrontDesk\Events\ReservationCreated;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReservationService
{
    public function __construct(
        private Reservation $reservation
    ) {}
    
    /**
     * Get paginated reservations with filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->reservation
            ->with(['room', 'guestProfile', 'createdBy'])
            ->applyFilters($filters)
            ->latest('created_at')
            ->paginate(15);
    }
    
    /**
     * Create reservation with business logic
     */
    public function create(ReservationData $data): Reservation
    {
        return $this->reservation->create($data->toArray());
    }
}
```

**Benefits:**
- Controllers stay thin
- Business logic is testable
- Easy to swap implementations
- Clear dependency injection

### 1.3 Action Classes (Single Responsibility)

```php
<?php

namespace App\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Enums\ReservationStatus;
use App\Modules\FrontDesk\Events\ReservationCheckedIn;

class CheckInReservation
{
    public function execute(Reservation $reservation): Reservation
    {
        throw_if(
            $reservation->status !== ReservationStatus::Confirmed,
            new \InvalidArgumentException('Only confirmed reservations can be checked in')
        );
        
        $reservation->update([
            'status' => ReservationStatus::CheckedIn,
            'actual_check_in' => now(),
        ]);
        
        $reservation->room->update(['status' => RoomStatus::Occupied]);
        
        event(new ReservationCheckedIn($reservation));
        
        return $reservation->fresh();
    }
}
```

**Usage:**
```php
// In controller
$checkIn = new CheckInReservation();
$reservation = $checkIn->execute($reservation);
```

### 1.4 Repository Pattern (Optional for Complex Queries)

```php
<?php

namespace App\Modules\FrontDesk\Repositories;

use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Database\Eloquent\Collection;

interface ReservationRepositoryInterface
{
    public function getAvailableRooms(\Carbon\Carbon $checkIn, \Carbon\Carbon $checkOut): Collection;
    public function getOccupancyRate(\Carbon\Carbon $start, \Carbon\Carbon $end): float;
}
```

---

## 2. Database & PostgreSQL Optimization

### 2.1 PostgreSQL-Specific Features

**Enable Extensions:**
```php
// In DatabaseServiceProvider
DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
DB::statement('CREATE EXTENSION IF NOT EXISTS "pg_trgm"');
```

**Use UUID for Primary Keys (Optional):**
```php
Schema::create('reservations', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
    // ... other columns
});
```

**Benefits:** Better for distributed systems, harder to guess IDs

### 2.2 Indexing Strategy

**Composite Indexes:**
```php
Schema::table('reservations', function (Blueprint $table) {
    // Common query patterns
    $table->index(['hotel_id', 'status']);
    $table->index(['hotel_id', 'check_in_date', 'check_out_date']);
    $table->index(['guest_profile_id', 'created_at']);
    
    // Partial index for active reservations (PostgreSQL)
    DB::statement("
        CREATE INDEX reservations_active_idx ON reservations (hotel_id, check_in_date) 
        WHERE status IN ('confirmed', 'checked_in')
    ");
});
```

**Full-Text Search (PostgreSQL):**
```php
// Migration
DB::statement("
    ALTER TABLE guest_profiles 
    ADD COLUMN search_vector tsvector 
    GENERATED ALWAYS AS (
        setweight(to_tsvector('english', coalesce(first_name, '')), 'A') ||
        setweight(to_tsvector('english', coalesce(last_name, '')), 'A') ||
        setweight(to_tsvector('english', coalesce(email, '')), 'B')
    ) STORED
");

DB::statement("CREATE INDEX guest_profiles_search_idx ON guest_profiles USING GIN (search_vector)");
```

**Query:**
```php
$guests = GuestProfile::whereRaw("search_vector @@ to_tsquery('english', ?)", [$search])
    ->get();
```

### 2.3 Query Optimization

**Use EXPLAIN ANALYZE:**
```bash
# In PostgreSQL
EXPLAIN ANALYZE SELECT * FROM reservations WHERE hotel_id = 1 AND status = 'confirmed';
```

**N+1 Prevention:**
```php
// ❌ BAD - N+1 query problem
$reservations = Reservation::all();
foreach ($reservations as $reservation) {
    echo $reservation->guestProfile->first_name; // Query per iteration
}

// ✅ GOOD - Eager loading
$reservations = Reservation::with('guestProfile', 'room')->get();
```

**Use Select Instead of Select Star:**
```php
// ❌ BAD
Reservation::all();

// ✅ GOOD
Reservation::select(['id', 'hotel_id', 'status', 'check_in_date', 'check_out_date'])->get();
```

### 2.4 Connection Pooling

**PostgreSQL Configuration (postgresql.conf):**
```conf
max_connections = 200
shared_buffers = 256MB
effective_cache_size = 1GB
work_mem = 16MB
maintenance_work_mem = 128MB
```

**Laravel Configuration (.env):**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pms_test
DB_USERNAME=pms_user
DB_PASSWORD=secure_password
DB_CHARSET=utf8
DB_PREFIX=
DB_PERSISTENT=true
```

### 2.5 Database Migrations Best Practices

```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            
            $table->string('reference')->unique();
            $table->enum('status', ['draft', 'confirmed', 'checked_in', 'checked_out', 'cancelled'])
                  ->default('draft');
            
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->timestamp('actual_check_in')->nullable();
            $table->timestamp('actual_check_out')->nullable();
            
            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);
            
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            
            $table->json('meta')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for common queries
            $table->index(['hotel_id', 'status']);
            $table->index(['hotel_id', 'check_in_date', 'check_out_date']);
            $table->index(['status', 'check_in_date']);
            
            // Prevent overlapping reservations (PostgreSQL)
            $table->rawIndex(
                '(hotel_id, daterange(check_in_date, check_out_date, \'[)\'))',
                'reservations_no_overlap_idx'
            );
        });
    }
};
```

---

## 3. Security Best Practices

### 3.1 Authentication & Authorization

**Multi-Tenancy Isolation:**
```php
<?php

namespace App\Traits;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Builder;

trait HasHotel
{
    protected static function bootHasHotel(): void
    {
        // Auto-scope queries to current hotel
        static::addGlobalScope('hotel', function (Builder $builder) {
            if ($hotelId = currentHotel()?->id) {
                $builder->where($builder->getModel()->getTable() . '.hotel_id', $hotelId);
            }
        });
        
        // Auto-assign hotel on create
        static::creating(function ($model) {
            if ($hotelId = currentHotel()?->id) {
                $model->hotel_id = $hotelId;
            }
        });
    }
    
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
```

**Policy-Based Authorization:**
```php
<?php

namespace App\Modules\FrontDesk\Policies;

use App\Models\User;
use App\Modules\FrontDesk\Models\Reservation;

class ReservationPolicy
{
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->hasRole('super_admin') 
            || $reservation->hotel_id === currentHotel()->id;
    }
    
    public function update(User $user, Reservation $reservation): bool
    {
        return $user->hasRole('super_admin') 
            || $user->hasPermissionTo('update reservations');
    }
    
    public function checkIn(User $user, Reservation $reservation): bool
    {
        return $user->hasRole(['super_admin', 'front_desk', 'hotel_admin']);
    }
}
```

### 3.2 Input Validation & Sanitization

**Form Request Validation:**
```php
<?php

namespace App\Modules\FrontDesk\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'guest_profile_id' => ['required', 'exists:guest_profiles,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'adults' => ['required', 'integer', 'min:1', 'max:10'],
            'children' => ['nullable', 'integer', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(['draft', 'confirmed'])],
        ];
    }
    
    public function messages(): array
    {
        return [
            'check_out_date.after' => 'Check-out date must be after check-in date',
            'adults.max' => 'Maximum 10 adults allowed per reservation',
        ];
    }
}
```

### 3.3 SQL Injection Prevention

```php
// ❌ VULNERABLE - Never do this
DB::select("SELECT * FROM reservations WHERE status = '$status'");

// ✅ SAFE - Use parameter binding
DB::select('SELECT * FROM reservations WHERE status = ?', [$status]);

// ✅ SAFER - Use Eloquent
Reservation::where('status', $status)->get();
```

### 3.4 XSS Prevention

**In Blade Templates:**
```blade
{{-- ✅ Auto-escaped --}}
{{ $reservation->guestName }}

{{-- ⚠️ Only use {!! !!} when you trust the content --}}
{!! $trustedHtmlContent !!}
```

**In Vue:**
```vue
<!-- ✅ Auto-escaped -->
<template>{{ guestName }}</template>

<!-- ⚠️ Only use v-html for trusted content -->
<div v-html="trustedContent"></div>
```

### 3.5 CSRF Protection

```php
// In forms (Blade)
<form method="POST">
    @csrf
    <!-- form fields -->
</form>

// In Vue/Inertia
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    // form data
});

form.post('/reservations'); // CSRF token auto-included
```

### 3.6 Rate Limiting

```php
// In routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/reservations', [ReservationController::class, 'store']);
});

// Stricter for authentication
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});
```

### 3.7 Encryption & Data Protection

**Encrypt Sensitive Data:**
```php
use Illuminate\Support\Facades\Crypt;

// Store encrypted
$guestProfile->passport_number = Crypt::encrypt($request->passport_number);

// Retrieve decrypted
$passportNumber = Crypt::decrypt($guestProfile->passport_number);
```

**Environment Variables:**
```env
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=false
APP_ENV=production
```

### 3.8 Audit Logging

```php
<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'old_values' => null,
                'new_values' => $model->getChanges(),
            ]);
        });
        
        static::updated(function ($model) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'old_values' => $model->getOriginal(),
                'new_values' => $model->getChanges(),
            ]);
        });
    }
}
```

---

## 4. Performance Optimization

### 4.1 Caching Strategy

**Redis Configuration:**
```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Cache Frequently Used Data:**
```php
use Illuminate\Support\Facades\Cache;

// Cache hotel settings (1 hour)
$settings = Cache::remember(
    "hotel.{$hotelId}.settings",
    3600,
    fn() => $hotel->settings
);

// Cache room availability (5 minutes)
$availableRooms = Cache::remember(
    "hotel.{$hotelId}.rooms.available.{$date}",
    300,
    fn() => $hotel->getAvailableRooms($date)
);
```

**Cache Tags for Invalidation:**
```php
// Store with tags
Cache::tags(['hotel-1', 'rooms'])
    ->remember('hotel.1.rooms', 3600, fn() => $rooms);

// Invalidate when room updated
Cache::tags(['hotel-1', 'rooms'])->flush();
```

### 4.2 Query Optimization

**Use Database Indexes:**
```php
// See Section 2.2 for indexing strategy
```

**Limit Results:**
```php
// Always paginate or limit
Reservation::latest()->limit(100)->get();
Reservation::paginate(15);
```

**Use Chunk for Large Datasets:**
```php
Reservation::where('hotel_id', $hotelId)
    ->chunk(200, function ($reservations) {
        foreach ($reservations as $reservation) {
            // Process
        }
    });
```

### 4.3 Eager Loading

```php
// ❌ BAD - N+1 queries
$reservations = Reservation::all();
foreach ($reservations as $r) {
    echo $r->guestProfile->first_name;
    echo $r->room->number;
}

// ✅ GOOD - Eager load relationships
$reservations = Reservation::with(['guestProfile', 'room'])->get();

// ✅ EVEN BETTER - Eager load with constraints
$reservations = Reservation::with([
    'guestProfile' => fn($q) => $q->select('id', 'first_name', 'last_name'),
    'room' => fn($q) => $q->select('id', 'number', 'type'),
])->get();
```

### 4.4 API Response Optimization

**Use API Resources:**
```php
<?php

namespace App\Modules\FrontDesk\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status->value,
            'check_in' => $this->check_in_date,
            'check_out' => $this->check_out_date,
            'guest' => new GuestProfileResource($this->whenLoaded('guestProfile')),
            'room' => new RoomResource($this->whenLoaded('room')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

**Sparse Fieldsets:**
```php
// Allow clients to request specific fields
// GET /api/v1/reservations?fields=id,reference,status
$reservations = Reservation::select($request->input('fields', ['id', 'reference']))->get();
```

### 4.5 Queue Long-Running Tasks

```php
<?php

namespace App\Modules\Booking\Jobs;

use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncReservationToOta implements ShouldQueue
{
    use Dispatchable, Queueable;
    
    public function __construct(
        private Reservation $reservation
    ) {}
    
    public function handle(): void
    {
        // Sync to Booking.com, Expedia, etc.
        // This can take seconds, so queue it
    }
}
```

**Usage:**
```php
SyncReservationToOta::dispatch($reservation);
```

### 4.6 Asset Optimization

**Vite Configuration:**
```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['vue', '@inertiajs/vue3', 'pinia'],
                    'charts': ['chart.js'],
                },
            },
        },
    },
});
```

---

## 5. Testing Strategy

### 5.1 Testing Pyramid

```
        /\
       /  \      E2E Tests (10%)
      /----\     - Critical user journeys
     /      \    - Cypress/Playwright
    /--------\   
   /          \  Integration Tests (30%)
  /------------\ - API tests
 /              \- Feature tests
/----------------\ 
Unit Tests (60%)    - Unit tests
                    - Action tests
                    - Service tests
```

### 5.2 Unit Tests

```php
<?php

namespace Tests\Unit\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Actions\CheckInReservation;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Enums\ReservationStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CheckInReservationTest extends TestCase
{
    #[Test]
    public function it_checks_in_confirmed_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->create([
            'status' => ReservationStatus::Confirmed,
        ]);
        
        $action = new CheckInReservation();
        
        // Act
        $result = $action->execute($reservation);
        
        // Assert
        $this->assertEquals(ReservationStatus::CheckedIn, $result->status);
        $this->assertNotNull($result->actual_check_in);
    }
    
    #[Test]
    public function it_throws_exception_for_non_confirmed_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->create([
            'status' => ReservationStatus::Cancelled,
        ]);
        
        $action = new CheckInReservation();
        
        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $action->execute($reservation);
    }
}
```

### 5.3 Feature Tests

```php
<?php

namespace Tests\Feature\Modules\FrontDesk;

use App\Models\User;
use Spatie\Permission\Models\Role;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    #[Test]
    public function front_desk_can_create_reservation(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('front_desk');
        
        $reservationData = [
            'room_id' => 1,
            'guest_profile_id' => 1,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'total_amount' => 500.00,
        ];
        
        // Act
        $response = $this->actingAs($user)
            ->post(route('reservations.store'), $reservationData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        
        $this->assertDatabaseHas('reservations', [
            'room_id' => 1,
            'adults' => 2,
        ]);
    }
    
    #[Test]
    public function unauthorized_user_cannot_create_reservation(): void
    {
        // Arrange
        $user = User::factory()->create();
        
        // Act & Assert
        $this->actingAs($user)
            ->post(route('reservations.store'), [])
            ->assertForbidden();
    }
}
```

### 5.4 API Tests

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservationApiTest extends TestCase
{
    use RefreshDatabase;
    
    #[Test]
    public function it_returns_reservations_paginated(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;
        
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/front-desk/reservations');
        
        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'reference',
                        'status',
                        'check_in_date',
                        'check_out_date',
                    ]
                ],
                'links',
                'meta',
            ]);
    }
}
```

### 5.5 Test Factories

```php
<?php

namespace Database\Factories;

use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'room_id' => Room::factory(),
            'guest_profile_id' => GuestProfile::factory(),
            'created_by' => User::factory(),
            'reference' => 'RES-' . strtoupper(fake()->unique()->randomNumber(6)),
            'status' => fake()->randomElement(ReservationStatus::cases()),
            'check_in_date' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'check_out_date' => fake()->dateTimeBetween('+31 days', '+60 days'),
            'adults' => fake()->numberBetween(1, 4),
            'children' => fake()->numberBetween(0, 2),
            'total_amount' => fake()->randomFloat(2, 100, 1000),
        ];
    }
    
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::Confirmed,
        ]);
    }
    
    public function checkedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::CheckedIn,
        ]);
    }
}
```

### 5.6 Test Coverage Goals

```xml
<!-- phpunit.xml -->
<coverage>
    <report>
        <html outputDirectory="coverage-report"/>
        <clover outputFile="coverage.xml"/>
    </report>
    <include>
        <directory suffix=".php">app/Modules</directory>
    </include>
    <threshold>
        <low>50</low>
        <high>80</high>
        <target>70</target>
    </threshold>
</coverage>
```

---

## 6. Code Quality & Developer Experience

### 6.1 PHP Code Standards

**Pest PHP (Linter):**
```json
// composer.json
{
    "scripts": {
        "lint": "pint",
        "lint:test": "pint --test"
    }
}
```

**PHPStan (Static Analysis):**
```neon
# phpstan.neon
parameters:
    level: 8
    paths:
        - app/
        - database/
    excludePaths:
        - app/Modules/*/Data/*
    checkGenericClassInNonGenericPositions: false
```

**Usage:**
```bash
composer lint          # Auto-fix
phpstan analyse        # Static analysis
```

### 6.2 TypeScript Configuration

```json
// tsconfig.json
{
  "compilerOptions": {
    "target": "ES2020",
    "module": "ESNext",
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "moduleResolution": "bundler",
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "preserve",
    "strict": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "noFallthroughCasesInSwitch": true,
    "skipLibCheck": true,
    "types": ["vite/client"]
  },
  "include": ["resources/js/**/*.ts", "resources/js/**/*.vue"],
  "exclude": ["node_modules"]
}
```

### 6.3 ESLint Configuration

```javascript
// .eslintrc.cjs
module.exports = {
  root: true,
  extends: [
    'plugin:vue/vue3-recommended',
    'eslint:recommended',
    '@vue/typescript/recommended',
  ],
  parser: 'vue-eslint-parser',
  parserOptions: {
    parser: '@typescript-eslint/parser',
    ecmaVersion: 2020,
  },
  rules: {
    'vue/multi-word-component-names': 'off',
    '@typescript-eslint/no-explicit-any': 'warn',
    '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
  },
};
```

### 6.4 Git Hooks (Husky)

```json
// package.json
{
  "scripts": {
    "prepare": "husky install",
    "lint": "eslint resources/js --ext .ts,.vue",
    "type-check": "vue-tsc --noEmit"
  },
  "devDependencies": {
    "husky": "^9.0.0",
    "lint-staged": "^15.0.0"
  },
  "lint-staged": {
    "*.php": ["pint"],
    "*.{ts,vue}": ["eslint --fix"],
    "*.{ts,vue}": ["vue-tsc --noEmit"]
  }
}
```

### 6.5 Debugging Tools

**Laravel Debugbar (Development):**
```bash
composer require barryvdh/laravel-debugbar --dev
```

**Spatie Laravel Ray:**
```bash
composer require spatie/laravel-ray --dev
```

**Usage:**
```php
use Spatie\LaravelRay\Ray;

ray($reservation)->green();
ray()->showQueries();
```

**Vue DevTools:**
- Install Vue DevTools browser extension
- Use Pinia devtools for state debugging

### 6.6 Documentation Standards

**PHPDoc:**
```php
/**
 * Check in a reservation
 * 
 * @throws \InvalidArgumentException If reservation is not confirmed
 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If reservation not found
 */
public function checkIn(Reservation $reservation): Reservation
{
    // ...
}
```

**README per Module:**
```markdown
# FrontDesk Module

## Overview
Handles reservations, room management, and guest check-in/out.

## Key Models
- Reservation
- Room
- RoomType

## Business Rules
- Only confirmed reservations can be checked in
- Check-out time is 12:00 PM by default
- ...
```

---

## 7. API Design Standards

### 7.1 RESTful Conventions

```
GET    /api/v1/reservations          # List
POST   /api/v1/reservations          # Create
GET    /api/v1/reservations/{id}     # Show
PUT    /api/v1/reservations/{id}     # Update
DELETE /api/v1/reservations/{id}     # Delete

# Sub-resources
GET    /api/v1/reservations/{id}/folio
POST   /api/v1/reservations/{id}/check-in
POST   /api/v1/reservations/{id}/check-out
```

### 7.2 Response Format

```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "type": "reservation",
    "attributes": {
      "reference": "HTL-20260317-A1B2",
      "status": "confirmed",
      "check_in_date": "2026-03-24",
      "check_out_date": "2026-03-27"
    },
    "relationships": {
      "guest": {
        "data": { "type": "guest", "id": "123" }
      }
    }
  },
  "meta": {
    "timestamp": "2026-03-17T10:30:00Z"
  }
}
```

### 7.3 Error Handling

```php
<?php

namespace App\Exceptions\Handler;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiExceptionHandler extends ExceptionHandler
{
    public function render($request, \Throwable $e)
    {
        if ($request->expectsJson()) {
            if ($e instanceof ValidationException) {
                return response()->json([
                    'error' => 'validation_error',
                    'message' => 'The given data was invalid',
                    'errors' => $e->errors(),
                ], 422);
            }
            
            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => 'Resource not found',
                ], 404);
            }
        }
        
        return parent::render($request, $e);
    }
}
```

### 7.4 API Versioning

```php
// routes/api.php
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::prefix('front-desk')->name('api.v1.front-desk.')->group(function () {
        Route::apiResource('reservations', ReservationController::class);
    });
});
```

---

## 8. Frontend Best Practices

### 8.1 Vue 3 Composition API

```vue
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import type { Reservation } from '@/types';

const props = defineProps<{
    reservations: Reservation[];
}>();

const search = ref('');
const loading = ref(false);

const filteredReservations = computed(() => {
    return props.reservations.filter(r => 
        r.reference.toLowerCase().includes(search.value.toLowerCase())
    );
});

async function checkIn(reservationId: number) {
    loading.value = true;
    try {
        await router.post(`/reservations/${reservationId}/check-in`);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div>
        <input v-model="search" placeholder="Search reservations..." />
        <div v-for="reservation in filteredReservations" :key="reservation.id">
            {{ reservation.reference }}
            <button @click="checkIn(reservation.id)" :disabled="loading">
                Check In
            </button>
        </div>
    </div>
</template>
```

### 8.2 Pinia State Management

```typescript
// resources/js/Stores/reservation.ts
import { defineStore } from 'pinia';
import type { Reservation } from '@/types';

interface ReservationState {
    reservations: Reservation[];
    selectedReservation: Reservation | null;
    loading: boolean;
}

export const useReservationStore = defineStore('reservation', {
    state: (): ReservationState => ({
        reservations: [],
        selectedReservation: null,
        loading: false,
    }),
    
    getters: {
        availableReservations: (state) => 
            state.reservations.filter(r => r.status === 'confirmed'),
    },
    
    actions: {
        async fetchReservations() {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/reservations');
                this.reservations = response.data.data;
            } finally {
                this.loading = false;
            }
        },
    },
});
```

### 8.3 Component Structure

```vue
<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    modelValue: string;
    label: string;
    error?: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const localValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});
</script>

<template>
    <div class="form-group">
        <label>{{ label }}</label>
        <input 
            v-model="localValue" 
            :disabled="disabled"
            class="input"
            :class="{ 'border-red-500': error }"
        />
        <span v-if="error" class="error">{{ error }}</span>
    </div>
</template>

<style scoped>
.form-group {
    margin-bottom: 1rem;
}
</style>
```

---

## 9. Monitoring & Observability

### 9.1 Logging

```env
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

**Custom Log Channel:**
```php
// config/logging.php
'channels' => [
    'audit' => [
        'driver' => 'daily',
        'path' => storage_path('logs/audit.log'),
        'level' => 'info',
    ],
],
```

### 9.2 Error Tracking

**Sentry Integration:**
```bash
composer require sentry/sentry-laravel
```

```php
// config/sentry.php
return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'traces_sample_rate' => 0.2,
];
```

### 9.3 Performance Monitoring

**Laravel Telescope (Development):**
```bash
composer require laravel/telescope
```

**New Relic / Datadog (Production):**
```env
NEW_RELIC_LICENSE_KEY=xxx
NEW_RELIC_APP_NAME=PMS Test
```

---

## 10. Deployment & DevOps

### 10.1 Environment Configuration

```env
# .env.production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pms.yourhotel.com

DB_CONNECTION=pgsql
DB_HOST=production-db-host
DB_DATABASE=pms_production

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

SANCTUM_STATEFUL_DOMAINS=pms.yourhotel.com
```

### 10.2 Deployment Checklist

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          
      - name: Install Dependencies
        run: composer install --no-dev --optimize-autoloader
        
      - name: Clear Cache
        run: php artisan config:cache && php artisan route:cache
        
      - name: Run Migrations
        run: php artisan migrate --force
        
      - name: Build Assets
        run: npm install && npm run build
```

### 10.3 Backup Strategy

```php
// config/backup.php
return [
    'backup' => [
        'source' => [
            'files' => [
                base_path(),
            ],
            'databases' => [
                'pgsql',
            ],
        ],
        'destination' => [
            'disk' => 's3',
            'path' => 'backups/' . env('APP_NAME'),
        ],
    ],
    'monitor_backups' => [
        'notify' => [
            'mail' => ['admin@hotel.com'],
        ],
    ],
];
```

---

## Conclusion

This best practices guide provides a comprehensive foundation for building a secure, performant, and maintainable Hotel PMS. Following these standards will ensure:

✅ **Security:** Multi-tenancy isolation, input validation, encryption, audit logging  
✅ **Performance:** Caching, query optimization, queue usage  
✅ **Maintainability:** Clean architecture, testing, code quality tools  
✅ **Scalability:** PostgreSQL optimization, Redis caching, horizontal scaling ready  
✅ **Developer Experience:** TypeScript, debugging tools, documentation  

**Next Steps:**
1. Review and adapt these practices to your specific needs
2. Set up development environment with all tools
3. Begin implementation following the development plan
4. Continuously improve based on feedback and metrics
