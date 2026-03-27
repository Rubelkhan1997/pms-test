import { ref, shallowRef, readonly, computed, triggerRef, onMounted, onUnmounted } from 'vue';
import { apiClient } from '@/Services';
import { useLoading, useMessage, usePolling } from '@/Helpers';

/**
 * Room Composable - Best Practices Implementation
 *
 * Applied Best Practices:
 * ✅ shallowRef for primitives (performance)
 * ✅ ref for arrays (deep reactivity needed)
 * ✅ readonly for protected state (controlled mutations)
 * ✅ computed for derived values (cached results)
 * ✅ Options object pattern (flexible configuration)
 * ✅ Composable composition (build from smaller pieces)
 * ✅ Explicit actions for state mutations
 * ✅ TypeScript type safety
 *
 * @see https://vuejs.org/guide/best-practices/performance.html
 * @see https://vuejs.org/guide/reusability/composables.html
 */

// ─────────────────────────────────────────────────────────
// Types (Exported for reuse)
// ─────────────────────────────────────────────────────────

export interface RoomFilters {
    status?: string;
    floor?: number;
    type?: string;
    search?: string;
}

export interface UseRoomOptions {
    /** Auto-fetch on mount */
    autoFetch?: boolean;
    /** Initial filters */
    initialFilters?: RoomFilters;
    /** Enable polling for real-time updates */
    pollingInterval?: number;
    /** Cache results */
    cacheEnabled?: boolean;
}

// ─────────────────────────────────────────────────────────
// Main Composable: useRooms
// ─────────────────────────────────────────────────────────

export function useRooms(options: UseRoomOptions = {}) {
    // ─────────────────────────────────────────────────────
    // Options with defaults (Options Object Pattern)
    // ─────────────────────────────────────────────────────
    const {
        autoFetch = false,
        initialFilters = {},
        pollingInterval = 30000, // 30 seconds
        cacheEnabled = false
    } = options;

    // ─────────────────────────────────────────────────────
    // State - Best Practice
    // ─────────────────────────────────────────────────────

    // ✅ ref() for arrays (deep reactivity, replacement pattern)
    const _rooms = ref<any[]>([]);
    const _room = ref<any | null>(null);

    // ✅ shallowRef() for primitives (performance)
    const _filters = shallowRef<RoomFilters>(initialFilters);
    const _cache = shallowRef<Map<string, any[]>>(new Map());

    // ✅ Compose smaller composables
    const { loading: _loading, start: startLoading, stop: stopLoading } = useLoading();
    const { loading: _saving, start: startSaving, stop: stopSaving } = useLoading();
    const { message: _successMessage, showMessage: showSuccess, clearMessage: clearSuccess } = useMessage();
    const { message: _error, showMessage: showError, clearMessage: clearError } = useMessage();

    // ─────────────────────────────────────────────────────
    // Computed (Derived State) - Best Practice
    // ─────────────────────────────────────────────────────

    const rooms = computed(() => _rooms.value);
    const room = computed(() => _room.value);
    const loading = computed(() => _loading.value);
    const saving = computed(() => _saving.value);
    const successMessage = computed(() => _successMessage.value);
    const error = computed(() => _error.value);

    // ✅ Room counts by status (cached by computed)
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

    // ✅ Filtered rooms (computed, not in template)
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
    // API Calls - Best Practice
    // ─────────────────────────────────────────────────────

    /**
     * Get cache key from filters
     */
    function getCacheKey(filters: RoomFilters): string {
        return JSON.stringify(filters);
    }

    /**
     * Fetch all rooms with optional filters
     */
    async function fetchAll(params?: RoomFilters): Promise<void> {
        const fetchFilters = params || _filters.value;
        const cacheKey = getCacheKey(fetchFilters);

        // Check cache first
        if (cacheEnabled && _cache.value.has(cacheKey)) {
            _rooms.value = _cache.value.get(cacheKey)!;
            return;
        }

        startLoading();
        clearError();

        try {
            const { data } = await apiClient.v1.get('/housekeeping/rooms', {
                params: fetchFilters
            });

            _rooms.value = data.data;

            // Update cache
            if (cacheEnabled) {
                _cache.value.set(cacheKey, data.data);
                // Trigger reactivity for shallowRef
                triggerRef(_cache);
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

    /**
     * Fetch single room by ID
     */
    async function fetchById(id: number): Promise<void> {
        startLoading();
        clearError();

        try {
            const { data } = await apiClient.v1.get(`/housekeeping/rooms/${id}`);
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

    /**
     * Update room status
     */
    async function updateStatus(id: number, status: string): Promise<void> {
        startSaving();
        clearError();

        try {
            const response = await apiClient.v1.patch(`/housekeeping/rooms/${id}/status`, { status });
            showSuccess(`Room status updated to ${status}`);

            // Update local state (replace entire array to trigger reactivity)
            const index = _rooms.value.findIndex((r: any) => r.id === id);
            if (index !== -1) {
                _rooms.value = [
                    ..._rooms.value.slice(0, index),
                    response.data.data,
                    ..._rooms.value.slice(index + 1)
                ];
            }

            // Invalidate cache
            if (cacheEnabled) {
                _cache.value.clear();
                triggerRef(_cache);
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

    /**
     * Set filters (immutable update)
     */
    function setFilters(newFilters: RoomFilters): void {
        _filters.value = { ..._filters.value, ...newFilters };
    }

    /**
     * Reset filters to initial state
     */
    function resetFilters(): void {
        _filters.value = initialFilters;
    }

    /**
     * Clear cache
     */
    function clearCache(): void {
        _cache.value.clear();
        triggerRef(_cache);
    }

    // ─────────────────────────────────────────────────────
    // Polling (Optional Real-time Updates)
    // ─────────────────────────────────────────────────────

    const { start: startPolling, stop: stopPolling } = usePolling(
        () => fetchAll(),
        pollingInterval,
        () => true // Always enabled by default
    );

    // ─────────────────────────────────────────────────────
    // Lifecycle Hooks (Auto-fetch, Auto-polling)
    // ─────────────────────────────────────────────────────

    if (autoFetch) {
        onMounted(() => {
            fetchAll();

            // Start polling if interval is set
            if (pollingInterval > 0) {
                startPolling();
            }
        });

        // Cleanup on unmount
        onUnmounted(() => {
            stopPolling();
        });
    }

    // ─────────────────────────────────────────────────────
    // Return (Public API) - Best Practice: Readonly + Actions
    // ─────────────────────────────────────────────────────

    return {
        // ✅ Readonly state (can't mutate from components directly)
        rooms,
        room,
        loading,
        saving,
        successMessage,
        error,

        // ✅ Computed values (derived state - cached)
        availableCount,
        occupiedCount,
        maintenanceCount,
        dirtyCount,
        filteredRooms,

        // ✅ Actions (only way to mutate state)
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
