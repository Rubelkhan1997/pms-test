# 🔄 Vue 3 + Inertia.js + Pinia Application Lifecycle
────────────────────────────────────────────────────────────────────────────
এই ডকুমেন্টে পুরো application এর lifecycle টা step-by-step বুঝানো হলো।

**উদাহরণ:** Reservation Management System


1) ✅ Implemented Files
────────────────────────────────────────────────────────────────────────────
| Layer           | File                             | Status           |
|-----------------|----------------------------------|------------------|
| **Types**       | `types/index.d.ts`               | ✅ Updated |
| **Composable**  | `Composables/useReservations.ts` | ✅ Implemented |
| **Store**       | `Stores/reservations.ts`         | ✅ Implemented |
| **Page**        | `Pages/Booking/Index.vue`        | ✅ Implemented |
| **Component**   | `Components/Shared/StatusBadge.vue` | ✅ Implemented |


2) 📁 Complete Project Structure (Reservation Feature)
────────────────────────────────────────────────────────────────────────────
resources/js/
├── types/index.d.ts                    ✅ TypeScript definitions
├── Composables/useReservations.ts      ✅ Business logic
├── Stores/reservations.ts              ✅ Global state
├── Pages/Booking/Index.vue             ✅ Reservation list page
├── Components/Shared/StatusBadge.vue   ✅ Status badge component
├── Layouts/HotelLayout.vue             ✅ Layout wrapper
├── app.ts                              ✅ Entry point
└── bootstrap.ts                        ✅ Axios setup


3) 🚀 Complete Request Lifecycle
────────────────────────────────────────────────────────────────────────────
### উদাহরণ: User `/booking` URL এ visit করল

┌─────────────────────────────────────────────────────────────────┐
│  STEP 0: User Browser এ URL টাইপ করল                           │
│  URL: http://localhost/booking                                  │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 1: Laravel Route (routes/web.php)                         │
│  Route::get('/booking', fn() =>                                 │
│      Inertia::render('Booking/Index', [                         │
│          'reservations' => Reservation::with('guest','room')->get()
│      ])                                                         │
│  );                                                              │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 2: Inertia.js Response (JSON)                             │
│  {                                                               │
│    component: 'Booking/Index',                                  │
│    props: { reservations: [...] },                              │
│    url: '/booking'                                              │
│  }                                                               │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 3: app.ts (Entry Point)                                   │
│  createInertiaApp resolves → Pages/Booking/Index.vue            │
│  Vue app created + Pinia mounted                                │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 4: Page Component Mounted                                 │
│  onMounted() → store.fetchAll() → API call                      │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 5: UI Render                                              │
│  Table shows all reservations with status badges                │
└─────────────────────────────────────────────────────────────────┘

4) 📄 প্রতিটি File এর ভূমিকা (Implemented Code)
────────────────────────────────────────────────────────────────────────────
### 1. **types/index.d.ts**
────────────────────────────────────────────────────────
```ts
declare namespace PMS {
    interface Room {
        id: number;
        number: string;
        status: 'available' | 'occupied' | 'maintenance' | 'dirty';
        type: string;
        price: number;
        floor?: number;
    }
}
```

**কাজ:** সব TypeScript type এক জায়গায় define করা

---

### 2. **bootstrap.ts** (Axios Setup)
────────────────────────────────────────────────────────
```ts
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
```

**কাজ:** axios library setup করে, CSRF token configure করে

---

### 3. **Composables/useReservations.ts** (Business Logic Layer)
────────────────────────────────────────────────────────
**Location:** `resources/js/Composables/useReservations.ts`

