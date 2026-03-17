import { ref, shallowRef, computed } from 'vue';
import { useLoading, useMessage } from '@/Helpers';

/**
 * Room Composable - Business Logic for Room Management
 * 
 * Note: This is a base template. Update API endpoints as needed.
 */

// ─────────────────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────────────────

export interface RoomFilters {
    status?: string;
    floor?: number;
    type?: string;
    search?: string;
}

export interface UseRoomOptions {
    autoFetch?: boolean;
    initialFilters?: RoomFilters;
}

// ─────────────────────────────────────────────────────────
// Main Composable: useRooms
// ─────────────────────────────────────────────────────────

export function useRooms(options: UseRoomOptions = {}) {
    // ─────────────────────────────────────────────────────
    // Options with defaults
    // ─────────────────────────────────────────────────────
    const {
        autoFetch = false,
        initialFilters = {}
    } = options;

    // ─────────────────────────────────────────────────────
    // State
    // ─────────────────────────────────────────────────────
    const _rooms = ref<any[]>([]);
    const _room = ref<any | null>(null);
    const _filters = shallowRef<RoomFilters>(initialFilters);

    // Compose smaller composables
    const { loading: _loading, start: startLoading, stop: stopLoading } = useLoading();
    const { loading: _saving, start: startSaving, stop: stopSaving } = useLoading();
    const { message: _successMessage, showMessage: showSuccess, clearMessage: clearSuccess } = useMessage();
    const { message: _error, showMessage: showError, clearMessage: clearError } = useMessage();

    // ─────────────────────────────────────────────────────
    // Computed (Derived State)
    // ─────────────────────────────────────────────────────
    const rooms = computed(() => _rooms.value);
    const room = computed(() => _room.value);
    const loading = computed(() => _loading.value);
    const saving = computed(() => _saving.value);
    const successMessage = computed(() => _successMessage.value);
    const error = computed(() => _error.value);

    // Room counts by status
    const availableCount = computed(() =>
        _rooms.value.filter((r: any) => r.status === 'available').length
    );

    const occupiedCount = computed(() =>
        _rooms.value.filter((r: any) => r.status === 'occupied').length
    );

    const maintenanceCount = computed(() =>
        _rooms.value.filter((r: any) => r.status === 'maintenance').length
    );

    const dirtyCount = computed(() =>
        _rooms.value.filter((r: any) => r.status === 'dirty').length
    );

    // Filtered rooms
    const filteredRooms = computed(() => {
        let filtered = [..._rooms.value];
        const filters = _filters.value;

        if (filters.status) {
            filtered = filtered.filter((r: any) => r.status === filters.status);
        }

        if (filters.floor) {
            filtered = filtered.filter((r: any) => r.floor === filters.floor);
        }

        if (filters.type) {
            filtered = filtered.filter((r: any) => r.type === filters.type);
        }

        if (filters.search) {
            const search = filters.search.toLowerCase();
            filtered = filtered.filter((r: any) =>
                r.number?.toLowerCase().includes(search) ||
                r.type?.toLowerCase().includes(search)
            );
        }

        return filtered;
    });

    // ─────────────────────────────────────────────────────
    // Actions (Update with your API endpoints)
    // ─────────────────────────────────────────────────────

    async function fetchAll(params?: RoomFilters): Promise<void> {
        const fetchFilters = params || _filters.value;

        startLoading();
        clearError();

        try {
            // TODO: Update with your actual API endpoint
            // const { data } = await axios.get('/api/v1/rooms', {
            //     params: fetchFilters
            // });
            // _rooms.value = data.data;

            // Mock data for base project
            _rooms.value = [];

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch rooms';
            showError(message);
            console.error('Fetch rooms error:', err);
        } finally {
            stopLoading();
        }
    }

    async function fetchById(id: number): Promise<void> {
        startLoading();
        clearError();

        try {
            // TODO: Update with your actual API endpoint
            // const { data } = await axios.get(`/api/v1/rooms/${id}`);
            // _room.value = data.data;

            _room.value = null;

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch room';
            showError(message);
            console.error('Fetch room error:', err);
        } finally {
            stopLoading();
        }
    }

    async function updateStatus(id: number, status: string): Promise<void> {
        startSaving();
        clearError();

        try {
            // TODO: Update with your actual API endpoint
            // await axios.patch(`/api/v1/rooms/${id}/status`, { status });
            showSuccess(`Room status updated to ${status}`);

            const index = _rooms.value.findIndex((r: any) => r.id === id);
            if (index !== -1) {
                _rooms.value = [
                    ..._rooms.value.slice(0, index),
                    { ..._rooms.value[index], status },
                    ..._rooms.value.slice(index + 1)
                ];
            }

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to update room status';
            showError(message);
            console.error('Update room status error:', err);
        } finally {
            stopSaving();
        }
    }

    // ─────────────────────────────────────────────────────
    // Filter Management
    // ─────────────────────────────────────────────────────

    function setFilters(newFilters: RoomFilters): void {
        _filters.value = { ..._filters.value, ...newFilters };
    }

    function resetFilters(): void {
        _filters.value = initialFilters;
    }

    // ─────────────────────────────────────────────────────
    // Return (Public API)
    // ─────────────────────────────────────────────────────

    return {
        // Readonly state
        rooms,
        room,
        loading,
        saving,
        successMessage,
        error,

        // Computed values
        availableCount,
        occupiedCount,
        maintenanceCount,
        dirtyCount,
        filteredRooms,

        // Actions
        fetchAll,
        fetchById,
        updateStatus,
        setFilters,
        resetFilters,
        clearError,
        clearSuccess
    };
}
