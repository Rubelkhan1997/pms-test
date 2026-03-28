import { defineStore } from 'pinia';
import { apiClient } from '@/Services';

// ─────────────────────────────────────────────────────────
// Type Definitions
// ─────────────────────────────────────────────────────────
interface ReservationFilters {
    status: string;
    check_in_date: string;
    check_out_date: string;
    search: string;
}

interface ReservationPagination {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
}

interface CreateReservationDto {
    hotel_id: number;
    guest_profile_id: number;
    room_id: number;
    check_in_date: string;
    check_out_date: string;
    total_amount: number;
    adults?: number;
    children?: number;
    status?: PMS.ReservationStatus;
    notes?: string;
}

interface CheckOutPaymentDto {
    payment_method_id?: number;
    amount_paid?: number;
    notes?: string;
}

// ─────────────────────────────────────────────────────────
// Store Definition
// ─────────────────────────────────────────────────────────
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
        } as ReservationFilters,
        pagination: {
            current_page: 1,
            per_page: 15,
            total: 0,
            last_page: 1
        } as ReservationPagination
    }),

    // ─────────────────────────────────────────────────────────
    // Getters
    // ─────────────────────────────────────────────────────────
    getters: {
        pendingCount: (state) =>
            state.reservations.filter(r => r.status === 'pending').length,

        confirmedCount: (state) =>
            state.reservations.filter(r => r.status === 'confirmed').length,

        checkedInCount: (state) =>
            state.reservations.filter(r => r.status === 'checked_in').length,

        todayCheckIns: (state) => {
            const today = new Date().toISOString().split('T')[0];
            return state.reservations.filter(r => r.check_in_date === today);
        },
    },

    // ─────────────────────────────────────────────────────────
    // Actions
    // ─────────────────────────────────────────────────────────
    actions: {
        setFilters(filters: Partial<ReservationFilters>) {
            this.filters = { ...this.filters, ...filters };
        },

        resetFilters() {
            this.filters = {
                status: '',
                check_in_date: '',
                check_out_date: '',
                search: ''
            } as ReservationFilters;
        },

        /**
         * Fetch all reservations with pagination
         */
        async fetchAll(page: number = 1): Promise<void> {
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
                } as ReservationPagination;
            } catch (err: unknown) {
                const message = err instanceof Error && err.message
                    ? err.message
                    : 'Failed to fetch reservations';
                this.error = message;
                throw err;
            } finally {
                this.loadingList = false;
            }
        },

        /**
         * Fetch single reservation
         */
        async fetchById(id: number): Promise<void> {
            this.loadingDetail = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.get(`/front-desk/reservations/${id}`);
                this.selectedReservation = data.data;
            } catch (err: unknown) {
                const message = err instanceof Error && err.message
                    ? err.message
                    : 'Failed to fetch reservation';
                this.error = message;
                throw err;
            } finally {
                this.loadingDetail = false;
            }
        },

        /**
         * Create new reservation
         */
        async create(data: CreateReservationDto): Promise<PMS.Reservation> {
            this.loading = true;
            this.error = null;

            try {
                const { data: res } = await apiClient.v1.post('/front-desk/reservations', data);
                this.addReservation(res.data);
                return res.data;
            } catch (err: unknown) {
                const message = err instanceof Error && err.message
                    ? err.message
                    : 'Failed to create reservation';
                this.error = message;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Update existing reservation
         */
        async update(id: number, data: Partial<PMS.Reservation>): Promise<PMS.Reservation> {
            this.loading = true;
            this.error = null;

            try {
                const { data: res } = await apiClient.v1.put(`/front-desk/reservations/${id}`, data);
                this.updateReservation(id, res.data);
                return res.data;
            } catch (err: unknown) {
                const message = err instanceof Error && err.message
                    ? err.message
                    : 'Failed to update reservation';
                this.error = message;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Cancel reservation
         */
        async cancel(id: number): Promise<void> {
            this.loading = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.patch(`/front-desk/reservations/${id}/cancel`);
                this.updateReservation(id, { status: 'cancelled' });
                return data;
            } catch (err: unknown) {
                const message = err instanceof Error && err.message
                    ? err.message
                    : 'Failed to cancel reservation';
                this.error = message;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Delete reservation
         */
        async delete(id: number): Promise<void> {
            this.loading = true;
            this.error = null;

            try {
                await apiClient.v1.delete(`/front-desk/reservations/${id}`);
                this.removeReservation(id);
            } catch (err: unknown) {
                const message = err instanceof Error && err.message
                    ? err.message
                    : 'Failed to delete reservation';
                this.error = message;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Check In guest
         */
        async checkIn(id: number): Promise<void> {
            this.loading = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.patch(`/front-desk/reservations/${id}/check-in`);
                this.updateReservation(id, { status: 'checked_in' });
                return data;
            } catch (err: unknown) {
                const message = err instanceof Error && err.message
                    ? err.message
                    : 'Check in failed';
                this.error = message;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Check Out guest
         */
        async checkOut(id: number, paymentData?: CheckOutPaymentDto): Promise<void> {
            this.loading = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.patch(
                    `/front-desk/reservations/${id}/check-out`,
                    paymentData ?? {}
                );
                this.updateReservation(id, { status: 'checked_out' });
                return data;
            } catch (err: unknown) {
                const message = err instanceof Error && err.message
                    ? err.message
                    : 'Check out failed';
                this.error = message;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        // Internal helper methods
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

        // clearError() {
        //     this.error = null;
        // },

        // $reset() {
        //     this.$patch({
        //         reservations: [],
        //         selectedReservation: null,
        //         loading: false,
        //         loadingList: false,
        //         loadingDetail: false,
        //         error: null,
        //         filters: {
        //             status: '',
        //             check_in_date: '',
        //             check_out_date: '',
        //             search: ''
        //         },
        //         pagination: {
        //             current_page: 1,
        //             per_page: 15,
        //             total: 0,
        //             last_page: 1
        //         }
        //     });
        // }
    }
});