import { ref, shallowRef, readonly, computed } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

// ─────────────────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────────────────

export interface ReservationFilters {
    status?: string;
    check_in_date?: string;
    check_out_date?: string;
    search?: string;
}

/**
 * Reservation Composable
 * Handles all reservation-related business logic
 *
 * Best Practices Applied:
 * - shallowRef for primitives (better performance)
 * - ref for arrays/objects (deep reactivity)
 * - readonly for protected state
 * - computed for derived values
 * - Options object pattern for complex configs
 */
export function useReservations(options?: {
    autoFetch?: boolean;
    initialFilters?: ReservationFilters;
}) {

    // ─────────────────────────────────────────────────────────
    // State (Reactive) - Best Practice
    // ─────────────────────────────────────────────────────────
    
    // ✅ ref() for arrays (deep reactivity, replacement pattern)
    const reservations = ref<PMS.Reservation[]>([]);
    const reservation = ref<PMS.Reservation | null>(null);
    
    // ✅ shallowRef() for primitives (performance optimization)
    const loading = shallowRef(false);
    const loadingCheckIn = shallowRef(false);
    const loadingCheckOut = shallowRef(false);
    const error = shallowRef<string | null>(null);
    const successMessage = shallowRef<string | null>(null);
    
    // ✅ shallowRef() for filter object (will use reactive in store)
    const filters = shallowRef<ReservationFilters>(options?.initialFilters || {});

    // ─────────────────────────────────────────────────────────
    // Computed (Derived State) - Best Practice
    // ─────────────────────────────────────────────────────────
    
    const pendingCount = computed(() => 
        reservations.value.filter(r => r.status === 'pending').length
    );

    const confirmedCount = computed(() => 
        reservations.value.filter(r => r.status === 'confirmed').length
    );

    const checkedInCount = computed(() => 
        reservations.value.filter(r => r.status === 'checked_in').length
    );

    const todayCheckIns = computed(() => {
        const today = new Date().toISOString().split('T')[0];
        return reservations.value.filter(r => r.check_in_date === today);
    });

    const todayCheckOuts = computed(() => {
        const today = new Date().toISOString().split('T')[0];
        return reservations.value.filter(r => r.check_out_date === today);
    });

    // ─────────────────────────────────────────────────────────
    // API Calls
    // ─────────────────────────────────────────────────────────

    /**
     * Fetch all reservations with optional filters
     */
    async function fetchAll(params?: ReservationFilters): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const { data } = await axios.get('/api/v1/front-desk/reservations', { 
                params: params || filters.value 
            });
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
        loadingCheckIn.value = true;
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
            loadingCheckIn.value = false;
        }
    }

    /**
     * Guest Check Out
     */
    async function checkOut(id: number, paymentData?: {
        paid_amount: number;
        payment_method: string;
    }): Promise<void> {
        loadingCheckOut.value = true;
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
            loadingCheckOut.value = false;
        }
    }

    /**
     * Set filters
     */
    function setFilters(newFilters: ReservationFilters): void {
        filters.value = { ...filters.value, ...newFilters };
    }

    /**
     * Reset filters
     */
    function resetFilters(): void {
        filters.value = {};
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
    // Auto-fetch on init (optional)
    // ─────────────────────────────────────────────────────────
    
    if (options?.autoFetch) {
        fetchAll();
    }

    // ─────────────────────────────────────────────────────────
    // Return (Public API) - Best Practice: Readonly state + Actions
    // ─────────────────────────────────────────────────────────
    
    return {
        // ✅ Readonly state (can't mutate from components directly)
        reservations: readonly(reservations),
        reservation: readonly(reservation),
        loading: readonly(loading),
        loadingCheckIn: readonly(loadingCheckIn),
        loadingCheckOut: readonly(loadingCheckOut),
        error: readonly(error),
        successMessage: readonly(successMessage),
        
        // ✅ Computed values (derived state)
        pendingCount,
        confirmedCount,
        checkedInCount,
        todayCheckIns,
        todayCheckOuts,
        
        // ✅ Actions (only way to mutate state)
        fetchAll,
        fetchById,
        create,
        update,
        cancel,
        checkIn,
        checkOut,
        setFilters,
        resetFilters,
        clearError,
        clearSuccess
    };
}
