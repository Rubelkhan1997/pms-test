<template>
    <Head :title="t('navigation.reservations')" />
    <div class="max-w-6xl mx-auto">
        <!-- ─── Header ─────────────────────────────────────────── -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">{{ t('reservations.title') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ t('reservations.manage_hint') }}</p>
            </div>
            <Link
                v-if="canCreate"
                href="/reservations/create"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
            >
                {{ t('reservations.new_reservation') }}
            </Link>
        </div>

        <!-- ─── Stats Cards ────────────────────────────────────── -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
                <div class="text-sm text-slate-500">{{ t('status.pending') }}</div>
                <div class="text-2xl font-bold text-slate-800">{{ pendingCount }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
                <div class="text-sm text-slate-500">{{ t('status.confirmed') }}</div>
                <div class="text-2xl font-bold text-slate-800">{{ confirmedCount }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
                <div class="text-sm text-slate-500">{{ t('status.checked_in') }}</div>
                <div class="text-2xl font-bold text-slate-800">{{ checkedInCount }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
                <div class="text-sm text-slate-500">{{ t('dashboard.check_ins_today') }}</div>
                <div class="text-2xl font-bold text-slate-800">{{ todayCheckIns.length }}</div>
            </div>
        </div>

            <!-- ─── Filters ────────────────────────────────────────── -->
            <div class="bg-white p-4 rounded-lg shadow mb-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ t('search') }}
                        </label>
                        <input
                            v-model="searchQuery"
                            @input="debouncedSearch"
                            type="text"
                            :placeholder="t('reservations.search_placeholder')"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ t('reservations.status') }}
                        </label>
                        <select
                            v-model="localFilters.status"
                            @change="applyFilters"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">{{ t('reservations.all_status') }}</option>
                            <option value="pending">{{ t('status.pending') }}</option>
                            <option value="confirmed">{{ t('status.confirmed') }}</option>
                            <option value="checked_in">{{ t('status.checked_in') }}</option>
                            <option value="checked_out">{{ t('status.checked_out') }}</option>
                            <option value="cancelled">{{ t('status.cancelled') }}</option>
                            <option value="no_show">{{ t('status.no_show') }}</option>
                        </select>
                    </div>

                    <!-- Check-in From -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ t('reservations.check_in') }}
                        </label>
                        <input
                            v-model="localFilters.checkInDate"
                            @change="applyFilters"
                            type="date"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Check-out To -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ t('reservations.check_out') }}
                        </label>
                        <input
                            v-model="localFilters.checkOutDate"
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
                        {{ t('actions.reset') }}
                    </button>
                </div>
            </div>

            <!-- ─── Table ──────────────────────────────────────────── -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ t('reservations.guest_name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ t('reservations.room_number') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ t('reservations.check_in') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ t('reservations.check_out') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ t('reservations.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ t('reservations.amount') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ t('reservations.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">

                            <!-- Loading - Full Table Overlay -->
                            <tr v-if="loading">
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-cyan-600 mb-4"></div>
                                        <p class="text-lg font-medium text-slate-700">{{ t('reservations.loading') }}</p>
                                        <p class="text-sm text-slate-500 mt-1">{{ t('reservations.please_wait') }}</p>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty -->
                            <tr v-else-if="reservations.length === 0">
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <p class="text-lg font-medium text-slate-700">{{ t('reservations.no_results') }}</p>
                                        <p class="text-sm text-slate-500 mt-1">{{ t('reservations.create_new') }}</p>
                                    </div>
                                </td>
                            </tr>

                            <!-- Rows -->
                            <template v-else>
                                <tr
                                    v-for="res in reservations"
                                    :key="res.id"
                                    class="hover:bg-slate-50 transition"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-slate-900">
                                            {{ res.guest?.firstName && res.guest?.lastName 
                                                ? res.guest.firstName + ' ' + res.guest.lastName 
                                                : t('na')
                                            }}
                                        </div>
                                        <div class="text-sm text-slate-500">{{ res.guest?.email || '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-slate-900">
                                            {{ t('reservations.room') }} {{ res.room?.number || t('na') }}
                                        </div>
                                        <div class="text-xs text-slate-500">{{ res.room?.type || '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-600">{{ formatDate(res.checkInDate) }}</div>
                                        <div class="text-xs text-slate-500">{{ res.hotel?.name || '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                        {{ formatDate(res.checkOutDate) }}
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
                                        ৳{{ res.totalAmount?.toLocaleString() || '0' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                v-if="canEdit"
                                                :href="`/reservations/${res.id}/edit`"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                {{ t('reservations.edit') }}
                                            </Link>
                                            <Link :href="`/reservations/${res.id}`" class="text-green-600 hover:text-green-900">
                                                {{ t('reservations.view') }}
                                            </Link>
                                            <button
                                                v-if="canDelete"
                                                @click="handleDelete(res)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                {{ t('reservations.delete') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- ─── Pagination ─────────────────────────────────── -->
                <div
                    v-if="pagination.lastPage > 1"
                    class="px-6 py-4 border-t border-slate-200 flex justify-between items-center"
                >
                    <div class="flex items-center gap-4">
                        <!-- Per Page -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-slate-500">{{ t('reservations.per_page') }}:</label>
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
                            {{ t('reservations.page') }} {{ pagination.currentPage }} {{ t('reservations.of') }} {{ pagination.lastPage }}
                            ({{ pagination.total }} {{ t('reservations.total') }})
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <button
                            @click="changePage(pagination.currentPage - 1)"
                            :disabled="pagination.currentPage === 1"
                            class="px-3 py-1 border border-slate-300 rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50"
                        >
                            {{ t('previous') }}
                        </button>
                        <button
                            @click="changePage(pagination.currentPage + 1)"
                            :disabled="pagination.currentPage === pagination.lastPage"
                            class="px-3 py-1 border border-slate-300 rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50"
                        >
                            {{ t('next') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <!-- </AppLayout> -->
</template>

<script setup lang="ts">
    import { ref, reactive, onMounted, inject, computed } from 'vue';
    import { useReservations } from '@/Composables/FrontDesk/useReservations';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import type { ConfirmType } from '@/Plugins/confirm';
    import { formatStatus } from '@/Utils/format';
    import { formatDate } from '@/Utils/date';
    import type { ReservationFilters, Reservation } from '@/Types/FrontDesk/reservation';

    // ─── Inject Confirm ─────────────────────────────────────
    const confirm = inject<ConfirmType>('confirm')!;
    const permission = usePermissionService();

    // ─── i18n ────────────────────────────────────────────────
    const { t } = useI18n();

    const {
        // State
        reservations,
        loading,
        pagination,
        pendingCount,
        confirmedCount,
        checkedInCount,
        todayCheckIns,

        // Actions
        fetchAll,
        deleteReservation,
        setFilters,
        resetFilters,
    } = useReservations();

    const canCreate = computed(() => permission.check('create reservations'));
    const canEdit = computed(() => permission.check('edit reservations'));
    const canDelete = computed(() => permission.check('delete reservations'));

    const searchQuery = ref('');
    const perPage = ref(15);
    const localFilters = reactive<ReservationFilters>({
        status: '',
        checkInDate: '',
        checkOutDate: '',
        search: '',
        perPage: 15,
    });

    let searchTimeout: ReturnType<typeof setTimeout>;

    function changePerPage() {
        setFilters({ perPage: perPage.value });
        fetchAll(1, { perPage: perPage.value });
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
        localFilters.checkInDate = '';
        localFilters.checkOutDate = '';
        localFilters.search = '';
        resetFilters();
        fetchAll(1);
    }

    async function handleDelete(res: Reservation) { 
        if (!canDelete.value) return;
        const confirmed = await confirm.show({
            title: t('actions.delete'),
            message: t('messages.confirm_delete'),
            confirmText: t('actions.delete'),
            cancelText: t('actions.cancel'),
            variant: 'danger',  
        });

        if (!confirmed) return;

        try {
            await deleteReservation(res.id);
            const targetPage = reservations.value.length === 0 && pagination.value.currentPage > 1
                ? pagination.value.currentPage - 1
                : pagination.value.currentPage;
            await fetchAll(targetPage);
        } catch (e) {
            console.error('Delete failed:', e);
        }
    }

    function changePage(page: number) {
        if (page < 1 || page > pagination.value.lastPage) return;
        fetchAll(page);
    }

    onMounted(() => {
        fetchAll(1);
    });
</script>
