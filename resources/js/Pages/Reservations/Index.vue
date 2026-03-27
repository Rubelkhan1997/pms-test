<template>
    <Head title="Reservations" />
    <HotelLayout>
        <section class="space-y-6">
            <!-- ───────────────────────────────────────────────────── -->
            <!-- Header Section -->
            <!-- ───────────────────────────────────────────────────── -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">Reservations</h1>
                    <p class="text-sm text-slate-500 mt-1">Manage all guest bookings</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    + New Reservation
                </button>
            </div>

            <!-- ───────────────────────────────────────────────────── -->
            <!-- Statistics Cards - Using Computed Getters -->
            <!-- ───────────────────────────────────────────────────── -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Pending -->
                <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
                    <div class="text-sm text-slate-500">Pending</div>
                    <div class="text-2xl font-bold text-slate-800">{{ store.pendingCount }}</div>
                </div>
                
                <!-- Confirmed -->
                <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
                    <div class="text-sm text-slate-500">Confirmed</div>
                    <div class="text-2xl font-bold text-slate-800">{{ store.confirmedCount }}</div>
                </div>
                
                <!-- Checked In -->
                <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
                    <div class="text-sm text-slate-500">Checked In</div>
                    <div class="text-2xl font-bold text-slate-800">{{ store.checkedInCount }}</div>
                </div>
                
                <!-- Today's Check-ins -->
                <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
                    <div class="text-sm text-slate-500">Today's Check-ins</div>
                    <div class="text-2xl font-bold text-slate-800">{{ store.todayCheckIns.length }}</div>
                </div>
            </div>

            <!-- ───────────────────────────────────────────────────── -->
            <!-- Filters Section -->
            <!-- ───────────────────────────────────────────────────── -->
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Search</label>
                        <input 
                            v-model="searchQuery"
                            @input="debouncedSearch"
                            type="text" 
                            placeholder="Reference or Guest name"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select 
                            v-model="localFilters.status"
                            @change="applyFilters"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Checked Out</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="no_show">No Show</option>
                        </select>
                    </div>

                    <!-- Check-in Date -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Check-in From</label>
                        <input 
                            v-model="localFilters.check_in_date"
                            @change="applyFilters"
                            type="date" 
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Check-out Date -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Check-out To</label>
                        <input 
                            v-model="localFilters.check_out_date"
                            @change="applyFilters"
                            type="date" 
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                </div>

                <!-- Reset Filters -->
                <div class="mt-4 flex justify-end">
                    <button 
                        @click="resetFilters"
                        class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 transition"
                    >
                        Reset Filters
                    </button>
                </div>
            </div>

            <!-- ───────────────────────────────────────────────────── -->
            <!-- Success/Error Messages -->
            <!-- ───────────────────────────────────────────────────── -->
            <div v-if="successMessage" class="p-4 bg-green-100 text-green-800 rounded-lg">
                {{ successMessage }}
            </div>

            <div v-if="errorMessage" class="p-4 bg-red-100 text-red-800 rounded-lg">
                {{ errorMessage }}
            </div>

            <!-- ───────────────────────────────────────────────────── -->
            <!-- Reservations Table -->
            <!-- ───────────────────────────────────────────────────── -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Reference
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Guest
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Room
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Check-in
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Check-out
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            <!-- Loading State - v-if only -->
                            <tr v-if="loading">
                                <td colspan="8" class="px-6 py-8 text-center">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                                    <p class="mt-2 text-slate-500">Loading reservations...</p>
                                </td>
                            </tr>

                            <!-- Empty State - v-else-if -->
                            <tr v-else-if="reservations.length === 0">
                                <td colspan="8" class="px-6 py-8 text-center text-slate-500">
                                    No reservations found
                                </td>
                            </tr>

                            <!-- Data Rows - v-for with :key -->
                            <tr 
                                v-for="reservation in reservations" 
                                :key="reservation.id"
                                class="hover:bg-slate-50 transition"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-blue-600">
                                        {{ reservation.reference }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">
                                        {{ reservation.guest?.name || 'N/A' }}
                                    </div>
                                    <div class="text-sm text-slate-500">
                                        {{ reservation.guest?.email || '' }}
                                    </div>
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
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <!-- View -->
                                        <button 
                                            @click="viewReservation(reservation)"
                                            class="text-blue-600 hover:text-blue-900"
                                            title="View"
                                        >
                                            View
                                        </button>

                                        <!-- Check In -->
                                        <button 
                                            v-if="reservation.status === 'confirmed'"
                                            @click="handleCheckIn(reservation)"
                                            :disabled="loadingCheckIn"
                                            class="text-green-600 hover:text-green-900 disabled:opacity-50"
                                            title="Check In"
                                        >
                                            {{ loadingCheckIn ? '...' : 'Check In' }}
                                        </button>

                                        <!-- Check Out -->
                                        <button 
                                            v-if="reservation.status === 'checked_in'"
                                            @click="handleCheckOut(reservation)"
                                            :disabled="loadingCheckOut"
                                            class="text-orange-600 hover:text-orange-900 disabled:opacity-50"
                                            title="Check Out"
                                        >
                                            {{ loadingCheckOut ? '...' : 'Check Out' }}
                                        </button>

                                        <!-- Cancel -->
                                        <button 
                                            v-if="['pending', 'confirmed'].includes(reservation.status)"
                                            @click="handleCancel(reservation)"
                                            :disabled="loading"
                                            class="text-red-600 hover:text-red-900 disabled:opacity-50"
                                            title="Cancel"
                                        >
                                            {{ loading ? '...' : 'Cancel' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div 
                    v-if="store.pagination.last_page > 1"
                    class="px-6 py-4 border-t border-slate-200 flex justify-between items-center"
                >
                    <div class="text-sm text-slate-500">
                        Page {{ store.pagination.current_page }} of {{ store.pagination.last_page }}
                    </div>
                    <div class="flex gap-2">
                        <button 
                            @click="changePage(store.pagination.current_page - 1)"
                            :disabled="store.pagination.current_page === 1"
                            class="px-3 py-1 border border-slate-300 rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50"
                        >
                            Previous
                        </button>
                        <button 
                            @click="changePage(store.pagination.current_page + 1)"
                            :disabled="store.pagination.current_page === store.pagination.last_page"
                            class="px-3 py-1 border border-slate-300 rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </HotelLayout>
</template>

<script setup lang="ts">
// ─────────────────────────────────────────────────────────────────────
// 1. IMPORTS
// ─────────────────────────────────────────────────────────────────────
import { ref, reactive, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useReservations } from '@/Composables';
import { useReservationsStore } from '@/Stores';
import { HotelLayout } from '@/Layouts';

// ─────────────────────────────────────────────────────────────────────
// 2. SETUP - Composables + Stores
// ─────────────────────────────────────────────────────────────────────
const { 
    reservations,
    loading,
    loadingCheckIn,
    loadingCheckOut,
    successMessage,
    error: errorMessage,
    checkIn,
    checkOut,
    cancel: cancelReservation,
    clearError,
    clearSuccess
} = useReservations();

const store = useReservationsStore();

// ─────────────────────────────────────────────────────────────────────
// 3. LOCAL STATE - Best Practice
// ─────────────────────────────────────────────────────────────────────
const searchQuery = ref('');

// ✅ Use reactive for local filter object
const localFilters = reactive({
    status: '',
    check_in_date: '',
    check_out_date: ''
});

let searchTimeout: NodeJS.Timeout;

// ─────────────────────────────────────────────────────────────────────
// 4. FUNCTIONS - Best Practice
// ─────────────────────────────────────────────────────────────────────

/**
 * Debounced search - Cancel previous request
 */
function debouncedSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        store.setFilters({ search: searchQuery.value });
        store.fetchAll();
    }, 500);
}

