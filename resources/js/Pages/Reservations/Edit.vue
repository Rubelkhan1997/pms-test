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
                            v-model="form.hotelId"
                            disabled
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg bg-slate-100 text-slate-500 cursor-not-allowed"
                        >
                            <option value="">Select a hotel</option>
                            <option v-for="hotel in hotelOptions" :key="hotel.id" :value="hotel.id">
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
                            v-model="form.guestId"
                            :class="{ 'border-red-500': form.errors.guestId }"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Select a guest</option>
                            <option v-for="guest in guestOptions" :key="guest.id" :value="guest.id">
                                {{ guest.firstName }} {{ guest.lastName }} ({{ guest.email }})
                            </option>
                            </select>
                            <p v-if="form.errors.guestId" class="mt-1 text-sm text-red-500">{{ form.errors.guestId }}</p>
                        </div>

                        <!-- Room Selection -->
                        <div>
                            <label for="room_id" class="block text-sm font-medium text-slate-700 mb-2">
                                Room <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="room_id"
                            v-model="form.roomId"
                            :class="{ 'border-red-500': form.errors.roomId }"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Select a room</option>
                                <!-- ✅ Fix: base_rate → price -->
                            <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                                Room {{ room.number }} - {{ room.type }} ({{ room.price }} BDT)
                            </option>
                            </select>
                            <p v-if="form.errors.roomId" class="mt-1 text-sm text-red-500">{{ form.errors.roomId }}</p>
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
                            v-model="form.checkInDate"
                            :class="{ 'border-red-500': form.errors.checkInDate }"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p v-if="form.errors.checkInDate" class="mt-1 text-sm text-red-500">{{ form.errors.checkInDate }}</p>
                        </div>

                        <!-- Check-out Date -->
                        <div>
                            <label for="check_out_date" class="block text-sm font-medium text-slate-700 mb-2">
                                Check-out Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="check_out_date"
                                type="date"
                            v-model="form.checkOutDate"
                            :class="{ 'border-red-500': form.errors.checkOutDate }"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p v-if="form.errors.checkOutDate" class="mt-1 text-sm text-red-500">{{ form.errors.checkOutDate }}</p>
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
                            v-model="form.totalAmount"
                            :class="{ 'border-red-500': form.errors.totalAmount }"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="0.00"
                            />
                            <p v-if="form.errors.totalAmount" class="mt-1 text-sm text-red-500">{{ form.errors.totalAmount }}</p>
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
    import { mapGuestOptionApi, mapHotelOptionApi, mapReservationApiToReservation, mapRoomOptionApi } from '@/Utils/Mappers/reservation';

    // ─── Props ───────────────────────────────────────────────
    const props = defineProps<{
        reservation: Record<string, any>;
        hotels: Array<Record<string, any>>;
        guests: Array<Record<string, any>>;
        rooms: Array<Record<string, any>>;
    }>();

    // ─── Composable ──────────────────────────────────────────
    const { update: updateReservation, saving, error } = useReservations();

    // ─── Available Rooms ─────────────────────────────────────
    const hotelOptions = computed<HotelOption[]>(() =>
        props.hotels.map(mapHotelOptionApi)
    );

    const guestOptions = computed<GuestOption[]>(() =>
        props.guests.map(mapGuestOptionApi)
    );

    const roomOptions = computed<RoomOption[]>(() =>
        props.rooms.map(mapRoomOptionApi)
    );

    const availableRooms = computed(() =>
        roomOptions.value.filter(room => room.status === 'available')
    );

    // ─── Form ────────────────────────────────────────────────
    const reservationData: Reservation = mapReservationApiToReservation(props.reservation);

    const form = useForm({
        hotelId: reservationData.hotelId,
        guestId: reservationData.guestId,
        roomId: reservationData.roomId,
        checkInDate: reservationData.checkInDate,
        checkOutDate: reservationData.checkOutDate,
        totalAmount: reservationData.totalAmount.toString(),
        adults: reservationData.adults || 1,
        children: reservationData.children || 0,
        status: reservationData.status,
        notes: reservationData.notes || '',
    });


    // template logic → computed-এ রাখা হয়েছে
    const isSaving    = computed(() => form.processing || saving.value);
    const submitLabel = computed(() => isSaving.value ? 'Updating...' : 'Update Reservation');

    // check_in_date বদলালে check_out_date re-validate হবে
    watch(() => form.checkInDate, () => {
        if (form.checkOutDate) validateCheckOutDate();
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
                hotelId:       Number(form.hotelId),
                guestId:       Number(form.guestId),
                roomId:        Number(form.roomId),
                checkInDate:   form.checkInDate,
                checkOutDate:  form.checkOutDate,
                totalAmount:   parseFloat(form.totalAmount as string),
                adults:         form.adults,
                children:       form.children,
                status:         form.status,
                notes:          form.notes || undefined,
            });

            // Check API response status - toast already shown by composable
            if (Number(result.status) === 1) {
                router.visit('/reservations');
            }
            // If status === 0, error toast already shown by composable

        } catch (err: unknown) {
            // Backend Laravel validation errors
            const apiErr = err as Record<string, any>;

            if (apiErr?.response?.data?.errors) {
                const backendErrors: Record<string, string[]> = apiErr.response.data.errors;
                Object.entries(backendErrors).forEach(([key, messages]) => {
                    const mappedKey = mapBackendField(key);
                    form.setError(mappedKey as any, messages[0]);
                });
                scrollToFirstError();
            }
        }
    }

    // ─── Validation ──────────────────────────────────────────

    function validateForm(): boolean {
        return validateInertiaForm(form, {
            hotelId:      [required],
            guestId:      [required],
            roomId:       [required],
            checkInDate:  [required, checkInDate],
            checkOutDate: [required],
            totalAmount:  [required, minValue(0)],
        });
    }

    function validateCheckOutDate(): void {
        if (form.checkOutDate && form.checkInDate) {
            const result = checkOutDate(form.checkOutDate, form.checkInDate);
            if (!result.valid) {
                form.setError('checkOutDate', result.message!);
            } else {
                form.clearErrors('checkOutDate');
            }
        }
    }

    function mapBackendField(field: string): string {
        const map: Record<string, string> = {
            hotel_id: 'hotelId',
            guest_id: 'guestId',
            room_id: 'roomId',
            check_in_date: 'checkInDate',
            check_out_date: 'checkOutDate',
            total_amount: 'totalAmount',
        };
        return map[field] ?? field;
    }

    function scrollToFirstError(): void {
        setTimeout(() => {
            const firstError = document.querySelector('.border-red-500');
            firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
</script>
