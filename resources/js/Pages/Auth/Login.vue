<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-b from-cyan-50 to-white px-4 py-12">
        <div class="w-full max-w-md">
            <!-- Logo / Title -->
            <div class="mb-8 text-center">
                <h1 class="mb-2 text-3xl font-bold text-gray-800">PMS</h1>
                <p class="text-gray-600">Property Management System</p>
            </div>

            <!-- Login Card -->
            <div class="rounded-2xl bg-white p-8 shadow-xl">
                <h2 class="mb-6 text-2xl font-semibold text-gray-800">Sign In</h2>

                <form @submit.prevent="handleSubmit">
                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700">
                            Email Address
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 placeholder-gray-400 transition-colors focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                            placeholder="you@example.com"
                            autocomplete="email"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 placeholder-gray-400 transition-colors focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            :class="{ 'border-red-500': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-6 flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-cyan-600 focus:ring-cyan-500"
                        />
                        <label for="remember" class="ml-2 text-sm text-gray-600">
                            Remember me
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-cyan-600 px-4 py-3 font-semibold text-white transition-colors hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-gray-400"
                        :disabled="form.processing || loading"
                    >
                        <span v-if="form.processing || loading">Signing in...</span>
                        <span v-else>Sign In</span>
                    </button>
                </form>

                <!-- Register Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Don't have an account?
                        <Link
                            :href="route('register')"
                            class="font-medium text-cyan-600 hover:text-cyan-700 hover:underline"
                        >
                            Sign up
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <p class="mt-8 text-center text-xs text-gray-500">
                &copy; {{ new Date().getFullYear() }} PMS. All rights reserved.
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { useAuth } from '@/Composables/Auth/useAuth';
import { inject } from 'vue';
import type { toast as ToastType } from '@/Plugins/toast';

// ─────────────────────────────────────────────────────────
// Inject Toast
// ─────────────────────────────────────────────────────────
const toast = inject('toast') as typeof ToastType;

// ─────────────────────────────────────────────────────────
// Inertia Form
// ─────────────────────────────────────────────────────────
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// ─────────────────────────────────────────────────────────
// Composable (for loading state)
// ─────────────────────────────────────────────────────────
const { loading } = useAuth();

// ─────────────────────────────────────────────────────────
// Check for flash messages from Inertia
// ─────────────────────────────────────────────────────────
const page = usePage();

watch(
    () => page.props.errors,
    (errors) => {
        if (errors && Object.keys(errors).length > 0) {
            const firstError = Object.values(errors)[0];
            if (firstError) {
                toast.error(firstError as string);
            }
        }
    },
    { immediate: true }
);

// Check for flash success message
watch(
    () => page.props.flash?.success,
    (success) => {
        if (success) {
            toast.success(success as string);
        }
    },
    { immediate: true }
);

// ─────────────────────────────────────────────────────────
// Redirect if already authenticated
// ─────────────────────────────────────────────────────────
onMounted(() => {
    const props = page.props as any;
    if (props.auth?.user) {
        window.location.href = '/dashboard';
    }
});

// ─────────────────────────────────────────────────────────
// Handle Form Submit
// ─────────────────────────────────────────────────────────
function handleSubmit() {
    form.post('/login', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Welcome back!');
        },
    });
}

// ─────────────────────────────────────────────────────────
// Route helper
// ─────────────────────────────────────────────────────────
function route(name: string, ...params: any[]): string {
    const routes = (window as any).Ziggy?.routes || {};
    const routeConfig = routes[name];
    if (!routeConfig) return `/${name}`;
    return routeConfig.uri;
}
</script>