```ts
import { ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

export function useReservations() {
    // ─────────────────────────────────────────────────────────
    // State (Reactive)
    // ─────────────────────────────────────────────────────────
    const reservations = ref<PMS.Reservation[]>([]);
    const reservation = ref<PMS.Reservation | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);
    const successMessage = ref<string | null>(null);

    // ─────────────────────────────────────────────────────────
    // API Calls
    // ─────────────────────────────────────────────────────────

    async function fetchAll(params?: {
        status?: string;
        check_in_date?: string;
        check_out_date?: string;
    }): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const { data } = await axios.get('/api/v1/front-desk/reservations', { params });
            reservations.value = data.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch reservations';
        } finally {
            loading.value = false;
        }
    }

    async function checkIn(id: number): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            await axios.post(`/api/v1/reservations/${id}/check-in`);
            successMessage.value = 'Guest checked in successfully';
            
            const index = reservations.value.findIndex(r => r.id === id);
            if (index !== -1) {
                reservations.value[index].status = 'checked_in';
            }
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Check in failed';
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function checkOut(id: number): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            await axios.post(`/api/v1/reservations/${id}/check-out`);
            successMessage.value = 'Guest checked out successfully';
            
            const index = reservations.value.findIndex(r => r.id === id);
            if (index !== -1) {
                reservations.value[index].status = 'checked_out';
            }
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Check out failed';
            throw err;
        } finally {
            loading.value = false;
        }
    }

    return {
        reservations,
        loading,
        error,
        successMessage,
        fetchAll,
        checkIn,
        checkOut
    };
}
```

**কাজ:**
- API call logic encapsulate করে
- Reactive state manage করে
- Reusable business logic provide করে

---

### 4. **Stores/reservations.ts** (Global State - Pinia)
────────────────────────────────────────────────────────
**Location:** `resources/js/Stores/reservations.ts`

```ts
import { defineStore } from 'pinia';
import axios from 'axios';

export const useReservationsStore = defineStore('reservations', {
    // ─────────────────────────────────────────────────────────
    // State (Reactive Data)
    // ─────────────────────────────────────────────────────────
    state: () => ({
        reservations: [] as PMS.Reservation[],
        selectedReservation: null as PMS.Reservation | null,
        loading: false,
        filters: {
            status: '',
            check_in_date: '',
            check_out_date: '',
            search: ''
        },
        pagination: {
            current_page: 1,
            per_page: 15,
            total: 0,
            last_page: 1
        }
    }),

    // ─────────────────────────────────────────────────────────
    // Getters (Computed Properties)
    // ─────────────────────────────────────────────────────────
    getters: {
        pendingCount: (state) => {
            return state.reservations.filter(r => r.status === 'pending').length;
        },
        confirmedCount: (state) => {
            return state.reservations.filter(r => r.status === 'confirmed').length;
        },
        checkedInCount: (state) => {
            return state.reservations.filter(r => r.status === 'checked_in').length;
        },
        todayCheckIns: (state) => {
            const today = new Date().toISOString().split('T')[0];
            return state.reservations.filter(r => r.check_in_date === today);
        }
    },

    // ─────────────────────────────────────────────────────────
    // Actions (Methods)
    // ─────────────────────────────────────────────────────────
    actions: {
        setFilters(filters: Partial<typeof this.filters>) {
            this.filters = { ...this.filters, ...filters };
        },

        async fetchAll(page: number = 1) {
            this.loading = true;
            const { data } = await axios.get('/api/v1/front-desk/reservations', {
                params: { ...this.filters, page }
            });
            this.reservations = data.data;
            this.pagination = data.pagination;
            this.loading = false;
        },

        resetFilters() {
            this.filters = { status: '', check_in_date: '', check_out_date: '', search: '' };
        }
    }
});
```

**কাজ:**
- Global state manage করে (যেকোনো page থেকে access করা যায়)
- State + Getters + Actions এক জায়গায় থাকে

---

### 5. **Pages/Booking/Index.vue** (Page Component)
────────────────────────────────────────────────────────
**Location:** `resources/js/Pages/Booking/Index.vue`

