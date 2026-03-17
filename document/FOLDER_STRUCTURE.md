# Folder Structure - PMS Vue 3 + Laravel (Modular)

## Application Structure

```
pms-test/
├── app/
│   ├── Enums/                      # Global Enums
│   │   ├── ReservationStatus.php
│   │   ├── RoomStatus.php
│   │   ├── PaymentMethod.php
│   │   └── UserRole.php
│   │
│   ├── Http/                       # Global HTTP layer
│   │   ├── Controllers/
│   │   │   └── Controller.php
│   │   └── Middleware/
│   │
│   ├── Models/                     # Global Models
│   │   └── User.php
│   │
│   ├── Modules/                    # Domain Modules (Main Business Logic)
│   │   │
│   │   ├── FrontDesk/              # Reservation & Room Management
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/
│   │   │   │   │   └── V1/
│   │   │   │   │       └── ReservationController.php
│   │   │   │   └── Web/
│   │   │   │       └── ReservationController.php
│   │   │   ├── Models/
│   │   │   │   ├── Reservation.php
│   │   │   │   └── Room.php
│   │   │   ├── Services/
│   │   │   │   └── ReservationService.php
│   │   │   ├── Requests/
│   │   │   │   └── StoreReservationRequest.php
│   │   │   ├── Resources/
│   │   │   │   └── ReservationResource.php
│   │   │   ├── Data/
│   │   │   │   └── ReservationData.php
│   │   │   ├── Actions/
│   │   │   │   └── CreateReservationAction.php
│   │   │   ├── Events/
│   │   │   │   └── ReservationCreated.php
│   │   │   ├── Listeners/
│   │   │   │   └── SendReservationCreatedNotification.php
│   │   │   ├── Notifications/
│   │   │   │   └── ReservationStatusNotification.php
│   │   │   ├── Observers/
│   │   │   │   └── ReservationObserver.php
│   │   │   ├── Policies/
│   │   │   │   └── ReservationPolicy.php
│   │   │   └── Jobs/
│   │   │       └── SyncReservationToChannelJob.php
│   │   │
│   │   ├── Guest/                  # Guest Management
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/V1/GuestProfileController.php
│   │   │   │   └── Web/GuestProfileController.php
│   │   │   ├── Models/
│   │   │   │   ├── GuestProfile.php
│   │   │   │   └── Agent.php
│   │   │   ├── Services/
│   │   │   │   └── GuestProfileService.php
│   │   │   ├── Requests/
│   │   │   ├── Resources/
│   │   │   ├── Data/
│   │   │   ├── Actions/
│   │   │   ├── Events/
│   │   │   ├── Listeners/
│   │   │   ├── Notifications/
│   │   │   ├── Observers/
│   │   │   ├── Policies/
│   │   │   └── Jobs/
│   │   │
│   │   ├── Housekeeping/           # Housekeeping Management
│   │   │   ├── Controllers/
│   │   │   ├── Models/
│   │   │   │   ├── HousekeepingTask.php
│   │   │   │   └── MaintenanceRequest.php
│   │   │   ├── Services/
│   │   │   └── ...
│   │   │
│   │   ├── Hr/                     # HR & Employee Management
│   │   │   ├── Controllers/
│   │   │   ├── Models/
│   │   │   │   └── Employee.php
│   │   │   ├── Services/
│   │   │   └── ...
│   │   │
│   │   ├── Pos/                    # Point of Sale
│   │   │   ├── Controllers/
│   │   │   ├── Models/
│   │   │   │   └── PosOrder.php
│   │   │   ├── Services/
│   │   │   └── ...
│   │   │
│   │   ├── Reports/                # Reports & Analytics
│   │   │   ├── Controllers/
│   │   │   ├── Services/
│   │   │   └── ...
│   │   │
│   │   ├── Booking/                # OTA & Booking Management
│   │   │   ├── Controllers/
│   │   │   ├── Models/
│   │   │   │   └── OtaSync.php
│   │   │   ├── Services/
│   │   │   │   └── OtaSyncService.php
│   │   │   └── ...
│   │   │
│   │   └── Mobile/                 # Mobile Task Management
│   │       ├── Controllers/
│   │       ├── Models/
│   │       │   └── MobileTask.php
│   │       ├── Services/
│   │       └── ...
│   │
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── ModuleServiceProvider.php


```
## Directory Structure

