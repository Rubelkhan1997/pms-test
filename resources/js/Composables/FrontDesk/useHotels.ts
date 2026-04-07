import { computed, onMounted, onUnmounted, inject } from 'vue';
import { useHotelsStore } from '@/Stores/FrontDesk/hotelStore';
import { useLoading, usePolling } from '@/Composables';
import { getApiError } from '@/Helpers/error';
import type { ApiResponse } from '@/Types/api';
import type { toast as ToastType } from '@/Plugins/toast';
import type {
    Hotel,
    HotelFilters,
    CreateHotelDto,
    UpdateHotelDto,
} from '@/Types/FrontDesk/hotel';

// Options to control default composable behavior from page level.
export interface UseHotelsOptions {
    autoFetch?: boolean;
    initialFilters?: Partial<HotelFilters>;
    pollingInterval?: number;
}

export function useHotels(options: UseHotelsOptions = {}) {
    // Resolve options with safe defaults.
    const {
        autoFetch = false,
        initialFilters = {},
        pollingInterval = 0,
    } = options;

    const store = useHotelsStore();
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
    const hotels = computed(() => store.hotels);
    const hotel = computed(() => store.selectedHotel);
    const loading = computed(() => _loading.value || store.loadingList || store.loadingDetail);
    const saving = computed(() => _saving.value || store.loading);
    const error = computed(() => store.error);
    const pagination = computed(() => store.pagination.meta);

    // Load hotel list with optional filter overrides.
    async function fetchAll(page = 1, params?: Partial<HotelFilters>): Promise<void> {
        startLoading();
        if (params) {
            store.setFilters(params);
        }

        try {
            await store.fetchAll(page);
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to fetch hotels'));
            throw err;
        } finally {
            stopLoading();
        }
    }

    // Load single hotel detail by id.
    async function fetchById(id: number): Promise<void> {
        startLoading();
        try {
            await store.fetchById(id);
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to fetch hotel'));
            throw err;
        } finally {
            stopLoading();
        }
    }

    // Create a new hotel record.
    async function create(payload: CreateHotelDto): Promise<ApiResponse<Hotel>> {
        startSaving();
        try {
            const result = await store.create(payload);
            const isSuccess = Number(result.status) === 1;

            if (isSuccess) {
                toast.success(result.message || 'Hotel created successfully');
            } else {
                toast.error(result.message || 'Failed to create hotel');
            }

            return result;
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to create hotel'));
            throw err;
        } finally {
            stopSaving();
        }
    }

    // Update existing hotel record.
    async function update(id: number, payload: UpdateHotelDto): Promise<ApiResponse<Hotel>> {
        startSaving();
        try {
            const result = await store.update(id, payload);
            const isSuccess = Number(result.status) === 1;

            if (isSuccess) {
                toast.success(result.message || 'Hotel updated successfully');
            } else {
                toast.error(result.message || 'Failed to update hotel');
            }

            return result;
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to update hotel'));
            throw err;
        } finally {
            stopSaving();
        }
    }

    // Delete hotel record by id.
    async function deleteHotel(id: number): Promise<ApiResponse<void>> {
        startSaving();
        try {
            const result = await store.delete(id);
            const isSuccess = Number(result.status) === 1;

            if (isSuccess) {
                toast.success(result.message || 'Hotel deleted successfully');
            } else {
                toast.error(result.message || 'Failed to delete hotel');
            }

            return result;
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to delete hotel'));
            throw err;
        } finally {
            stopSaving();
        }
    }

    // Update local filter state.
    function setFilters(filters: Partial<HotelFilters>): void {
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
        hotels,
        hotel,
        pagination,
        loading,
        saving,
        error,

        fetchAll,
        fetchById,
        create,
        update,
        deleteHotel,
        setFilters,
        resetFilters,
    };
}
