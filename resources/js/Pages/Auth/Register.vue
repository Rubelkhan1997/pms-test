<template>
    <Head title="Register" />
    <div class="min-h-screen bg-gradient-to-b from-cyan-50 to-white flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">

            <!-- Logo / Title -->
            <div class="mb-8 text-center">
                <h1 class="mb-2 text-3xl font-bold text-slate-800">PMS</h1>
                <p class="text-slate-600">Property Management System</p>
            </div>

            <!-- Register Card -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="mb-6 text-2xl font-semibold text-slate-800">Create Account</h2>

                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                            Full Name
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="John Doe"
                            autocomplete="name"
                            :class="{ 'border-red-500': form.errors.name }"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                            Email Address
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="you@example.com"
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
                            Password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            :class="{ 'border-red-500': form.errors.password }"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-2">
                            Confirm Password
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            :class="{ 'border-red-500': form.errors.password_confirmation }"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                        />
                        <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-500">
                            {{ form.errors.password_confirmation }}
                        </p>
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

                <!-- Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-slate-600">
                        Already have an account?
                        <Link
                            href="/login"
                            class="font-medium text-cyan-600 hover:text-cyan-700 hover:underline"
                        >
                            Sign in
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <p class="mt-8 text-center text-xs text-slate-500">
                &copy; {{ new Date().getFullYear() }} PMS. All rights reserved.
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { computed } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import { useAuth } from '@/Composables/Auth/useAuth';
    import { hasToken } from '@/Helpers/auth';
    import { required, email as emailRule, minLength, confirmed, validateInertiaForm } from '@/Utils/validation';
    import type { RegisterDto } from '@/Types/Auth/auth';

    // ─── Layout ──────────────────────────────────────────────
    defineOptions({ layout: null });

    // ─── Guard: already authenticated (check token) ──────────
    if (hasToken()) {
        router.visit('/dashboard');
    }

    // ─── Composable ──────────────────────────────────────────
    const { register, loadingAuth } = useAuth();

    // ─── Form ────────────────────────────────────────────────
    const form = useForm<RegisterDto>({
        name:                  '',
        email:                 '',
        password:              '',
        password_confirmation: '',
        role:                  'staff',
    });

    const isLoading   = computed(() => form.processing || loadingAuth.value);
    const submitLabel = computed(() => isLoading.value ? 'Creating account...' : 'Create Account');

    // ─── Submit ──────────────────────────────────────────────
    async function submit(): Promise<void> {
        form.clearErrors();

        if (!validateForm()) return;

        try {
            const result = await register({
                name:                  form.name,
                email:                 form.email,
                password:              form.password,
                password_confirmation: form.password_confirmation,
                role:                  form.role,
            });

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
            }
        }
    }

    // ─── Validation ──────────────────────────────────────────
    function validateForm(): boolean {
        return validateInertiaForm(form, {
            name:                  [required],
            email:                 [required, emailRule],
            password:              [required, minLength(8)],
            password_confirmation: [required, confirmed(() => form.password)],
        });
    }
</script>