import { ref, shallowRef, readonly, computed, triggerRef, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { useLoading, useMessage, usePolling } from '@/Helpers';

/**
 * Reservation Composable - Best Practices Implementation
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

export interface ReservationFilters {
    status?: string;
    check_in_date?: string;
    check_out_date?: string;
    search?: string;
}

export interface UseReservationOptions {
    /** Auto-fetch on mount */
    autoFetch?: boolean;
    /** Initial filters */
    initialFilters?: ReservationFilters;
    /** Enable polling for real-time updates */
    pollingInterval?: number;
    /** Cache results */
    cacheEnabled?: boolean;
}

// ─────────────────────────────────────────────────────────
// Main Composable: useReservations
// ─────────────────────────────────────────────────────────

export function useReservations(options: UseReservationOptions = {}) {
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
    const _reservations = ref<PMS.Reservation[]>([]);
    const _reservation = ref<PMS.Reservation | null>(null);

    // ✅ shallowRef() for primitives (performance)
    const _filters = shallowRef<ReservationFilters>(initialFilters);
    const _cache = shallowRef<Map<string, PMS.Reservation[]>>(new Map());

    // ✅ Compose smaller composables
    const { loading: _loading, start: startLoading, stop: stopLoading } = useLoading();
    const { loading: _saving, start: startSaving, stop: stopSaving } = useLoading();
    const { message: _successMessage, showMessage: showSuccess, clearMessage: clearSuccess } = useMessage();
    const { message: _error, showMessage: showError, clearMessage: clearError } = useMessage();

    // ─────────────────────────────────────────────────────
    // Computed (Derived State) - Best Practice
    // ─────────────────────────────────────────────────────

    const reservations = computed(() => _reservations.value);
    const reservation = computed(() => _reservation.value);
    const loading = computed(() => _loading.value);
    const saving = computed(() => _saving.value);
    const successMessage = computed(() => _successMessage.value);
    const error = computed(() => _error.value);

    // ✅ Derived counts (cached by computed)
    const pendingCount = computed(() =>
        _reservations.value.filter(r => r.status === 'pending').length
    );

    const confirmedCount = computed(() =>
        _reservations.value.filter(r => r.status === 'confirmed').length
    );

    const checkedInCount = computed(() =>
        _reservations.value.filter(r => r.status === 'checked_in').length
    );

    const checkedOutCount = computed(() =>
        _reservations.value.filter(r => r.status === 'checked_out').length
    );

    const cancelledCount = computed(() =>
        _reservations.value.filter(r => r.status === 'cancelled').length
    );

    // ✅ Derived lists with date calculations
    const todayCheckIns = computed(() => {
        const today = new Date().toISOString().split('T')[0];
        return _reservations.value.filter(r => r.check_in_date === today);
    });

    const todayCheckOuts = computed(() => {
        const today = new Date().toISOString().split('T')[0];
        return _reservations.value.filter(r => r.check_out_date === today);
    });

    const upcomingCheckIns = computed(() => {
        const today = new Date().toISOString().split('T')[0];
        return _reservations.value
            .filter(r => r.check_in_date > today && r.status === 'confirmed')
            .sort((a, b) => a.check_in_date.localeCompare(b.check_in_date));
    });

    // ✅ Filtered reservations (computed, not in template)
    const filteredReservations = computed(() => {
        let filtered = [..._reservations.value];
        const filters = _filters.value;

        // Filter by status
        if (filters.status) {
            filtered = filtered.filter(r => r.status === filters.status);
        }

        // Filter by check-in date
        if (filters.check_in_date) {
            filtered = filtered.filter(r => r.check_in_date >= filters.check_in_date!);
        }

        // Filter by check-out date
        if (filters.check_out_date) {
            filtered = filtered.filter(r => r.check_out_date <= filters.check_out_date!);
        }

        // Search by reference or guest name
        if (filters.search) {
            const search = filters.search.toLowerCase();
            filtered = filtered.filter(r =>
                r.reference.toLowerCase().includes(search) ||
                r.guest?.name.toLowerCase().includes(search)
            );
        }

        return filtered;
    });

    // ✅ Revenue calculations
    const totalRevenue = computed(() =>
        _reservations.value.reduce((sum, r) => sum + r.total_amount, 0)
    );

    const pendingRevenue = computed(() =>
        _reservations.value
            .filter(r => r.status === 'pending')
            .reduce((sum, r) => sum + r.total_amount, 0)
    );

    // ─────────────────────────────────────────────────────
    // API Calls - Best Practice
    // ─────────────────────────────────────────────────────

    /**
     * Get cache key from filters
     */
    function getCacheKey(filters: ReservationFilters): string {
        return JSON.stringify(filters);
    }

    /**
     * Fetch all reservations with optional filters
     */
    async function fetchAll(params?: ReservationFilters): Promise<void> {
        const fetchFilters = params || _filters.value;
        const cacheKey = getCacheKey(fetchFilters);

        // Check cache first
        if (cacheEnabled && _cache.value.has(cacheKey)) {
            _reservations.value = _cache.value.get(cacheKey)!;
            return;
        }

        startLoading();
        clearError();

        try {
            const { data } = await axios.get('/api/v1/front-desk/reservations', {
                params: fetchFilters
            });

            _reservations.value = data.data;

            // Update cache
            if (cacheEnabled) {
                _cache.value.set(cacheKey, data.data);
                // Trigger reactivity for shallowRef
                triggerRef(_cache);
            }

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch reservations';
            showError(message);
            console.error('Fetch reservations error:', err);
            throw err;
        } finally {
            stopLoading();
        }
    }

    /**
     * Fetch single reservation by ID
     */
    async function fetchById(id: number): Promise<void> {
        startLoading();
        clearError();

        try {
            const { data } = await axios.get(`/api/v1/reservations/${id}`);
            _reservation.value = data.data;
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch reservation';
            showError(message);
            console.error('Fetch reservation error:', err);
            throw err;
        } finally {
            stopLoading();
        }
    }

    /**
     * Create new reservation
     */
    async function create(data: {
        guest_id: number;
        room_id: number;
        check_in_date: string;
        check_out_date: string;
        total_amount: number;
        notes?: string;
    }): Promise<void> {
        startSaving();
        clearError();

        try {
            const response = await axios.post('/api/v1/reservations', data);
            showSuccess('Reservation created successfully');

            // Invalidate cache
            if (cacheEnabled) {
                _cache.value.clear();
                triggerRef(_cache);
            }

            // Refresh list
            await fetchAll();

            return response.data;
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to create reservation';
            showError(message);
            console.error('Create reservation error:', err);
            throw err;
        } finally {
            stopSaving();
        }
    }

    /**
     * Update existing reservation
     */
    async function update(id: number, data: Partial<PMS.Reservation>): Promise<void> {
        startSaving();
        clearError();

        try {
            const response = await axios.put(`/api/v1/reservations/${id}`, data);
            showSuccess('Reservation updated successfully');

            // Update local state (replace entire array to trigger reactivity)
            const index = _reservations.value.findIndex(r => r.id === id);
            if (index !== -1) {
                _reservations.value = [
                    ..._reservations.value.slice(0, index),
                    response.data.data,
                    ..._reservations.value.slice(index + 1)
                ];
            }

            // Invalidate cache
            if (cacheEnabled) {
                _cache.value.clear();
                triggerRef(_cache);
            }
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to update reservation';
            showError(message);
            console.error('Update reservation error:', err);
            throw err;
        } finally {
            stopSaving();
        }
    }

    /**
     * Cancel reservation
     */
    async function cancel(id: number): Promise<void> {
        startSaving();
        clearError();

        try {
            await axios.post(`/api/v1/reservations/${id}/cancel`);
            showSuccess('Reservation cancelled successfully');

            // Update local state
            const index = _reservations.value.findIndex(r => r.id === id);
            if (index !== -1) {
                _reservations.value = [
                    ..._reservations.value.slice(0, index),
                    { ..._reservations.value[index], status: 'cancelled' },
                    ..._reservations.value.slice(index + 1)
                ];
            }

            // Invalidate cache
            if (cacheEnabled) {
                _cache.value.clear();
                triggerRef(_cache);
            }
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to cancel reservation';
            showError(message);
            console.error('Cancel reservation error:', err);
            throw err;
        } finally {
            stopSaving();
        }
    }

    /**
     * Guest Check In
     */
    async function checkIn(id: number): Promise<void> {
        startSaving();
        clearError();

        try {
            const response = await axios.post(`/api/v1/reservations/${id}/check-in`);
            showSuccess('Guest checked in successfully');

            // Update local state
            const index = _reservations.value.findIndex(r => r.id === id);
            if (index !== -1) {
                _reservations.value = [
                    ..._reservations.value.slice(0, index),
                    { ..._reservations.value[index], status: 'checked_in' },
                    ..._reservations.value.slice(index + 1)
                ];
            }

            // Invalidate cache
            if (cacheEnabled) {
                _cache.value.clear();
                triggerRef(_cache);
            }
        } catch (err: any) {
            const message = err.response?.data?.message || 'Check in failed';
            showError(message);
            console.error('Check in error:', err);
            throw err;
        } finally {
            stopSaving();
        }
    }

    /**
     * Guest Check Out
     */
    async function checkOut(id: number, paymentData?: {
        paid_amount: number;
        payment_method: string;
    }): Promise<void> {
        startSaving();
        clearError();

        try {
            const response = await axios.post(`/api/v1/reservations/${id}/check-out`, paymentData);
            showSuccess('Guest checked out successfully');

            // Update local state
            const index = _reservations.value.findIndex(r => r.id === id);
            if (index !== -1) {
                _reservations.value = [
                    ..._reservations.value.slice(0, index),
                    { ..._reservations.value[index], status: 'checked_out' },
                    ..._reservations.value.slice(index + 1)
                ];
            }

            // Invalidate cache
            if (cacheEnabled) {
                _cache.value.clear();
                triggerRef(_cache);
            }
        } catch (err: any) {
            const message = err.response?.data?.message || 'Check out failed';
            showError(message);
            console.error('Check out error:', err);
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
    function setFilters(newFilters: ReservationFilters): void {
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
        reservations,
        reservation,
        loading,
        saving,
        successMessage,
        error,

        // ✅ Computed values (derived state - cached)
        pendingCount,
        confirmedCount,
        checkedInCount,
        checkedOutCount,
        cancelledCount,
        todayCheckIns,
        todayCheckOuts,
        upcomingCheckIns,
        filteredReservations,
        totalRevenue,
        pendingRevenue,

        // ✅ Actions (only way to mutate state)
        fetchAll,
        fetchById,
        create,
        update,
        cancel,
        checkIn,
        checkOut,
        setFilters,
        resetFilters,
        clearCache,
        clearError,
        clearSuccess
    };
}
