<template>
    <Head :title="`Reservation #${reservation?.reference || 'Details'} `" />
    <HotelLayout>
        <div class="space-y-6">
            <!-- Header with Back Button -->
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link
                        href="/reservations"
                        class="px-4 py-2 text-slate-600 hover:text-slate-800 transition"
                    >
                        ← Back to Reservations
                    </Link>
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-800">
                            Reservation #{{ reservation?.reference }}
                        </h1>
                        <p class="text-sm text-slate-500 mt-1">
                            Reservation Details
                        </p>
                    </div>
                </div>

                <!-- Status Badge -->
                <div v-if="reservation" class="px-4 py-2 rounded-full text-sm font-medium"
                    :class="{
                        'bg-yellow-100 text-yellow-800': reservation.status === 'pending',
                        'bg-green-100 text-green-800': reservation.status === 'confirmed',
                        'bg-blue-100 text-blue-800': reservation.status === 'checked_in',
                        'bg-gray-100 text-gray-800': reservation.status === 'checked_out',
                        'bg-red-100 text-red-800': reservation.status === 'cancelled'
                    }"
                >
                    {{ formatStatus(reservation.status) }}
                </div>
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
                            <p class="text-slate-800 font-medium">{{ reservation.guest?.name || 'N/A' }}</p>
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
                            <label class="text-sm text-slate-500">Floor</label>
                            <p class="text-slate-800">{{ reservation.room?.floor || 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Booking Dates -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Booking Dates</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-slate-500">Check-in</label>
                            <p class="text-slate-800 font-medium">{{ formatDate(reservation.check_in_date) }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">Check-out</label>
                            <p class="text-slate-800 font-medium">{{ formatDate(reservation.check_out_date) }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">Duration</label>
                            <p class="text-slate-800">{{ calculateNights }} nights</p>
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
                        <div>
                            <label class="text-sm text-slate-500">Paid Amount</label>
                            <p class="text-slate-800 font-medium">৳{{ reservation.paid_amount?.toLocaleString() }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">Due Amount</label>
                            <p class="text-red-600 font-medium text-lg">৳{{ (reservation.total_amount - reservation.paid_amount).toLocaleString() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="bg-white p-6 rounded-lg shadow md:col-span-2">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Additional Information</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-slate-500">Reference</label>
                            <p class="text-slate-800 font-medium">{{ reservation.reference }}</p>
                        </div>
                        <div v-if="reservation.notes">
                            <label class="text-sm text-slate-500">Notes</label>
                            <p class="text-slate-800">{{ reservation.notes }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500">Created At</label>
                            <p class="text-slate-800">{{ formatDate(reservation.created_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div v-if="reservation" class="flex gap-4">
                <button
                    v-if="reservation.status === 'confirmed'"
                    @click="handleCheckIn"
                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                >
                    Check In
                </button>
                <button
                    v-if="reservation.status === 'checked_in'"
                    @click="handleCheckOut"
                    class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition"
                >
                    Check Out
                </button>
                <button
                    v-if="['pending', 'confirmed'].includes(reservation.status)"
                    @click="handleCancel"
                    class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                >
                    Cancel Reservation
                </button>
            </div>
        </div>
    </HotelLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { HotelLayout } from '@/Layouts';
import { useReservationsStore } from '@/Stores';

const store = useReservationsStore();
const reservation = computed(() => store.selectedReservation);
const loading = computed(() => store.loadingDetail);

// Calculate number of nights
const calculateNights = computed(() => {
    if (!reservation.value) return 0;
    const checkIn = new Date(reservation.value.check_in_date);
    const checkOut = new Date(reservation.value.check_out_date);
    const diffTime = Math.abs(checkOut.getTime() - checkIn.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
});

// Format date
function formatDate(dateString: string): string {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Format status
function formatStatus(status: string): string {
    const statusMap: Record<string, string> = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        checked_in: 'Checked In',
        checked_out: 'Checked Out',
        cancelled: 'Cancelled',
        no_show: 'No Show'
    };
    return statusMap[status] || status;
}

// Handle Check In
function handleCheckIn() {
    if (!confirm('Are you sure you want to check in this guest?')) return;

    if (reservation.value) {
        // Use store action or API call
        // For now, using store
        console.log('Check in reservation:', reservation.value.id);
    }
}

// Handle Check Out
function handleCheckOut() {
    if (!confirm('Are you sure you want to check out this guest?')) return;

    if (reservation.value) {
        console.log('Check out reservation:', reservation.value.id);
    }
}

// Handle Cancel
function handleCancel() {
    if (!confirm('Are you sure you want to cancel this reservation?')) return;

    if (reservation.value) {
        console.log('Cancel reservation:', reservation.value.id);
    }
}

// Load reservation on mount
onMounted(() => {
    // Get reservation ID from URL
    const pathSegments = window.location.pathname.split('/');
    const reservationId = pathSegments[pathSegments.length - 1];

    if (reservationId) {
        store.fetchById(parseInt(reservationId));
    }
});
</script>
