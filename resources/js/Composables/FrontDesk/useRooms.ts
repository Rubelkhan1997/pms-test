import { computed, onMounted, onUnmounted, inject } from 'vue';
import { useRoomsStore } from '@/Stores/FrontDesk/roomStore';
import { useLoading, usePolling } from '@/Composables';
import { getApiError } from '@/Helpers/error';
import type { ApiResponse } from '@/Types/api';
import type { toast as ToastType } from '@/Plugins/toast';
import type {
    Room,
    RoomFilters,
    CreateRoomDto,
    UpdateRoomDto,
} from '@/Types/FrontDesk/room';

export interface UseRoomsOptions {
    autoFetch?: boolean;
    initialFilters?: Partial<RoomFilters>;
    pollingInterval?: number;
}

export function useRooms(options: UseRoomsOptions = {}) {
    const {
        autoFetch = false,
        initialFilters = {},
        pollingInterval = 0,
    } = options;

    const store = useRoomsStore();
    const toast = inject('toast') as typeof ToastType;

    const { loading: _loading, start: startLoading, stop: stopLoading } = useLoading();
    const { loading: _saving, start: startSaving, stop: stopSaving } = useLoading();

    const { start: startPolling, stop: stopPolling } = usePolling(
        () => fetchAll(),
        pollingInterval,
        () => pollingInterval > 0
    );

    const rooms = computed(() => store.rooms);
    const room = computed(() => store.selectedRoom);
    const loading = computed(() => _loading.value || store.loadingList || store.loadingDetail);
    const saving = computed(() => _saving.value || store.loading);
    const error = computed(() => store.error);
    const pagination = computed(() => store.pagination.meta);

    async function fetchAll(page = 1, params?: Partial<RoomFilters>): Promise<void> {
        startLoading();

        if (params) {
            store.setFilters(params);
        }

        try {
            await store.fetchAll(page);
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to fetch rooms'));
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
            toast.error(getApiError(err, 'Failed to fetch room'));
            throw err;
        } finally {
            stopLoading();
        }
    }

    async function create(payload: CreateRoomDto): Promise<ApiResponse<Room>> {
        startSaving();

        try {
            const result = await store.create(payload);
            const isSuccess = Number(result.status) === 1;

            if (isSuccess) {
                toast.success(result.message || 'Room created successfully');
            } else {
                toast.error(result.message || 'Failed to create room');
            }

            return result;
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to create room'));
            throw err;
        } finally {
            stopSaving();
        }
    }

    async function update(id: number, payload: UpdateRoomDto): Promise<ApiResponse<Room>> {
        startSaving();

        try {
            const result = await store.update(id, payload);
            const isSuccess = Number(result.status) === 1;

            if (isSuccess) {
                toast.success(result.message || 'Room updated successfully');
            } else {
                toast.error(result.message || 'Failed to update room');
            }

            return result;
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to update room'));
            throw err;
        } finally {
            stopSaving();
        }
    }

    async function deleteRoom(id: number): Promise<ApiResponse<void>> {
        startSaving();

        try {
            const result = await store.delete(id);
            const isSuccess = Number(result.status) === 1;

            if (isSuccess) {
                toast.success(result.message || 'Room deleted successfully');
            } else {
                toast.error(result.message || 'Failed to delete room');
            }

            return result;
        } catch (err: unknown) {
            toast.error(getApiError(err, 'Failed to delete room'));
            throw err;
        } finally {
            stopSaving();
        }
    }

    function setFilters(filters: Partial<RoomFilters>): void {
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
        rooms,
        room,
        pagination,
        loading,
        saving,
        error,
        fetchAll,
        fetchById,
        create,
        update,
        deleteRoom,
        setFilters,
        resetFilters,
    };
}
