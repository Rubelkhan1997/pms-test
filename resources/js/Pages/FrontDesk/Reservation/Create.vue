<template>
    <Head :title="t('reservations.new_reservation')" />
    <div v-if="canCreate" class="max-w-4xl mx-auto">
        <section class="space-y-6">

            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">{{ t('reservations.new_reservation') }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ t('reservations.create_hint') }}</p>
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
                    <FormSelect
                        id="hotel_id"
                        v-model="form.hotelId"
                        :label="t('navigation.hotels')"
                        :required="true"
                        :placeholder="t('reservations.select_hotel')"
                        :options="hotelSelectOptions"
                        option-label="label"
                        option-value="value"
                        :error="form.errors.hotelId"
                        wrapper-class="mb-0"
                    />

                    <!-- Guest & Room -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <FormSelect
                            id="guest_id"
                            v-model="form.guestId"
                            :label="t('navigation.guests')"
                            :required="true"
                            :placeholder="t('reservations.select_guest')"
                            :options="guestSelectOptions"
                            option-label="label"
                            option-value="value"
                            :error="form.errors.guestId"
                            wrapper-class="mb-0"
                        />

                        <FormSelect
                            id="room_id"
                            v-model="form.roomId"
                            :label="t('navigation.rooms')"
                            :required="true"
                            :placeholder="t('reservations.select_room')"
                            :options="roomSelectOptions"
                            option-label="label"
                            option-value="value"
                            :error="form.errors.roomId"
                            wrapper-class="mb-0"
                        />
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <DatePicker
                            id="check_in_date"
                            v-model="form.checkInDate"
                            :label="t('reservations.check_in')"
                            :required="true"
                            :error="form.errors.checkInDate"
                            wrapper-class="mb-0"
                        />

                        <DatePicker
                            id="check_out_date"
                            v-model="form.checkOutDate"
                            :label="t('reservations.check_out')"
                            :required="true"
                            :error="form.errors.checkOutDate"
                            wrapper-class="mb-0"
                            @update:model-value="validateCheckOutDate"
                        />
                    </div>

                    <!-- Amount & Status -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <FormInput
                            id="total_amount"
                            v-model="form.totalAmount"
                            type="number"
                            :label="t('reservations.total_price')"
                            :required="true"
                            placeholder="0.00"
                            :step="0.01"
                            :min="1"
                            :error="form.errors.totalAmount"
                            wrapper-class="mb-0"
                        />

                        <FormSelect
                            id="status"
                            v-model="form.status"
                            :label="t('reservations.status')"
                            :required="true"
                            :options="statusOptions"
                            option-label="label"
                            option-value="value"
                            :error="form.errors.status"
                            wrapper-class="mb-0"
                        />
                    </div>

                    <!-- Notes -->
                    <FormTextarea
                        id="notes"
                        v-model="form.notes"
                        :label="t('reservations.notes')"
                        :placeholder="t('reservations.notes_placeholder')"
                        :error="form.errors.notes"
                        :rows="3"
                        wrapper-class="mb-0"
                    />

                    <!-- Submit -->
                    <div class="flex gap-4 pt-4">
                        <FormButton
                            type="submit"
                            color="primary"
                            :name="submitLabel"
                            :disabled="isSaving"
                            button-class="px-6"
                        />
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
    import { required, minValue, checkInDate, checkOutDate, validateInertiaForm } from '@/Utils/validation';
    import type { ReservationStatus, HotelOption, GuestOption, RoomOption } from '@/Types/FrontDesk/reservation';
    import { mapGuestOptionApi, mapHotelOptionApi, mapRoomOptionApi } from '@/Utils/Mappers/reservation';

    // ─── i18n ────────────────────────────────────────────────
    const { t } = useI18n();

    // ─── Props ───────────────────────────────────────────────
    const props = defineProps<{
        hotels: Array<Record<string, any>>;
        guests: Array<Record<string, any>>;
        rooms: Array<Record<string, any>>;
    }>();

    // ─── Composable ──────────────────────────────────────────
    const { create: createReservation, saving } = useReservations();
    const permission = usePermissionService();
    const canCreate = computed(() => permission.check('create reservations'));

    const hotelOptions = computed<HotelOption[]>(() =>
        props.hotels.map(mapHotelOptionApi)
    );

    const guestOptions = computed<GuestOption[]>(() =>
        props.guests.map(mapGuestOptionApi)
    );

    const roomOptions = computed<RoomOption[]>(() =>
        props.rooms.map(mapRoomOptionApi)
    );

    const hotelSelectOptions = computed(() =>
        hotelOptions.value.map((hotel) => ({ value: hotel.id, label: `${hotel.name} (${hotel.code})` }))
    );

    const guestSelectOptions = computed(() =>
        guestOptions.value.map((guest) => ({ value: guest.id, label: `${guest.firstName} ${guest.lastName} (${guest.email || ''})` }))
    );

    // ─── Available Rooms ─────────────────────────────────────
    const availableRooms = computed(() =>
        roomOptions.value.filter(room => room.status === 'available')
    );

    const roomSelectOptions = computed(() =>
        availableRooms.value.map((room) => ({
            value: room.id,
            label: `${t('rooms.room_number')} ${room.number} - ${room.type} (${room.price} BDT)`,
        }))
    );

    const statusOptions = computed(() => ([
        { value: 'pending', label: t('status.pending') },
        { value: 'draft', label: t('status.draft') },
        { value: 'confirmed', label: t('status.confirmed') },
        { value: 'checked_in', label: t('status.checked_in') },
        { value: 'checked_out', label: t('status.checked_out') },
        { value: 'cancelled', label: t('status.cancelled') },
    ]));

    // ─── Form ────────────────────────────────────────────────
    const form = useForm({
        hotelId:       '' as number | string,
        guestId:       '' as number | string,
        roomId:        '' as number | string,
        checkInDate:   new Date().toISOString().split('T')[0],
        checkOutDate:  '',
        totalAmount:   '' as number | string,
        adults:         1,
        children:       0,
        status:         'pending' as ReservationStatus,
        notes:          '',
    });
 
    const isSaving    = computed(() => form.processing || saving.value);
    const submitLabel = computed(() => isSaving.value ? t('actions.creating') : t('actions.create'));

    // Watchers to validate check-out date when check-in date or check-out date changes
    watch(() => form.checkInDate, () => {
        if (form.checkOutDate) validateCheckOutDate();
    });

    onMounted(() => {
        if (!canCreate.value) {
            router.visit('/reservations');
            return;
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
            const result = await createReservation({
                hotelId:       Number(form.hotelId),
                guestId:       Number(form.guestId),
                roomId:        Number(form.roomId),
                checkInDate:   form.checkInDate,
                checkOutDate:  form.checkOutDate,
                totalAmount:   parseFloat(form.totalAmount as string),
                adults:        form.adults,
                children:      form.children,
                status:        form.status,
                notes:         form.notes || undefined,
            });

            if (Number(result.status) === 1) {
                form.reset();
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

<style scoped>
    section.space-y-6 {
        padding-bottom: 2rem;
    }
</style>
