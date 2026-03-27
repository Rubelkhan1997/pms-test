import { defineStore } from 'pinia';
import { apiClient } from '@/Services';

/**
 * Reservation Store (Pinia)
 * Global state management for reservations
 *
 * Best Practices Applied:
 * - Minimal state, derive rest with getters
 * - Explicit actions for mutations
 * - Typed state and actions
 * - Use apiClient for API calls
 */
export const useReservationsStore = defineStore('reservations', {
    // ─────────────────────────────────────────────────────────
    // State (Reactive Data) - Best Practice
    // ─────────────────────────────────────────────────────────
    state: () => ({
        // ✅ ref() for arrays (replacement pattern)
        reservations: [] as PMS.Reservation[],
        
        // Currently selected reservation
        selectedReservation: null as PMS.Reservation | null,
        
        // ✅ shallowRef equivalent for loading states (via state)
        loading: false,
        loadingList: false,
        loadingDetail: false,
        
        // Error states
        error: null as string | null,
        
        // ✅ Use separate state for filters (will be reactive)
        filters: {
            status: '',
            check_in_date: '',
            check_out_date: '',
            search: ''
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
         * Get reservations by status
         */
        byStatus: (state) => {
            return (status: string) => state.reservations.filter(r => r.status === status);
        },

        /**
         * Get pending reservations count - Derived state
         */
        pendingCount: (state) => {
            return state.reservations.filter(r => r.status === 'pending').length;
        },

        /**
         * Get confirmed reservations count - Derived state
         */
        confirmedCount: (state) => {
            return state.reservations.filter(r => r.status === 'confirmed').length;
        },

        /**
         * Get checked in reservations count - Derived state
         */
        checkedInCount: (state) => {
            return state.reservations.filter(r => r.status === 'checked_in').length;
        },

        /**
         * Get today's check-ins - Derived state
         */
        todayCheckIns: (state) => {
            const today = new Date().toISOString().split('T')[0];
            return state.reservations.filter(r => r.check_in_date === today);
        },

        /**
         * Get today's check-outs - Derived state
         */
        todayCheckOuts: (state) => {
            const today = new Date().toISOString().split('T')[0];
            return state.reservations.filter(r => r.check_out_date === today);
        },

        /**
         * Get filtered reservations - Derived state with multiple criteria
         */
        filteredReservations: (state) => {
            let filtered = [...state.reservations];

            // Filter by status
            if (state.filters.status) {
                filtered = filtered.filter(r => r.status === state.filters.status);
            }

            // Filter by check-in date
            if (state.filters.check_in_date) {
                filtered = filtered.filter(r => r.check_in_date >= state.filters.check_in_date);
            }

            // Filter by check-out date
            if (state.filters.check_out_date) {
                filtered = filtered.filter(r => r.check_out_date <= state.filters.check_out_date);
            }

            // Search by reference or guest name
            if (state.filters.search) {
                const search = state.filters.search.toLowerCase();
                filtered = filtered.filter(r => 
                    r.reference.toLowerCase().includes(search) ||
                    r.guest?.name.toLowerCase().includes(search)
                );
            }

            return filtered;
        },

        /**
         * Get total revenue from reservations - Derived state
         */
        totalRevenue: (state) => {
            return state.reservations.reduce((sum, r) => sum + r.total_amount, 0);
        },

        /**
         * Get average stay duration - Derived state
         */
        averageStayDuration: (state) => {
            if (state.reservations.length === 0) return 0;
            
            const totalDays = state.reservations.reduce((sum, r) => {
                const checkIn = new Date(r.check_in_date);
                const checkOut = new Date(r.check_out_date);
                const days = Math.ceil((checkOut.getTime() - checkIn.getTime()) / (1000 * 60 * 60 * 24));
                return sum + days;
            }, 0);
            
            return Math.round(totalDays / state.reservations.length);
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
                status: '',
                check_in_date: '',
                check_out_date: '',
                search: ''
            };
        },

        /**
         * Fetch all reservations with pagination
         */
        async fetchAll(page: number = 1) {
            this.loadingList = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.get('/front-desk/reservations', {
                    params: {
                        ...this.filters,
                        page
                    }
                });

                this.reservations = data.data;
                this.pagination = {
                    current_page: data.current_page,
                    per_page: data.per_page,
                    total: data.total,
                    last_page: data.last_page
                };
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to fetch reservations';
                console.error('Fetch reservations error:', err);
            } finally {
                this.loadingList = false;
            }
        },

        /**
         * Fetch single reservation
         */
        async fetchById(id: number) {
            this.loadingDetail = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.get(`/front-desk/reservations/${id}`);
                this.selectedReservation = data.data;
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to fetch reservation';
                console.error('Fetch reservation error:', err);
            } finally {
                this.loadingDetail = false;
            }
        },

        /**
         * Set selected reservation
         */
        setSelectedReservation(reservation: PMS.Reservation | null) {
            this.selectedReservation = reservation;
        },

        /**
         * Clear selected reservation
         */
        clearSelectedReservation() {
            this.selectedReservation = null;
        },

        /**
         * Add reservation to list
         */
        addReservation(reservation: PMS.Reservation) {
            this.reservations.unshift(reservation);
            this.pagination.total++;
        },

        /**
         * Update reservation in list
         */
        updateReservation(id: number, data: Partial<PMS.Reservation>) {
            const index = this.reservations.findIndex(r => r.id === id);
            if (index !== -1) {
                this.reservations[index] = { ...this.reservations[index], ...data };
            }

            // Also update selected if it's the same
            if (this.selectedReservation?.id === id) {
                this.selectedReservation = { ...this.selectedReservation, ...data };
            }
        },

        /**
         * Remove reservation from list
         */
        removeReservation(id: number) {
            const index = this.reservations.findIndex(r => r.id === id);
            if (index !== -1) {
                this.reservations.splice(index, 1);
                this.pagination.total--;
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
                reservations: [],
                selectedReservation: null,
                loading: false,
                loadingList: false,
                loadingDetail: false,
                error: null,
                filters: {
                    status: '',
                    check_in_date: '',
                    check_out_date: '',
                    search: ''
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