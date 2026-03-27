import { ref, shallowRef, readonly, computed, triggerRef, onMounted, onUnmounted } from 'vue';
import { apiClient } from '@/Services';
import { useLoading, useMessage, usePolling } from '@/Helpers';

/**
 * Guest Composable - Best Practices Implementation
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

export interface GuestFilters {
    search?: string;
    email?: string;
    phone?: string;
}

export interface UseGuestOptions {
    /** Auto-fetch on mount */
    autoFetch?: boolean;
    /** Initial filters */
    initialFilters?: GuestFilters;
    /** Enable polling for real-time updates */
    pollingInterval?: number;
    /** Cache results */
    cacheEnabled?: boolean;
}

// ─────────────────────────────────────────────────────────
// Main Composable: useGuests
// ─────────────────────────────────────────────────────────

export function useGuests(options: UseGuestOptions = {}) {
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
    const _guests = ref<any[]>([]);
    const _guest = ref<any | null>(null);

    // ✅ shallowRef() for primitives (performance)
    const _filters = shallowRef<GuestFilters>(initialFilters);
    const _cache = shallowRef<Map<string, any[]>>(new Map());

    // ✅ Compose smaller composables
    const { loading: _loading, start: startLoading, stop: stopLoading } = useLoading();
    const { loading: _saving, start: startSaving, stop: stopSaving } = useLoading();
    const { message: _successMessage, showMessage: showSuccess, clearMessage: clearSuccess } = useMessage();
    const { message: _error, showMessage: showError, clearMessage: clearError } = useMessage();

    // ─────────────────────────────────────────────────────
    // Computed (Derived State) - Best Practice
    // ─────────────────────────────────────────────────────

    const guests = computed(() => _guests.value);
    const guest = computed(() => _guest.value);
    const loading = computed(() => _loading.value);
    const saving = computed(() => _saving.value);
    const successMessage = computed(() => _successMessage.value);
    const error = computed(() => _error.value);

    // ✅ Guest counts (cached by computed)
    const totalGuests = computed(() => _guests.value.length);

    // ✅ Filtered guests (computed, not in template)
    const filteredGuests = computed(() => {
        let filtered = [..._guests.value];
        const filters = _filters.value;

        if (filters.search) {
            const search = filters.search.toLowerCase();
            filtered = filtered.filter((g: any) =>
                g.name?.toLowerCase().includes(search) ||
                g.email?.toLowerCase().includes(search)
            );
        }

        if (filters.email) {
            filtered = filtered.filter((g: any) => g.email === filters.email);
        }

        if (filters.phone) {
            filtered = filtered.filter((g: any) => g.phone === filters.phone);
        }

        return filtered;
    });

    // ─────────────────────────────────────────────────────
    // API Calls - Best Practice
    // ─────────────────────────────────────────────────────

    /**
     * Get cache key from filters
     */
    function getCacheKey(filters: GuestFilters): string {
        return JSON.stringify(filters);
    }

    /**
     * Fetch all guests with optional filters
     */
    async function fetchAll(params?: GuestFilters): Promise<void> {
        const fetchFilters = params || _filters.value;
        const cacheKey = getCacheKey(fetchFilters);

        // Check cache first
        if (cacheEnabled && _cache.value.has(cacheKey)) {
            _guests.value = _cache.value.get(cacheKey)!;
            return;
        }

        startLoading();
        clearError();

        try {
            const { data } = await apiClient.v1.get('/guests/profiles', {
                params: fetchFilters
            });

            _guests.value = data.data;

            // Update cache
            if (cacheEnabled) {
                _cache.value.set(cacheKey, data.data);
                // Trigger reactivity for shallowRef
                triggerRef(_cache);
            }

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch guests';
            showError(message);
            console.error('Fetch guests error:', err);
            throw err;
        } finally {
            stopLoading();
        }
    }

    /**
     * Fetch single guest by ID
     */
    async function fetchById(id: number): Promise<void> {
        startLoading();
        clearError();

        try {
            const { data } = await apiClient.v1.get(`/guests/profiles/${id}`);
            _guest.value = data.data;
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch guest';
            showError(message);
            console.error('Fetch guest error:', err);
            throw err;
        } finally {
            stopLoading();
        }
    }

    /**
     * Create new guest
     */
    async function create(data: any): Promise<void> {
        startSaving();
        clearError();

        try {
            const response = await apiClient.v1.post('/guests/profiles', data);
            showSuccess('Guest created successfully');

            // Invalidate cache
            if (cacheEnabled) {
                _cache.value.clear();
                triggerRef(_cache);
            }

            // Refresh list
            await fetchAll();

            return response.data;
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to create guest';
            showError(message);
            console.error('Create guest error:', err);
            throw err;
        } finally {
            stopSaving();
        }
    }

    /**
     * Update existing guest
     */
    async function update(id: number, data: any): Promise<void> {
        startSaving();
        clearError();

        try {
            const response = await apiClient.v1.put(`/guests/profiles/${id}`, data);
            showSuccess('Guest updated successfully');

            // Update local state (replace entire array to trigger reactivity)
            const index = _guests.value.findIndex((g: any) => g.id === id);
            if (index !== -1) {
                _guests.value = [
                    ..._guests.value.slice(0, index),
                    response.data.data,
                    ..._guests.value.slice(index + 1)
                ];
            }

            // Invalidate cache
            if (cacheEnabled) {
                _cache.value.clear();
                triggerRef(_cache);
            }
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to update guest';
            showError(message);
            console.error('Update guest error:', err);
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
    function setFilters(newFilters: GuestFilters): void {
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
        guests,
        guest,
        loading,
        saving,
        successMessage,
        error,

        // ✅ Computed values (derived state - cached)
        totalGuests,
        filteredGuests,

        // ✅ Actions (only way to mutate state)
        fetchAll,
        fetchById,
        create,
        update,
        setFilters,
        resetFilters,
        clearCache,
        clearError,
        clearSuccess
    };
}
