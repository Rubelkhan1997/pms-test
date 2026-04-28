<template>
    <Head title="Super Admin Login" />
    <div class="min-h-screen bg-white flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-[#1B1E51] items-center justify-center p-12">
            <div class="max-w-md text-center">
                <div class="w-20 h-20 bg-[#F47F20] rounded-2xl flex items-center justify-center mx-auto mb-8">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-white mb-4">PMS Platform</h1>
                <p class="text-xl text-white/70 mb-6">Property Management System</p>
                <p class="text-white/50 text-sm leading-relaxed">
                    The complete solution for managing hotel properties, reservations, and guest experiences across multiple tenants.
                </p>
                <div class="mt-12 space-y-4">
                    <div class="flex items-center gap-3 text-white/60">
                        <svg class="w-5 h-5 text-[#F47F20]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm">Multi-tenant Architecture</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/60">
                        <svg class="w-5 h-5 text-[#F47F20]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm">Real-time Analytics</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/60">
                        <svg class="w-5 h-5 text-[#F47F20]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm">Subscription Management</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">
                <!-- Mobile Logo (shown on small screens) -->
                <div class="lg:hidden text-center mb-10">
                    <div class="w-16 h-16 bg-[#1B1E51] rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-[#F47F20]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-[#1B1E51]">PMS Platform</h1>
                    <p class="text-gray-500 text-sm mt-1">Super Admin Portal</p>
                </div>

                <div class="bg-white">
                    <h2 class="text-3xl font-bold text-[#1B1E51] mb-2">Welcome Back</h2>
                    <p class="text-gray-500 mb-8">Sign in to access the super admin dashboard</p>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-[#1B1E51] mb-2">
                                Email Address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    :class="{ 'border-red-500 ring-red-500': form.errors.email }"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F47F20] focus:border-[#F47F20] transition text-[#1B1E51] placeholder-gray-400"
                                    placeholder="admin@example.com"
                                />
                            </div>
                            <p v-if="form.errors.email" class="mt-2 text-sm text-red-500">{{ form.errors.email }}</p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-[#1B1E51] mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    autocomplete="current-password"
                                    required
                                    :class="{ 'border-red-500 ring-red-500': form.errors.password }"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F47F20] focus:border-[#F47F20] transition text-[#1B1E51] placeholder-gray-400"
                                    placeholder="••••••••"
                                />
                            </div>
                            <p v-if="form.errors.password" class="mt-2 text-sm text-red-500">{{ form.errors.password }}</p>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3 px-4 bg-[#F47F20] hover:bg-[#E06D10] text-white font-semibold rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            {{ form.processing ? 'Signing in...' : 'Sign In' }}
                        </button>
                    </form>
                </div>

                <p class="mt-8 text-center text-xs text-gray-400">
                    &copy; {{ new Date().getFullYear() }} PMS Platform. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

defineOptions({ layout: null });

const form = useForm({
    email: '',
    password: '',
});

function submit(): void {
    form.post('/login');
}
</script>
