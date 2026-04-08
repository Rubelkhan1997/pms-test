<template>
    <Head :title="`${t('reservations.reservation')} #${reservation?.reference || t('reservations.details')}`" />
    <HotelLayout v-if="canView" class="max-w-6xl mx-auto">
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <FormButton
                        type="button"
                        color="secondary"
                        :name="t('navigation.reservations')"
                        @click="router.visit('/reservations')"
                    />
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-800">
                            {{ t('reservations.reservation') }} #{{ reservation?.reference }}
                        </h1>
                        <p class="text-sm text-slate-500 mt-1">{{ t('reservations.reservation_details') }}</p>
                    </div>
                </div>

                <span
                    v-if="reservation"
                    class="px-4 py-2 rounded-full text-sm font-medium"
                    :class="{
                        'bg-yellow-100 text-yellow-800': reservation.status === 'pending',
                        'bg-green-100 text-green-800': reservation.status === 'confirmed',
                        'bg-blue-100 text-blue-800': reservation.status === 'checked_in',
                        'bg-purple-100 text-purple-800': reservation.status === 'checked_out',
                        'bg-red-100 text-red-800': reservation.status === 'cancelled',
                        'bg-slate-100 text-slate-800': reservation.status === 'no_show',
                    }"
                >
                    {{ formatStatus(reservation.status) }}
                </span>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>

            <div v-else-if="reservation" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ t('reservations.guest_information') }}</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-slate-500">{{ t('guests.full_name') }}</label>
                            <p class="text-slate-800 font-medium">
                                {{ reservation.guest?.firstName && reservation.guest?.lastName
                                    ? `${reservation.guest.firstName} ${reservation.guest.lastName}`
                                    : 'N/A'
                                }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">{{ t('guests.email') }}</label>
                            <p class="text-slate-800">{{ reservation.guest?.email || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">{{ t('guests.phone') }}</label>
                            <p class="text-slate-800">{{ reservation.guest?.phone || 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ t('reservations.room_information') }}</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-slate-500">{{ t('rooms.room_number') }}</label>
                            <p class="text-slate-800 font-medium">{{ reservation.room?.number || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">{{ t('rooms.room_type') }}</label>
                            <p class="text-slate-800">{{ reservation.room?.type || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">{{ t('rooms.price') }}</label>
                            <p class="text-slate-800 font-medium">{{ reservation.room?.price?.toLocaleString() || 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ t('reservations.booking_dates') }}</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-slate-500">{{ t('reservations.check_in') }}</label>
                            <p class="text-slate-800 font-medium">{{ reservation.checkInDate ? formatDate(reservation.checkInDate) : 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">{{ t('reservations.check_out') }}</label>
                            <p class="text-slate-800 font-medium">{{ reservation.checkOutDate ? formatDate(reservation.checkOutDate) : 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">{{ t('reservations.duration') }}</label>
                            <p class="text-slate-800">{{ nights }} {{ t('time.nights') }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ t('reservations.payment_information') }}</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-slate-500">{{ t('reservations.total_price') }}</label>
                            <p class="text-slate-800 font-medium text-lg">{{ reservation.totalAmount?.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow md:col-span-2">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ t('reservations.additional_information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm text-slate-500">{{ t('reservations.reference') }}</label>
                            <p class="text-slate-800 font-medium">{{ reservation.reference || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">{{ t('navigation.hotels') }}</label>
                            <p class="text-slate-800 font-medium">{{ reservation.hotel?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">{{ t('reservations.created_at') }}</label>
                            <p class="text-slate-800">{{ reservation.createdAt ? formatDate(reservation.createdAt) : 'N/A' }}</p>
                        </div>
                        <div v-if="reservation.notes" class="md:col-span-3">
                            <label class="text-sm text-slate-500">{{ t('reservations.notes') }}</label>
                            <p class="text-slate-800">{{ reservation.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="reservation" class="flex gap-4 pt-4">
                <FormButton
                    v-if="canCancel && ['pending', 'confirmed'].includes(reservation.status)"
                    type="button"
                    color="danger"
                    :name="t('reservations.cancel_reservation')"
                    @click="handleCancel"
                />
                <FormButton
                    v-if="canEdit"
                    type="button"
                    color="primary"
                    :name="t('reservations.edit_reservation')"
                    @click="router.visit(`/reservations/${reservation.id}/edit`)"
                />
            </div>
        </div>
    </HotelLayout>
    <HotelLayout v-else class="max-w-6xl mx-auto">
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <h1 class="text-xl font-semibold text-slate-800">{{ t('messages.access_denied') }}</h1>
            <p class="text-sm text-slate-500 mt-2">{{ t('messages.no_permission') }}</p>
            <FormButton
                type="button"
                color="secondary"
                :name="t('actions.back')"
                button-class="mt-4"
                @click="router.visit('/reservations')"
            />
        </div>
    </HotelLayout>
</template>

<script setup lang="ts">
    // Vue 3 reactivity: computed for derived state, onMounted for lifecycle hook, inject for dependency injection
    import { computed, onMounted, inject } from 'vue';
    import { router } from '@inertiajs/vue3';
    import { HotelLayout } from '@/Layouts';
    import { useReservations } from '@/Composables/FrontDesk/useReservations';
    import { useI18n } from '@/Composables/useI18n';
    import { usePermissionService } from '@/Composables/usePermissionService';
    import type { ConfirmType } from '@/Plugins/confirm';
    import { formatDate, calculateNights } from '@/Utils/date';
    import { formatStatus } from '@/Utils/format';
    import { mapReservationApiToReservation } from '@/Utils/Mappers/reservation';
    import type { Reservation } from '@/Types/FrontDesk/reservation';

    // ─── i18n ────────────────────────────────────────────────
    // useI18n: provides translation function 't'
    const { t } = useI18n();

    // ─── Inject Confirm ─────────────────────────────────────
    // inject: gets the global confirm dialog plugin for cancel confirmation
    const confirm = inject<ConfirmType>('confirm')!;

    // ─── Permissions ─────────────────────────────────────────
    // usePermissionService: provides methods to check user permissions
    const permission = usePermissionService();

    // canView: true if user has 'view reservations' permission (controls page access)
    const canView = computed(() => permission.check('view reservations'));
    
    // canEdit: true if user has 'edit reservations' permission (controls Edit button visibility)
    const canEdit = computed(() => permission.check('edit reservations'));
    
    // canCancel: true if user has 'edit reservations' permission (reuses same permission for cancel)
    const canCancel = computed(() => permission.check('edit reservations'));

    // ─── Composables ─────────────────────────────────────────
    // useReservations: provides reservation operations
    // loading: boolean indicating if data is being fetched
    // cancel: function to send cancel request for a reservation
    const { loading, cancel: cancelAction } = useReservations();

    // ─── Lifecycle ───────────────────────────────────────────
    // Redirect to /reservations if user doesn't have view permission
    onMounted(() => {
        if (!canView.value) {
            router.visit('/reservations');
        }
    });

    // ─── Props ───────────────────────────────────────────────
    // Data passed from the backend controller
    const props = defineProps<{
        reservation: Record<string, any>;   // Raw reservation data from API
    }>();

    // ─── Mapped Reservation ──────────────────────────────────
    // Map the raw reservation API data to a typed Reservation object
    const reservation: Reservation = mapReservationApiToReservation(props.reservation);

    // Nights is derived from check-in/check-out dates
    // Recomputes automatically if dates change
    const nights = computed(() => {
        if (!reservation?.checkInDate || !reservation?.checkOutDate) {
            return 0;
        }

        return calculateNights(reservation.checkInDate, reservation.checkOutDate);
    });

    // ─── Cancel Action ───────────────────────────────────────
    // handleCancel: shows confirmation dialog, then cancels the reservation if confirmed
    async function handleCancel(): Promise<void> {
        // Show confirmation dialog with reservation reference number
        const confirmed = await confirm.show({
            title: 'Cancel Reservation?',
            message: `Reservation ${reservation?.reference} will be cancelled. This cannot be undone.`,
            confirmText: 'Cancel',
            cancelText: 'Keep',
            variant: 'danger',
        });

        // If user clicked "Keep", stop here
        if (!confirmed) {
            return;
        }

        try {
            // Cancel the reservation via API
            const result = await cancelAction(reservation.id);

            // Reload the page to show updated status
            if (Number(result.status) === 1) {
                router.visit(`/reservations/${reservation.id}`);
            }
        } catch (e) {
            console.error('Delete failed:', e);
        }
    }
</script>
