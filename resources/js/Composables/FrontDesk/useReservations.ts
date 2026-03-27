import { computed } from 'vue';
import { useReservationsStore } from '@/Stores/FrontDesk/reservationStore';
import { useLoading, useMessage } from '@/Helpers';

/**
 * Reservation Composable - Business Logic Layer
 *
 * Architecture:
 * - Store  (reservationStore.ts) : Global state (Pinia)
 * - Composable (useReservations.ts) : Business logic + UI
 * - Pages  : Use composable only — never access store directly
 */

// ─────────────────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────────────────

export interface ReservationFilters {
    status?: string;
    check_in_date?: string;
    check_out_date?: string;
    search?: string;
}

export interface UseReservationOptions {
    autoFetch?: boolean;
    initialFilters?: ReservationFilters;
    pollingInterval?: number;
}

// ─────────────────────────────────────────────────────────
// Composable
// ─────────────────────────────────────────────────────────

export function useReservations(options: UseReservationOptions = {}) {

    const {
        autoFetch = false,
        initialFilters = {},
        pollingInterval = 30000,
    } = options;

    // ─── Store ───────────────────────────────────────────
    const store = useReservationsStore();

    // ─── UI Helpers ──────────────────────────────────────
    const { loading: _loading, start: startLoading, stop: stopLoading } = useLoading();
    const { loading: _saving, start: startSaving, stop: stopSaving } = useLoading();
    const { message: _success, showMessage: showSuccess, clearMessage: clearSuccess } = useMessage();
    const { message: _error, showMessage: showError, clearMessage: clearError } = useMessage();

    // ─── Reactive State (from Store) ─────────────────────
    const reservations = computed(() => store.reservations);
    const reservation = computed(() => store.selectedReservation);
    const filters = computed(() => store.filters);
    const pagination = computed(() => store.pagination);

    // ─── UI State ────────────────────────────────────────
    const loading = computed(() => _loading.value || store.loadingList);
    const saving = computed(() => _saving.value || store.loading);
    const successMessage = computed(() => _success.value);
    const error = computed(() => _error.value || store.error);

    // ─── Derived from Store (Getters) ────────────────────
    const pendingCount = computed(() => store.pendingCount);
    const confirmedCount = computed(() => store.confirmedCount);
    const checkedInCount = computed(() => store.checkedInCount);
    const todayCheckIns = computed(() => store.todayCheckIns);

    // ─────────────────────────────────────────────────────
    // Actions
    // ─────────────────────────────────────────────────────

    async function fetchAll(page: number = 1, params?: ReservationFilters): Promise<void> {
        startLoading();
        clearError();

        if (params) store.setFilters(params);

        try {
            await store.fetchAll(page);
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch reservations';
            showError(message);
            throw err;
        } finally {
            stopLoading();
        }
    }

    async function fetchById(id: number): Promise<void> {
        startLoading();
        clearError();

        try {
            await store.fetchById(id);
        } catch (err: any) {
            showError(err.response?.data?.message || 'Failed to fetch reservation');
            throw err;
        } finally {
            stopLoading();
        }
    }

    async function create(data: {
        hotel_id: number | string;
        guest_profile_id: number | string;
        room_id: number | string;
        check_in_date: string;
        check_out_date: string;
        total_amount: number;
        adults?: number;
        children?: number;
        status?: string;
        notes?: string;
    }): Promise<any> {
        startSaving();
        clearError();

        try {
            const response = await store.create(data);
            showSuccess('Reservation created successfully');
            return response;
        } catch (err: any) {
            showError(err.response?.data?.message || 'Failed to create reservation');
            throw err;
        } finally {
            stopSaving();
        }
    }

    async function update(id: number, data: Partial<PMS.Reservation>): Promise<any> {
        startSaving();
        clearError();

        try {
            const response = await store.update(id, data);
            showSuccess('Reservation updated successfully');
            return response;
        } catch (err: any) {
            showError(err.response?.data?.message || 'Failed to update reservation');
            throw err;
        } finally {
            stopSaving();
        }
    }

    async function cancel(id: number): Promise<void> {
        startSaving();
        clearError();

        try {
            await store.cancel(id);
            showSuccess('Reservation cancelled successfully');
        } catch (err: any) {
            showError(err.response?.data?.message || 'Failed to cancel reservation');
            throw err;
        } finally {
            stopSaving();
        }
    }

    async function deleteReservation(id: number): Promise<void> {
        startSaving();
        clearError();

        try {
            await store.delete(id);
            showSuccess('Reservation deleted successfully');
        } catch (err: any) {
            showError(err.response?.data?.message || 'Failed to delete reservation');
            throw err;
        } finally {
            stopSaving();
        }
    }

    async function checkIn(id: number): Promise<void> {
        startSaving();
        clearError();

        try {
            await store.checkIn(id);
            showSuccess('Guest checked in successfully');
        } catch (err: any) {
            showError(err.response?.data?.message || 'Check in failed');
            throw err;
        } finally {
            stopSaving();
        }
    }

    async function checkOut(id: number, paymentData?: any): Promise<void> {
        startSaving();
        clearError();

        try {
            await store.checkOut(id, paymentData);
            showSuccess('Guest checked out successfully');
        } catch (err: any) {
            showError(err.response?.data?.message || 'Check out failed');
            throw err;
        } finally {
            stopSaving();
        }
    }

    function setFilters(newFilters: ReservationFilters): void {
        store.setFilters(newFilters);
    }

    function resetFilters(): void {
        store.resetFilters();
    }

    // ─── Public API ──────────────────────────────────────

    return {
        // State
        reservations,
        reservation,
        filters,
        pagination,
        loading,
        saving,
        successMessage,
        error,

        // Computed
        pendingCount,
        confirmedCount,
        checkedInCount,
        todayCheckIns,

        // Actions
        fetchAll,
        fetchById,
        create,
        update,
        cancel,
        deleteReservation,
        checkIn,
        checkOut,
        setFilters,
        resetFilters,
        clearError,
        clearSuccess,
    };
}
