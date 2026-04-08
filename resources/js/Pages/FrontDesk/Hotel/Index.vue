<template>
    <Head :title="t('navigation.hotels')" />
    <div class="max-w-6xl mx-auto">
        <!-- ─── Header ─────────────────────────────────────────── -->
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

        <!-- ─── Filters ────────────────────────────────────────── -->
        <div class="bg-white p-4 rounded-lg shadow mb-4 mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Search -->
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

        <!-- ─── Table ──────────────────────────────────────────── -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ t('hotels.name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ t('hotels.code') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ t('hotels.email') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ t('hotels.phone') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ t('hotels.currency') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ t('reservations.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">

                        <!-- Loading -->
                        <tr v-if="loading">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-cyan-600 mb-4"></div>
                                    <p class="text-lg font-medium text-slate-700">{{ t('hotels.loading') }}</p>
                                    <p class="text-sm text-slate-500 mt-1">{{ t('hotels.please_wait') }}</p>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty -->
                        <tr v-else-if="hotels.length === 0">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <p class="text-lg font-medium text-slate-700">{{ t('hotels.no_results') }}</p>
                                    <p class="text-sm text-slate-500 mt-1">{{ t('hotels.create_new') }}</p>
                                </div>
                            </td>
                        </tr>

                        <!-- Rows -->
                        <template v-else>
                            <tr
                                v-for="item in hotels"
                                :key="item.id"
                                class="hover:bg-slate-50 transition"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ item.name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-600">{{ item.code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-600">{{ item.email || t('na') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-600">{{ item.phone || t('na') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-600">{{ item.currency || t('na') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="`/hotels/${item.id}`"
                                            class="text-green-600 hover:text-green-900"
                                        >
                                            {{ t('actions.view') }}
                                        </Link>
                                        <Link
                                            v-if="canEdit"
                                            :href="`/hotels/${item.id}/edit`"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            {{ t('actions.edit') }}
                                        </Link>
                                        <button
                                            v-if="canDelete"
                                            @click="handleDelete(item)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            {{ t('actions.delete') }}
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
                        </select>
                    </div>

                    <!-- Page Info -->
                    <div class="text-sm text-slate-500">
                        {{ t('reservations.page') }} {{ pagination.currentPage }} {{ t('reservations.of') }} {{ pagination.lastPage }}
                        ({{ pagination.total }} {{ t('reservations.total') }})
                    </div>
                </div>

                <div class="flex gap-2">
                    <FormButton
                        @click="changePage(pagination.currentPage - 1)"
                        :disabled="pagination.currentPage === 1"
                        type="button"
                        color="secondary"
                        :name="t('previous')"
                        button-class="px-3 py-1"
                    />
                    <FormButton
                        @click="changePage(pagination.currentPage + 1)"
                        :disabled="pagination.currentPage === pagination.lastPage"
                        type="button"
                        color="secondary"
                        :name="t('next')"
                        button-class="px-3 py-1"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { ref, reactive, onMounted, inject, computed } from 'vue';
    import { FormButton, FormInput } from '@/Components/Form';
    import { useHotels } from '@/Composables/FrontDesk/useHotels';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import type { ConfirmType } from '@/Plugins/confirm';
    import type { Hotel } from '@/Types/FrontDesk/hotel';

    // ─── Inject Confirm ─────────────────────────────────────
    const confirm = inject<ConfirmType>('confirm')!;
    const permission = usePermissionService();
    const { t } = useI18n();

    const {
        // State
        hotels,
        loading,
        pagination,

        // Actions
        fetchAll,
        deleteHotel,
        setFilters,
        resetFilters,
    } = useHotels();

    const canCreate = computed(() => permission.check('create hotels'));
    const canEdit = computed(() => permission.check('edit hotels'));
    const canDelete = computed(() => permission.check('delete hotels'));

    const searchQuery = ref('');
    const perPage = ref(15);
    const localFilters = reactive({
        search: '',
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
