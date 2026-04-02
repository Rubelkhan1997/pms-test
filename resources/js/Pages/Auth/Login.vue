<template>
    <Head :title="t('auth.login')" />
    <div class="min-h-screen bg-gradient-to-b from-cyan-50 to-white flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">

            <!-- Logo / Title -->
            <div class="mb-8 text-center">
                <h1 class="mb-2 text-3xl font-bold text-slate-800">PMS</h1>
                <p class="text-slate-600">{{ t('auth.pms_full_name') }}</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="mb-6 text-2xl font-semibold text-slate-800">{{ t('auth.sign_in') }}</h2>

                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ t('auth.email') }}
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            :placeholder="t('auth.email_placeholder')"
                            autocomplete="email"
                            :class="{ 'border-red-500': form.errors.email }"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ t('auth.password') }}
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            :class="{ 'border-red-500': form.errors.password }"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                        />
                        <label for="remember" class="ml-2 text-sm text-slate-600">
                            {{ t('auth.remember_me') }}
                        </label>
                    </div>

                    <!-- Submit -->
                    <div>
                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="w-full px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ submitLabel }}
                        </button>
                    </div>

                </form>

                <!-- Register Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-slate-600">
                        {{ t('auth.dont_have_account') }}
                        <Link
                            href="/register"
                            class="font-medium text-cyan-600 hover:text-cyan-700 hover:underline"
                        >
                            {{ t('auth.sign_up') }}
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <p class="mt-8 text-center text-xs text-slate-500">
                &copy; {{ new Date().getFullYear() }} PMS. {{ t('auth.all_rights_reserved') }}
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { computed } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import { useAuth } from '@/Composables/Auth/useAuth';
    import { useI18n } from '@/Composables/useI18n';
    import { hasToken } from '@/Utils/authToken';
    import { required, email as emailRule, validateInertiaForm } from '@/Utils/validation';
    import type { LoginDto } from '@/Types/Auth/auth';

    // ─── Layout ──────────────────────────────────────────────
    defineOptions({ layout: null });

    // ─── i18n ────────────────────────────────────────────────
    const { t } = useI18n();

    // ─── Guard: already authenticated (check token) ──────────
    if (hasToken()) {
        router.visit('/dashboard');
    }

    // ─── Composable ──────────────────────────────────────────
    const { login, loadingAuth } = useAuth();

    // ─── Form ────────────────────────────────────────────────
    const form = useForm<LoginDto & { remember: boolean }>({
        email:    '',
        password: '',
        remember: false,
    });

    const isLoading   = computed(() => form.processing || loadingAuth.value);
    const submitLabel = computed(() => isLoading.value ? t('auth.signing_in') : t('auth.sign_in'));

    // ─── Submit ──────────────────────────────────────────────
    async function submit(): Promise<void> {
        form.clearErrors();

        if (!validateForm()) {
            scrollToFirstError();
            return;
        }

        try {
            const result = await login({ email: form.email, password: form.password, remember: form.remember });

            // Redirect to dashboard on successful registration
            if (result?.status == 1) {
                router.visit('/dashboard');
            }
        } catch (err: unknown) {
            const apiErr = err as Record<string, any>;

            if (apiErr?.response?.status === 422) {
                const backendErrors: Record<string, string[]> = apiErr.response.data?.errors ?? {};
                Object.entries(backendErrors).forEach(([key, messages]) => {
                    form.setError(key as any, messages[0]);
                });
                scrollToFirstError();
            } else if (apiErr?.response?.status === 401) {
                form.setError('email', t('auth.invalid_credentials'));
            }
        }
    }

    // ─── Validation ──────────────────────────────────────────
    function validateForm(): boolean {
        return validateInertiaForm(form, {
            email:    [required, emailRule],
            password: [required],
        });
    }

    function scrollToFirstError(): void {
        setTimeout(() => {
            const firstError = document.querySelector('.border-red-500');
            firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
</script>
