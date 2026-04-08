<template>
    <!-- Page title shown in browser tab -->
    <Head :title="t('reservations.edit_reservation')" />
    
    <!-- Show form only if user has permission to edit reservations -->
    <div v-if="canEdit" class="max-w-4xl mx-auto">
        <section class="space-y-6">
            
            <!-- Header Section: Title + Back button -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">{{ t('reservations.edit_reservation') }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ t('reservations.update_hint') }}</p>
                </div>
                <Link href="/reservations" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition">
                    {{ t('navigation.reservations') }}
                </Link>
            </div>

            <!-- Reservation Edit Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Hotel Selection (Read-only - can't change hotel after creation) -->
                    <div>
                        <FormSelect
                            id="hotel_id"
                            v-model="form.hotelId"
                            :label="t('navigation.hotels')"
                            :placeholder="t('reservations.select_hotel')"
                            :options="hotelSelectOptions"
                            option-label="label"
                            option-value="value"
                            :disabled="true"
                            wrapper-class="mb-0"
                        />
                        <p class="mt-1 text-sm text-slate-500">{{ t('reservations.hotel_cannot_change') }}</p>
                    </div>

                    <!-- Guest & Room Selection (2 columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Guest Selection Dropdown -->
                        <FormSelect
                            id="guest_id"
                            v-model="form.guestId"
                            :label="t('guests.title')"
                            :required="true"
                            :placeholder="t('reservations.select_guest')"
                            :options="guestSelectOptions"
                            option-label="label"
                            option-value="value"
                            :error="form.errors.guestId"
                            wrapper-class="mb-0"
                        />

                        <!-- Room Selection Dropdown -->
                        <FormSelect
                            id="room_id"
                            v-model="form.roomId"
                            :label="t('rooms.title')"
                            :required="true"
                            :placeholder="t('reservations.select_room')"
                            :options="roomSelectOptions"
                            option-label="label"
                            option-value="value"
                            :error="form.errors.roomId"
                            wrapper-class="mb-0"
                        />
                    </div>
                    
                    <!-- Date Selection (2 columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Check-in Date Picker -->
                        <DatePicker
                            id="check_in_date"
                            v-model="form.checkInDate"
                            :label="t('reservations.check_in')"
                            :required="true"
                            :error="form.errors.checkInDate"
                            wrapper-class="mb-0"
                        />
                        
                        <!-- Check-out Date Picker -->
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

                    <!-- Amount & Status (2 columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Total Amount Input -->
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

                        <!-- Status Dropdown -->
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

                    <!-- Notes Textarea -->
                    <FormTextarea
                        id="notes"
                        v-model="form.notes"
                        :label="t('reservations.notes')"
                        :placeholder="t('reservations.notes_placeholder')"
                        :error="form.errors.notes"
                        :rows="3"
                        wrapper-class="mb-0"
                    />

                    <!-- Submit & Cancel Buttons -->
                    <div class="flex gap-4 pt-4">
                        <FormButton
                            type="submit"
                            color="primary"
                            :name="submitLabel"
                            :disabled="isSaving"
                            button-class="px-6"
                        />
                        <Link href="/reservations" class="px-6 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition">
                            {{ t('actions.cancel') }}
                        </Link>
                    </div>
                </form>
            </div>
        </section>
    </div>
    
    <!-- Access Denied Message (shown when user lacks permission) -->
    <div v-else class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <h1 class="text-xl font-semibold text-slate-800">{{ t('messages.access_denied') }}</h1>
            <p class="text-sm text-slate-500 mt-2">{{ t('messages.no_permission') }}</p>
            <Link href="/reservations" class="inline-flex mt-4 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition">
                {{ t('actions.back') }}
            </Link>
        </div>
    </div>
</template>

<script setup lang="ts">
    // Vue 3 reactivity: watch for reactive changes, onMounted for lifecycle hook, computed for derived state
    import { computed, watch, onMounted } from 'vue';
    import { useForm, router } from '@inertiajs/vue3';
    import { useReservations } from '@/Composables/FrontDesk/useReservations';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import type { Reservation, HotelOption, GuestOption, RoomOption } from '@/Types/FrontDesk/reservation';
    import { required, minValue, checkInDate, checkOutDate, validateInertiaForm } from '@/Utils/validation';
    import { mapGuestOptionApi, mapHotelOptionApi, mapReservationApiToReservation, mapRoomOptionApi } from '@/Utils/Mappers/reservation';

    // ─── i18n ────────────────────────────────────────────────
    // useI18n: provides translation function 't'
    const { t } = useI18n();

    // ─── Permissions ─────────────────────────────────────────
    // usePermissionService: provides methods to check user permissions
    const permission = usePermissionService();
    
    // canEdit: computed property that returns true if user has 'edit reservations' permission
    const canEdit = computed(() => permission.check('edit reservations'));

    // ─── Composable ──────────────────────────────────────────
    // useReservations: provides CRUD operations for reservations
    // update: function to send PUT/PATCH request to update an existing reservation
    // saving: reactive boolean indicating if the API call is in progress
    const { update: updateReservation, saving } = useReservations();

    // ─── Lifecycle ───────────────────────────────────────────
    // Redirect to /reservations if user doesn't have edit permission
    onMounted(() => {
        if (!canEdit.value) {
            router.visit('/reservations');
            return;
        }
    });

    // ─── Props ───────────────────────────────────────────────
    // Data passed from the backend controller
    const props = defineProps<{
        reservation: Record<string, any>;   // Raw reservation data from API
        hotels: Array<Record<string, any>>; // List of all hotels
        guests: Array<Record<string, any>>; // List of all guests
        rooms: Array<Record<string, any>>;  // List of all rooms
    }>();
 
    // ─── Mapped Options ──────────────────────────────────────
    // Transform raw API data into typed frontend objects
    const hotelOptions = computed<HotelOption[]>(() => props.hotels.map(mapHotelOptionApi));
    const guestOptions = computed<GuestOption[]>(() => props.guests.map(mapGuestOptionApi));
    const roomOptions = computed<RoomOption[]>(() => props.rooms.map(mapRoomOptionApi));

    // Format options for dropdown display
    const hotelSelectOptions = computed(() =>
        hotelOptions.value.map((hotel) => ({ value: hotel.id, label: `${hotel.name} (${hotel.code})` }))
    );

    const guestSelectOptions = computed(() =>
        guestOptions.value.map((guest) => ({ value: guest.id, label: `${guest.firstName} ${guest.lastName} (${guest.email || ''})` }))
    );

    // Filter only available rooms (status === 'available')
    const availableRooms = computed(() => roomOptions.value.filter((room) => room.status === 'available'));

    const roomSelectOptions = computed(() =>
        availableRooms.value.map((room) => ({
            value: room.id,
            label: `${t('rooms.room_number')} ${room.number} - ${room.type} (${room.price} BDT)`,
        }))
    );

    // Define all possible reservation statuses for the dropdown
    const statusOptions = computed(() => ([
        { value: 'pending', label: t('status.pending') },
        { value: 'draft', label: t('status.draft') },
        { value: 'confirmed', label: t('status.confirmed') },
        { value: 'checked_in', label: t('status.checked_in') },
        { value: 'checked_out', label: t('status.checked_out') },
        { value: 'cancelled', label: t('status.cancelled') },
    ]));

    // ─── Form ────────────────────────────────────────────────
    // Map the raw reservation API data to a typed Reservation object
    const reservation: Reservation = mapReservationApiToReservation(props.reservation);

    // useForm: creates a reactive form object pre-filled with existing reservation data
    // form.errors: tracks validation errors per field
    // form.processing: true while form is being submitted
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

    // ─── Computed Properties ────────────────────────────────────────────
    // isSaving: true if form is processing OR the update API call is in progress
    const isSaving = computed(() => form.processing || saving.value);
    
    // submitLabel: dynamic button text
    const submitLabel = computed(() => isSaving.value ? t('actions.updating') : t('actions.update_reservation'));

    // ─── Watchers ────────────────────────────────────────────
    // Watch for changes in checkInDate
    // Why: When check-in date changes, re-validate check-out date to ensure it's still valid
    // Example: If user picks a new check-in that's after the current check-out, it should show an error
    watch(() => form.checkInDate, () => {
        if (form.checkOutDate) {
            validateCheckOutDate();
        }
    });

    // ─── Submit ──────────────────────────────────────────────
    async function submit(): Promise<void> {
        // Clear all previous validation errors
        form.clearErrors();

        // Run client-side validation
        if (!validateForm()) {
            scrollToFirstError();
            return;
        }

        try {
            // Send update data to backend API
            const result = await updateReservation(reservation.id, {
                hotelId: Number(form.hotelId),
                guestId: Number(form.guestId),
                roomId: Number(form.roomId),
                checkInDate: form.checkInDate,
                checkOutDate: form.checkOutDate,
                totalAmount: parseFloat(form.totalAmount as string),
                adults: form.adults,
                children: form.children,
                status: form.status,
                notes: form.notes || undefined,
            });

            // Check if API response indicates success (status === 1)
            if (Number(result.status) === 1) {
                form.reset();
                router.visit('/reservations');  // Navigate back to reservations list
            }
        } catch (err: unknown) {
            // Handle API errors (e.g., 422 validation errors from backend)
            const apiErr = err as Record<string, any>;

            if (apiErr?.response?.data?.errors) {
                const backendErrors: Record<string, string[]> = apiErr.response.data.errors;
                
                Object.entries(backendErrors).forEach(([key, messages]) => {
                    // mapBackendField converts snake_case (backend) to camelCase (frontend)
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
            hotelId: [required],
            guestId: [required],
            roomId: [required],
            checkInDate: [required, checkInDate],
            checkOutDate: [required],
            totalAmount: [required, minValue(0)],
        });
    }

    // validateCheckOutDate: validates that check-out date is after check-in date
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

    // Convert backend snake_case keys to local camelCase keys
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

    // Auto-scrolls the page to the first field with a validation error
    function scrollToFirstError(): void {
        setTimeout(() => {
            const firstError = document.querySelector('.border-red-500');
            firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
</script>
