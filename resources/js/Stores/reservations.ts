import { defineStore } from 'pinia';
import axios from 'axios';

/**
 * Reservation Store (Pinia)
 * Global state management for reservations
 */
export const useReservationsStore = defineStore('reservations', {
    // ─────────────────────────────────────────────────────────
    // State (Reactive Data)
    // ─────────────────────────────────────────────────────────
    state: () => ({
        // All reservations list
        reservations: [] as PMS.Reservation[],
        
        // Currently selected reservation
        selectedReservation: null as PMS.Reservation | null,
        
        // Loading states
        loading: false,
        loadingList: false,
        loadingDetail: false,
        
        // Error states
        error: null as string | null,
        
        // Filters
        filters: {
            status: '' as string,
            check_in_date: '' as string,
            check_out_date: '' as string,
            search: '' as string
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
    // Getters (Computed Properties)
    // ─────────────────────────────────────────────────────────
    getters: {
        /**
         * Get reservations by status
         */
        byStatus: (state) => {
            return (status: string) => state.reservations.filter(r => r.status === status);
        },

        /**
         * Get pending reservations count
         */
        pendingCount: (state) => {
            return state.reservations.filter(r => r.status === 'pending').length;
        },

        /**
         * Get confirmed reservations count
         */
        confirmedCount: (state) => {
            return state.reservations.filter(r => r.status === 'confirmed').length;
        },

        /**
         * Get checked in reservations count
         */
        checkedInCount: (state) => {
            return state.reservations.filter(r => r.status === 'checked_in').length;
        },

        /**
         * Get today's check-ins
         */
        todayCheckIns: (state) => {
            const today = new Date().toISOString().split('T')[0];
            return state.reservations.filter(r => r.check_in_date === today);
        },

        /**
         * Get today's check-outs
         */
        todayCheckOuts: (state) => {
            const today = new Date().toISOString().split('T')[0];
            return state.reservations.filter(r => r.check_out_date === today);
        },

        /**
         * Get reservations with search
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
         * Get total revenue from reservations
         */
        totalRevenue: (state) => {
            return state.reservations.reduce((sum, r) => sum + r.total_amount, 0);
        }
    },

    // ─────────────────────────────────────────────────────────
    // Actions (Methods)
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
                const { data } = await axios.get('/api/v1/front-desk/reservations', {
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
                const { data } = await axios.get(`/api/v1/reservations/${id}`);
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
         * Clear all state
         */
        $reset() {
            this.$reset();
        }
    }
});