```
resources/js/
│
├── Components/                   # Reusable UI Components
│   ├── Common/                   # Global shared components
│   │   ├── AppButton.vue
│   │   ├── AppInput.vue
│   │   ├── AppModal.vue
│   │   ├── AppTable.vue
│   │   ├── AppCard.vue
│   │   ├── AppDropdown.vue
│   │   ├── AppBadge.vue
│   │   └── AppAvatar.vue
│   │
│   ├── Forms/                    # Form components
│   │   ├── DatePicker.vue
│   │   ├── TimePicker.vue
│   │   ├── FileUpload.vue
│   │   ├── SelectInput.vue
│   │   └── TextArea.vue
│
├── Composables/                   # Business Logic (Services equivalent)
│   ├── useReservations.ts        # Reservation business logic
│   ├── useRooms.ts               # Room business logic
│   ├── useGuests.ts              # Guest business logic 
|
│
├── Helpers/                       # Shared Helper Functions 
│   ├── useLoading.ts             # Loading state management
│   ├── useMessage.ts             # Message/notification management
│   ├── usePolling.ts             # Polling functionality
│   ├── useDebounce.ts            # Debounce utility
│   ├── useThrottle.ts            # Throttle utility
│   └── index.ts                  # Central export file
│
|
├── Layouts/                        
│   ├── AppLayout.vue
│   ├── HotelLayout.vue
│   ├── MobileLayout.vue
│   
|
├── Pages/                         # Inertia Page Components (Controllers equivalent)
│   ├── Dashboard/
│   │   └── Index.vue
│   │
│   ├── Reservations/
│   │   ├── Index.vue             # List all reservations
│   │   ├── Show.vue              # View single reservation
│   │   ├── Create.vue            # Create reservation form
│   │   └── Edit.vue              # Edit reservation form
│   │

|
├── Stores/                        # Pinia Stores (State Management)
│   ├── reservationStore.ts       # Reservation state
│   ├── roomStore.ts              # Room state
│   ├── guestStore.ts             # Guest state
│   ├── authStore.ts              # Authentication state
│   ├── settingsStore.ts          # Application settings
│   ├── notificationStore.ts      # Global notifications
│   └── index.ts                  # Store registry
│
├── Types/                         # TypeScript Type Definitions (Models equivalent)
│   ├── reservation.ts            # Reservation interfaces/types
│   ├── room.ts                   # Room interfaces/types
│   ├── guest.ts                  # Guest interfaces/types
│   ├── user.ts                   # User interfaces/types
│   ├── payment.ts                # Payment interfaces/types
│   ├── report.ts                 # Report interfaces/types
│   ├── common.ts                 # Common/shared types
│   ├── api.ts                    # API response types
│   └── index.ts                  # Central export file
│
├── Services/                      # API Service Layer (Optional - for complex API calls)
│   ├── reservationService.ts     # Reservation API calls
│   ├── roomService.ts            # Room API calls
│   ├── guestService.ts           # Guest API calls
│   ├── authService.ts            # Authentication API calls
│   ├── reportService.ts          # Reports API calls
│   └── apiClient.ts              # Axios instance configuration
│
├── Utils/                         # Pure Utility Functions
│   ├── date.ts                   # Date formatting/manipulation
│   ├── format.ts                 # Number/currency formatting
│   ├── validation.ts             # Validation rules/helpers
│   ├── storage.ts                # LocalStorage/SessionStorage helpers
│   └── constants.ts              # Application constants
│
├── Plugins/                       # Vue Plugins
│   ├── toast.ts                  # Toast notification plugin
│   ├── confirm.ts                # Confirmation dialog plugin
│   └── directives/               # Custom Vue directives
│       ├── permission.ts         # Role-based permission directive
│       └── focus.ts              # Auto-focus directive
│
|
├── Styles/                        # Global Styles
│   ├── main.scss                 # Main stylesheet
│   ├── variables.scss            # SCSS variables
│   ├── mixins.scss               # SCSS mixins
│   └── components/               # Component-specific styles
│
├── Assets/                        # Static Assets
│   ├── images/                   # Images
│   ├── icons/                    # SVG icons
│   └── fonts/                    # Custom fonts
│
├── Locales/                       # Internationalization (i18n)
│   ├── en/                       # English translations
│   │   ├── common.json
│   │   ├── reservations.json
│   │   └── ...
│   ├── bn/                       # Bengali translations
│   │   └── ...
│   └── index.ts
│
├── Tests/                         # Test Files
│   ├── unit/                     # Unit tests
│   ├── components/               # Component tests
│   └── e2e/                      # End-to-end tests
│
└── app.ts                         # Application Entry Point
```

---

## File Naming Conventions

