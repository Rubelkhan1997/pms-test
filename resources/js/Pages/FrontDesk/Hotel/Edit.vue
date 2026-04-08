<template>
    <Head :title="t('hotels.edit_hotel')" />
    <div v-if="canEdit" class="max-w-4xl mx-auto">
        <section class="space-y-6">

            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">{{ t('hotels.edit_hotel') }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ t('hotels.update_hint') }}</p>
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
    import { computed, onMounted } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import { FormButton, FormInput, FormRadio, FormTextarea } from '@/Components/Form';
    import { useHotels } from '@/Composables/FrontDesk/useHotels';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import type { Hotel } from '@/Types/FrontDesk/hotel';
    import { mapToHotel } from '@/Utils/Mappers/hotel';
    import { required, validateInertiaForm } from '@/Utils/validation';

    // Props
    const props = defineProps<{
        hotel: Record<string, any>;
    }>();

    // Composable
    const { update: updateHotel, saving } = useHotels();
    const { t } = useI18n();
    const permission = usePermissionService();
    const canEdit = computed(() => permission.check('edit hotels'));

    // Form
    const hotel: Hotel = mapToHotel(props.hotel);

    const form = useForm({
        name:     hotel.name || '',
        code:     hotel.code || '',
        timezone: hotel.timezone || '',
        currency: hotel.currency || '',
        email:    hotel.email || '',
        phone:    hotel.phone || '',
        address:  hotel.address || '',
    });

    // Computed
    const isSaving = computed(() => form.processing || saving.value);
    const submitLabel = computed(() => isSaving.value ? t('actions.updating') : t('actions.update'));

    onMounted(() => {
        if (!canEdit.value) {
            router.visit('/hotels');
            return;
        }
    });

    // Submit
    async function submit(): Promise<void> {
        form.clearErrors();

        if (!validateForm()) {
            scrollToFirstError();
            return;
        }

        try {
            const result = await updateHotel(Number(hotel.id), {
                name:     form.name,
                code:     form.code,
                timezone: form.timezone || undefined,
                currency: form.currency || undefined,
                email:    form.email || undefined,
                phone:    form.phone || undefined,
                address:  form.address || undefined,
            });

            if (Number(result.status) === 1) {
                router.visit('/hotels');
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

    // Validation
    function validateForm(): boolean {
        return validateInertiaForm(form, {
            name: [required],
            code: [required],
        });
    }

    function mapBackendField(field: string): string {
        const map: Record<string, string> = {
            name:     'name',
            code:     'code',
            timezone: 'timezone',
            currency: 'currency',
            email:    'email',
            phone:    'phone',
            address:  'address',
        };
        return map[field] ?? field;
    }

    function scrollToFirstError(): void {
        setTimeout(() => {
            const firstError = document.querySelector('.border-red-500');
            firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
</script>
