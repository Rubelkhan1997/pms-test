import { defineStore } from 'pinia';
import { apiClient } from '@/Services';

export const useReservationsStore = defineStore('reservations', {
    // ─────────────────────────────────────────────────────────
    // State
    // ─────────────────────────────────────────────────────────
    state: () => ({
        reservations: [] as PMS.Reservation[],
        selectedReservation: null as PMS.Reservation | null,
        loading: false,
        loadingList: false,
        loadingDetail: false,
        error: null as string | null,
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
    }),

    // ─────────────────────────────────────────────────────────
    // Getters
    // ─────────────────────────────────────────────────────────
    getters: {
        byStatus: (state) => {
            return (status: string) => state.reservations.filter(r => r.status === status);
        },

        pendingCount: (state) =>
            state.reservations.filter(r => r.status === 'pending').length,

        confirmedCount: (state) =>
            state.reservations.filter(r => r.status === 'confirmed').length,

        checkedInCount: (state) =>
            state.reservations.filter(r => r.status === 'checked_in').length,

        checkedOutCount: (state) =>
            state.reservations.filter(r => r.status === 'checked_out').length,

        cancelledCount: (state) =>
            state.reservations.filter(r => r.status === 'cancelled').length,

        todayCheckIns: (state) => {
            const today = new Date().toISOString().split('T')[0];
            return state.reservations.filter(r => r.check_in_date === today);
        },

        todayCheckOuts: (state) => {
            const today = new Date().toISOString().split('T')[0];
            return state.reservations.filter(r => r.check_out_date === today);
        },

        filteredReservations: (state) => {
            let filtered = [...(state.reservations || [])];

            if (state.filters.status) {
                filtered = filtered.filter(r => r.status === state.filters.status);
            }
            if (state.filters.check_in_date) {
                filtered = filtered.filter(r => r.check_in_date >= state.filters.check_in_date);
            }
            if (state.filters.check_out_date) {
                filtered = filtered.filter(r => r.check_out_date <= state.filters.check_out_date);
            }
            if (state.filters.search) {
                const search = state.filters.search.toLowerCase();
                filtered = filtered.filter(r =>
                    r.reference.toLowerCase().includes(search) ||
                    r.guest?.name.toLowerCase().includes(search)
                );
            }

            return filtered;
        },

        totalRevenue: (state) =>
            state.reservations.reduce((sum, r) => sum + r.total_amount, 0),

        pendingRevenue: (state) =>
            state.reservations
                .filter(r => r.status === 'pending')
                .reduce((sum, r) => sum + r.total_amount, 0),

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
    // Actions
    // ─────────────────────────────────────────────────────────
    actions: {
        setFilters(filters: Partial<typeof this.filters>) {
            this.filters = { ...this.filters, ...filters };
        },

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
                    params: { ...this.filters, page }
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
                throw err;
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
                throw err;
            } finally {
                this.loadingDetail = false;
            }
        },

        /**
         * Create new reservation
         */
        async create(data: {
            hotel_id: number | string;
            guest_profile_id: number | string;
            room_id: number | string;
            check_in_date: string;
            check_out_date: string;
            total_amount: number;
            adults?: number;
            children?: number;
            status?: string;
            notes?: string;
        }) {
            this.loading = true;
            this.error = null;

            try {
                const { data: res } = await apiClient.v1.post('/front-desk/reservations', data);
                this.addReservation(res.data);
                return res.data;
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to create reservation';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Update existing reservation
         */
        async update(id: number, data: Partial<PMS.Reservation>) {
            this.loading = true;
            this.error = null;

            try {
                const { data: res } = await apiClient.v1.put(`/front-desk/reservations/${id}`, data);
                this.updateReservation(id, res.data);
                return res.data;
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to update reservation';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Cancel reservation
         */
        async cancel(id: number) {
            this.loading = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.patch(`/front-desk/reservations/${id}/cancel`);
                this.updateReservation(id, { status: 'cancelled' });
                return data;
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to cancel reservation';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Delete reservation
         */
        async delete(id: number) {
            this.loading = true;
            this.error = null;

            try {
                await apiClient.v1.delete(`/front-desk/reservations/${id}`);
                this.removeReservation(id);
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to delete reservation';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Check In guest
         */
        async checkIn(id: number) {
            this.loading = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.patch(`/front-desk/reservations/${id}/check-in`);
                this.updateReservation(id, { status: 'checked_in' });
                return data;
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Check in failed';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Check Out guest
         */
        async checkOut(id: number, paymentData?: any) {
            this.loading = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.patch(
                    `/front-desk/reservations/${id}/check-out`,
                    paymentData ?? {}
                );
                this.updateReservation(id, { status: 'checked_out' });
                return data;
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Check out failed';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        setSelectedReservation(reservation: PMS.Reservation | null) {
            this.selectedReservation = reservation;
        },

        clearSelectedReservation() {
            this.selectedReservation = null;
        },

        addReservation(reservation: PMS.Reservation) {
            this.reservations.unshift(reservation);
            this.pagination.total++;
        },

        updateReservation(id: number, data: Partial<PMS.Reservation>) {
            const index = this.reservations.findIndex(r => r.id === id);
            if (index !== -1) {
                this.reservations[index] = { ...this.reservations[index], ...data };
            }
            if (this.selectedReservation?.id === id) {
                this.selectedReservation = { ...this.selectedReservation, ...data };
            }
        },

        removeReservation(id: number) {
            const index = this.reservations.findIndex(r => r.id === id);
            if (index !== -1) {
                this.reservations.splice(index, 1);
                this.pagination.total--;
            }
        },

        clearError() {
            this.error = null;
        },

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