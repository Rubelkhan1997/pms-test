<template>
    <!-- Page title shown in browser tab -->
    <Head :title="t('navigation.dashboard')" />
    
    <!-- Main container with spacing between elements -->
    <div class="space-y-6">
        
        <!-- Header Section: Title + Current Date -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">{{ t('navigation.dashboard') }}</h1>
                <p class="text-sm text-slate-500 mt-1">
                    <!-- Welcome message with logged-in user's name -->
                    {{ t('messages.welcome') }}, {{ userName }}!
                </p>
            </div>
            <div class="text-sm text-slate-600">
                {{ currentDate }}
            </div>
        </div>

        <!-- Stats Grid (4 columns on medium+ screens) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            <!-- Total Reservations Stat Card -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">{{ t('dashboard.total_reservations') }}</div>
                        <div class="text-3xl font-bold text-slate-800 mt-2">
                            {{ dashboardStats.totalReservations }}
                        </div>
                    </div>
                    <!-- Blue icon with document/clipboard SVG -->
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Available Rooms Stat Card -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">{{ t('dashboard.available_rooms') }}</div>
                        <div class="text-3xl font-bold text-green-600 mt-2">
                            {{ dashboardStats.availableRooms }}
                        </div>
                    </div>
                    <!-- Green icon with home/building SVG -->
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Check-ins Today Stat Card -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">{{ t('dashboard.check_ins_today') }}</div>
                        <div class="text-3xl font-bold text-blue-600 mt-2">
                            {{ dashboardStats.checkInsToday }}
                        </div>
                    </div>
                    <!-- Blue icon with sign-in SVG -->
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Check-outs Today Stat Card -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">{{ t('dashboard.check_outs_today') }}</div>
                        <div class="text-3xl font-bold text-orange-600 mt-2">
                            {{ dashboardStats.checkOutsToday }}
                        </div>
                    </div>
                    <!-- Orange icon with sign-out SVG -->
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Overview Section (2 columns) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Recent Reservations Card -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-slate-800">{{ t('dashboard.recent_activity') }}</h2>
                    <!-- Link to view all reservations -->
                    <Link href="/reservations" class="text-sm text-cyan-600 hover:text-cyan-700">
                        {{ t('actions.view') }}
                    </Link>
                </div>
                
                <!-- Loading State: Shows spinner while data is being fetched -->
                <div v-if="loading" class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-600 mx-auto"></div>
                    <p class="text-sm text-slate-500 mt-2">{{ t('messages.loading') }}</p>
                </div>
                
                <!-- Empty State: Shown when no reservations exist -->
                <div v-else-if="recentReservations.length === 0" class="text-center py-8">
                    <p class="text-slate-500">{{ t('reservations.no_data') }}</p>
                </div>
                
                <!-- Reservation List: Shows up to 5 most recent reservations -->
                <div v-else class="space-y-3">
                    <div
                        v-for="reservation in recentReservations"
                        :key="reservation.id"
                        class="flex items-center justify-between p-3 bg-slate-50 rounded-lg"
                    >
                        <!-- Guest name and room info -->
                        <div>
                            <p class="font-medium text-slate-800">
                                {{ reservation.guest?.firstName }} {{ reservation.guest?.lastName }}
                            </p>
                            <p class="text-sm text-slate-500">
                                {{ t('rooms.room_number') }} {{ reservation.room?.number }} • {{ formatStatus(reservation.status) }}
                            </p>
                        </div>
                        <!-- View reservation link -->
                        <Link
                            :href="`/reservations/${reservation.id}`"
                            class="text-sm text-cyan-600 hover:text-cyan-700"
                        >
                            {{ t('actions.view') }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ t('dashboard.quick_actions') }}</h2>
                <div class="space-y-3">
                    
                    <!-- New Reservation Quick Action -->
                    <Link
                        href="/reservations/create"
                        class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-cyan-500 hover:bg-cyan-50 transition"
                    >
                        <!-- Green plus icon -->
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-slate-800">{{ t('reservations.new_reservation') }}</p>
                            <p class="text-sm text-slate-500">{{ t('reservations.create_hint') }}</p>
                        </div>
                    </Link>

                    <!-- View All Reservations Quick Action -->
                    <Link
                        href="/reservations"
                        class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-cyan-500 hover:bg-cyan-50 transition"
                    >
                        <!-- Blue list icon -->
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-slate-800">{{ t('reservations.view_all') }}</p>
                            <p class="text-sm text-slate-500">{{ t('reservations.manage_hint') }}</p>
                        </div>
                    </Link>

                    <!-- Reports Quick Action (placeholder - coming soon) -->
                    <Link
                        href="/dashboard"
                        class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-cyan-500 hover:bg-cyan-50 transition"
                    >
                        <!-- Purple chart icon -->
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-slate-800">{{ t('navigation.reports') }}</p>
                            <p class="text-sm text-slate-500">{{ t('messages.coming_soon') }}</p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    // Vue 3 reactivity: computed for derived state
    import { computed } from 'vue';
    import { useReservations } from '@/Composables/FrontDesk/useReservations';
    import { useAuth } from '@/Composables/Auth/useAuth';
    import { useI18n } from '@/Composables/useI18n';
    import { formatDate } from '@/Utils/date';
    import { formatStatus } from '@/Utils/format';

    // ─── i18n ────────────────────────────────────────────────
    // useI18n: provides translation function 't'
    const { t } = useI18n();
    
    // ─── Composable ──────────────────────────────────────────
    // useReservations: provides reservation list with autoFetch enabled
    // { autoFetch: true } means data is fetched immediately when component mounts
    // reservations: reactive array of all reservations
    // loading: boolean indicating if data is being fetched
    const { reservations, loading } = useReservations({ autoFetch: true });
    
    // useAuth: provides authenticated user information
    // userName: returns the logged-in user's name
    const { userName } = useAuth();

    // ─── Computed Properties ──────────────────────────────────────────
    // currentDate: formatted current date for display in header
    const currentDate = computed(() => {
        return formatDate(new Date().toISOString());
    });

    // recentReservations: returns the 5 most recent reservations
    // .slice(0, 5) gets first 5 items from the array
    const recentReservations = computed(() => {
        return reservations.value.slice(0, 5);
    });

    // ─── Dashboard Stats Calculation ──────────────────────────────────────────
    // dashboardStats: computed object containing all stat card values
    // Recalculates whenever reservations array changes
    const dashboardStats = computed(() => {
        // Get today's date in YYYY-MM-DD format for comparison
        const today = new Date().toISOString().split('T')[0];

        return {
            // Total number of reservations
            totalReservations: reservations.value.length,
            
            // TODO: This is hardcoded to 10 - should fetch actual count from rooms API
            availableRooms: 10,
            
            // Count reservations where checkInDate equals today
            checkInsToday: reservations.value.filter((r) => r.checkInDate === today).length,
            
            // Count reservations where checkOutDate equals today
            checkOutsToday: reservations.value.filter((r) => r.checkOutDate === today).length,
        };
    });
</script>
