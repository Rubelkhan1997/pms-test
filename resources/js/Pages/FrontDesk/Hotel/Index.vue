<template>
    <!-- Page title shown in browser tab -->
    <Head :title="t('navigation.hotels')" />
    
    <!-- Main container with max width -->
    <div class="max-w-6xl mx-auto">
        
        <!-- Header Section: Title + Create button (shown only if user has create permission) -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">{{ t('hotels.title') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ t('hotels.manage_hint') }}</p>
            </div>
            <Link
                v-if="canCreate"
                href="/hotels/create"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
            >
                {{ t('hotels.new_hotel') }}
            </Link>
        </div>

        <!-- Search & Filter Bar -->
        <div class="bg-white p-4 rounded-lg shadow mb-4 mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Search Input -->
                <FormInput
                    id="search"
                    v-model="searchQuery"
                    :label="t('search')"
                    :placeholder="t('hotels.search_placeholder')"
                    wrapper-class="mb-0"
                    @update:model-value="debouncedSearch"
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

        <!-- Hotels Data Table -->
        <Table
            :headers="tableHeaders"
            :columns="tableColumns"
            :rows="hotels"
            :loading="loading"
            :loading-title="t('hotels.loading')"
            :loading-subtitle="t('hotels.please_wait')"
            :empty-title="t('hotels.no_results')"
            :empty-subtitle="t('hotels.create_new')"
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
            <!-- Empty State Icon (shown when no hotels exist) -->
            <template #empty-icon>
                <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </template>

            <!-- Action Buttons for Each Row (View, Edit, Delete) -->
            <template #actions="{ item }">
                <!-- View button: always visible -->
                <Link :href="`/hotels/${item.id}`" class="text-green-600 hover:text-green-900">
                    {{ t('actions.view') }}
                </Link>
                <!-- Edit button: shown only if user has edit permission -->
                <Link v-if="canEdit" :href="`/hotels/${item.id}/edit`" class="text-blue-600 hover:text-blue-900">
                    {{ t('actions.edit') }}
                </Link>
                <!-- Delete button: shown only if user has delete permission -->
                <button v-if="canDelete" @click="handleDelete(item as Hotel)" class="text-red-600 hover:text-red-900">
                    {{ t('actions.delete') }}
                </button>
            </template>
        </Table>
    </div>
</template>

<script setup lang="ts">
    // Vue 3 reactivity: ref for mutable reactive values, reactive for objects, onMounted for lifecycle, inject for dependency injection, computed for derived state
    import { ref, reactive, onMounted, inject, computed } from 'vue';
    import { router } from '@inertiajs/vue3';
    import { useHotels } from '@/Composables/FrontDesk/useHotels';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import type { ConfirmType } from '@/Plugins/confirm';
    import type { Hotel } from '@/Types/FrontDesk/hotel';

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
    const canCreate = computed(() => permission.check('create hotels'));
    const canView = computed(() => permission.check('view hotels'));
    const canEdit = computed(() => permission.check('edit hotels'));
    const canDelete = computed(() => permission.check('delete hotels'));
    
    // ─── Composables ─────────────────────────────────────────
    // useHotels: provides hotel list, loading state, pagination, CRUD operations, filters
    const {
        hotels,           // Reactive array of hotel data
        loading,          // Boolean: true while fetching data
        pagination,       // Object with currentPage, lastPage, total, etc.
        
        fetchAll,         // Function to fetch all hotels with pagination
        deleteHotel,      // Function to delete a hotel
        setFilters,       // Function to set search/filter parameters
        resetFilters,     // Function to clear all filters
    } = useHotels();

    // ─── Lifecycle ───────────────────────────────────────────
    // onMounted: runs when component is first loaded into the DOM
    // Redirect to /dashboard if user doesn't have view permission
    onMounted(() => {
        if (!canView.value) {
            router.visit('/dashboard');
            return;
        }

        // Fetches first page of hotels when page loads
        // Only fetch if data is not already cached in Pinia
        fetchAll(1, false);  // forceRefresh = false (use cache if available)
    });

    // ─── Table Configuration ─────────────────────────────────────────
    // tableHeaders: defines column headers for the table component
    const tableHeaders = computed(() => ([
        { key: 'name', label: t('hotels.name') },
        { key: 'code', label: t('hotels.code') },
        { key: 'email', label: t('hotels.email') },
        { key: 'phone', label: t('hotels.phone') },
        { key: 'currency', label: t('hotels.currency') },
        { key: 'actions', label: t('reservations.actions'), align: 'right' as const },
    ]));
    
    // tableColumns: defines column styling and fallback text for empty values
    const tableColumns = [
        { key: 'name',     className: 'font-medium text-slate-900' },
        { key: 'code' },
        { key: 'email',    fallback: t('na') },
        { key: 'phone',    fallback: t('na') },
        { key: 'currency', fallback: t('na') },
    ]

    // ─── Search & Filter State ─────────────────────────────────────────
    // searchQuery: reactive string for the search input field
    const searchQuery = ref('');
    
    // perPage: number of items to show per page (default 15)
    const perPage = ref(15);
    
    // localFilters: reactive object to track current filter values
    const localFilters = reactive({
        search: '',
    });
    
    // ─── Event Handlers ─────────────────────────────────────────
    // searchTimeout: stores the setTimeout ID for debouncing search
    let searchTimeout: ReturnType<typeof setTimeout>;

    // changePerPage: called when user selects different items-per-page from dropdown
    // Updates perPage value and fetches fresh data from page 1
    function changePerPage(value: number) {
        perPage.value = value;
        setFilters({ perPage: value });
        fetchAll(1, true, { perPage: value });  // forceRefresh = true (new perPage means fresh data)
    }

    // debouncedSearch: delays search API call by 500ms while user is typing
    // Why: Prevents excessive API calls on every keystroke
    // How: Clears previous timeout and sets a new one
    function debouncedSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            setFilters({ ...localFilters, search: searchQuery.value });
            fetchAll(1, true);  // forceRefresh = true (new search means fresh data)
        }, 500);
    }

    // handleResetFilters: clears all filter inputs and fetches fresh unfiltered data
    function handleResetFilters() {
        localFilters.search = '';
        searchQuery.value = '';
        resetFilters();
        fetchAll(1, true);  // forceRefresh = true (reset means fresh data)
    }

    // handleDelete: shows confirmation dialog, then deletes the hotel if confirmed
    async function handleDelete(item: Hotel) {
        // Check permission before allowing delete
        if (!canDelete.value) return;
        
        // Show confirmation dialog with hotel details
        const confirmed = await confirm.show({
            title: t('hotels.delete_title'),
            message: `${t('hotels.delete_message')} "${item.name}". ${t('hotels.delete_warning')}`,
            confirmText: t('actions.delete'),
            cancelText: t('actions.cancel'),
            variant: 'danger',
        });

        // If user clicked "Cancel", stop here
        if (!confirmed) return;

        try {
            // Delete the hotel via API
            await deleteHotel(item.id);

            // After deletion, determine which page to fetch:
            // If current page has no items and we're not on page 1, go to previous page
            const targetPage = hotels.value.length === 0 && pagination.value.currentPage > 1
                ? pagination.value.currentPage - 1
                : pagination.value.currentPage;
            await fetchAll(targetPage, true);  // forceRefresh = true (data changed)
        } catch (e) {
            console.error('Delete failed:', e);
        }
    }

    // changePage: navigates to specified page number
    function changePage(page: number) {
        if (page < 1 || page > pagination.value.lastPage) return;
        fetchAll(page, false);  // forceRefresh = false (may use cache)
    }
</script>
