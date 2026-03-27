<template>
    <Head title="New Reservation" />
    <AppLayout class="min-h-screen bg-slate-100 p-8">
        <div class="max-w-4xl mx-auto">
            <section class="space-y-6">

                <!-- Header -->
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-800">New Reservation</h1>
                        <p class="text-sm text-slate-500 mt-1">Create a new guest reservation</p>
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

                    <!-- Global Error -->
                    <div v-if="form.hasErrors" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-red-800 font-medium">Please fix the errors below</span>
                        </div>
                    </div>

                    <!-- Composable Error -->
                    <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <span class="text-red-800">{{ error }}</span>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Hotel Selection -->
                        <div>
                            <label for="hotel_id" class="block text-sm font-medium text-slate-700 mb-2">
                                Hotel <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="hotel_id"
                                v-model="form.hotel_id"
                                :class="{ 'border-red-500': form.errors.hotel_id }"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Select a hotel</option>
                                <option v-for="hotel in hotels" :key="hotel.id" :value="hotel.id">
                                    {{ hotel.name }} ({{ hotel.code }})
                                </option>
                            </select>
                            <p v-if="form.errors.hotel_id" class="mt-1 text-sm text-red-500">
                                {{ form.errors.hotel_id }}
                            </p>
                        </div>

                        <!-- Guest & Room -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Guest -->
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
                                <p v-if="form.errors.guest_profile_id" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.guest_profile_id }}
                                </p>
                            </div>

                            <!-- Room -->
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
                                    <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                                        Room {{ room.number }} - {{ room.type }} ({{ room.base_rate }} BDT)
                                    </option>
                                </select>
                                <p v-if="form.errors.room_id" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.room_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Check-in -->
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
                                <p v-if="form.errors.check_in_date" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.check_in_date }}
                                </p>
                            </div>

                            <!-- Check-out -->
                            <div>
                                <label for="check_out_date" class="block text-sm font-medium text-slate-700 mb-2">
                                    Check-out Date <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="check_out_date"
                                    type="date"
                                    v-model="form.check_out_date"
                                    @change="validateCheckOutDate"
                                    :class="{ 'border-red-500': form.errors.check_out_date }"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                                <p v-if="form.errors.check_out_date" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.check_out_date }}
                                </p>
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
                                <p v-if="form.errors.total_amount" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.total_amount }}
                                </p>
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
                                <p v-if="form.errors.status" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.status }}
                                </p>
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
                            <p v-if="form.errors.notes" class="mt-1 text-sm text-red-500">
                                {{ form.errors.notes }}
                            </p>
                        </div>

                        <!-- Submit -->
                        <div class="flex gap-4 pt-4">
                            <button
                                type="submit"
                                :disabled="isSaving"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <!-- ✅ Fix: একটাই saving state ব্যবহার -->
                                {{ isSaving ? 'Creating...' : 'Create Reservation' }}
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
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
// ✅ Fix: Link এবং Head import যোগ করা হয়েছে
import { computed } from 'vue';
import { useForm, router, Link, Head } from '@inertiajs/vue3';
import { AppLayout } from '@/Layouts';
import { useReservations } from '@/Composables';
import { required, minValue, checkInDate, checkOutDate, validateInertiaForm } from '@/Utils/validation';

// ─── Types ───────────────────────────────────────────────

interface Hotel {
    id: number;
    name: string;
    code: string;
}

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

interface Props {
    hotels: Hotel[];
    guests: Guest[];
    rooms: Room[];
}

// ─── Props ───────────────────────────────────────────────

const props = defineProps<Props>();

// ─── Composable ──────────────────────────────────────────

const { create: createReservation, saving, error, clearError } = useReservations();

// ─── Available Rooms ─────────────────────────────────────

const availableRooms = props.rooms.filter(room => room.status === 'available');

// ─── Form ────────────────────────────────────────────────

const form = useForm({
    hotel_id: '' as number | string,
    guest_profile_id: '' as number | string,
    room_id: '' as number | string,
    check_in_date: new Date().toISOString().split('T')[0],
    check_out_date: '',
    total_amount: '' as number | string,
    adults: 1,
    children: 0,
    status: 'pending',
    notes: '',
});

// ✅ Fix: একটাই computed saving state — form.processing OR composable saving
const isSaving = computed(() => form.processing || saving.value);

// ─── Submit ──────────────────────────────────────────────

async function submit() {
    clearError();

    if (!validateForm()) {
        const firstError = document.querySelector('.border-red-500');
        firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    try {
        // ✅ Fix: hotel_id এখন পাঠানো হচ্ছে
        await createReservation({
            hotel_id: form.hotel_id,
            guest_profile_id: form.guest_profile_id,
            room_id: form.room_id,
            check_in_date: form.check_in_date,
            check_out_date: form.check_out_date,
            total_amount: parseFloat(form.total_amount as string),
            adults: form.adults,
            children: form.children,
            status: form.status,
            notes: form.notes,
        });

        router.visit('/reservations');
    } catch (err) {
        console.error('Create failed:', err);
    }
}

// ─── Validation ──────────────────────────────────────────

function validateForm(): boolean {
    return validateInertiaForm(form, {
        hotel_id: [required],
        guest_profile_id: [required],
        room_id: [required],
        check_in_date: [required, checkInDate],
        check_out_date: [required],
        total_amount: [required, minValue(0)],
    });
}

function validateCheckOutDate() {
    if (form.check_out_date && form.check_in_date) {
        const result = checkOutDate(form.check_out_date, form.check_in_date);
        if (!result.valid) {
            form.setError('check_out_date', result.message!);
        } else {
            form.clearErrors('check_out_date');
        }
    }
}
</script>

<style scoped>
section.space-y-6 {
    padding-bottom: 2rem;
}
</style>