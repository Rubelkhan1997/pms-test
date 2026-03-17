import { ref, shallowRef, computed } from 'vue';
import { useLoading, useMessage } from '@/Helpers';

/**
 * Housekeeping Composable - Business Logic for Housekeeping Management
 * 
 * Note: This is a base template. Update API endpoints as needed.
 */

// ─────────────────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────────────────

export interface TaskFilters {
    status?: string;
    priority?: string;
    assigned_to?: number;
    room_id?: number;
}

export interface UseHousekeepingOptions {
    autoFetch?: boolean;
    initialFilters?: TaskFilters;
}

// ─────────────────────────────────────────────────────────
// Main Composable: useHousekeeping
// ─────────────────────────────────────────────────────────

export function useHousekeeping(options: UseHousekeepingOptions = {}) {
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
    const _tasks = ref<any[]>([]);
    const _task = ref<any | null>(null);
    const _filters = shallowRef<TaskFilters>(initialFilters);

    // Compose smaller composables
    const { loading: _loading, start: startLoading, stop: stopLoading } = useLoading();
    const { loading: _saving, start: startSaving, stop: stopSaving } = useLoading();
    const { message: _successMessage, showMessage: showSuccess, clearMessage: clearSuccess } = useMessage();
    const { message: _error, showMessage: showError, clearMessage: clearError } = useMessage();

    // ─────────────────────────────────────────────────────
    // Computed (Derived State)
    // ─────────────────────────────────────────────────────
    const tasks = computed(() => _tasks.value);
    const task = computed(() => _task.value);
    const loading = computed(() => _loading.value);
    const saving = computed(() => _saving.value);
    const successMessage = computed(() => _successMessage.value);
    const error = computed(() => _error.value);

    // Task counts by status
    const pendingCount = computed(() =>
        _tasks.value.filter((t: any) => t.status === 'pending').length
    );

    const inProgressCount = computed(() =>
        _tasks.value.filter((t: any) => t.status === 'in_progress').length
    );

    const completedCount = computed(() =>
        _tasks.value.filter((t: any) => t.status === 'completed').length
    );

    // Filtered tasks
    const filteredTasks = computed(() => {
        let filtered = [..._tasks.value];
        const filters = _filters.value;

        if (filters.status) {
            filtered = filtered.filter((t: any) => t.status === filters.status);
        }

        if (filters.priority) {
            filtered = filtered.filter((t: any) => t.priority === filters.priority);
        }

        return filtered;
    });

    // ─────────────────────────────────────────────────────
    // Actions (Update with your API endpoints)
    // ─────────────────────────────────────────────────────

    async function fetchAll(params?: TaskFilters): Promise<void> {
        const fetchFilters = params || _filters.value;

        startLoading();
        clearError();

        try {
            // TODO: Update with your actual API endpoint
            // const { data } = await axios.get('/api/v1/housekeeping/tasks', {
            //     params: fetchFilters
            // });
            // _tasks.value = data.data;

            // Mock data for base project
            _tasks.value = [];

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch tasks';
            showError(message);
            console.error('Fetch tasks error:', err);
        } finally {
            stopLoading();
        }
    }

    async function fetchById(id: number): Promise<void> {
        startLoading();
        clearError();

        try {
            // TODO: Update with your actual API endpoint
            // const { data } = await axios.get(`/api/v1/housekeeping/tasks/${id}`);
            // _task.value = data.data;

            _task.value = null;

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch task';
            showError(message);
            console.error('Fetch task error:', err);
        } finally {
            stopLoading();
        }
    }

    async function updateStatus(id: number, status: string): Promise<void> {
        startSaving();
        clearError();

        try {
            // TODO: Update with your actual API endpoint
            // await axios.patch(`/api/v1/housekeeping/tasks/${id}/status`, { status });
            showSuccess('Task status updated successfully');

            const index = _tasks.value.findIndex((t: any) => t.id === id);
            if (index !== -1) {
                _tasks.value = [
                    ..._tasks.value.slice(0, index),
                    { ..._tasks.value[index], status },
                    ..._tasks.value.slice(index + 1)
                ];
            }

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to update task status';
            showError(message);
            console.error('Update task status error:', err);
        } finally {
            stopSaving();
        }
    }

    async function assignTask(id: number, assignedTo: number): Promise<void> {
        startSaving();
        clearError();

        try {
            // TODO: Update with your actual API endpoint
            // await axios.patch(`/api/v1/housekeeping/tasks/${id}/assign`, {
            //     assigned_to: assignedTo
            // });
            showSuccess('Task assigned successfully');

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to assign task';
            showError(message);
            console.error('Assign task error:', err);
        } finally {
            stopSaving();
        }
    }

    // ─────────────────────────────────────────────────────
    // Filter Management
    // ─────────────────────────────────────────────────────

    function setFilters(newFilters: TaskFilters): void {
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
        tasks,
        task,
        loading,
        saving,
        successMessage,
        error,

        // Computed values
        pendingCount,
        inProgressCount,
        completedCount,
        filteredTasks,

        // Actions
        fetchAll,
        fetchById,
        updateStatus,
        assignTask,
        setFilters,
        resetFilters,
        clearError,
        clearSuccess
    };
}
