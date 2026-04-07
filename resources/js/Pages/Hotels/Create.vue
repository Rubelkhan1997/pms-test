<template>
    <Head :title="t('hotels.new_hotel')" />
    <div v-if="canCreate" class="max-w-4xl mx-auto">
        <section class="space-y-6">

            <!-- Header -->
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

            <!-- Hotel Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Name & Code -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ t('hotels.name') }} <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                :class="{ 'border-red-500': form.errors.name }"
                                type="text"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :placeholder="t('hotels.name_placeholder')"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Code -->
                        <div>
                            <label for="code" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ t('hotels.code') }} <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="code"
                                v-model="form.code"
                                :class="{ 'border-red-500': form.errors.code }"
                                type="text"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :placeholder="t('hotels.code_placeholder')"
                            />
                            <p v-if="form.errors.code" class="mt-1 text-sm text-red-500">{{ form.errors.code }}</p>
                        </div>
                    </div>

                    <!-- Timezone & Currency -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Timezone -->
                        <div>
                            <label for="timezone" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ t('hotels.timezone') }}
                            </label>
                            <input
                                id="timezone"
                                v-model="form.timezone"
                                :class="{ 'border-red-500': form.errors.timezone }"
                                type="text"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :placeholder="t('hotels.timezone_placeholder')"
                            />
                            <p v-if="form.errors.timezone" class="mt-1 text-sm text-red-500">{{ form.errors.timezone }}</p>
                        </div>

                        <!-- Currency -->
                        <div>
                            <label for="currency" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ t('hotels.currency') }}
                            </label>
                            <input
                                id="currency"
                                v-model="form.currency"
                                :class="{ 'border-red-500': form.errors.currency }"
                                type="text"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :placeholder="t('hotels.currency_placeholder')"
                            />
                            <p v-if="form.errors.currency" class="mt-1 text-sm text-red-500">{{ form.errors.currency }}</p>
                        </div>
                    </div>

                    <!-- Email & Phone -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ t('hotels.email') }}
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                :class="{ 'border-red-500': form.errors.email }"
                                type="email"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :placeholder="t('hotels.email_placeholder')"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ t('hotels.phone') }}
                            </label>
                            <input
                                id="phone"
                                v-model="form.phone"
                                :class="{ 'border-red-500': form.errors.phone }"
                                type="text"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :placeholder="t('hotels.phone_placeholder')"
                            />
                            <p v-if="form.errors.phone" class="mt-1 text-sm text-red-500">{{ form.errors.phone }}</p>
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ t('hotels.address') }}
                        </label>
                        <textarea
                            id="address"
                            v-model="form.address"
                            :class="{ 'border-red-500': form.errors.address }"
                            rows="3"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :placeholder="t('hotels.address_placeholder')"
                        ></textarea>
                        <p v-if="form.errors.address" class="mt-1 text-sm text-red-500">{{ form.errors.address }}</p>
                    </div>

                    <!-- Submit -->
                    <div class="flex gap-4 pt-4">
                        <button
                            type="submit"
                            :disabled="isSaving"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ submitLabel }}
                        </button>
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
</template>

<script setup lang="ts">
    import { computed, onMounted } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import { useHotels } from '@/Composables/FrontDesk/useHotels';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import { required, validateInertiaForm } from '@/Utils/validation';

    // Composable
    const { create: createRoom, saving } = useHotels();
    const { t } = useI18n();
    const permission = usePermissionService();
    const canCreate = computed(() => permission.check('create hotels'));

    // Form
    const form = useForm({
        name:     '',
        code:     '',
        timezone: '',
        currency: '',
        email:    '',
        phone:    '',
        address:  '',
    });

    // Computed
    const isSaving = computed(() => form.processing || saving.value);
    const submitLabel = computed(() => isSaving.value ? t('actions.creating') : t('hotels.new_hotel'));

    onMounted(() => {
        if (!canCreate.value) {
            router.visit('/hotels');
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
            const result = await createRoom({
                name:     form.name,
                code:     form.code,
                timezone: form.timezone || undefined,
                currency: form.currency || undefined,
                email:    form.email || undefined,
                phone:    form.phone || undefined,
                address:  form.address || undefined,
            });

            if (Number(result.status) === 1) {
                form.reset();
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
