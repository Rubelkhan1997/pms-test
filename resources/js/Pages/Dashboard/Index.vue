<template>
    <Head title="Dashboard" />
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">Dashboard</h1>
                    <p class="text-sm text-slate-500 mt-1">Welcome to PMS</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Total Reservations -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-sm text-slate-500">Total Reservations</div>
                    <div class="text-3xl font-bold text-slate-800 mt-2">0</div>
                </div>

                <!-- Available Rooms -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-sm text-slate-500">Available Rooms</div>
                    <div class="text-3xl font-bold text-green-600 mt-2">0</div>
                </div>

                <!-- Check-ins Today -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-sm text-slate-500">Check-ins Today</div>
                    <div class="text-3xl font-bold text-blue-600 mt-2">0</div>
                </div>

                <!-- Check-outs Today -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-sm text-slate-500">Check-outs Today</div>
                    <div class="text-3xl font-bold text-orange-600 mt-2">0</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Quick Actions</h2>
                <div class="flex gap-4">
                    <Link
                        href="/reservations"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        View Reservations
                    </Link>
                    <Link
                        href="/reservations/create"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                    >
                        New Reservation
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>
