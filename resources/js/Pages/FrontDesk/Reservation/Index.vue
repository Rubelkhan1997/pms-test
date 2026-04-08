<template>
    <Head :title="t('navigation.reservations')" />
    <div class="max-w-6xl mx-auto">
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

        <div class="bg-white p-4 rounded-lg shadow mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <FormInput
                    id="search"
                    v-model="searchQuery"
                    :label="t('search')"
                    :placeholder="t('reservations.search_placeholder')"
                    wrapper-class="mb-0"
                    @update:model-value="debouncedSearch"
                />

                <FormSelect
                    id="status"
                    v-model="localFilters.status"
                    :label="t('reservations.status')"
                    :placeholder="t('reservations.all_status')"
                    :options="statusFilterOptions"
                    option-label="label"
                    option-value="value"
                    wrapper-class="mb-0"
                    @update:model-value="applyFilters"
                />

                <DatePicker
                    id="check_in"
                    v-model="localFilters.checkInDate"
                    :label="t('reservations.check_in')"
                    wrapper-class="mb-0"
                    @update:model-value="applyFilters"
                />

                <DatePicker
                    id="check_out"
                    v-model="localFilters.checkOutDate"
                    :label="t('reservations.check_out')"
                    wrapper-class="mb-0"
                    @update:model-value="applyFilters"
                />
            </div>

            <div class="mt-4 flex justify-end">
                <FormButton
                    @click="handleResetFilters"
                    type="button"
                    color="secondary"
                    :name="t('actions.reset')"
                    button-class="text-sm"
                />
            </div>
        </div>

        <Table
            :headers="tableHeaders"
            :columns="tableColumns"
            :rows="reservations"
            :loading="loading"
            :loading-title="t('reservations.loading')"
            :loading-subtitle="t('reservations.please_wait')"
            :empty-title="t('reservations.no_results')"
            :empty-subtitle="t('reservations.create_new')"
            :pagination="pagination"
            :per-page="perPage"
            :per-page-options="[5, 15, 25, 50, 100]"
            :show-pagination="pagination.lastPage > 1"
            :per-page-label="t('reservations.per_page')"
            :page-label="t('reservations.page')"
            :of-label="t('reservations.of')"
            :total-label="t('reservations.total')"
            :previous-label="t('previous')"
            :next-label="t('next')"
            @change-page="changePage"
            @change-per-page="changePerPage"
        >
            <!-- Guest -->
            <template #cell-guest="{ row }">
                <div class="text-sm font-medium text-slate-900">
                    {{ row.guest?.firstName && row.guest?.lastName
                        ? row.guest.firstName + ' ' + row.guest.lastName
                        : t('na') }}
                </div>
                <div class="text-sm text-slate-500">{{ row.guest?.email || '' }}</div>
            </template>

            <!-- Room -->
            <template #cell-room="{ row }">
                <div class="text-sm font-medium text-slate-900">
                    {{ t('reservations.room') }} {{ row.room?.number || t('na') }}
                </div>
                <div class="text-xs text-slate-500">{{ row.room?.type || '' }}</div>
            </template>

            <!-- Check-in -->
            <template #cell-checkInDate="{ row }">
                <div class="text-sm text-slate-600">{{ formatDate(row.checkInDate) }}</div>
                <div class="text-xs text-slate-500">{{ row.hotel?.name || '' }}</div>
            </template>

            <!-- Check-out -->
            <template #cell-checkOutDate="{ row }">
                <div class="text-sm text-slate-600">{{ formatDate(row.checkOutDate) }}</div>
            </template>

            <!-- Status badge -->
            <template #cell-status="{ row }">
                <span
                    :class="{
                        'bg-yellow-100 text-yellow-800': row.status === 'pending',
                        'bg-green-100 text-green-800':   row.status === 'confirmed',
                        'bg-blue-100 text-blue-800':     row.status === 'checked_in',
                        'bg-purple-100 text-purple-800': row.status === 'checked_out',
                        'bg-red-100 text-red-800':       row.status === 'cancelled',
                        'bg-slate-100 text-slate-800':   row.status === 'no_show',
                    }"
                    class="px-2 py-1 text-xs font-medium rounded"
                >
                    {{ formatStatus(row.status) }}
                </span>
            </template>

            <!-- Total amount -->
            <template #cell-totalAmount="{ row }">
                <div class="text-sm font-medium text-slate-900">
                    {{ row.totalAmount?.toLocaleString() || '0' }}
                </div>
            </template>

            <!-- Actions -->
            <template #actions="{ item }">
                <Link
                    v-if="canEdit"
                    :href="`/reservations/${item.id}/edit`"
                    class="text-blue-600 hover:text-blue-900"
                >
                    {{ t('reservations.edit') }}
                </Link>
                <Link :href="`/reservations/${item.id}`" class="text-green-600 hover:text-green-900">
                    {{ t('reservations.view') }}
                </Link>
                <button
                    v-if="canDelete"
                    @click="handleDelete(item as Reservation)"
                    class="text-red-600 hover:text-red-900"
                >
                    {{ t('reservations.delete') }}
                </button>
            </template>
        </Table>
    </div>
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

    const confirm = inject<ConfirmType>('confirm')!;
    const permission = usePermissionService();
    const { t } = useI18n();

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

    const canCreate = computed(() => permission.check('create reservations'));
    const canEdit = computed(() => permission.check('edit reservations'));
    const canDelete = computed(() => permission.check('delete reservations'));
    const tableHeaders = computed(() => ([
        { key: 'guest', label: t('reservations.guest_name') },
        { key: 'room', label: t('reservations.room_number') },
        { key: 'check_in', label: t('reservations.check_in') },
        { key: 'check_out', label: t('reservations.check_out') },
        { key: 'status', label: t('reservations.status') },
        { key: 'amount', label: t('reservations.amount') },
        { key: 'actions', label: t('reservations.actions'), align: 'right' as const },
    ]));
    const tableColumns = [
        { key: 'guest',     className: 'font-medium text-slate-900' },
        { key: 'room' },
        { key: 'check_in',  fallback: t('na') },
        { key: 'check_out', fallback: t('na') },
        { key: 'status',    fallback: t('na') },
        { key: 'amount',    fallback: t('na') },
    ]

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

    const statusFilterOptions = computed(() => ([
        { value: 'pending', label: t('status.pending') },
        { value: 'confirmed', label: t('status.confirmed') },
        { value: 'checked_in', label: t('status.checked_in') },
        { value: 'checked_out', label: t('status.checked_out') },
        { value: 'cancelled', label: t('status.cancelled') },
        { value: 'no_show', label: t('status.no_show') },
    ]));

    function changePerPage(value: number) {
        perPage.value = value;
        setFilters({ perPage: value });
        fetchAll(1, { perPage: value });
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
