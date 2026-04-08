<template>
    <!-- Page title shown in browser tab -->
    <Head :title="t('navigation.reservations')" />
    
    <!-- Main container with max width -->
    <div class="max-w-6xl mx-auto">
        
        <!-- Header Section: Title + Create button (shown only if user has create permission) -->
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

        <!-- Status Summary Cards (4 columns) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <!-- Pending Reservations Count -->
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
                <div class="text-sm text-slate-500">{{ t('status.pending') }}</div>
                <div class="text-2xl font-bold text-slate-800">{{ pendingCount }}</div>
            </div>
            <!-- Confirmed Reservations Count -->
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
                <div class="text-sm text-slate-500">{{ t('status.confirmed') }}</div>
                <div class="text-2xl font-bold text-slate-800">{{ confirmedCount }}</div>
            </div>
            <!-- Checked-in Reservations Count -->
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
                <div class="text-sm text-slate-500">{{ t('status.checked_in') }}</div>
                <div class="text-2xl font-bold text-slate-800">{{ checkedInCount }}</div>
            </div>
            <!-- Today's Check-ins Count -->
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
                <div class="text-sm text-slate-500">{{ t('dashboard.check_ins_today') }}</div>
                <div class="text-2xl font-bold text-slate-800">{{ todayCheckIns.length }}</div>
            </div>
        </div>

        <!-- Search & Filter Bar (4 columns) -->
        <div class="bg-white p-4 rounded-lg shadow mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Input -->
                <FormInput
                    id="search"
                    v-model="searchQuery"
                    :label="t('search')"
                    :placeholder="t('reservations.search_placeholder')"
                    wrapper-class="mb-0"
                    @update:model-value="debouncedSearch"
                />

                <!-- Status Filter Dropdown -->
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

                <!-- Check-in Date Filter -->
                <DatePicker
                    id="check_in"
                    v-model="localFilters.checkInDate"
                    :label="t('reservations.check_in')"
                    wrapper-class="mb-0"
                    @update:model-value="applyFilters"
                />

                <!-- Check-out Date Filter -->
                <DatePicker
                    id="check_out"
                    v-model="localFilters.checkOutDate"
                    :label="t('reservations.check_out')"
                    wrapper-class="mb-0"
                    @update:model-value="applyFilters"
                />
            </div>

            <!-- Reset Filters Button -->
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

        <!-- Reservations Data Table -->
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
            <!-- Guest Column: Shows full name + email -->
            <template #cell-guest="{ row }">
                <div class="text-sm font-medium text-slate-900">
                    {{ row.guest?.firstName && row.guest?.lastName
                        ? row.guest.firstName + ' ' + row.guest.lastName
                        : t('na') }}
                </div>
                <div class="text-sm text-slate-500">{{ row.guest?.email || '' }}</div>
            </template>

            <!-- Room Column: Shows room number + type -->
            <template #cell-room="{ row }">
                <div class="text-sm font-medium text-slate-900">
                    {{ t('reservations.room') }} {{ row.room?.number || t('na') }}
                </div>
                <div class="text-xs text-slate-500">{{ row.room?.type || '' }}</div>
            </template>

            <!-- Check-in Column: Shows formatted date + hotel name -->
            <template #cell-checkInDate="{ row }">
                <div class="text-sm text-slate-600">{{ formatDate(row.checkInDate) }}</div>
                <div class="text-xs text-slate-500">{{ row.hotel?.name || '' }}</div>
            </template>

            <!-- Check-out Column: Shows formatted date -->
            <template #cell-checkOutDate="{ row }">
                <div class="text-sm text-slate-600">{{ formatDate(row.checkOutDate) }}</div>
            </template>

            <!-- Status Column: Shows colored badge based on status -->
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

            <!-- Total Amount Column: Shows formatted number with thousands separator -->
            <template #cell-totalAmount="{ row }">
                <div class="text-sm font-medium text-slate-900">
                    {{ row.totalAmount?.toLocaleString() || '0' }}
                </div>
            </template>

            <!-- Actions Column: Edit, View, Delete buttons -->
            <template #actions="{ item }">
                <!-- Edit button: shown only if user has edit permission -->
                <Link
                    v-if="canEdit"
                    :href="`/reservations/${item.id}/edit`"
                    class="text-blue-600 hover:text-blue-900"
                >
                    {{ t('reservations.edit') }}
                </Link>
                <!-- View button: always visible -->
                <Link :href="`/reservations/${item.id}`" class="text-green-600 hover:text-green-900">
                    {{ t('reservations.view') }}
                </Link>
                <!-- Delete button: shown only if user has delete permission -->
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
    // Vue 3 reactivity: ref for mutable reactive values, reactive for objects, onMounted for lifecycle, inject for dependency injection, computed for derived state
    import { ref, reactive, onMounted, inject, computed } from 'vue';
    import { router } from '@inertiajs/vue3';
    import { useReservations } from '@/Composables/FrontDesk/useReservations';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import type { ConfirmType } from '@/Plugins/confirm';
    import { formatStatus } from '@/Utils/format';
    import { formatDate } from '@/Utils/date';
    import type { ReservationFilters, Reservation } from '@/Types/FrontDesk/reservation';

    // ─── Dependency Injection ────────────────────────────────────────────────
    // inject: gets the global confirm dialog plugin for delete confirmation
    // The '!' tells TypeScript this will always be defined
    const confirm = inject<ConfirmType>('confirm')!;

    // ─── i18n ────────────────────────────────────────────────
    // useI18n: provides translation function 't'
    const { t } = useI18n();

    // ─── Permissions ─────────────────────────────────────────
    // usePermissionService: provides methods to check user permissions
    const permission = usePermissionService();
    
    // canCreate: true if user has 'create reservations' permission (controls "New Reservation" button)
    const canCreate = computed(() => permission.check('create reservations'));
    
    // canView: true if user has 'view reservations' permission (controls page access and visibility of reservation list)
    const canView = computed(() => permission.check('view reservations'));
    
    // canEdit: true if user has 'edit reservations' permission (controls "Edit" button in table)
    const canEdit = computed(() => permission.check('edit reservations'));
    
    // canDelete: true if user has 'delete reservations' permission (controls "Delete" button in table)
    const canDelete = computed(() => permission.check('delete reservations'));
    
    // ─── Composables ─────────────────────────────────────────
    // useReservations: provides reservation list, loading state, pagination, CRUD operations, filters, status counts
    const {
        reservations,     // Reactive array of reservation data
        loading,          // Boolean: true while fetching data
        pagination,       // Object with currentPage, lastPage, total, etc.
        pendingCount,     // Count of reservations with status 'pending'
        confirmedCount,   // Count of reservations with status 'confirmed'
        checkedInCount,   // Count of reservations with status 'checked_in'
        todayCheckIns,    // Array of reservations with check-in date = today
       
        fetchAll,         // Function to fetch all reservations with pagination
        deleteReservation,// Function to delete a reservation
        setFilters,       // Function to set search/filter parameters
        resetFilters,     // Function to clear all filters
    } = useReservations();

    // ─── Lifecycle ───────────────────────────────────────────
    // onMounted: runs when component is first loaded into the DOM
    // Redirect to /dashboard if user doesn't have view permission
    onMounted(() => {
        if (!canView.value) {
            router.visit('/dashboard');
            return;
        }

        // Fetches first page of hotels when page loads
        fetchAll(1);
    });

    // ─── Table Configuration ─────────────────────────────────────────
    // tableHeaders: defines column headers for the table component
    const tableHeaders = computed(() => ([
        { key: 'guest', label: t('reservations.guest_name') },
        { key: 'room', label: t('reservations.room_number') },
        { key: 'check_in', label: t('reservations.check_in') },
        { key: 'check_out', label: t('reservations.check_out') },
        { key: 'status', label: t('reservations.status') },
        { key: 'amount', label: t('reservations.amount') },
        { key: 'actions', label: t('reservations.actions'), align: 'right' as const },
    ]));
    
    // tableColumns: defines column styling and fallback text for empty values
    const tableColumns = [
        { key: 'guest',     className: 'font-medium text-slate-900' },
        { key: 'room' },
        { key: 'check_in',  fallback: t('na') },
        { key: 'check_out', fallback: t('na') },
        { key: 'status',    fallback: t('na') },
        { key: 'amount',    fallback: t('na') },
    ]

    // ─── Search & Filter State ─────────────────────────────────────────
    // searchQuery: reactive string for the search input field
    const searchQuery = ref('');
    
    // perPage: number of items to show per page (default 15)
    const perPage = ref(15);
    
    // localFilters: reactive object to track current filter values 
    const localFilters = reactive<ReservationFilters>({
        status: '',
        checkInDate: '',
        checkOutDate: '',
        search: '',
        perPage: 15,
    });

    // statusFilterOptions: defines all possible status filter options for the dropdown
    const statusFilterOptions = computed(() => ([
        { value: 'pending', label: t('status.pending') },
        { value: 'confirmed', label: t('status.confirmed') },
        { value: 'checked_in', label: t('status.checked_in') },
        { value: 'checked_out', label: t('status.checked_out') },
        { value: 'cancelled', label: t('status.cancelled') },
        { value: 'no_show', label: t('status.no_show') },
    ]));

    // ─── Event Handlers ─────────────────────────────────────────
    // searchTimeout: stores the setTimeout ID for debouncing search
    let searchTimeout: ReturnType<typeof setTimeout>;

    // changePerPage: called when user selects different items-per-page from dropdown
    // Updates perPage value and fetches fresh data from page 1
    function changePerPage(value: number) {
        perPage.value = value;
        setFilters({ perPage: value });
        fetchAll(1, { perPage: value });
    }

    // debouncedSearch: delays search API call by 500ms while user is typing
    // Why: Prevents excessive API calls on every keystroke
    function debouncedSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            setFilters({ ...localFilters, search: searchQuery.value });
            fetchAll(1);
        }, 500);
    }

    // applyFilters: applies all current filter values and fetches filtered data from page 1
    function applyFilters() {
        setFilters({ ...localFilters, search: searchQuery.value });
        fetchAll(1);
    }

    // handleResetFilters: clears all filter inputs and fetches fresh unfiltered data
    function handleResetFilters() {
        localFilters.status = '';
        localFilters.checkInDate = '';
        localFilters.checkOutDate = '';
        localFilters.search = '';
        resetFilters();
        fetchAll(1);
    }

    // handleDelete: shows confirmation dialog, then deletes the reservation if confirmed
    async function handleDelete(res: Reservation) {
        // Check permission before allowing delete
        if (!canDelete.value) return;

        // Show confirmation dialog
        const confirmed = await confirm.show({
            title: t('actions.delete'),
            message: t('messages.confirm_delete'),
            confirmText: t('actions.delete'),
            cancelText: t('actions.cancel'),
            variant: 'danger',
        });

        // If user clicked "Cancel", stop here
        if (!confirmed) return;

        try {
            // Delete the reservation via API
            await deleteReservation(res.id);
            
            // After deletion, determine which page to fetch
            const targetPage = reservations.value.length === 0 && pagination.value.currentPage > 1
                ? pagination.value.currentPage - 1
                : pagination.value.currentPage;
            await fetchAll(targetPage);
        } catch (e) {
            console.error('Delete failed:', e);
        }
    }

    // changePage: navigates to specified page number
    function changePage(page: number) {
        if (page < 1 || page > pagination.value.lastPage) return;
        fetchAll(page);
    }
</script>
