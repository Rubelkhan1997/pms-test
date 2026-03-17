import { ref, shallowRef, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useLoading, useMessage, usePolling } from '@/Helpers';

/**
 * Room Composable - Business Logic for Room Management
 *
 * @see https://vuejs.org/guide/best-practices/performance.html
 * @see https://vuejs.org/guide/reusability/composables.html
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
    pollingInterval?: number;
    cacheEnabled?: boolean;
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
        initialFilters = {},
        pollingInterval = 30000,
        cacheEnabled = false
    } = options;

    // ─────────────────────────────────────────────────────
    // State
    // ─────────────────────────────────────────────────────
    const _rooms = ref<PMS.Room[]>([]);
    const _room = ref<PMS.Room | null>(null);
    const _filters = shallowRef<RoomFilters>(initialFilters);
    const _cache = shallowRef<Map<string, PMS.Room[]>>(new Map());

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
        _rooms.value.filter(r => r.status === 'available').length
    );

    const occupiedCount = computed(() =>
        _rooms.value.filter(r => r.status === 'occupied').length
    );

    const maintenanceCount = computed(() =>
        _rooms.value.filter(r => r.status === 'maintenance').length
    );

    const dirtyCount = computed(() =>
        _rooms.value.filter(r => r.status === 'dirty').length
    );

    // Filtered rooms
    const filteredRooms = computed(() => {
        let filtered = [..._rooms.value];
        const filters = _filters.value;

        if (filters.status) {
            filtered = filtered.filter(r => r.status === filters.status);
        }

        if (filters.floor) {
            filtered = filtered.filter(r => r.floor === filters.floor);
        }

        if (filters.type) {
            filtered = filtered.filter(r => r.type === filters.type);
        }

        if (filters.search) {
            const search = filters.search.toLowerCase();
            filtered = filtered.filter(r =>
                r.number.toLowerCase().includes(search) ||
                r.type.toLowerCase().includes(search)
            );
        }

        return filtered;
    });

    // Total revenue potential
    const totalRevenuePotential = computed(() =>
        _rooms.value.reduce((sum, r) => sum + r.price, 0)
    );

    // ─────────────────────────────────────────────────────
    // API Calls
    // ─────────────────────────────────────────────────────

    async function fetchAll(params?: RoomFilters): Promise<void> {
        const fetchFilters = params || _filters.value;
        const cacheKey = JSON.stringify(fetchFilters);

        if (cacheEnabled && _cache.value.has(cacheKey)) {
            _rooms.value = _cache.value.get(cacheKey)!;
            return;
        }

        startLoading();
        clearError();

        try {
            const { data } = await axios.get('/api/v1/rooms', {
                params: fetchFilters
            });

            _rooms.value = data.data;

            if (cacheEnabled) {
                _cache.value.set(cacheKey, data.data);
            }

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch rooms';
            showError(message);
            console.error('Fetch rooms error:', err);
            throw err;
        } finally {
            stopLoading();
        }
    }

    async function fetchById(id: number): Promise<void> {
        startLoading();
        clearError();

        try {
            const { data } = await axios.get(`/api/v1/rooms/${id}`);
            _room.value = data.data;
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch room';
            showError(message);
            console.error('Fetch room error:', err);
            throw err;
        } finally {
            stopLoading();
        }
    }

    async function updateStatus(id: number, status: PMS.Room['status']): Promise<void> {
        startSaving();
        clearError();

        try {
            const response = await axios.patch(`/api/v1/rooms/${id}/status`, { status });
            showSuccess(`Room ${response.data.data.number} status updated to ${status}`);

            const index = _rooms.value.findIndex(r => r.id === id);
            if (index !== -1) {
                _rooms.value = [
                    ..._rooms.value.slice(0, index),
                    { ..._rooms.value[index], status },
                    ..._rooms.value.slice(index + 1)
                ];
            }

            if (cacheEnabled) {
                _cache.value.clear();
            }
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to update room status';
            showError(message);
            console.error('Update room status error:', err);
            throw err;
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

    function clearCache(): void {
        _cache.value.clear();
    }

    // ─────────────────────────────────────────────────────
    // Polling
    // ─────────────────────────────────────────────────────

    const { start: startPolling, stop: stopPolling } = usePolling(
        () => fetchAll(),
        pollingInterval,
        () => true
    );

    // ─────────────────────────────────────────────────────
    // Lifecycle Hooks
    // ─────────────────────────────────────────────────────

    if (autoFetch) {
        onMounted(() => {
            fetchAll();
            if (pollingInterval > 0) {
                startPolling();
            }
        });

        onUnmounted(() => {
            stopPolling();
        });
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
        totalRevenuePotential,

        // Actions
        fetchAll,
        fetchById,
        updateStatus,
        setFilters,
        resetFilters,
        clearCache,
        clearError,
        clearSuccess
    };
}
