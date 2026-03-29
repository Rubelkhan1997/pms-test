<template>
    <Head :title="`Reservation #${reservation?.reference || 'Details'}`" />
    <HotelLayout class="max-w-6xl mx-auto">
        <div class="space-y-6">
            <!-- Header with Back Button -->
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link href="/reservations" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition">
                        ← Back to Reservations
                    </Link>
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-800">
                            Reservation #{{ reservation?.reference }}
                        </h1>
                        <p class="text-sm text-slate-500 mt-1">Reservation Details</p>
                    </div>
                </div>

                <!-- Status Badge -->
                <span v-if="reservation" class="px-4 py-2 rounded-full text-sm font-medium"
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

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>

            <!-- Reservation Details -->
            <div v-else-if="reservation" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Guest Information -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Guest Information</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-slate-500">Name</label>
                            <p class="text-slate-800 font-medium">
                                {{ reservation.guest?.first_name && reservation.guest?.last_name
                                    ? `${reservation.guest.first_name} ${reservation.guest.last_name}`
                                    : 'N/A'
                                }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">Email</label>
                            <p class="text-slate-800">{{ reservation.guest?.email || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">Phone</label>
                            <p class="text-slate-800">{{ reservation.guest?.phone || 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Room Information -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Room Information</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-slate-500">Room Number</label>
                            <p class="text-slate-800 font-medium">{{ reservation.room?.number || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">Room Type</label>
                            <p class="text-slate-800">{{ reservation.room?.type || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">Price</label>
                            <p class="text-slate-800 font-medium">৳{{ reservation.room?.price?.toLocaleString() || 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Booking Dates -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Booking Dates</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-slate-500">Check-in</label>
                            <p class="text-slate-800 font-medium">{{ reservation.check_in_date ? formatDate(reservation.check_in_date) : 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">Check-out</label>
                            <p class="text-slate-800 font-medium">{{ reservation.check_out_date ? formatDate(reservation.check_out_date) : 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">Duration</label>
                            <p class="text-slate-800">{{ nights }} nights</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Payment Information</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-slate-500">Total Amount</label>
                            <p class="text-slate-800 font-medium text-lg">৳{{ reservation.total_amount?.toLocaleString() }}</p>
                        </div>
                       
                      
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="bg-white p-6 rounded-lg shadow md:col-span-2">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Additional Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm text-slate-500">Reference</label>
                            <p class="text-slate-800 font-medium">{{ reservation.reference || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">Hotel</label>
                            <p class="text-slate-800 font-medium">{{ reservation.hotel?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">Created At</label>
                            <p class="text-slate-800">{{ reservation.created_at ? formatDate(reservation.created_at) : 'N/A' }}</p>
                        </div>
                        <div v-if="reservation.notes" class="md:col-span-3">
                            <label class="text-sm text-slate-500">Notes</label>
                            <p class="text-slate-800">{{ reservation.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div v-if="reservation" class="flex gap-4 pt-4">
                
                <button
                    v-if="['pending', 'confirmed'].includes(reservation.status)"
                    @click="handleCancel"
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                >
                    Cancel Reservation
                </button>
                <Link
                    :href="`/reservations/${reservation.id}/edit`"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    Edit Reservation
                </Link>
            </div>
        </div>
    </HotelLayout>
</template>

<script setup lang="ts">
    import { computed, onMounted } from 'vue';
    import { router } from '@inertiajs/vue3';
    import { HotelLayout } from '@/Layouts';
    import { useReservations } from '@/Composables/FrontDesk/useReservations';
    import { formatDate, calculateNights } from '@/Utils/date';
    import { formatStatus } from '@/Utils/format';
   
    const { reservation, loading, error, fetchById, cancel: cancelAction} = useReservations();

    // Calculate number of nights (reusable function from Utils)
    const nights = computed(() => {
        if (!reservation.value || !reservation.value.check_in_date || !reservation.value.check_out_date) {
            return 0;
        }
        return calculateNights(reservation.value.check_in_date, reservation.value.check_out_date);
    });

    // Handle Cancel
    async function handleCancel() {
        if (!confirm('Are you sure you want to cancel this reservation?')) return;

        if (reservation.value) {
            await cancelAction(reservation.value.id);
            router.reload();
            // Toast already shown in composable
        }
    }

    // Load reservation on mount
    onMounted(() => {
        const pathSegments = window.location.pathname.split('/');
        const reservationId = pathSegments[pathSegments.length - 1];

        if (reservationId) {
            fetchById(parseInt(reservationId));
        }
    });
</script>
