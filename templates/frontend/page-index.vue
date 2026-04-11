<!-- FILE: resources/js/Pages/[MODULE_NAME]/[FEATURE_NAME]/Index.vue -->

<template>
    <!-- Page title shown in browser tab -->
    <Head :title="t('navigation.[feature_name]')" />

    <!-- Main container with max width -->
    <div class="max-w-6xl mx-auto">

        <!-- Header Section: Title + Create button (shown only if user has create permission) -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">{{ t('[feature_name].title') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ t('[feature_name].manage_hint') }}</p>
            </div>
            <Link
                v-if="canCreate"
                href="/[web_route]/create"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
            >
                {{ t('[feature_name].new_[feature_name]') }}
            </Link>
        </div>

        <!-- Search & Filter Bar -->
        <div class="bg-white p-4 rounded-lg shadow mb-4 mt-4">
            <div class="grid grid-cols-1 md:grid-cols-[COLUMNS] gap-4">
                <!-- Search Input -->
                <FormInput
                    id="search"
                    v-model="searchQuery"
                    :label="t('search')"
                    :placeholder="t('[feature_name].search_placeholder')"
                    wrapper-class="mb-0"
                    @update:model-value="debouncedSearch"
                />

                <!-- [ADDITIONAL_FILTERS - e.g., Status Filter if enum exists] -->
                <!--
                <FormSelect
                    id="status"
                    v-model="localFilters.status"
                    :label="t('[feature_name].status')"
                    :placeholder="t('[feature_name].all_status')"
                    :options="statusFilterOptions"
                    option-label="label"
                    option-value="value"
                    wrapper-class="mb-0"
                    @update:model-value="applyFilters"
                />
                -->
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

        <!-- Data Table -->
        <Table
            :headers="tableHeaders"
            :columns="tableColumns"
            :rows="[feature_plural]"
            :loading="loading"
            :loading-title="t('[feature_name].loading')"
            :loading-subtitle="t('[feature_name].please_wait')"
            :empty-title="t('[feature_name].no_results')"
            :empty-subtitle="t('[feature_name].create_new')"
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
            <!-- Actions Column: View, Edit, Delete buttons -->
            <template #actions="{ item }">
                <!-- View button: always visible -->
                <Link :href="`/[web_route]/${item.id}`" class="text-green-600 hover:text-green-900">
                    {{ t('actions.view') }}
                </Link>
                <!-- Edit button: shown only if user has edit permission -->
                <Link v-if="canEdit" :href="`/[web_route]/${item.id}/edit`" class="text-blue-600 hover:text-blue-900">
                    {{ t('actions.edit') }}
                </Link>
                <!-- Delete button: shown only if user has delete permission -->
                <button v-if="canDelete" @click="handleDelete(item as [MODEL_NAME])" class="text-red-600 hover:text-red-900">
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
    import { use[MODEL_NAME]s } from '@/Composables/[MODULE_NAME]/use[MODEL_NAME]s';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import type { ConfirmType } from '@/Plugins/confirm';
    import type { [MODEL_NAME] } from '@/Types/[MODULE_NAME]/[model_name]';

    // ─── Dependency Injection ────────────────────────────────────────────────
    const confirm = inject<ConfirmType>('confirm')!;

    // ─── i18n ────────────────────────────────────────────────
    const { t } = useI18n();

    // ─── Permissions ─────────────────────────────────────────
    const permission = usePermissionService();
    const canCreate = computed(() => permission.check('create [feature_name_plural]'));
    const canView = computed(() => permission.check('view [feature_name_plural]'));
    const canEdit = computed(() => permission.check('edit [feature_name_plural]'));
    const canDelete = computed(() => permission.check('delete [feature_name_plural]'));

    // ─── Composables ─────────────────────────────────────────
    const {
        [feature_plural],     // Reactive array of data
        loading,          // Boolean: true while fetching data
        pagination,       // Object with currentPage, lastPage, total, etc.

        fetchAll,         // Function to fetch all with pagination
        delete[MODEL_NAME],      // Function to delete
        setFilters,       // Function to set search/filter parameters
        resetFilters,     // Function to clear all filters
    } = use[MODEL_NAME]s();

    // ─── Lifecycle ───────────────────────────────────────────
    onMounted(() => {
        if (!canView.value) {
            router.visit('/dashboard');
            return;
        }

        // Fetches first page when page loads (uses cache if available)
        fetchAll(1, false);
    });

    // ─── Table Configuration ─────────────────────────────────────────
    const tableHeaders = computed(() => ([
        // [HEADERS - customize for your fields]
        // Example:
        // { key: 'name', label: t('[feature_name].name') },
        // { key: 'code', label: t('[feature_name].code') },
        // { key: 'email', label: t('[feature_name].email') },
        // { key: 'phone', label: t('[feature_name].phone') },
        // { key: 'status', label: t('[feature_name].status') },
        { key: 'actions', label: t('reservations.actions'), align: 'right' as const },
    ]));

    const tableColumns = [
        // [COLUMNS - customize for your fields]
        // Example:
        // { key: 'name', className: 'font-medium text-slate-900' },
        // { key: 'code' },
        // { key: 'email', fallback: t('na') },
        // { key: 'phone', fallback: t('na') },
        // { key: 'status', fallback: t('na') },
    ];

    // ─── Search & Filter State ─────────────────────────────────────────
    const searchQuery = ref('');
    const perPage = ref(15);

    const localFilters = reactive({
        search: '',
        // [ADDITIONAL_FILTERS]
        // Example:
        // status: '',
    });

    // [FILTER_OPTIONS - if enum exists]
    // Example:
    // const statusFilterOptions = computed(() => ([
    //     { value: 'active', label: t('status.active') },
    //     { value: 'inactive', label: t('status.inactive') },
    // ]));

    // ─── Event Handlers ─────────────────────────────────────────
    let searchTimeout: ReturnType<typeof setTimeout>;

    function changePerPage(value: number) {
        perPage.value = value;
        fetchAll(1, true, { perPage: value });  // forceRefresh = true
    }

    function debouncedSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            setFilters({ ...localFilters, search: searchQuery.value });
            fetchAll(1, true);  // forceRefresh = true
        }, 500);
    }

    // [APPLY_FILTERS - if additional filters exist]
    // function applyFilters() {
    //     setFilters({ ...localFilters, search: searchQuery.value });
    //     fetchAll(1, true);  // forceRefresh = true
    // }

    function handleResetFilters() {
        localFilters.search = '';
        // [RESET_ADDITIONAL_FILTERS]
        searchQuery.value = '';
        resetFilters();
        fetchAll(1, true);  // forceRefresh = true
    }

    async function handleDelete(item: [MODEL_NAME]) {
        if (!canDelete.value) return;

        const confirmed = await confirm.show({
            title: t('[feature_name].delete_title'),
            message: `${t('[feature_name].delete_message')} "${item.[DISPLAY_FIELD]}". ${t('[feature_name].delete_warning')}`,
            confirmText: t('actions.delete'),
            cancelText: t('actions.cancel'),
            variant: 'danger',
        });

        if (!confirmed) return;

        try {
            await delete[MODEL_NAME](item.id);

            const targetPage = [feature_plural].value.length === 0 && pagination.value.currentPage > 1
                ? pagination.value.currentPage - 1
                : pagination.value.currentPage;
            await fetchAll(targetPage, true);  // forceRefresh = true
        } catch (e) {
            console.error('Delete failed:', e);
        }
    }

    function changePage(page: number) {
        if (page < 1 || page > pagination.value.lastPage) return;
        fetchAll(page, false);  // forceRefresh = false (may use cache)
    }
</script>