```vue
<template>
    <HotelLayout>
        <section class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">Reservations</h1>
                    <p class="text-sm text-slate-500 mt-1">Manage all guest bookings</p>
                </div>
                <button @click="openCreateModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                    + New Reservation
                </button>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
                    <div class="text-sm text-slate-500">Pending</div>
                    <div class="text-2xl font-bold text-slate-800">{{ store.pendingCount }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
                    <div class="text-sm text-slate-500">Confirmed</div>
                    <div class="text-2xl font-bold text-slate-800">{{ store.confirmedCount }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
                    <div class="text-sm text-slate-500">Checked In</div>
                    <div class="text-2xl font-bold text-slate-800">{{ store.checkedInCount }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
                    <div class="text-sm text-slate-500">Today's Check-ins</div>
                    <div class="text-2xl font-bold text-slate-800">{{ store.todayCheckIns.length }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Search</label>
                        <input v-model="searchQuery" @input="debouncedSearch" type="text" 
                               class="w-full px-3 py-2 border rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select v-model="filters.status" @change="applyFilters" 
                                class="w-full px-3 py-2 border rounded-lg">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Checked Out</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Check-in From</label>
                        <input v-model="filters.check_in_date" @change="applyFilters" type="date" 
                               class="w-full px-3 py-2 border rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Check-out To</label>
                        <input v-model="filters.check_out_date" @change="applyFilters" type="date" 
                               class="w-full px-3 py-2 border rounded-lg" />
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div v-if="successMessage" class="p-4 bg-green-100 text-green-800 rounded-lg">
                {{ successMessage }}
            </div>
            <div v-if="errorMessage" class="p-4 bg-red-100 text-red-800 rounded-lg">
                {{ errorMessage }}
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Guest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Check-in</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Check-out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <tr v-if="loading">
                            <td colspan="8" class="px-6 py-8 text-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                            </td>
                        </tr>
                        <tr v-else-if="reservations.length === 0">
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">
                                No reservations found
                            </td>
                        </tr>
                        <tr v-for="reservation in reservations" :key="reservation.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-blue-600">{{ reservation.reference }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ reservation.guest?.name || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-slate-100 text-slate-800 rounded">
                                    {{ reservation.room?.number || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ formatDate(reservation.check_in_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ formatDate(reservation.check_out_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <StatusBadge :status="reservation.status" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                ৳{{ reservation.total_amount.toLocaleString() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button v-if="reservation.status === 'confirmed'" 
                                        @click="handleCheckIn(reservation)"
                                        class="text-green-600 hover:text-green-900">
                                    Check In
                                </button>
                                <button v-if="reservation.status === 'checked_in'" 
                                        @click="handleCheckOut(reservation)"
                                        class="text-orange-600 hover:text-orange-900">
                                    Check Out
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </HotelLayout>
</template>

<script setup lang="ts">
// ─────────────────────────────────────────────────────────────────────
// 1. IMPORTS
// ─────────────────────────────────────────────────────────────────────
import { ref, onMounted } from 'vue';
import { useReservations } from '@/Composables/useReservations';
import { useReservationsStore } from '@/Stores/reservations';
import HotelLayout from '@/Layouts/HotelLayout.vue';

// ─────────────────────────────────────────────────────────────────────
// 2. SETUP
// ─────────────────────────────────────────────────────────────────────
const { 
    reservations, 
    loading, 
    successMessage, 
    error: errorMessage,
    checkIn, 
    checkOut 
} = useReservations();

const store = useReservationsStore();

// ─────────────────────────────────────────────────────────────────────
// 3. LOCAL STATE
// ─────────────────────────────────────────────────────────────────────
const searchQuery = ref('');
const filters = ref({
    status: '',
    check_in_date: '',
    check_out_date: ''
});

// ─────────────────────────────────────────────────────────────────────
// 4. FUNCTIONS
// ─────────────────────────────────────────────────────────────────────
function formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

async function handleCheckIn(reservation: PMS.Reservation) {
    if (!confirm(`Check in guest: ${reservation.guest?.name}?`)) return;
    await checkIn(reservation.id);
    await store.fetchAll();
}

async function handleCheckOut(reservation: PMS.Reservation) {
    if (!confirm(`Check out guest: ${reservation.guest?.name}?`)) return;
    await checkOut(reservation.id);
    await store.fetchAll();
}

function debouncedSearch() {
    setTimeout(() => {
        store.setFilters({ search: searchQuery.value });
        store.fetchAll();
    }, 500);
}

function applyFilters() {
    store.setFilters(filters.value);
    store.fetchAll();
}

// ─────────────────────────────────────────────────────────────────────
// 5. LIFECYCLE HOOKS
// ─────────────────────────────────────────────────────────────────────
onMounted(() => {
    store.fetchAll();
});
</script>
```

**কাজ:**
- Page render + data fetch + UI display
- User interaction handle করে
- Composable + Store use করে

---

### 6. **Components/Shared/StatusBadge.vue** (Reusable Component)
─────────────────────────────────────────────────────────────────
**Location:** `resources/js/Components/Shared/StatusBadge.vue`

