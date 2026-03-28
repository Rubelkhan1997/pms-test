<template>
    <Head title="Reservations" />
    <AppLayout class="space-y-6">

        <!-- ─── Header ─────────────────────────────────────────── -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Reservations</h1>
                <p class="text-sm text-slate-500 mt-1">Manage all guest bookings</p>
            </div>
            <Link
                href="/reservations/create"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
            >
                New Reservation
            </Link>
        </div>

        <!-- ─── Stats Cards ────────────────────────────────────── -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
                <div class="text-sm text-slate-500">Pending</div>
                <div class="text-2xl font-bold text-slate-800">{{ pendingCount }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
                <div class="text-sm text-slate-500">Confirmed</div>
                <div class="text-2xl font-bold text-slate-800">{{ confirmedCount }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
                <div class="text-sm text-slate-500">Checked In</div>
                <div class="text-2xl font-bold text-slate-800">{{ checkedInCount }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
                <div class="text-sm text-slate-500">Today's Check-ins</div>
                <div class="text-2xl font-bold text-slate-800">{{ todayCheckIns.length }}</div>
            </div>
        </div>

        <!-- ─── Filters ────────────────────────────────────────── -->
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

                <!-- Status -->
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

                <!-- Check-in From -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Check-in From</label>
                    <input
                        v-model="localFilters.check_in_date"
                        @change="applyFilters"
                        type="date"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <!-- Check-out To -->
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

            <div class="mt-4 flex justify-end">
                <button
                    @click="handleResetFilters"
                    class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 transition"
                >
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- ─── Messages ───────────────────────────────────────── -->
        <div v-if="successMessage" class="p-4 bg-green-100 text-green-800 rounded-lg flex justify-between">
            <span>{{ successMessage }}</span>
            <button @click="clearSuccess" class="text-green-600 hover:text-green-800">✕</button>
        </div>

        <div v-if="error" class="p-4 bg-red-100 text-red-800 rounded-lg flex justify-between">
            <span>{{ error }}</span>
            <button @click="clearError" class="text-red-600 hover:text-red-800">✕</button>
        </div>

        <!-- ─── Table ──────────────────────────────────────────── -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Guest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Check-in</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Check-out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">

                        <!-- Loading -->
                        <tr v-if="loading">
                            <td colspan="8" class="px-6 py-8 text-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                                <p class="mt-2 text-slate-500">Loading reservations...</p>
                            </td>
                        </tr>

                        <!-- Empty -->
                        <tr v-else-if="reservations.length === 0">
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">
                                No reservations found
                            </td>
                        </tr>

                        <!-- Rows -->
                        <tr
                            v-for="res in reservations"
                            :key="res.id"
                            class="hover:bg-slate-50 transition"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-blue-600">{{ res.reference }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ res.guest?.name || 'N/A' }}</div>
                                <div class="text-sm text-slate-500">{{ res.guest?.email || '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-slate-100 text-slate-800 rounded">
                                    {{ res.room?.number || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ formatDate(res.check_in_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ formatDate(res.check_out_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="{
                                        'bg-yellow-100 text-yellow-800': res.status === 'pending',
                                        'bg-green-100 text-green-800':  res.status === 'confirmed',
                                        'bg-blue-100 text-blue-800':    res.status === 'checked_in',
                                        'bg-purple-100 text-purple-800':res.status === 'checked_out',
                                        'bg-red-100 text-red-800':      res.status === 'cancelled',
                                        'bg-slate-100 text-slate-800':  res.status === 'no_show',
                                    }"
                                    class="px-2 py-1 text-xs font-medium rounded"
                                >
                                    {{ res.status.replace('_', ' ').toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                ৳{{ res.total_amount?.toLocaleString() || '0' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <Link :href="`/reservations/${res.id}/edit`" class="text-blue-600 hover:text-blue-900">
                                        Edit
                                    </Link>
                                    <Link :href="`/reservations/${res.id}`" class="text-green-600 hover:text-green-900">
                                        View
                                    </Link>
                                    <button @click="handleDelete(res)" class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- ─── Pagination ─────────────────────────────────── -->
            <div
                v-if="pagination.last_page > 1"
                class="px-6 py-4 border-t border-slate-200 flex justify-between items-center"
            >
                <div class="text-sm text-slate-500">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                    ({{ pagination.total }} total)
                </div>
                <div class="flex gap-2">
                    <button
                        @click="changePage(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-1 border border-slate-300 rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50"
                    >
                        Previous
                    </button>
                    <button
                        @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 border border-slate-300 rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import { AppLayout } from '@/Layouts';
import { useReservations } from '@/Composables';

// ─── Composable ──────────────────────────────────────────

const {
    reservations,
    loading,
    saving,
    successMessage,
    error,
    pagination,
    pendingCount,
    confirmedCount,
    checkedInCount,
    todayCheckIns,
    fetchAll,
    checkIn,
    checkOut,
    cancel: cancelReservation,
    deleteReservation,
    setFilters,
    resetFilters,
    clearError,
    clearSuccess,
} = useReservations();

// ─── Local State ─────────────────────────────────────────

const searchQuery = ref('');
const localFilters = reactive({
    status: '',
    check_in_date: '',
    check_out_date: ''
});

let searchTimeout: ReturnType<typeof setTimeout>;

// ─── Functions ───────────────────────────────────────────

function debouncedSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        setFilters({ search: searchQuery.value });
        fetchAll(1);
    }, 500);
}

function applyFilters() {
    setFilters(localFilters);
    fetchAll(1);
}

function handleResetFilters() {
    localFilters.status = '';
    localFilters.check_in_date = '';
    localFilters.check_out_date = '';
    searchQuery.value = '';
    resetFilters();
    fetchAll(1);
}

function formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

async function handleCheckIn(res: PMS.Reservation) {
    if (!confirm(`Check in guest: ${res.guest?.name}?`)) return;
    try {
        await checkIn(res.id);
        await fetchAll(pagination.value.current_page);
    } catch (e) {
        console.error('Check in failed:', e);
    }
}

async function handleCheckOut(res: PMS.Reservation) {
    if (!confirm(`Check out guest: ${res.guest?.name}?`)) return;
    try {
        await checkOut(res.id);
        await fetchAll(pagination.value.current_page);
    } catch (e) {
        console.error('Check out failed:', e);
    }
}

async function handleCancel(res: PMS.Reservation) {
    if (!confirm(`Cancel reservation: ${res.reference}?`)) return;
    try {
        await cancelReservation(res.id);
        await fetchAll(pagination.value.current_page);
    } catch (e) {
        console.error('Cancel failed:', e);
    }
}

async function handleDelete(res: PMS.Reservation) {
    if (!confirm(`Delete reservation: ${res.reference}? This cannot be undone.`)) return;
    try {
        await deleteReservation(res.id);
        const targetPage = reservations.value.length === 0 && pagination.value.current_page > 1
            ? pagination.value.current_page - 1
            : pagination.value.current_page;
        await fetchAll(targetPage);
    } catch (e) {
        console.error('Delete failed:', e);
    }
}

function changePage(page: number) {
    if (page < 1 || page > pagination.value.last_page) return;
    fetchAll(page);
}

// ─── Lifecycle ───────────────────────────────────────────

onMounted(() => {
    fetchAll(1);
});
</script>