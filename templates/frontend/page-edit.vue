<!-- FILE: resources/js/Pages/[MODULE_NAME]/[FEATURE_NAME]/Edit.vue -->

<template>
    <Head :title="t('[feature_name].edit_[feature_name]')" />

    <div v-if="canEdit" class="max-w-4xl mx-auto">
        <section class="space-y-6">

            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">{{ t('[feature_name].edit_[feature_name]') }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ t('[feature_name].update_hint') }}</p>
                </div>
                <Link
                    href="/[web_route]"
                    class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition"
                >
                    {{ t('[feature_name].back_to_[feature_plural]') }}
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- [FORM_FIELDS - same as Create.vue but pre-filled] -->

                    <div class="flex gap-4 pt-4">
                        <FormButton
                            type="submit"
                            color="primary"
                            :name="submitLabel"
                            :disabled="isSaving"
                            button-class="px-6"
                        />
                        <Link
                            href="/[web_route]"
                            class="px-6 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition"
                        >
                            {{ t('actions.cancel') }}
                        </Link>
                    </div>

                </form>
            </div>
        </section>
    </div>
    <div v-else class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <h1 class="text-xl font-semibold text-slate-800">{{ t('messages.access_denied') }}</h1>
            <p class="text-sm text-slate-500 mt-2">{{ t('messages.no_permission') }}</p>
            <Link
                href="/[web_route]"
                class="inline-flex mt-4 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition"
            >
                {{ t('actions.back') }}
            </Link>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { computed, onMounted } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import { use[MODEL_NAME]s } from '@/Composables/[MODULE_NAME]/use[MODEL_NAME]s';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import { mapTo[MODEL_NAME] } from '@/Utils/Mappers/[model_name]';
    import { required, validateInertiaForm } from '@/Utils/validation';
    import type { [MODEL_NAME] } from '@/Types/[MODULE_NAME]/[model_name]';

    // ─── i18n ────────────────────────────────────────────────
    const { t } = useI18n();

    // ─── Permissions ─────────────────────────────────────────
    const permission = usePermissionService();
    const canEdit = computed(() => permission.check('edit [feature_name_plural]'));

    // ─── Composables ─────────────────────────────────────────
    const { update: update[MODEL_NAME], saving } = use[MODEL_NAME]s();

    // ─── Lifecycle ───────────────────────────────────────────
    onMounted(() => {
        if (!canEdit.value) {
            router.visit('/[web_route]');
            return;
        }
    });

    // ─── Props ───────────────────────────────────────────────
    const props = defineProps<{
        [model_name]: Record<string, any>;   // Raw data from API
    }>();

    // ─── Form ────────────────────────────────────────────────
    const [model_name]: [MODEL_NAME] = mapTo[MODEL_NAME](props.[model_name]);

    const form = useForm({
        // [FORM_FIELDS - pre-filled with existing data]
        // Example:
        // name: [model_name].name || '',
        // code: [model_name].code || '',
        // email: [model_name].email || '',
        // phone: [model_name].phone || '',
        // address: [model_name].address || '',
    });

    // ─── Computed Properties ─────────────────────────────────
    const isSaving = computed(() => form.processing || saving.value);
    const submitLabel = computed(() => isSaving.value ? t('actions.updating') : t('actions.update'));

    // ─── Submit ──────────────────────────────────────────────
    async function submit(): Promise<void> {
        form.clearErrors();

        if (!validateForm()) {
            scrollToFirstError();
            return;
        }

        try {
            const result = await update[MODEL_NAME](Number([model_name].id), {
                // [FORM_DATA]
                // Example:
                // name: form.name,
                // code: form.code,
                // email: form.email || undefined,
                // phone: form.phone || undefined,
                // address: form.address || undefined,
            });

            if (Number(result.status) === 1) {
                form.reset();
                router.visit('/[web_route]');
            }
        } catch (err: unknown) {
            const apiErr = err as Record<string, any>;

            if (apiErr?.response?.data?.errors) {
                const backendErrors: Record<string, string[]> = apiErr.response.data.errors;
                Object.entries(backendErrors).forEach(([key, messages]) => {
                    const mappedKey = mapBackendField(key);
                    form.setError(mappedKey as any, messages[0]);
                });
                scrollToFirstError();
            }
        }
    }

    // ─── Validation ──────────────────────────────────────────
    function validateForm(): boolean {
        return validateInertiaForm(form, {
            // [VALIDATION_RULES]
            // Example:
            // name: [required],
            // code: [required],
        });
    }

    // Backend uses snake_case keys; this maps them to local form keys.
    function mapBackendField(field: string): string {
        const map: Record<string, string> = {
            // [FIELD_MAPPING]
            // Example:
            // first_name: 'firstName',
            // last_name: 'lastName',
        };

        return map[field] ?? field;
    }

    // Auto-scrolls the page to the first field with a validation error
    function scrollToFirstError(): void {
        setTimeout(() => {
            const firstError = document.querySelector('.border-red-500');
            firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
</script>
