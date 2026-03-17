import { ref, shallowRef, computed } from 'vue';
import { useLoading, useMessage } from '@/Helpers';

/**
 * Guest Composable - Business Logic for Guest Management
 * 
 * Note: This is a base template. Update API endpoints as needed.
 */

// ─────────────────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────────────────

export interface GuestFilters {
    search?: string;
    email?: string;
    phone?: string;
}

export interface UseGuestOptions {
    autoFetch?: boolean;
    initialFilters?: GuestFilters;
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
        initialFilters = {}
    } = options;

    // ─────────────────────────────────────────────────────
    // State
    // ─────────────────────────────────────────────────────
    const _guests = ref<any[]>([]);
    const _guest = ref<any | null>(null);
    const _filters = shallowRef<GuestFilters>(initialFilters);

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

    // Filtered guests
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
    // Actions (Update with your API endpoints)
    // ─────────────────────────────────────────────────────

    async function fetchAll(params?: GuestFilters): Promise<void> {
        const fetchFilters = params || _filters.value;

        startLoading();
        clearError();

        try {
            // TODO: Update with your actual API endpoint
            // const { data } = await axios.get('/api/v1/guests', {
            //     params: fetchFilters
            // });
            // _guests.value = data.data;

            // Mock data for base project
            _guests.value = [];

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch guests';
            showError(message);
            console.error('Fetch guests error:', err);
        } finally {
            stopLoading();
        }
    }

    async function fetchById(id: number): Promise<void> {
        startLoading();
        clearError();

        try {
            // TODO: Update with your actual API endpoint
            // const { data } = await axios.get(`/api/v1/guests/${id}`);
            // _guest.value = data.data;

            _guest.value = null;

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to fetch guest';
            showError(message);
            console.error('Fetch guest error:', err);
        } finally {
            stopLoading();
        }
    }

    async function create(data: any): Promise<void> {
        startSaving();
        clearError();

        try {
            // TODO: Update with your actual API endpoint
            // const response = await axios.post('/api/v1/guests', data);
            showSuccess('Guest created successfully');

            // _guests.value.unshift(response.data.data);

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to create guest';
            showError(message);
            console.error('Create guest error:', err);
        } finally {
            stopSaving();
        }
    }

    async function update(id: number, data: any): Promise<void> {
        startSaving();
        clearError();

        try {
            // TODO: Update with your actual API endpoint
            // const response = await axios.put(`/api/v1/guests/${id}`, data);
            showSuccess('Guest updated successfully');

            const index = _guests.value.findIndex((g: any) => g.id === id);
            if (index !== -1) {
                _guests.value = [
                    ..._guests.value.slice(0, index),
                    data,
                    ..._guests.value.slice(index + 1)
                ];
            }

        } catch (err: any) {
            const message = err.response?.data?.message || 'Failed to update guest';
            showError(message);
            console.error('Update guest error:', err);
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
        filteredGuests,

        // Actions
        fetchAll,
        fetchById,
        create,
        update,
        setFilters,
        resetFilters,
        clearError,
        clearSuccess
    };
}
