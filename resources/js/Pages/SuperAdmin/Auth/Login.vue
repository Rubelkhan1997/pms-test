<template>
    <Head title="Super Admin Login" />
    <div class="min-h-screen bg-gradient-to-b from-cyan-50 to-white flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <h1 class="mb-2 text-3xl font-bold text-slate-800">PMS</h1>
                <p class="text-slate-600">Super Admin Portal</p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="mb-6 text-2xl font-semibold text-slate-800">Sign In</h2>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            :class="{ 'border-red-500': form.errors.email }"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            :class="{ 'border-red-500': form.errors.password }"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Signing in...' : 'Sign In' }}
                    </button>
                </form>
            </div>

            <p class="mt-8 text-center text-xs text-slate-500">
                &copy; {{ new Date().getFullYear() }} PMS. All rights reserved.
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

defineOptions({ layout: null });

const form = useForm({
    email: '',
    password: '',
});

function submit(): void {
    form.post('/login');
}
</script>
