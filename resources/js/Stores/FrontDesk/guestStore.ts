import { defineStore } from 'pinia';
import { apiClient } from '@/Services';

/**
 * Guest Store (Pinia)
 * Global state management for guest profiles
 *
 * Best Practices Applied:
 * - Minimal state, derive rest with getters
 * - Explicit actions for mutations
 * - Typed state and actions
 * - Use apiClient for API calls
 */
export const useGuestsStore = defineStore('guests', {
    // ─────────────────────────────────────────────────────────
    // State (Reactive Data) - Best Practice
    // ─────────────────────────────────────────────────────────
    state: () => ({
        // Guests list
        guests: [] as any[],

        // Currently selected guest
        selectedGuest: null as any | null,

        // Loading states
        loading: false,
        loadingList: false,
        loadingDetail: false,

        // Error states
        error: null as string | null,

        // Filters
        filters: {
            search: '',
            email: '',
            phone: ''
        },

        // Pagination
        pagination: {
            current_page: 1,
            per_page: 15,
            total: 0,
            last_page: 1
        }
    }),

    // ─────────────────────────────────────────────────────────
    // Getters (Computed Properties) - Best Practice
    // ─────────────────────────────────────────────────────────
    getters: {
        /**
         * Get guests by search term
         */
        bySearch: (state) => {
            return (search: string) => {
                const searchLower = search.toLowerCase();
                return state.guests.filter(g =>
                    g.name?.toLowerCase().includes(searchLower) ||
                    g.email?.toLowerCase().includes(searchLower)
                );
            };
        },

        /**
         * Get total guests count - Derived state
         */
        totalGuests: (state) => {
            return state.guests.length;
        },

        /**
         * Get guests with email - Derived state
         */
        guestsWithEmail: (state) => {
            return state.guests.filter(g => g.email);
        },

        /**
         * Get guests with phone - Derived state
         */
        guestsWithPhone: (state) => {
            return state.guests.filter(g => g.phone);
        },

        /**
         * Get VIP guests - Derived state
         */
        vipGuests: (state) => {
            return state.guests.filter(g => g.is_vip);
        },

        /**
         * Get filtered guests - Derived state with multiple criteria
         */
        filteredGuests: (state) => {
            let filtered = [...state.guests];
            const filters = state.filters;

            // Search by name or email
            if (filters.search) {
                const search = filters.search.toLowerCase();
                filtered = filtered.filter(g =>
                    g.name?.toLowerCase().includes(search) ||
                    g.email?.toLowerCase().includes(search)
                );
            }

            // Filter by email
            if (filters.email) {
                filtered = filtered.filter(g => g.email === filters.email);
            }

            // Filter by phone
            if (filters.phone) {
                filtered = filtered.filter(g => g.phone === filters.phone);
            }

            return filtered;
        }
    },

    // ─────────────────────────────────────────────────────────
    // Actions (Methods) - Best Practice
    // ─────────────────────────────────────────────────────────
    actions: {
        /**
         * Set filters
         */
        setFilters(filters: Partial<typeof this.filters>) {
            this.filters = { ...this.filters, ...filters };
        },

        /**
         * Reset filters
         */
        resetFilters() {
            this.filters = {
                search: '',
                email: '',
                phone: ''
            };
        },

        /**
         * Fetch all guests with pagination
         */
        async fetchAll(page: number = 1) {
            this.loadingList = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.get('/guests/profiles', {
                    params: {
                        ...this.filters,
                        page
                    }
                });

                this.guests = data.data;
                this.pagination = {
                    current_page: data.current_page,
                    per_page: data.per_page,
                    total: data.total,
                    last_page: data.last_page
                };
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to fetch guests';
                console.error('Fetch guests error:', err);
            } finally {
                this.loadingList = false;
            }
        },

        /**
         * Fetch single guest
         */
        async fetchById(id: number) {
            this.loadingDetail = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.get(`/guests/profiles/${id}`);
                this.selectedGuest = data.data;
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to fetch guest';
                console.error('Fetch guest error:', err);
            } finally {
                this.loadingDetail = false;
            }
        },

        /**
         * Set selected guest
         */
        setSelectedGuest(guest: any | null) {
            this.selectedGuest = guest;
        },

        /**
         * Clear selected guest
         */
        clearSelectedGuest() {
            this.selectedGuest = null;
        },

        /**
         * Add guest to list
         */
        addGuest(guest: any) {
            this.guests.unshift(guest);
            this.pagination.total++;
        },

        /**
         * Update guest in list
         */
        updateGuest(id: number, data: any) {
            const index = this.guests.findIndex(g => g.id === id);
            if (index !== -1) {
                this.guests[index] = { ...this.guests[index], ...data };
            }

            // Also update selected if it's the same
            if (this.selectedGuest?.id === id) {
                this.selectedGuest = { ...this.selectedGuest, ...data };
            }
        },

        /**
         * Remove guest from list
         */
        removeGuest(id: number) {
            const index = this.guests.findIndex(g => g.id === id);
            if (index !== -1) {
                this.guests.splice(index, 1);
                this.pagination.total--;
            }
        },

        /**
         * Create new guest
         */
        async createGuest(data: any) {
            this.loading = true;
            this.error = null;

            try {
                const response = await apiClient.v1.post('/guests/profiles', data);
                
                // Add to list
                this.addGuest(response.data.data);
                
                return response.data;
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to create guest';
                console.error('Create guest error:', err);
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Update guest
         */
        async updateGuestDetail(id: number, data: any) {
            this.loading = true;
            this.error = null;

            try {
                const response = await apiClient.v1.put(`/guests/profiles/${id}`, data);
                
                // Update local state
                this.updateGuest(id, response.data.data);
                
                return response.data;
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to update guest';
                console.error('Update guest error:', err);
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Clear error
         */
        clearError() {
            this.error = null;
        },

        /**
         * Reset store to initial state
         */
        $reset() {
            this.$patch({
                guests: [],
                selectedGuest: null,
                loading: false,
                loadingList: false,
                loadingDetail: false,
                error: null,
                filters: {
                    search: '',
                    email: '',
                    phone: ''
                },
                pagination: {
                    current_page: 1,
                    per_page: 15,
                    total: 0,
                    last_page: 1
                }
            });
        }
    }
});
