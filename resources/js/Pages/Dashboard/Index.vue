<template>
    <Head title="Dashboard" />
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Dashboard</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Welcome back, {{ userName }}!
                </p>
            </div>
            <div class="text-sm text-slate-600">
                {{ currentDate }}
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Total Reservations -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">Total Reservations</div>
                        <div class="text-3xl font-bold text-slate-800 mt-2">
                            {{ dashboardStats.totalReservations }}
                        </div>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Available Rooms -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">Available Rooms</div>
                        <div class="text-3xl font-bold text-green-600 mt-2">
                            {{ dashboardStats.availableRooms }}
                        </div>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Check-ins Today -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">Check-ins Today</div>
                        <div class="text-3xl font-bold text-blue-600 mt-2">
                            {{ dashboardStats.checkInsToday }}
                        </div>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Check-outs Today -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">Check-outs Today</div>
                        <div class="text-3xl font-bold text-orange-600 mt-2">
                            {{ dashboardStats.checkOutsToday }}
                        </div>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Recent Reservations -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-slate-800">Recent Reservations</h2>
                    <Link href="/reservations" class="text-sm text-cyan-600 hover:text-cyan-700">
                        View All
                    </Link>
                </div>
                <div v-if="loading" class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-600 mx-auto"></div>
                    <p class="text-sm text-slate-500 mt-2">Loading...</p>
                </div>
                <div v-else-if="recentReservations.length === 0" class="text-center py-8">
                    <p class="text-slate-500">No reservations yet</p>
                </div>
                <div v-else class="space-y-3">
                    <div
                        v-for="reservation in recentReservations"
                        :key="reservation.id"
                        class="flex items-center justify-between p-3 bg-slate-50 rounded-lg"
                    >
                        <div>
                            <p class="font-medium text-slate-800">
                                {{ reservation.guest?.firstName }} {{ reservation.guest?.lastName }}
                            </p>
                            <p class="text-sm text-slate-500">
                                Room {{ reservation.room?.number }} • {{ reservation.status }}
                            </p>
                        </div>
                        <Link
                            :href="`/reservations/${reservation.id}`"
                            class="text-sm text-cyan-600 hover:text-cyan-700"
                        >
                            View
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    <Link
                        href="/reservations/create"
                        class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-cyan-500 hover:bg-cyan-50 transition"
                    >
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-slate-800">New Reservation</p>
                            <p class="text-sm text-slate-500">Create a new booking</p>
                        </div>
                    </Link>

                    <Link
                        href="/reservations"
                        class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-cyan-500 hover:bg-cyan-50 transition"
                    >
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-slate-800">View All Reservations</p>
                            <p class="text-sm text-slate-500">Manage bookings</p>
                        </div>
                    </Link>

                    <Link
                        href="/dashboard"
                        class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-cyan-500 hover:bg-cyan-50 transition"
                    >
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-slate-800">View Reports</p>
                            <p class="text-sm text-slate-500">Coming soon</p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, reactive } from 'vue';
import { useReservations } from '@/Composables/FrontDesk/useReservations';
import { useAuth } from '@/Composables/Auth/useAuth';
import { formatDate } from '@/Utils/date';

// ─────────────────────────────────────────────────────────
// Composables
// ─────────────────────────────────────────────────────────
const { reservations, fetchAll, loading } = useReservations({ autoFetch: true });
const { userName } = useAuth();

// ─────────────────────────────────────────────────────────
// Dashboard Stats
// ─────────────────────────────────────────────────────────
const dashboardStats = reactive({
    totalReservations: 0,
    availableRooms: 0,
    checkInsToday: 0,
    checkOutsToday: 0,
});

// ─────────────────────────────────────────────────────────
// Computed
// ─────────────────────────────────────────────────────────
const currentDate = computed(() => {
    return formatDate(new Date().toISOString());
});

const recentReservations = computed(() => {
    return reservations.value.slice(0, 5);
});

// ─────────────────────────────────────────────────────────
// Initialize Dashboard
// ─────────────────────────────────────────────────────────
onMounted(() => {
    calculateStats();
});

// ─────────────────────────────────────────────────────────
// Calculate Stats
// ─────────────────────────────────────────────────────────
function calculateStats() {
    const today = new Date().toISOString().split('T')[0];

    dashboardStats.totalReservations = reservations.value.length;
    dashboardStats.availableRooms = 10; // TODO: Fetch from API
    dashboardStats.checkInsToday = reservations.value.filter(
        (r) => r.check_in_date === today
    ).length;
    dashboardStats.checkOutsToday = reservations.value.filter(
        (r) => r.check_out_date === today
    ).length;
}
</script>
