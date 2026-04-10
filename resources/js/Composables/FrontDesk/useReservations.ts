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

// Options to control default composable behavior from page level.
export interface UseReservationsOptions {
    autoFetch?: boolean;
    initialFilters?: Partial<ReservationFilters>;
    pollingInterval?: number;
}

export function useReservations(options: UseReservationsOptions = {}) {
    // Resolve options with safe defaults.
    const {
        autoFetch = false,
        initialFilters = {},
        pollingInterval = 0,
    } = options;

    const store = useReservationsStore();
    const toast = inject('toast') as typeof ToastType;

    // Separate loading states for list fetching and data mutations.
    const { loading: _loading, start: startLoading, stop: stopLoading } = useLoading();
    const { loading: _saving, start: startSaving, stop: stopSaving } = useLoading();

    // Optional polling for real-time style refreshes.
    const { start: startPolling, stop: stopPolling } = usePolling(
        () => fetchAll(),
        pollingInterval,
        () => pollingInterval > 0
    );

    // Reactive data exposed to pages.
    const reservations = computed(() => store.reservations);
    const reservation = computed(() => store.selectedReservation);
    const loading = computed(() => _loading.value || store.loadingList || store.loadingDetail);
    const saving = computed(() => _saving.value || store.loading);
    const error = computed(() => store.error);
    const pagination = computed(() => store.pagination.meta);

    // Aggregated dashboard metrics from reservation list state.
    const pendingCount = computed(() => store.pendingCount);
    const confirmedCount = computed(() => store.confirmedCount);
    const checkedInCount = computed(() => store.checkedInCount);
    const todayCheckIns = computed(() => store.todayCheckIns);

    // Load reservation list with optional filter overrides.
    async function fetchAll(page = 1, forceRefresh = false, params?: Partial<ReservationFilters>): Promise<void> {
        startLoading();
        if (params) {
            store.setFilters(params);
        }

        try {
            await store.fetchAll(page, forceRefresh);
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to fetch reservations'));
            throw err;
        } finally {
            stopLoading();
        }
    }

    // Load single reservation detail by id.
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

    // Create a new reservation record.
    async function create(payload: CreateReservationDto): Promise<ApiResponse<Reservation>> {
        startSaving();
        try {
            const result = await store.create(payload);
            const isSuccess = Number(result.status) === 1;

            if (isSuccess) {
                toast.success(result.message || 'Reservation created successfully');
            } else {
                toast.error(result.message || 'Failed to create reservation');
            }

            return result;
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to create reservation'));
            throw err;
        } finally {
            stopSaving();
        }
    }

    // Update existing reservation record.
    async function update(id: number, payload: UpdateReservationDto): Promise<ApiResponse<Reservation>> {
        startSaving();
        try {
            const result = await store.update(id, payload);
            const isSuccess = Number(result.status) === 1;

            if (isSuccess) {
                toast.success(result.message || 'Reservation updated successfully');
            } else {
                toast.error(result.message || 'Failed to update reservation');
            }

            return result;
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to update reservation'));
            throw err;
        } finally {
            stopSaving();
        }
    }

    // Cancel reservation by id.
    async function cancel(id: number): Promise<ApiResponse<void>> {
        startSaving();
        try {
            const result = await store.cancel(id);
            const isSuccess = Number(result.status) === 1;

            if (isSuccess) {
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

    // Delete reservation by id.
    async function deleteReservation(id: number): Promise<ApiResponse<void>> {
        startSaving();
        try {
            const result = await store.delete(id);
            const isSuccess = Number(result.status) === 1;

            if (isSuccess) {
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

    // Update local filter state.
    function setFilters(filters: Partial<ReservationFilters>): void {
        store.setFilters(filters);
    }

    // Reset local filter state to defaults.
    function resetFilters(): void {
        store.resetFilters();
    }

    // Auto-fetch lifecycle behavior when enabled.
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

    return {
        reservations,
        reservation,
        pagination,
        loading,
        saving,
        error,

        pendingCount,
        confirmedCount,
        checkedInCount,
        todayCheckIns,

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
