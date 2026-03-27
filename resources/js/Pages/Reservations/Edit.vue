<template>
    <Head title="Edit Reservation" />
    <AppLayout>
        <section class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Edit Reservation</h1>
                <p class="text-sm text-slate-500 mt-1">Update reservation details</p>
            </div>
            <Link
                href="/reservations"
                class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition"
            >
                ← Back to Reservations
            </Link>
        </div>

        <!-- Reservation Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Guest & Room Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Guest Selection -->
                    <div>
                        <label for="guest_profile_id" class="block text-sm font-medium text-slate-700 mb-2">
                            Guest <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="guest_profile_id"
                            v-model="form.guest_profile_id"
                            :class="{ 'border-red-500': form.errors.guest_profile_id }"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Select a guest</option>
                            <option v-for="guest in guests" :key="guest.id" :value="guest.id">
                                {{ guest.first_name }} {{ guest.last_name }} ({{ guest.email }})
                            </option>
                        </select>
                        <p v-if="form.errors.guest_profile_id" class="mt-1 text-sm text-red-500">{{ form.errors.guest_profile_id }}</p>
                    </div>

                    <!-- Room Selection -->
                    <div>
                        <label for="room_id" class="block text-sm font-medium text-slate-700 mb-2">
                            Room <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="room_id"
                            v-model="form.room_id"
                            :class="{ 'border-red-500': form.errors.room_id }"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Select a room</option>
                            <option v-for="room in rooms" :key="room.id" :value="room.id">
                                Room {{ room.number }} - {{ room.type }} ({{ room.base_rate }} BDT)
                            </option>
                        </select>
                        <p v-if="form.errors.room_id" class="mt-1 text-sm text-red-500">{{ form.errors.room_id }}</p>
                    </div>
                </div>

                <!-- Date Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Check-in Date -->
                    <div>
                        <label for="check_in_date" class="block text-sm font-medium text-slate-700 mb-2">
                            Check-in Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="check_in_date"
                            type="date"
                            v-model="form.check_in_date"
                            :class="{ 'border-red-500': form.errors.check_in_date }"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p v-if="form.errors.check_in_date" class="mt-1 text-sm text-red-500">{{ form.errors.check_in_date }}</p>
                    </div>

                    <!-- Check-out Date -->
                    <div>
                        <label for="check_out_date" class="block text-sm font-medium text-slate-700 mb-2">
                            Check-out Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="check_out_date"
                            type="date"
                            v-model="form.check_out_date"
                            :class="{ 'border-red-500': form.errors.check_out_date }"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p v-if="form.errors.check_out_date" class="mt-1 text-sm text-red-500">{{ form.errors.check_out_date }}</p>
                    </div>
                </div>

                <!-- Amount & Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Total Amount -->
                    <div>
                        <label for="total_amount" class="block text-sm font-medium text-slate-700 mb-2">
                            Total Amount (BDT) <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="total_amount"
                            type="number"
                            step="0.01"
                            v-model="form.total_amount"
                            :class="{ 'border-red-500': form.errors.total_amount }"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="0.00"
                        />
                        <p v-if="form.errors.total_amount" class="mt-1 text-sm text-red-500">{{ form.errors.total_amount }}</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="status"
                            v-model="form.status"
                            :class="{ 'border-red-500': form.errors.status }"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Checked Out</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <p v-if="form.errors.status" class="mt-1 text-sm text-red-500">{{ form.errors.status }}</p>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-slate-700 mb-2">
                        Notes
                    </label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        :class="{ 'border-red-500': form.errors.notes }"
                        rows="3"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Any special requests or notes..."
                    ></textarea>
                    <p v-if="form.errors.notes" class="mt-1 text-sm text-red-500">{{ form.errors.notes }}</p>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4 pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Updating...' : 'Update Reservation' }}
                    </button>
                    <Link
                        href="/reservations"
                        class="px-6 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition"
                    >
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </section>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AppLayout } from '@/Layouts';

interface Guest {
    id: number;
    first_name: string;
    last_name: string;
    email?: string;
    phone?: string;
}

interface Room {
    id: number;
    number: string;
    type: string;
    base_rate: number;
    status: string;
}

interface Reservation {
    id: number;
    guest_profile_id: number;
    room_id: number;
    check_in_date: string;
    check_out_date: string;
    total_amount: number;
    status: string;
    notes?: string;
    adults?: number;
    children?: number;
}

interface Props {
    reservation: Reservation;
    guests: Guest[];
    rooms: Room[];
}

const props = defineProps<Props>();

const form = useForm({
    guest_profile_id: props.reservation.guest_profile_id,
    room_id: props.reservation.room_id,
    check_in_date: props.reservation.check_in_date,
    check_out_date: props.reservation.check_out_date,
    total_amount: props.reservation.total_amount.toString(),
    adults: props.reservation.adults || 1,
    children: props.reservation.children || 0,
    status: props.reservation.status,
    notes: props.reservation.notes || '',
});

function submit() {
    form.put(`/reservations/${props.reservation.id}`, {
        onSuccess: () => {
            // Success message will be shown by flash data
        },
    });
}
</script>
