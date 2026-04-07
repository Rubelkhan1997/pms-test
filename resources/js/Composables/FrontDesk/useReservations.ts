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

export interface UseReservationsOptions {
    autoFetch?: boolean;
    initialFilters?: Partial<ReservationFilters>;
    pollingInterval?: number;
}

export function useReservations(options: UseReservationsOptions = {}) {
    const {
        autoFetch = false,
        initialFilters = {},
        pollingInterval = 0,
    } = options;

    const store = useReservationsStore();
    const toast = inject('toast') as typeof ToastType;

    const { loading: _loading, start: startLoading, stop: stopLoading } = useLoading();
    const { loading: _saving, start: startSaving, stop: stopSaving } = useLoading();

    const { start: startPolling, stop: stopPolling } = usePolling(
        () => fetchAll(),
        pollingInterval,
        () => pollingInterval > 0
    );

    const reservations = computed(() => store.items);
    const reservation = computed(() => store.selectedItem);
    const loading = computed(() => _loading.value || store.loadingList || store.loadingDetail);
    const saving = computed(() => _saving.value || store.loading);
    const error = computed(() => store.error);
    const pagination = computed(() => store.pagination.meta);

    const pendingCount = computed(() => store.pendingCount);
    const confirmedCount = computed(() => store.confirmedCount);
    const checkedInCount = computed(() => store.checkedInCount);
    const todayCheckIns = computed(() => store.todayCheckIns);

    async function fetchAll(page = 1, params?: Partial<ReservationFilters>): Promise<void> {
        startLoading();
        if (params) {
            store.setFilters(params);
        }

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

    function setFilters(filters: Partial<ReservationFilters>): void {
        store.setFilters(filters);
    }

    function resetFilters(): void {
        store.resetFilters();
    }

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
