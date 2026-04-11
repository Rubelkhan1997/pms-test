<!-- FILE: resources/js/Pages/[MODULE_NAME]/[FEATURE_NAME]/Show.vue -->

<template>
    <Head :title="[model_name].[DISPLAY_FIELD]" />

    <div v-if="canView" class="max-w-4xl mx-auto">
        <section class="space-y-6">

            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">{{ [model_name].[DISPLAY_FIELD] }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ t('[feature_name].[feature_name]_details') }}</p>
                </div>
                <div class="flex gap-2">
                    <FormButton
                        v-if="canEdit"
                        type="button"
                        color="primary"
                        :name="t('actions.edit')"
                        @click="router.visit(`/[web_route]/${[model_name].id}/edit`)"
                    />
                    <FormButton
                        type="button"
                        color="secondary"
                        :name="t('actions.back')"
                        @click="router.visit('/[web_route]')"
                    />
                </div>
            </div>

            <!-- Details Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- [DETAIL_FIELDS - customize for your fields] -->
                    <!-- Example:
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('[feature_name].name') }}</label>
                        <p class="text-slate-900">{{ [model_name].name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('[feature_name].code') }}</label>
                        <p class="text-slate-900">{{ [model_name].code }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('[feature_name].email') }}</label>
                        <p class="text-slate-900">{{ [model_name].email || t('na') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('[feature_name].phone') }}</label>
                        <p class="text-slate-900">{{ [model_name].phone || t('na') }}</p>
                    </div>
                    -->
                </div>

                <!-- Status badge (if enum exists) -->
                <!--
                <div class="mt-6" v-if="[model_name].status">
                    <label class="block text-sm font-medium text-slate-500 mb-1">{{ t('[feature_name].status') }}</label>
                    <span
                        :class="{
                            'bg-green-100 text-green-800': [model_name].status === 'active',
                            'bg-red-100 text-red-800': [model_name].status === 'inactive',
                            'bg-yellow-100 text-yellow-800': [model_name].status === 'pending',
                        }"
                        class="px-2 py-1 text-xs font-medium rounded"
                    >
                        {{ [model_name].statusLabel || [model_name].status }}
                    </span>
                </div>
                -->

                <div class="mt-6 pt-6 border-t border-slate-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-slate-500">{{ t('[feature_name].created_at') }}:</span>
                            <span class="ml-2 text-slate-900">{{ [model_name].createdAt ? formatDate([model_name].createdAt) : t('na') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">{{ t('[feature_name].updated_at') }}:</span>
                            <span class="ml-2 text-slate-900">{{ [model_name].updatedAt ? formatDate([model_name].updatedAt) : t('na') }}</span>
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
                @click="router.visit('/[web_route]')"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
    import { computed, onMounted } from 'vue';
    import { router } from '@inertiajs/vue3';
    import { formatDate } from '@/Utils/date';
    import { mapTo[MODEL_NAME] } from '@/Utils/Mappers/[model_name]';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import type { [MODEL_NAME] } from '@/Types/[MODULE_NAME]/[model_name]';

    // ─── i18n ────────────────────────────────────────────────
    const { t } = useI18n();

    // ─── Permissions ─────────────────────────────────────────
    const permission = usePermissionService();
    const canView = computed(() => permission.check('view [feature_name_plural]'));
    const canEdit = computed(() => permission.check('edit [feature_name_plural]'));

    // ─── Lifecycle ───────────────────────────────────────────
    onMounted(() => {
        if (!canView.value) {
            router.visit('/[web_route]');
            return;
        }
    });

    // ─── Props ───────────────────────────────────────────────
    const props = defineProps<{
        [model_name]: Record<string, any>;   // Raw data from API
    }>();

    // ─── Computed Properties ─────────────────────────────────
    const [model_name]: [MODEL_NAME] = mapTo[MODEL_NAME](props.[model_name]);

</script>
