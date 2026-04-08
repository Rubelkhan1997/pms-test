<template>
    <Head :title="hotel.name" />
    <div v-if="canView" class="max-w-4xl mx-auto">
        <section class="space-y-6">

            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">{{ hotel.name }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ t('hotels.hotel_details') }}</p>
                </div>
                <div class="flex gap-2">
                    <FormButton
                        v-if="canEdit"
                        type="button"
                        color="primary"
                        :name="t('actions.edit')"
                        @click="router.visit(`/hotels/${hotel.id}/edit`)"
                    />
                    <FormButton
                        type="button"
                        color="secondary"
                        :name="t('actions.back')"
                        @click="router.visit('/hotels')"
                    />
                </div>
            </div>

            <!-- Details Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('hotels.name') }}</label>
                        <p class="text-slate-900">{{ hotel.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('hotels.code') }}</label>
                        <p class="text-slate-900">{{ hotel.code }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('hotels.timezone') }}</label>
                        <p class="text-slate-900">{{ hotel.timezone || t('na') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('hotels.currency') }}</label>
                        <p class="text-slate-900">{{ hotel.currency || t('na') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('hotels.email') }}</label>
                        <p class="text-slate-900">{{ hotel.email || t('na') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('hotels.phone') }}</label>
                        <p class="text-slate-900">{{ hotel.phone || t('na') }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('hotels.address') }}</label>
                    <p class="text-slate-900">{{ hotel.address || t('na') }}</p>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-slate-500">{{ t('hotels.created_at') }}:</span>
                            <span class="ml-2 text-slate-900">{{ hotel.createdAt ? formatDate(hotel.createdAt) : t('na') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">{{ t('hotels.updated_at') }}:</span>
                            <span class="ml-2 text-slate-900">{{ hotel.updatedAt ? formatDate(hotel.updatedAt) : t('na') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div v-else class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <h1 class="text-xl font-semibold text-slate-800">{{ t('messages.access_denied') }}</h1>
            <p class="text-sm text-slate-500 mt-2">{{ t('messages.no_permission') }}</p>
            <FormButton
                type="button"
                color="secondary"
                :name="t('actions.back')"
                button-class="mt-4"
                @click="router.visit('/hotels')"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
    import { computed, onMounted } from 'vue';
    import { router } from '@inertiajs/vue3';
    import { formatDate } from '@/Utils/date';
    import type { Hotel } from '@/Types/FrontDesk/hotel';
    import { mapToHotel } from '@/Utils/Mappers/hotel';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';

    // Permissions
    const permission = usePermissionService();
    const canView = computed(() => permission.check('view hotels'));
    const canEdit = computed(() => permission.check('edit hotels'));
    const { t } = useI18n();

    // Props
    const props = defineProps<{
        hotel: Record<string, any>;
    }>();

    // Computed
    const hotel: Hotel = mapToHotel(props.hotel);

    onMounted(() => {
        if (!canView.value) {
            router.visit('/hotels');
            return;
        }
    });
</script>
