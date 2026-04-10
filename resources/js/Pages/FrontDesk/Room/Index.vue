<template>
    <Head :title="t('navigation.rooms')" />

    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">{{ t('rooms.title') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ t('rooms.manage_hint') }}</p>
            </div>

            <Link
                v-if="canCreate"
                href="/rooms/create"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
            >
                {{ t('rooms.new_room') }}
            </Link>
        </div>

        <div class="bg-white p-4 rounded-lg shadow mb-4 mt-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <FormInput
                    id="search"
                    v-model="searchQuery"
                    :label="t('search')"
                    :placeholder="t('rooms.search_placeholder')"
                    wrapper-class="mb-0"
                    @update:model-value="debouncedSearch"
                />

                <FormSelect
                    id="status"
                    v-model="statusFilter"
                    :label="t('rooms.status')"
                    :options="statusOptions"
                    wrapper-class="mb-0"
                    @update:model-value="debouncedSearch"
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
            :rows="rooms"
            :loading="loading"
            :loading-title="t('rooms.loading')"
            :loading-subtitle="t('rooms.please_wait')"
            :empty-title="t('rooms.no_results')"
            :empty-subtitle="t('rooms.create_new')"
            :pagination="pagination"
            :per-page="perPage"
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
            <template #cell-hotel="{ row }">
                <div class="text-sm text-slate-700">{{ row.hotel?.name || t('na') }}</div>
            </template>

            <template #cell-status="{ value, row }">
                <span
                    v-if="value"
                    :class="[
                        'px-2 py-1 text-xs font-semibold rounded-full',
                        statusBadgeClass(value)
                    ]"
                >
                    {{ row.statusLabel || value }}
                </span>
                <span v-else class="text-sm text-slate-400">{{ t('na') }}</span>
            </template>

            <template #actions="{ item }">
                <Link :href="`/rooms/${item.id}`" class="text-green-600 hover:text-green-900">
                    {{ t('actions.view') }}
                </Link>
                <Link v-if="canEdit" :href="`/rooms/${item.id}/edit`" class="text-blue-600 hover:text-blue-900">
                    {{ t('actions.edit') }}
                </Link>
                <button v-if="canDelete" @click="handleDelete(item as Room)" class="text-red-600 hover:text-red-900">
                    {{ t('actions.delete') }}
                </button>
            </template>
        </Table>
    </div>
</template>

<script setup lang="ts">
    import { ref, reactive, onMounted, inject, computed } from 'vue';
    import { router } from '@inertiajs/vue3';
    import { useRooms } from '@/Composables/FrontDesk/useRooms';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import type { ConfirmType } from '@/Plugins/confirm';
    import type { Room, RoomStatus } from '@/Types/FrontDesk/room';

    const confirm = inject<ConfirmType>('confirm')!;
    const { t } = useI18n();

    const permission = usePermissionService();
    const canCreate = computed(() => permission.check('create rooms'));
    const canView = computed(() => permission.check('view rooms'));
    const canEdit = computed(() => permission.check('edit rooms'));
    const canDelete = computed(() => permission.check('delete rooms'));

    const {
        rooms,
        loading,
        pagination,
        fetchAll,
        deleteRoom,
        setFilters,
        resetFilters,
    } = useRooms();

    onMounted(() => {
        if (!canView.value) {
            router.visit('/dashboard');
            return;
        }

        // Fetches first page of rooms when page loads
        // Only fetch if data is not already cached in Pinia
        fetchAll(1, false);  // forceRefresh = false (use cache if available)
    });

    const tableHeaders = computed(() => ([
        { key: 'number', label: t('rooms.room_number') },
        { key: 'hotel', label: t('navigation.hotels') },
        { key: 'type', label: t('rooms.room_type') },
        { key: 'floor', label: t('rooms.floor') },
        { key: 'baseRate', label: t('rooms.base_rate') },
        { key: 'status', label: t('rooms.status') },
        { key: 'actions', label: t('reservations.actions'), align: 'right' as const },
    ]));

    const tableColumns = [
        { key: 'number', className: 'font-medium text-slate-900' },
        { key: 'hotel' },
        { key: 'type', fallback: t('na') },
        { key: 'floor', fallback: t('na') },
        { key: 'baseRate', fallback: '0' },
        { key: 'status' },
    ];

    const statusOptions = computed(() => [
        { value: '', label: t('rooms.all_statuses') },
        { value: 'available', label: t('enums.room_status.available') },
        { value: 'occupied', label: t('enums.room_status.occupied') },
        { value: 'dirty', label: t('enums.room_status.dirty') },
        { value: 'out_of_order', label: t('enums.room_status.out_of_order') },
    ]);

    function statusBadgeClass(status: string): string {
        const map: Record<string, string> = {
            available: 'bg-green-100 text-green-800',
            occupied: 'bg-blue-100 text-blue-800',
            dirty: 'bg-yellow-100 text-yellow-800',
            out_of_order: 'bg-red-100 text-red-800',
        };

        return map[status] ?? 'bg-slate-100 text-slate-600';
    }

    const searchQuery = ref('');
    const statusFilter = ref<'' | RoomStatus>('');
    const perPage = ref(15);

    const localFilters = reactive<{
        search: string;
        status: '' | RoomStatus;
    }>({
        search: '',
        status: '',
    });

    let searchTimeout: ReturnType<typeof setTimeout>;

    function changePerPage(value: number) {
        perPage.value = value;
        setFilters({ perPage: value });
        fetchAll(1, true, { perPage: value });  // forceRefresh = true (new perPage means fresh data)
    }

    function debouncedSearch() {
        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(() => {
            localFilters.search = searchQuery.value;
            localFilters.status = statusFilter.value;
            setFilters({ ...localFilters, status: statusFilter.value || '' });
            fetchAll(1, true);  // forceRefresh = true (new search means fresh data)
        }, 500);
    }

    function handleResetFilters() {
        localFilters.search = '';
        localFilters.status = '';
        searchQuery.value = '';
        statusFilter.value = '';
        resetFilters();
        fetchAll(1, true);  // forceRefresh = true (reset means fresh data)
    }

    async function handleDelete(item: Room) {
        if (!canDelete.value) return;

        const confirmed = await confirm.show({
            title: t('rooms.delete_title'),
            message: `${t('rooms.delete_message')} "${item.number}". ${t('rooms.delete_warning')}`,
            confirmText: t('actions.delete'),
            cancelText: t('actions.cancel'),
            variant: 'danger',
        });

        if (!confirmed) return;

        try {
            await deleteRoom(item.id);

            const targetPage = rooms.value.length === 0 && pagination.value.currentPage > 1
                ? pagination.value.currentPage - 1
                : pagination.value.currentPage;
            await fetchAll(targetPage, true);  // forceRefresh = true (data changed)
        } catch (error) {
            console.error('Delete failed:', error);
        }
    }

    function changePage(page: number) {
        if (page < 1 || page > pagination.value.lastPage) return;
        fetchAll(page, false);  // forceRefresh = false (may use cache)
    }
</script>
