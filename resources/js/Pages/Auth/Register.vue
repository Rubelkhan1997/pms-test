<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-b from-cyan-50 to-white px-4 py-12">
        <div class="w-full max-w-md">
            <!-- Logo / Title -->
            <div class="mb-8 text-center">
                <h1 class="mb-2 text-3xl font-bold text-gray-800">PMS</h1>
                <p class="text-gray-600">Property Management System</p>
            </div>

            <!-- Register Card -->
            <div class="rounded-2xl bg-white p-8 shadow-xl">
                <h2 class="mb-6 text-2xl font-semibold text-gray-800">Create Account</h2>

                <form @submit.prevent="handleSubmit">
                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700">
                            Full Name
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 placeholder-gray-400 transition-colors focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                            placeholder="John Doe"
                            autocomplete="name"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>

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
                            autocomplete="new-password"
                            :class="{ 'border-red-500': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700">
                            Confirm Password
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 placeholder-gray-400 transition-colors focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            :class="{ 'border-red-500': form.errors.password_confirmation }"
                        />
                        <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-cyan-600 px-4 py-3 font-semibold text-white transition-colors hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-gray-400"
                        :disabled="form.processing || loading"
                    >
                        <span v-if="form.processing || loading">Creating account...</span>
                        <span v-else>Create Account</span>
                    </button>
                </form>

                <!-- Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <Link
                            :href="route('login')"
                            class="font-medium text-cyan-600 hover:text-cyan-700 hover:underline"
                        >
                            Sign in
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
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'staff',
});

// ─────────────────────────────────────────────────────────
// Loading state (computed from form.processing)
// ─────────────────────────────────────────────────────────
const loading = computed(() => form.processing);

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
    form.post('/register', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Account created successfully!');
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
