import { computed, onMounted, onUnmounted, inject } from 'vue';
import { useReservationsStore } from '@/Stores/FrontDesk/reservationStore';
import { useLoading, usePolling } from '@/Composables';
import { getApiError } from '@/Helpers/error';
import type { ApiResponse } from '@/Types/api';
import type { toast as ToastType } from '@/Plugins/toast';
import type {
    Reservation,
    ReservationFilters,
    CreateReservationDto,
    UpdateReservationDto,
} from '@/Types/FrontDesk/reservation';

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

    // ─── Inject Toast ────────────────────────────────────
    const toast = inject('toast') as typeof ToastType;

    // ─── UI Helpers ──────────────────────────────────────
    const { loading: _loading, start: startLoading, stop: stopLoading } = useLoading();
    const { loading: _saving, start: startSaving, stop: stopSaving } = useLoading();

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
    const error = computed(() => store.error);

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
        if (params) store.setFilters(params);
        try {
            await store.fetchAll(page);
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to fetch reservations'));
            throw err;
        } finally {
            stopLoading();
        }
    }

    async function fetchById(id: number): Promise<void> {
        startLoading();
        try {
            await store.fetchById(id);
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to fetch reservation'));
            throw err;
        } finally {
            stopLoading();
        }
    }

    async function create(payload: CreateReservationDto): Promise<ApiResponse<Reservation>> {
        startSaving();
        try {
            const result = await store.create(payload);
            // Show toast based on API response status
            if (result.status === 1) {
                toast.success(result.message);
            } else {
                toast.error(result.message);
            }
            return result;
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to create reservation'));
            throw err;
        } finally {
            stopSaving();
        }
    }

    async function update(id: number, payload: UpdateReservationDto): Promise<ApiResponse<Reservation>> {
        startSaving();
        try {
            const result = await store.update(id, payload);
            // Show toast based on API response status
            if (result.status === 1) {
                toast.success(result.message);
            } else {
                toast.error(result.message);
            }
            return result;
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to update reservation'));
            throw err;
        } finally {
            stopSaving();
        }
    }

    async function cancel(id: number): Promise<ApiResponse<void>> {
        startSaving();
        try {
            const result = await store.cancel(id);
            // Show toast based on API response status
            if (result.status === 1) {
                toast.success(result.message || 'Reservation cancelled successfully');
            } else {
                toast.error(result.message || 'Failed to cancel reservation');
            }
            return result;
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to cancel reservation'));
            throw err;
        } finally {
            stopSaving();
        }
    }

    async function deleteReservation(id: number): Promise<ApiResponse<void>> {
        startSaving();
        try {
            const result = await store.delete(id);
            // Show toast based on API response status
            if (result.status === 1) {
                toast.success(result.message || 'Reservation deleted successfully');
            } else {
                toast.error(result.message || 'Failed to delete reservation');
            }
            return result;
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to delete reservation'));
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
    // ✅ Fix: All errors now show as toast notifications
    // ─────────────────────────────────────────────────────

    return {
        // State
        reservations,
        reservation,
        pagination,
        loading,
        saving,
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
    };
}