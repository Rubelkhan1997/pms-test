import { ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

/**
 * Reservation Composable
 * Handles all reservation-related business logic
 */
export function useReservations() {
    // ─────────────────────────────────────────────────────────
    // State (Reactive)
    // ─────────────────────────────────────────────────────────
    const reservations = ref<PMS.Reservation[]>([]);
    const reservation = ref<PMS.Reservation | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);
    const successMessage = ref<string | null>(null);

    // ─────────────────────────────────────────────────────────
    // API Calls
    // ─────────────────────────────────────────────────────────

    /**
     * Fetch all reservations with optional filters
     */
    async function fetchAll(params?: {
        status?: string;
        check_in_date?: string;
        check_out_date?: string;
    }): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const { data } = await axios.get('/api/v1/front-desk/reservations', { params });
            reservations.value = data.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch reservations';
            console.error('Fetch reservations error:', err);
        } finally {
            loading.value = false;
        }
    }

    /**
     * Fetch single reservation by ID
     */
    async function fetchById(id: number): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const { data } = await axios.get(`/api/v1/reservations/${id}`);
            reservation.value = data.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch reservation';
            console.error('Fetch reservation error:', err);
        } finally {
            loading.value = false;
        }
    }

    /**
     * Create new reservation
     */
    async function create(data: {
        guest_id: number;
        room_id: number;
        check_in_date: string;
        check_out_date: string;
        total_amount: number;
        notes?: string;
    }): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/v1/reservations', data);
            successMessage.value = 'Reservation created successfully';
            
            // Refresh the list
            await fetchAll();
            
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to create reservation';
            console.error('Create reservation error:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Update existing reservation
     */
    async function update(id: number, data: Partial<PMS.Reservation>): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.put(`/api/v1/reservations/${id}`, data);
            successMessage.value = 'Reservation updated successfully';
            
            // Update local state
            const index = reservations.value.findIndex(r => r.id === id);
            if (index !== -1) {
                reservations.value[index] = response.data.data;
            }
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to update reservation';
            console.error('Update reservation error:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Cancel reservation
     */
    async function cancel(id: number): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            await axios.post(`/api/v1/reservations/${id}/cancel`);
            successMessage.value = 'Reservation cancelled successfully';
            
            // Update local state
            const index = reservations.value.findIndex(r => r.id === id);
            if (index !== -1) {
                reservations.value[index].status = 'cancelled';
            }
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to cancel reservation';
            console.error('Cancel reservation error:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Guest Check In
     */
    async function checkIn(id: number): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post(`/api/v1/reservations/${id}/check-in`);
            successMessage.value = 'Guest checked in successfully';
            
            // Update local state
            const index = reservations.value.findIndex(r => r.id === id);
            if (index !== -1) {
                reservations.value[index].status = 'checked_in';
            }
            
            // Also update room status via store if needed
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Check in failed';
            console.error('Check in error:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Guest Check Out
     */
    async function checkOut(id: number, paymentData?: {
        paid_amount: number;
        payment_method: string;
    }): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post(`/api/v1/reservations/${id}/check-out`, paymentData);
            successMessage.value = 'Guest checked out successfully';
            
            // Update local state
            const index = reservations.value.findIndex(r => r.id === id);
            if (index !== -1) {
                reservations.value[index].status = 'checked_out';
            }
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Check out failed';
            console.error('Check out error:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Clear error message
     */
    function clearError(): void {
        error.value = null;
    }

    /**
     * Clear success message
     */
    function clearSuccess(): void {
        successMessage.value = null;
    }

    // ─────────────────────────────────────────────────────────
    // Return (Public API)
    // ─────────────────────────────────────────────────────────
    return {
        // State
        reservations,
        reservation,
        loading,
        error,
        successMessage,
        
        // Methods
        fetchAll,
        fetchById,
        create,
        update,
        cancel,
        checkIn,
        checkOut,
        clearError,
        clearSuccess
    };
}
