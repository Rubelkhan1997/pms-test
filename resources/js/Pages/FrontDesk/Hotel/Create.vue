<template>
    <Head :title="t('hotels.new_hotel')" />
    <div v-if="canCreate" class="max-w-4xl mx-auto">
        <section class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">{{ t('hotels.new_hotel') }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ t('hotels.create_hint') }}</p>
                </div>
                <Link
                    href="/hotels"
                    class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition"
                >
                    {{ t('hotels.back_to_hotels') }}
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormInput
                            id="name"
                            v-model="form.name"
                            :label="t('hotels.name')"
                            :placeholder="t('hotels.name_placeholder')"
                            :required="true"
                            :error="form.errors.name"
                            wrapper-class="mb-0"
                        />

                        <FormInput
                            id="code"
                            v-model="form.code"
                            :label="t('hotels.code')"
                            :placeholder="t('hotels.code_placeholder')"
                            :required="true"
                            :error="form.errors.code"
                            wrapper-class="mb-0"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormInput
                            id="timezone"
                            v-model="form.timezone"
                            :label="t('hotels.timezone')"
                            :placeholder="t('hotels.timezone_placeholder')"
                            :error="form.errors.timezone"
                            wrapper-class="mb-0"
                        />

                        <div class="mb-0">
                            <label class="text-gray-700 font-medium mb-2 text-[16px] block">{{ t('hotels.currency') }}</label>
                            <div class="flex items-center gap-4 mb-2">
                                <FormRadio v-model="form.currency" name="currency" value="BDT" label="BDT" />
                                <FormRadio v-model="form.currency" name="currency" value="USD" label="USD" />
                            </div>
                            <FormInput
                                id="currency"
                                v-model="form.currency"
                                :placeholder="t('hotels.currency_placeholder')"
                                :error="form.errors.currency"
                                wrapper-class="mb-0"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            :label="t('hotels.email')"
                            :placeholder="t('hotels.email_placeholder')"
                            :error="form.errors.email"
                            wrapper-class="mb-0"
                        />

                        <FormInput
                            id="phone"
                            v-model="form.phone"
                            :label="t('hotels.phone')"
                            :placeholder="t('hotels.phone_placeholder')"
                            :error="form.errors.phone"
                            wrapper-class="mb-0"
                        />
                    </div>

                    <FormTextarea
                        id="address"
                        v-model="form.address"
                        :label="t('hotels.address')"
                        :placeholder="t('hotels.address_placeholder')"
                        :rows="3"
                        :error="form.errors.address"
                        wrapper-class="mb-0"
                    />

                    <div class="flex gap-4 pt-4">
                        <FormButton
                            type="submit"
                            color="primary"
                            :name="submitLabel"
                            :disabled="isSaving"
                            button-class="px-6"
                        />
                        <Link
                            href="/hotels"
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
                href="/hotels"
                class="inline-flex mt-4 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition"
            >
                {{ t('actions.back') }}
            </Link>
        </div>
    </div>
</template>

<script setup lang="ts"> 
    // Vue 3 reactivity: computed for derived state, onMounted for lifecycle hook
    import { computed, onMounted } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import { useHotels } from '@/Composables/FrontDesk/useHotels';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import { required, validateInertiaForm } from '@/Utils/validation';

    // ─── i18n ────────────────────────────────────────────────
    // useI18n: provides translation function 't'
    const { t } = useI18n();
    
    // ─── Permissions ─────────────────────────────────────────
    // usePermissionService: provides methods to check user permissions
    const permission = usePermissionService();
    const canCreate = computed(() => permission.check('create hotels'));

    // ─── Composables ─────────────────────────────────────────
    // useHotels: provides CRUD operations for hotels
    // create: function to send POST request to create a new hotel
    // saving: reactive boolean indicating if the API call is in progress
    const { create: createHotel, saving } = useHotels();
    
    // ─── Lifecycle ───────────────────────────────────────────
    // onMounted: runs when component is first loaded into the DOM
    // Redirect to /hotels if user doesn't have create permission
    onMounted(() => {
        if (!canCreate.value) {
            router.visit('/hotels');
            return;
        }
    });

    // ─── Form ────────────────────────────────────────────────
    // useForm: creates a reactive form object with hotel fields
    // form.errors: tracks validation errors per field
    // form.processing: true while form is being submitted
    const form = useForm({
        name: '',
        code: '',
        timezone: '',
        currency: '',
        email: '',
        phone: '',
        address: '',
    });

    // ─── Computed Properties ─────────────────────────────────
    // isSaving: true if form is processing OR the create API call is in progress
    const isSaving = computed(() => form.processing || saving.value);
    
    // submitLabel: dynamic button text
    const submitLabel = computed(() => isSaving.value ? t('actions.creating') : t('hotels.new_hotel'));

    // ─── Submit ──────────────────────────────────────────────
    async function submit(): Promise<void> {
        // Clear all previous validation errors
        form.clearErrors();

        // If any rule fails, validateForm returns false
        if (!validateForm()) {
            scrollToFirstError();
            return;
        }

        try {
            // Send hotel data to backend API
            const result = await createHotel({
                name: form.name,
                code: form.code,
                timezone: form.timezone || undefined,     // Send undefined if empty
                currency: form.currency || undefined,
                email: form.email || undefined,
                phone: form.phone || undefined,
                address: form.address || undefined,
            });

            // Check if API response indicates success (status === 1)
            if (Number(result.status) === 1) {
                form.reset();
                router.visit('/hotels');                // Navigate to hotels list
            }
        } catch (err: unknown) {
            // Handle API errors (e.g., 422 validation errors from backend)
            const apiErr = err as Record<string, any>;

            if (apiErr?.response?.data?.errors) {
                const backendErrors: Record<string, string[]> = apiErr.response.data.errors;
                Object.entries(backendErrors).forEach(([key, messages]) => {
                    // mapBackendField converts backend keys to frontend form field keys
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
            name: [required],
            code: [required],
        });
    }

    // Backend uses snake_case keys; this maps them to local form keys.
    // For hotels, the keys are the same (no snake_case to camelCase conversion needed)
    function mapBackendField(field: string): string {
        const map: Record<string, string> = {
            name: 'name',
            code: 'code',
            timezone: 'timezone',
            currency: 'currency',
            email: 'email',
            phone: 'phone',
            address: 'address',
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
