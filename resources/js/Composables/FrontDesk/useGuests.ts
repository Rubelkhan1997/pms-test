import { ref, shallowRef, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useLoading, useMessage, usePolling } from '@/Helpers';

/**
 * Guest Composable - Business Logic for Guest Management
 */

// ─────────────────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────────────────

export interface GuestFilters {
    search?: string;
    email?: string;
    phone?: string;
    loyalty_member?: boolean;
}

export interface UseGuestOptions {
    autoFetch?: boolean;
    initialFilters?: GuestFilters;
    pollingInterval?: number;
    cacheEnabled?: boolean;
}

// ─────────────────────────────────────────────────────────
// Main Composable: useGuests
// ─────────────────────────────────────────────────────────

export function useGuests(options: UseGuestOptions = {}) {
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
    const _guests = ref<PMS.Guest[]>([]);
    const _guest = ref<PMS.Guest | null>(null);
    const _filters = shallowRef<GuestFilters>(initialFilters);
    const _cache = shallowRef<Map<string, PMS.Guest[]>>(new Map());

    // Compose smaller composables
    const { loading: _loading, start: startLoading, stop: stopLoading } = useLoading();
    const { loading: _saving, start: startSaving, stop: stopSaving } = useLoading();
    const { message: _successMessage, showMessage: showSuccess, clearMessage: clearSuccess } = useMessage();
    const { message: _error, showMessage: showError, clearMessage: clearError } = useMessage();

    // ─────────────────────────────────────────────────────
    // Computed (Derived State)
    // ─────────────────────────────────────────────────────
    const guests = computed(() => _guests.value);
    const guest = computed(() => _guest.value);
    const loading = computed(() => _loading.value);
    const saving = computed(() => _saving.value);
    const successMessage = computed(() => _successMessage.value);
    const error = computed(() => _error.value);

    // Guest counts
    const totalGuests = computed(() => _guests.value.length);

    const loyaltyMembersCount = computed(() =>
        _guests.value.filter(g => (g.loyalty_points ?? 0) > 0).length
    );

    // Filtered guests
    const filteredGuests = computed(() => {
        let filtered = [..._guests.value];
        const filters = _filters.value;

        if (filters.search) {
            const search = filters.search.toLowerCase();
            filtered = filtered.filter(g =>
                g.name.toLowerCase().includes(search) ||
                g.email.toLowerCase().includes(search) ||
                (g.nid && g.nid.toLowerCase().includes(search))
            );
        }

        if (filters.email) {
            filtered = filtered.filter(g => g.email === filters.email);
        }

        if (filters.phone) {
            filtered = filtered.filter(g => g.phone === filters.phone);
        }

        return filtered;
    });

    // ─────────────────────────────────────────────────────
    // API Calls
    // ─────────────────────────────────────────────────────

    async function fetchAll(params?: GuestFilters): Promise<void> {
        const fetchFilters = params || _filters.value;
        const cacheKey = JSON.stringify(fetchFilters);

        if (cacheEnabled && _cache.value.has(cacheKey)) {
            _guests.value = _cache.value.get(cacheKey)!;
            return;
        }

        startLoading();
        clearError();

        try {
            const { data } = await axios.get('/api/v1/guests', {
                params: fetchFilters
            });

            _guests.value = data.data;

            if (cacheEnabled) {
                _cache.value.set(cacheKey, data.data);
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

    async function fetchById(id: number): Promise<void> {
        startLoading();
        clearError();

        try {
            const { data } = await axios.get(`/api/v1/guests/${id}`);
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

    async function create(data: {
        name: string;
        email: string;
        phone: string;
        nid?: string;
        address?: string;
        notes?: string;
    }): Promise<void> {
        startSaving();
        clearError();

        try {
            const response = await axios.post('/api/v1/guests', data);
            showSuccess('Guest created successfully');

            _guests.value.unshift(response.data.data);

            if (cacheEnabled) {
                _cache.value.clear();
            }
        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to create guest';
            showError(message);
            console.error('Create guest error:', err);
            throw err;
        } finally {
            stopSaving();
        }
    }

    async function update(id: number, data: Partial<PMS.Guest>): Promise<void> {
        startSaving();
        clearError();

        try {
            const response = await axios.put(`/api/v1/guests/${id}`, data);
            showSuccess('Guest updated successfully');

            const index = _guests.value.findIndex(g => g.id === id);
            if (index !== -1) {
                _guests.value = [
                    ..._guests.value.slice(0, index),
                    response.data.data,
                    ..._guests.value.slice(index + 1)
                ];
            }

            if (cacheEnabled) {
                _cache.value.clear();
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

    function setFilters(newFilters: GuestFilters): void {
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
        guests,
        guest,
        loading,
        saving,
        successMessage,
        error,

        // Computed values
        totalGuests,
        loyaltyMembersCount,
        filteredGuests,

        // Actions
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
