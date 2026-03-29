<template>
    <Head title="Reservations" />
    <!-- <AppLayout class="space-y-6"> -->
        <div class="max-w-6xl mx-auto">
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
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
            <div class="bg-white p-4 rounded-lg shadow mb-4">
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

            <!-- ─── Table ──────────────────────────────────────────── -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr> 
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
                                    <div class="text-sm font-medium text-slate-900">
                                        {{ res.guest?.first_name && res.guest?.last_name 
                                            ? res.guest.first_name + ' ' + res.guest.last_name 
                                            : 'N/A' 
                                        }}
                                    </div>
                                    <div class="text-sm text-slate-500">{{ res.guest?.email || '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">
                                        Room {{ res.room?.number || 'N/A' }}
                                    </div>
                                    <div class="text-xs text-slate-500">{{ res.room?.type || '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-600">{{ formatDate(res.check_in_date) }}</div>
                                    <div class="text-xs text-slate-500">{{ res.hotel?.name || '' }}</div>
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
                                        {{ formatStatus(res.status) }}
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
                    <div class="flex items-center gap-4">
                        <!-- Per Page -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-slate-500">Per Page:</label>
                            <select
                                v-model="perPage"
                                @change="changePerPage"
                                class="px-2 py-1 border border-slate-300 rounded text-sm focus:ring-2 focus:ring-blue-500"
                            >
                                <option :value="5">5</option>
                                <option :value="15">15</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>
                        
                        <!-- Page Info -->
                        <div class="text-sm text-slate-500">
                            Page {{ pagination.current_page }} of {{ pagination.last_page }}
                            ({{ pagination.total }} total)
                        </div>
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
        </div>
    <!-- </AppLayout> -->
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useReservations } from '@/Composables/FrontDesk/useReservations';
import type { ReservationFilters, Reservation } from '@/Types/FrontDesk/reservation';
import { formatDate } from '@/Utils/date';
import { formatStatus } from '@/Utils/format';

const {
    reservations,
    loading, 
    pagination,
    pendingCount,
    confirmedCount,
    checkedInCount,
    todayCheckIns,
    
    fetchAll,
    deleteReservation,
    setFilters,
    resetFilters,
} = useReservations();

const searchQuery = ref('');
const perPage = ref(15);
const localFilters = reactive<ReservationFilters>({
    status: '',
    check_in_date: '',
    check_out_date: '',
    search: '',
    per_page: 15,
});

let searchTimeout: ReturnType<typeof setTimeout>;

function changePerPage() {
    setFilters({ per_page: perPage.value });
    fetchAll(1, { per_page: perPage.value });
}

function debouncedSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        setFilters({ ...localFilters, search: searchQuery.value });
        fetchAll(1);
    }, 500);
}

function applyFilters() {
    setFilters({ ...localFilters, search: searchQuery.value });
    fetchAll(1);
}

function handleResetFilters() {
    localFilters.status = '';
    localFilters.check_in_date = '';
    localFilters.check_out_date = '';
    localFilters.search = '';
    resetFilters();
    fetchAll(1);
}

async function handleDelete(res: Reservation) {
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

onMounted(() => {
    fetchAll(1);
});
</script>