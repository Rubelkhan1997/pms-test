import { computed, onMounted, onUnmounted } from 'vue';
import { useReservationsStore } from '@/Stores/FrontDesk/reservationStore';
import { useLoading, useMessage, getApiError, usePolling } from '@/Helpers';
import type { ApiResponse } from '@/types/api';
import type {
    Reservation,
    ReservationFilters,
    CreateReservationDto,
    UpdateReservationDto,
} from '@/types/FrontDesk/reservation';

// ─────────────────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────────────────

export interface UseReservationOptions {
    autoFetch?: boolean;
    initialFilters?: Partial<ReservationFilters>;
    pollingInterval?: number;
}

// ─────────────────────────────────────────────────────────
// Composable
// ─────────────────────────────────────────────────────────

export function useReservations(options: UseReservationOptions = {}) {

    const {
        autoFetch = false,
        initialFilters = {},
        pollingInterval = 0,  // ✅ Fix: default 0 — polling off by default
    } = options;

    // ─── Store (internal — expose করা হবে না) ────────────
    const store = useReservationsStore();

    // ─── UI Helpers ──────────────────────────────────────
    const { loading: _loading, start: startLoading, stop: stopLoading } = useLoading();
    const { loading: _saving, start: startSaving, stop: stopSaving } = useLoading();
    const { message: _success, showMessage: showSuccess, clearMessage: clearSuccess } = useMessage();
    const { message: _error, showMessage: showError, clearMessage: clearError } = useMessage();

    // ─── Polling ─────────────────────────────────────────
    const { start: startPolling, stop: stopPolling } = usePolling(
        () => fetchAll(),
        pollingInterval,
        () => pollingInterval > 0
    );

    // ─────────────────────────────────────────────────────
    // Reactive State
    // ─────────────────────────────────────────────────────

    const reservations = computed(() => store.reservations);
    const reservation = computed(() => store.selectedReservation);
    const pagination = computed(() => store.pagination);

    const loading = computed(() => _loading.value || store.loadingList);
    const saving = computed(() => _saving.value || store.loading);
    const successMessage = computed(() => _success.value); 
    const error = computed(() => _error.value || store.error);

    // ─── Derived (Store Getters) ──────────────────────────
    const pendingCount = computed(() => store.pendingCount);
    const confirmedCount = computed(() => store.confirmedCount);
    const checkedInCount = computed(() => store.checkedInCount);
    const todayCheckIns = computed(() => store.todayCheckIns);

    
    // ─────────────────────────────────────────────────────
    // Actions
    // ─────────────────────────────────────────────────────

    async function fetchAll(page = 1, params?: Partial<ReservationFilters>): Promise<void> {
        startLoading();
        clearError();
        if (params) store.setFilters(params);
        try {
            await store.fetchAll(page);
        } catch (err: unknown) {
            showError(getApiError(err, 'Failed to fetch reservations'));
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
        } catch (err: unknown) {
            showError(getApiError(err, 'Failed to fetch reservation'));
            throw err;
        } finally {
            stopLoading();
        }
    }
 
    async function create(payload: CreateReservationDto): Promise<ApiResponse<Reservation>> {
        startSaving();
        clearError();
        try {
            const result = await store.create(payload);
            showSuccess('Reservation created successfully');
            return result;
        } catch (err: unknown) {
            showError(getApiError(err, 'Failed to create reservation'));
            throw err;
        } finally {
            stopSaving();
        }
    }

    async function update(id: number, payload: UpdateReservationDto): Promise<ApiResponse<Reservation>> {
        startSaving();
        clearError();
        try {
            const result = await store.update(id, payload);
            showSuccess('Reservation updated successfully');
            return result;
        } catch (err: unknown) {
            showError(getApiError(err, 'Failed to update reservation'));
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
        } catch (err: unknown) {
            showError(getApiError(err, 'Failed to cancel reservation'));
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
        } catch (err: unknown) {
            showError(getApiError(err, 'Failed to delete reservation'));
            throw err;
        } finally {
            stopSaving();
        }
    }
 
    function setFilters(filters: Partial<ReservationFilters>): void {
        store.setFilters(filters);
    }

    function resetFilters(): void {
        store.resetFilters();
    }

    // ─── Lifecycle ───────────────────────────────────────

    if (autoFetch) {
        onMounted(() => {
            if (Object.keys(initialFilters).length > 0) {
                store.setFilters(initialFilters);
            }
            fetchAll();
            startPolling();
        });

        onUnmounted(() => {
            stopPolling();
        });
    }

    // ─────────────────────────────────────────────────────
    // Public API
    // ✅ Fix: store expose করা হয়নি — encapsulation বজায় আছে
    // ✅ Fix: showError internal — expose করা হয়নি
    // ─────────────────────────────────────────────────────

    return {
        // State
        reservations,
        reservation,
        pagination,
        loading,
        saving,
        successMessage,
        error,

        // Derived
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
        setFilters,
        resetFilters,
        clearError,
        clearSuccess,
    };
}