| Type | Convention | Example |
|------|-----------|---------|
| **Components** | PascalCase | `AppButton.vue`, `ReservationCard.vue` |
| **Pages** | PascalCase | `Index.vue`, `Show.vue`, `Create.vue` |
| **Composables** | camelCase with `use` prefix | `useReservations.ts`, `useAuth.ts` |
| **Stores** | camelCase with `Store` suffix | `reservationStore.ts`, `authStore.ts` |
| **Types** | camelCase | `reservation.ts`, `user.ts` |
| **Services** | camelCase with `Service` suffix | `reservationService.ts` |
| **Helpers** | camelCase with `use` prefix | `useLoading.ts`, `useMessage.ts` |
| **Utils** | camelCase | `date.ts`, `format.ts` |

---

## Laravel to Vue 3 Mapping

| Laravel Concept | Vue 3 Equivalent | Location |
|----------------|------------------|----------|
| `app/Models` | TypeScript Types | `Types/` |
| `app/Http/Controllers` | Pages | `Pages/` |
| `app/Http/Resources` | Components | `Components/` |
| `app/Services` | Composables/Services | `Composables/` or `Services/` |
| `app/Providers` | Pinia Stores | `Stores/` |
| `routes/web.php` | Inertia Routes | `routes/web.php` (Laravel) |
| `resources/views` | Vue Components | `Components/` + `Pages/` |
| `public/assets` | Static Assets | `Assets/` |
| `config/` | Constants/Config | `Utils/constants.ts` |
| `helpers.php` | Helper Functions | `Helpers/` |

---

## Import Patterns

### Types (Central Export)

```typescript
// Types/index.ts
export * from './reservation';
export * from './room';
export * from './guest';
export * from './user';

// Usage in any file
import type { Reservation, Room, Guest } from '@/Types';
```

### Helpers (Central Export)

```typescript
// Helpers/index.ts
export { useLoading } from './useLoading';
export { useMessage } from './useMessage';
export { usePolling } from './usePolling';

// Usage in any file
import { useLoading, useMessage, usePolling } from '@/Helpers';
```

### Stores (Central Export)

```typescript
// Stores/index.ts
export { useReservationStore } from './reservationStore';
export { useRoomStore } from './roomStore';
export { useAuthStore } from './authStore';

// Usage in any file
import { useReservationStore, useAuthStore } from '@/Stores';
```

### Composables (Domain-based)

```typescript
// Each composable is independent
import { useReservations } from '@/Composables/useReservations';
import { useRooms } from '@/Composables/useRooms';
```

### Components (Domain-based)

```typescript
// Common components
import { AppButton, AppModal, AppInput } from '@/Components/common';

// Domain components
import ReservationCard from '@/Components/reservations/ReservationCard.vue';
import RoomStatus from '@/Components/rooms/RoomStatus.vue';
```

---

## Best Practices

### 1. **Domain-Based Organization**
Keep related files together. All reservation-related logic, components, and types should be easy to find.

### 2. **Single Responsibility**
Each file should have one clear purpose:
- Components: UI rendering only
- Composables: Business logic only
- Types: Type definitions only
- Stores: State management only

### 3. **Central Exports**
Use `index.ts` files for clean imports:
```typescript
// ❌ Bad
import { useLoading } from '@/Helpers/useLoading';
import { useMessage } from '@/Helpers/useMessage';

// ✅ Good
import { useLoading, useMessage } from '@/Helpers';
```

### 4. **Type Safety**
Always use TypeScript types from the `Types/` folder:
```typescript
import type { Reservation } from '@/Types';

const reservations = ref<Reservation[]>([]);
```

### 5. **Composable Pattern**
Use composables for reusable logic:
```typescript
// ✅ Good
export function useReservations() {
    const { loading, start, stop } = useLoading();
    const { message, showMessage } = useMessage();
    // ... logic
}
```

---

## Migration Checklist

- [ ] Create folder structure
- [ ] Move Types to `Types/` folder
- [ ] Move Stores to `Stores/` folder
- [ ] Organize Components by domain
- [ ] Organize Pages by domain
- [ ] Create central export files (`index.ts`)
- [ ] Update import paths
- [ ] Test build process
- [ ] Update documentation

---

## Quick Reference

### Current Structure → New Structure

| Current | New |
|---------|-----|
| `resources/js/types/` | `resources/js/Types/` |
| `resources/js/stores/` | `resources/js/Stores/` |
| `resources/js/components/` | `resources/js/Components/` |
| `resources/js/pages/` | `resources/js/Pages/` |
| `resources/js/Composables/` | `resources/js/Composables/` (unchanged) |
| `resources/js/Helpers/` | `resources/js/Helpers/` (unchanged) |

---

## Document Version

- **Version:** 1.0.0
- **Last Updated:** 2026-03-17
- **Author:** Development Team
