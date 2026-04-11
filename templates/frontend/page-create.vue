<!-- FILE: resources/js/Pages/[MODULE_NAME]/[FEATURE_NAME]/Create.vue -->

<template>
    <Head :title="t('[feature_name].new_[feature_name]')" />

    <div v-if="canCreate" class="max-w-4xl mx-auto">
        <section class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">{{ t('[feature_name].new_[feature_name]') }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ t('[feature_name].create_hint') }}</p>
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
                    <!-- [FORM_FIELDS - customize for your fields] -->
                    <!-- Example:
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormInput
                            id="name"
                            v-model="form.name"
                            :label="t('[feature_name].name')"
                            :placeholder="t('[feature_name].name_placeholder')"
                            :required="true"
                            :error="form.errors.name"
                            wrapper-class="mb-0"
                        />

                        <FormInput
                            id="code"
                            v-model="form.code"
                            :label="t('[feature_name].code')"
                            :placeholder="t('[feature_name].code_placeholder')"
                            :required="true"
                            :error="form.errors.code"
                            wrapper-class="mb-0"
                        />
                    </div>
                    -->

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
    import { required, validateInertiaForm } from '@/Utils/validation';

    // ─── i18n ────────────────────────────────────────────────
    const { t } = useI18n();

    // ─── Permissions ─────────────────────────────────────────
    const permission = usePermissionService();
    const canCreate = computed(() => permission.check('create [feature_name_plural]'));

    // ─── Composables ─────────────────────────────────────────
    const { create: create[MODEL_NAME], saving } = use[MODEL_NAME]s();

    // ─── Lifecycle ───────────────────────────────────────────
    onMounted(() => {
        if (!canCreate.value) {
            router.visit('/[web_route]');
            return;
        }
    });

    // ─── Form ────────────────────────────────────────────────
    const form = useForm({
        // [FORM_FIELDS - with proper TS types]
        // Example:
        // name: '',
        // code: '',
        // email: '',
        // phone: '',
        // address: '',
    });

    // ─── Computed Properties ─────────────────────────────────
    const isSaving = computed(() => form.processing || saving.value);
    const submitLabel = computed(() => isSaving.value ? t('actions.creating') : t('[feature_name].new_[feature_name]'));

    // ─── Submit ──────────────────────────────────────────────
    async function submit(): Promise<void> {
        form.clearErrors();

        if (!validateForm()) {
            scrollToFirstError();
            return;
        }

        try {
            const result = await create[MODEL_NAME]({
                // [FORM_DATA - send undefined for optional empty fields]
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

    // Backend uses snake_case keys; this maps them to frontend form field keys.
    function mapBackendField(field: string): string {
        const map: Record<string, string> = {
            // [FIELD_MAPPING - snake_case to camelCase]
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
