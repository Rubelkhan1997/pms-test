<template>
    <Head :title="t('navigation.hotels')" />
    <div class="max-w-6xl mx-auto">
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

        <div class="bg-white p-4 rounded-lg shadow mb-4 mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormInput
                    id="search"
                    v-model="searchQuery"
                    :label="t('search')"
                    :placeholder="t('hotels.search_placeholder')"
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
            <template #empty-icon>
                <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </template>

            <template #actions="{ item }">
                <Link :href="`/hotels/${item.id}`" class="text-green-600 hover:text-green-900">
                    {{ t('actions.view') }}
                </Link>
                <Link v-if="canEdit" :href="`/hotels/${item.id}/edit`" class="text-blue-600 hover:text-blue-900">
                    {{ t('actions.edit') }}
                </Link>
                <button v-if="canDelete" @click="handleDelete(item as Hotel)" class="text-red-600 hover:text-red-900">
                    {{ t('actions.delete') }}
                </button>
            </template>
        </Table>
    </div>
</template>

<script setup lang="ts">
    import { ref, reactive, onMounted, inject, computed } from 'vue';
    import { useHotels } from '@/Composables/FrontDesk/useHotels';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import type { ConfirmType } from '@/Plugins/confirm';
    import type { Hotel } from '@/Types/FrontDesk/hotel';

    const confirm = inject<ConfirmType>('confirm')!;
    const permission = usePermissionService();
    const { t } = useI18n();

    const {
        hotels,
        loading,
        pagination,
        fetchAll,
        deleteHotel,
        setFilters,
        resetFilters,
    } = useHotels();

    const canCreate = computed(() => permission.check('create hotels'));
    const canEdit = computed(() => permission.check('edit hotels'));
    const canDelete = computed(() => permission.check('delete hotels'));
    const tableHeaders = computed(() => ([
        { key: 'name', label: t('hotels.name') },
        { key: 'code', label: t('hotels.code') },
        { key: 'email', label: t('hotels.email') },
        { key: 'phone', label: t('hotels.phone') },
        { key: 'currency', label: t('hotels.currency') },
        { key: 'actions', label: t('reservations.actions'), align: 'right' as const },
    ]));
    const tableColumns = [
        { key: 'name',     className: 'font-medium text-slate-900' },
        { key: 'code' },
        { key: 'email',    fallback: t('na') },
        { key: 'phone',    fallback: t('na') },
        { key: 'currency', fallback: t('na') },
    ]

    const searchQuery = ref('');
    const perPage = ref(15);
    const localFilters = reactive({
        search: '',
    });

    let searchTimeout: ReturnType<typeof setTimeout>;

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

    function handleResetFilters() {
        localFilters.search = '';
        searchQuery.value = '';
        resetFilters();
        fetchAll(1);
    }

    async function handleDelete(item: Hotel) {
        const confirmed = await confirm.show({
            title: t('hotels.delete_title'),
            message: `${t('hotels.delete_message')} "${item.name}". ${t('hotels.delete_warning')}`,
            confirmText: t('actions.delete'),
            cancelText: t('actions.cancel'),
            variant: 'danger',
        });

        if (!confirmed) return;

        try {
            await deleteHotel(item.id);
            const targetPage = hotels.value.length === 0 && pagination.value.currentPage > 1
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