/**
 * Apply filters
 */
function applyFilters() {
    store.setFilters(localFilters);
    store.fetchAll();
}

/**
 * Reset filters
 */
function resetFilters() {
    localFilters.status = '';
    localFilters.check_in_date = '';
    localFilters.check_out_date = '';
    searchQuery.value = '';
    store.resetFilters();
    store.fetchAll();
}

/**
 * Format date - Pure function
 */
function formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

/**
 * View reservation details
 */
function viewReservation(reservation: PMS.Reservation) {
    store.setSelectedReservation(reservation);
    console.log('View reservation:', reservation);
}

/**
 * Handle Check In - Action with confirmation
 */
async function handleCheckIn(reservation: PMS.Reservation) {
    if (!confirm(`Check in guest: ${reservation.guest?.name}?`)) return;
    
    try {
        await checkIn(reservation.id);
        await store.fetchAll();
    } catch (error) {
        console.error('Check in failed:', error);
    }
}

/**
 * Handle Check Out - Action with confirmation
 */
async function handleCheckOut(reservation: PMS.Reservation) {
    if (!confirm(`Check out guest: ${reservation.guest?.name}?`)) return;
    
    try {
        await checkOut(reservation.id);
        await store.fetchAll();
    } catch (error) {
        console.error('Check out failed:', error);
    }
}

/**
 * Handle Cancel - Action with confirmation
 */
async function handleCancel(reservation: PMS.Reservation) {
    if (!confirm(`Cancel reservation: ${reservation.reference}?`)) return;
    
    try {
        await cancelReservation(reservation.id);
        await store.fetchAll();
    } catch (error) {
        console.error('Cancel failed:', error);
    }
}

/**
 * Change page
 */
function changePage(page: number) {
    if (page < 1 || page > store.pagination.last_page) return;
    store.fetchAll(page);
}

/**
 * Open create modal
 */
function openCreateModal() {
    console.log('Open create reservation modal');
}

// ─────────────────────────────────────────────────────────────────────
// 5. LIFECYCLE HOOKS - Best Practice
// ─────────────────────────────────────────────────────────────────────
onMounted(() => {
    store.fetchAll();
});
</script>
