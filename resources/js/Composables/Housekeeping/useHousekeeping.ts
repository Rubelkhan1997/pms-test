import { ref, shallowRef, readonly, computed, triggerRef, onMounted, onUnmounted } from 'vue';
import { apiClient } from '@/Services';
import { useLoading, useMessage, usePolling } from '@/Helpers';

///////////// Fine Code Na /////////////

/**
 * Housekeeping Composable - Best Practices Implementation
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

export interface TaskFilters {
    status?: string;
    priority?: string;
    assigned_to?: number;
    room_id?: number;
}

export interface UseHousekeepingOptions {
    /** Auto-fetch on mount */
    autoFetch?: boolean;
    /** Initial filters */
    initialFilters?: TaskFilters;
    /** Enable polling for real-time updates */
    pollingInterval?: number;
    /** Cache results */
    cacheEnabled?: boolean;
}

// ─────────────────────────────────────────────────────────
// Main Composable: useHousekeeping
// ─────────────────────────────────────────────────────────

export function useHousekeeping(options: UseHousekeepingOptions = {}) {
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
    const _tasks = ref<any[]>([]);
    const _task = ref<any | null>(null);

    // ✅ shallowRef() for primitives (performance)
    const _filters = shallowRef<TaskFilters>(initialFilters);
    const _cache = shallowRef<Map<string, any[]>>(new Map());

    // ✅ Compose smaller composables
    const { loading: _loading, start: startLoading, stop: stopLoading } = useLoading();
    const { loading: _saving, start: startSaving, stop: stopSaving } = useLoading();
    const { message: _successMessage, showMessage: showSuccess, clearMessage: clearSuccess } = useMessage();
    const { message: _error, showMessage: showError, clearMessage: clearError } = useMessage();

    // ─────────────────────────────────────────────────────
    // Computed (Derived State) - Best Practice
    // ─────────────────────────────────────────────────────

    const tasks = computed(() => _tasks.value);
    const task = computed(() => _task.value);
    const loading = computed(() => _loading.value);
    const saving = computed(() => _saving.value);
    const successMessage = computed(() => _successMessage.value);
    const error = computed(() => _error.value);

    // ✅ Task counts by status (cached by computed)
    const pendingCount = computed(() =>
        _tasks.value.filter((t: any) => t.status === 'pending').length
    );

    const inProgressCount = computed(() =>
        _tasks.value.filter((t: any) => t.status === 'in_progress').length
    );

    const completedCount = computed(() =>
        _tasks.value.filter((t: any) => t.status === 'completed').length
    );

    // ✅ Filtered tasks (computed, not in template)
    const filteredTasks = computed(() => {
        let filtered = [..._tasks.value];
        const filters = _filters.value;

        if (filters.status) {
            filtered = filtered.filter((t: any) => t.status === filters.status);
        }

        if (filters.priority) {
            filtered = filtered.filter((t: any) => t.priority === filters.priority);
        }

        if (filters.room_id) {
            filtered = filtered.filter((t: any) => t.room_id === filters.room_id);
        }

        if (filters.assigned_to) {
            filtered = filtered.filter((t: any) => t.assigned_to === filters.assigned_to);
        }

        return filtered;
    });

    // ─────────────────────────────────────────────────────
    // API Calls - Best Practice
    // ─────────────────────────────────────────────────────

    /**
     * Get cache key from filters
     */
    function getCacheKey(filters: TaskFilters): string {
        return JSON.stringify(filters);
    }

    /**
     * Fetch all tasks with optional filters
     */
    async function fetchAll(params?: TaskFilters): Promise<void> {
        const fetchFilters = params || _filters.value;
        const cacheKey = getCacheKey(fetchFilters);

        // Check cache first
        if (cacheEnabled && _cache.value.has(cacheKey)) {
            _tasks.value = _cache.value.get(cacheKey)!;
            return;
        }

        startLoading();
        clearError();

        try {
            const { data } = await apiClient.v1.get('/housekeeping/tasks', {
                params: fetchFilters
            });

            _tasks.value = data.data;

            // Update cache
            if (cacheEnabled) {
                _cache.value.set(cacheKey, data.data);
                // Trigger reactivity for shallowRef
                triggerRef(_cache);
            }

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch tasks';
            showError(message);
            console.error('Fetch tasks error:', err);
            throw err;
        } finally {
            stopLoading();
        }
    }

    /**
     * Fetch single task by ID
     */
    async function fetchById(id: number): Promise<void> {
        startLoading();
        clearError();

        try {
            const { data } = await apiClient.v1.get(`/housekeeping/tasks/${id}`);
            _task.value = data.data;
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch task';
            showError(message);
            console.error('Fetch task error:', err);
            throw err;
        } finally {
            stopLoading();
        }
    }

    /**
     * Update task status
     */
    async function updateStatus(id: number, status: string): Promise<void> {
        startSaving();
        clearError();

        try {
            const response = await apiClient.v1.patch(`/housekeeping/tasks/${id}/status`, { status });
            showSuccess('Task status updated successfully');

            // Update local state (replace entire array to trigger reactivity)
            const index = _tasks.value.findIndex((t: any) => t.id === id);
            if (index !== -1) {
                _tasks.value = [
                    ..._tasks.value.slice(0, index),
                    response.data.data,
                    ..._tasks.value.slice(index + 1)
                ];
            }

            // Invalidate cache
            if (cacheEnabled) {
                _cache.value.clear();
                triggerRef(_cache);
            }
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to update task status';
            showError(message);
            console.error('Update task status error:', err);
            throw err;
        } finally {
            stopSaving();
        }
    }

    /**
     * Assign task to staff
     */
    async function assignTask(id: number, assignedTo: number): Promise<void> {
        startSaving();
        clearError();

        try {
            const response = await apiClient.v1.patch(`/housekeeping/tasks/${id}/assign`, {
                assigned_to: assignedTo
            });
            showSuccess('Task assigned successfully');

            // Update local state
            const index = _tasks.value.findIndex((t: any) => t.id === id);
            if (index !== -1) {
                _tasks.value = [
                    ..._tasks.value.slice(0, index),
                    response.data.data,
                    ..._tasks.value.slice(index + 1)
                ];
            }

            // Invalidate cache
            if (cacheEnabled) {
                _cache.value.clear();
                triggerRef(_cache);
            }
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to assign task';
            showError(message);
            console.error('Assign task error:', err);
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
    function setFilters(newFilters: TaskFilters): void {
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
        tasks,
        task,
        loading,
        saving,
        successMessage,
        error,

        // ✅ Computed values (derived state - cached)
        pendingCount,
        inProgressCount,
        completedCount,
        filteredTasks,

        // ✅ Actions (only way to mutate state)
        fetchAll,
        fetchById,
        updateStatus,
        assignTask,
        setFilters,
        resetFilters,
        clearCache,
        clearError,
        clearSuccess
    };
}
