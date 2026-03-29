<template>
    <Head title="Edit Reservation" /> 
    <div class="max-w-4xl mx-auto">
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
                    <!-- Hotel Selection (Read-only - can't change hotel) -->
                    <div>
                        <label for="hotel_id" class="block text-sm font-medium text-slate-700 mb-2">
                            Hotel
                        </label>
                        <select
                            id="hotel_id"
                            v-model="form.hotel_id"
                            disabled
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg bg-slate-100 text-slate-500 cursor-not-allowed"
                        >
                            <option value="">Select a hotel</option>
                            <option v-for="hotel in hotels" :key="hotel.id" :value="hotel.id">
                                {{ hotel.name }} ({{ hotel.code }})
                            </option>
                        </select>
                        <p class="mt-1 text-sm text-slate-500">Hotel cannot be changed after creation</p>
                    </div>

                    <!-- Guest & Room Selection -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Guest Selection -->
                        <div>
                            <label for="guest_id" class="block text-sm font-medium text-slate-700 mb-2">
                                Guest <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="guest_id"
                                v-model="form.guest_id"
                                :class="{ 'border-red-500': form.errors.guest_id }"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Select a guest</option>
                                <option v-for="guest in guests" :key="guest.id" :value="guest.id">
                                    {{ guest.first_name }} {{ guest.last_name }} ({{ guest.email }})
                                </option>
                            </select>
                            <p v-if="form.errors.guest_id" class="mt-1 text-sm text-red-500">{{ form.errors.guest_id }}</p>
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
                                <!-- ✅ Fix: base_rate → price -->
                                <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                                    Room {{ room.number }} - {{ room.type }} ({{ room.price }} BDT)
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
                                min="1"
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
                            :disabled="isSaving"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ submitLabel }}
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
</template>

<script setup lang="ts">
    import { computed, watch } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import { useReservations } from '@/Composables/FrontDesk/useReservations';
    import type { Reservation, HotelOption, GuestOption, RoomOption } from '@/Types/FrontDesk/reservation';
    import { required, minValue, checkInDate, checkOutDate, validateInertiaForm } from '@/Utils/validation';

    // ─── Props ───────────────────────────────────────────────
    const props = defineProps<{
        reservation: Reservation
        hotels: HotelOption[];
        guests: GuestOption[];
        rooms: RoomOption[];
    }>();

    // ─── Composable ──────────────────────────────────────────
    const { update: updateReservation, saving, error } = useReservations();

    // ─── Available Rooms ─────────────────────────────────────
    const availableRooms = computed(() =>
        props.rooms.filter(room => room.status === 'available')
    );

    // ─── Form ────────────────────────────────────────────────
    const form = useForm({
        hotel_id: props.reservation.hotel_id,
        guest_id: props.reservation.guest_id,
        room_id: props.reservation.room_id,
        check_in_date: props.reservation.check_in_date,
        check_out_date: props.reservation.check_out_date,
        total_amount: props.reservation.total_amount.toString(),
        adults: props.reservation.adults || 1,
        children: props.reservation.children || 0,
        status: props.reservation.status,
        notes: props.reservation.notes || '',
    });


    // ✅ Fix: template logic → computed-এ রাখা হয়েছে
    const isSaving    = computed(() => form.processing || saving.value);
    const submitLabel = computed(() => isSaving.value ? 'Updating...' : 'Update Reservation');

    // ✅ Fix: check_in_date বদলালে check_out_date re-validate হবে
    watch(() => form.check_in_date, () => {
        if (form.check_out_date) validateCheckOutDate();
    });

    // ─── Submit ──────────────────────────────────────────────

    async function submit(): Promise<void> {
        form.clearErrors();

        if (!validateForm()) {
            scrollToFirstError();
            return;
        }

        try {
            const result = await updateReservation(props.reservation.id, {
                hotel_id:       Number(form.hotel_id),
                guest_id:       Number(form.guest_id),
                room_id:        Number(form.room_id),
                check_in_date:  form.check_in_date,
                check_out_date: form.check_out_date,
                total_amount:   parseFloat(form.total_amount as string),
                adults:         form.adults,
                children:       form.children,
                status:         form.status,
                notes:          form.notes || undefined,
            });

            // Check API response status - toast already shown by composable
            if (result.status === 1) {
                router.visit('/reservations');
            }
            // If status === 0, error toast already shown by composable

        } catch (err: unknown) {
            // Backend Laravel validation errors
            const apiErr = err as Record<string, any>;

            if (apiErr?.response?.data?.errors) {
                const backendErrors: Record<string, string[]> = apiErr.response.data.errors;
                Object.entries(backendErrors).forEach(([key, messages]) => {
                    form.setError(key as any, messages[0]);
                });
                scrollToFirstError();
            }
        }
    }

    // ─── Validation ──────────────────────────────────────────

    function validateForm(): boolean {
        return validateInertiaForm(form, {
            hotel_id:       [required],
            guest_id:       [required],
            room_id:        [required],
            check_in_date:  [required, checkInDate],
            check_out_date: [required],
            total_amount:   [required, minValue(0)],
        });
    }

    function validateCheckOutDate(): void {
        if (form.check_out_date && form.check_in_date) {
            const result = checkOutDate(form.check_out_date, form.check_in_date);
            if (!result.valid) {
                form.setError('check_out_date', result.message!);
            } else {
                form.clearErrors('check_out_date');
            }
        }
    }

    function scrollToFirstError(): void {
        setTimeout(() => {
            const firstError = document.querySelector('.border-red-500');
            firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
</script>