```vue
<template>
    <span :class="badgeClasses">
        {{ statusLabel }}
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    status: PMS.ReservationStatus;
}>();

const badgeClasses = computed(() => {
    const baseClasses = 'px-2 py-1 text-xs font-medium rounded-full';
    
    const statusClasses: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-green-100 text-green-800',
        checked_in: 'bg-blue-100 text-blue-800',
        checked_out: 'bg-slate-100 text-slate-800',
        cancelled: 'bg-red-100 text-red-800',
        no_show: 'bg-purple-100 text-purple-800'
    };
    
    return `${baseClasses} ${statusClasses[props.status] || 'bg-gray-100 text-gray-800'}`;
});

const statusLabel = computed(() => {
    const labels: Record<string, string> = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        checked_in: 'Checked In',
        checked_out: 'Checked Out',
        cancelled: 'Cancelled',
        no_show: 'No Show'
    };
    
    return labels[props.status] || props.status;
});
</script>
```

**কাজ:**
- Reusable status badge component
- Different colors for different statuses

---

## 📊 Lifecycle Summary Table

| Step | File/Location | কাজ |
|------|---------------|-----|
| 0 | Browser URL | User visit করে |
| 1 | `routes/web.php` | Laravel route match + Inertia render |
| 2 | `bootstrap.ts` | axios setup |
| 3 | `app.ts` | Inertia + Vue + Pinia initialize |
| 4 | `Pages/*.vue` | Page component load + props receive |
| 5 | `onMounted()` | store.fetchAll() → API call |
| 6 | `Composables/*.ts` | API call + business logic |
| 7 | `Stores/*.ts` | Global state management |
| 8 | Laravel API | Backend processing |
| 9 | Response | UI auto-update (reactive) |

---

4) 🎯 Key Concepts 
────────────────────────────────────────────────────────────────────────────
### 1. **Reactive System**
```ts
const reservations = ref<PMS.Reservation[]>([]);

// Update → Auto re-render UI
reservations.value = newData;
```

### 2. **Composition API**
```ts
// Old Vue 2 (Options API)
export default {
    data() { return { count: 0 } },
    methods: { increment() { this.count++ } }
}

// New Vue 3 (Composition API)
<script setup>
const count = ref(0);
function increment() { count.value++ }
</script>
```

### 3. **Composable Pattern**
```ts
// useReservations.ts
export function useReservations() {
    const reservations = ref([]);
    async function fetchAll() { }
    async function checkIn(id) { }
    return { reservations, fetchAll, checkIn };
}

// Usage in Page
const { reservations, checkIn } = useReservations();
```

### 4. **Pinia Store Pattern**
```ts
// Stores/reservations.ts
export const useReservationsStore = defineStore('reservations', {
    state: () => ({ reservations: [] }),
    getters: { pendingCount: (state) => state.reservations.filter(...).length },
    actions: { async fetchAll() { } }
});

// Usage in Page
const store = useReservationsStore();
store.fetchAll();
console.log(store.pendingCount);
```

---

5) 🔍 Debug Tips
────────────────────────────────────────────────────────────────────────────
```ts
// Page props দেখতে
import { usePage } from '@inertiajs/vue3';
console.log(usePage().props);

// Lifecycle event দেখতে
onMounted(() => {
    console.log('Component mounted');
});

// Store state দেখতে
const store = useReservationsStore();
console.log(store.$state);
console.log(store.pendingCount);
```

---

6) ✅ Quick Reference
────────────────────────────────────────────────────────────────────────────
### Import Path Alias
```ts
import X from '@/Composables/useX';  // resources/js/Composables/
import Y from '@/Stores/y';          // resources/js/Stores/
import Z from '@/Layouts/Z';         // resources/js/Layouts/
import C from '@/Components/C';      // resources/js/Components/
```

### Common Patterns
```ts
// Composable pattern
export function useX() {
    const state = ref([]);
    async function fetch() { }
    return { state, fetch };
}

// Store pattern
export const useXStore = defineStore('x', {
    state: () => ({ items: [] }),
    getters: { count: (state) => state.items.length },
    actions: { async fetchAll() {} }
});

// Page pattern
<script setup lang="ts">
const props = defineProps<{ items: Array }>();
const { fetch } = useX();
const store = useXStore();
onMounted(() => {
    fetch();
    store.fetchAll();
});
</script>
```

---

**Document Version:** 1.0  
**Last Updated:** 2026-03-08  
**Feature:** Reservation Management System
