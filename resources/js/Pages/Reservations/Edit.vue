<template>
    <Head :title="t('reservations.edit_reservation')" />
    <div v-if="canEdit" class="max-w-4xl mx-auto">
        <section class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">{{ t('reservations.edit_reservation') }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ t('reservations.update_hint') }}</p>
                </div>
                <Link
                    href="/reservations"
                    class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition"
                >
                    ← {{ t('navigation.reservations') }}
                </Link>
            </div>

            <!-- Reservation Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Hotel Selection (Read-only - can't change hotel) -->
                    <div>
                        <label for="hotel_id" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ t('navigation.hotels') }}
                        </label>
                        <select
                            id="hotel_id"
                            v-model="form.hotelId"
                            disabled
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg bg-slate-100 text-slate-500 cursor-not-allowed"
                        >
                            <option value="">{{ t('reservations.select_hotel') }}</option>
                            <option v-for="hotel in hotelOptions" :key="hotel.id" :value="hotel.id">
                                {{ hotel.name }} ({{ hotel.code }})
                            </option>
                        </select>
                        <p class="mt-1 text-sm text-slate-500">{{ t('reservations.hotel_cannot_change') }}</p>
                    </div>

                    <!-- Guest & Room Selection -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Guest Selection -->
                        <div>
                            <label for="guest_id" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ t('guests.title') }} <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="guest_id"
                                v-model="form.guestId"
                                :class="{ 'border-red-500': form.errors.guestId }"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                <option value="">{{ t('reservations.select_guest') }}</option>
                            <option v-for="guest in guestOptions" :key="guest.id" :value="guest.id">
                                {{ guest.firstName }} {{ guest.lastName }} ({{ guest.email }})
                            </option>
                            </select>
                            <p v-if="form.errors.guestId" class="mt-1 text-sm text-red-500">{{ form.errors.guestId }}</p>
                        </div>

                        <!-- Room Selection -->
                        <div>
                            <label for="room_id" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ t('rooms.title') }} <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="room_id"
                            v-model="form.roomId"
                            :class="{ 'border-red-500': form.errors.roomId }"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">{{ t('reservations.select_room') }}</option>
                                <!-- ✅ Fix: base_rate → price -->
                            <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                                {{ t('rooms.room_number') }} {{ room.number }} - {{ room.type }} ({{ room.price }} BDT)
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
                                {{ t('reservations.check_in') }} <span class="text-red-500">*</span>
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
                                {{ t('reservations.check_out') }} <span class="text-red-500">*</span>
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
                                {{ t('reservations.total_price') }} <span class="text-red-500">*</span>
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
                                {{ t('reservations.status') }} <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="status"
                                v-model="form.status"
                                :class="{ 'border-red-500': form.errors.status }"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="pending">{{ t('status.pending') }}</option>
                                <option value="draft">{{ t('status.draft') }}</option>
                                <option value="confirmed">{{ t('status.confirmed') }}</option>
                                <option value="checked_in">{{ t('status.checked_in') }}</option>
                                <option value="checked_out">{{ t('status.checked_out') }}</option>
                                <option value="cancelled">{{ t('status.cancelled') }}</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1 text-sm text-red-500">{{ form.errors.status }}</p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ t('reservations.notes') }}
                        </label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            :class="{ 'border-red-500': form.errors.notes }"
                            rows="3"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :placeholder="t('reservations.notes_placeholder')"
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
                            {{ t('actions.cancel') }}
                        </Link>
                    </div>
                </form>
            </div>
        </section>
    </div>
    <div v-else class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <h1 class="text-xl font-semibold text-slate-800">{{ t('messages.access_denied') }}</h1>
            <p class="text-sm text-slate-500 mt-2">{{ t('messages.no_permission') }}</p>
            <Link
                href="/reservations"
                class="inline-flex mt-4 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition"
            >
                {{ t('actions.back') }}
            </Link>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { computed, watch, onMounted } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import { useReservations } from '@/Composables/FrontDesk/useReservations';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import type { Reservation, HotelOption, GuestOption, RoomOption } from '@/Types/FrontDesk/reservation';
    import { required, minValue, checkInDate, checkOutDate, validateInertiaForm } from '@/Utils/validation';
    import { mapGuestOptionApi, mapHotelOptionApi, mapReservationApiToReservation, mapRoomOptionApi } from '@/Utils/Mappers/reservation';

    // ─── i18n ────────────────────────────────────────────────
    const { t } = useI18n();

    // ─── Props ───────────────────────────────────────────────
    const props = defineProps<{
        reservation: Record<string, any>;
        hotels: Array<Record<string, any>>;
        guests: Array<Record<string, any>>;
        rooms: Array<Record<string, any>>;
    }>();

    // ─── Composable ──────────────────────────────────────────
    const { update: updateReservation, saving } = useReservations();
    const permission = usePermissionService();
    const canEdit = computed(() => permission.check('edit reservations'));

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
    const reservation: Reservation = mapReservationApiToReservation(props.reservation);

    const form = useForm({
        hotelId: reservation.hotelId,
        guestId: reservation.guestId,
        roomId: reservation.roomId,
        checkInDate: reservation.checkInDate,
        checkOutDate: reservation.checkOutDate,
        totalAmount: reservation.totalAmount.toString(),
        adults: reservation.adults || 1,
        children: reservation.children || 0,
        status: reservation.status,
        notes: reservation.notes || '',
    });

    // ─── Computed ────────────────────────────────────────────
    const isSaving    = computed(() => form.processing || saving.value);
    const submitLabel = computed(() => isSaving.value ? t('actions.updating') : t('actions.update_reservation'));

    // Watchers to validate check-out date when check-in date or check-out date changes
    watch(() => form.checkInDate, () => {
        if (form.checkOutDate) validateCheckOutDate();
    });

    onMounted(() => {
        if (!canEdit.value) {
            router.visit('/reservations');
        }
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

            if (Number(result.status) === 1) {
                router.visit('/reservations');
            }
        } catch (err: unknown) {
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
