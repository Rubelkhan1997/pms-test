import { defineStore } from 'pinia';
import apiClient from '@/Services/apiClient';
import { getErrorMessage } from '@/Helpers/error';
import type { ApiResponse } from '@/Types/api';
import type {
    Reservation,
    ReservationFilters,
    ReservationPagination,
    CreateReservationDto,
    UpdateReservationDto,
} from '@/Types/FrontDesk/reservation';

// ─────────────────────────────────────────────────────────
// Constants
// ─────────────────────────────────────────────────────────
const DEFAULT_PAGINATION: ReservationPagination = {
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1,
};

const DEFAULT_FILTERS: ReservationFilters = {
    status: '',
    check_in_date: '',
    check_out_date: '',
    search: '',
    per_page: 15,
};

// ─────────────────────────────────────────────────────────
// Store
// ─────────────────────────────────────────────────────────
export const useReservationsStore = defineStore('reservations', {

    state: () => ({
        reservations: [] as Reservation[],
        selectedReservation: null as Reservation | null,
        loading: false,
        loadingList: false,
        loadingDetail: false,
        error: null as string | null,
        filters: { ...DEFAULT_FILTERS } as ReservationFilters,
        pagination: { ...DEFAULT_PAGINATION } as ReservationPagination,
    }),

    // ─────────────────────────────────────────────────────
    // Getters
    // ─────────────────────────────────────────────────────
    getters: {
        pendingCount: (state): number => state.reservations.filter(r => r.status === 'pending').length,
        confirmedCount: (state): number => state.reservations.filter(r => r.status === 'confirmed').length,
        checkedInCount: (state): number => state.reservations.filter(r => r.status === 'checked_in').length,
        todayCheckIns: (state): Reservation[] => {
            const today = new Date().toISOString().split('T')[0];
            return state.reservations.filter(r => r.check_in_date === today);
        },
    },

    // ─────────────────────────────────────────────────────
    // Actions
    // ─────────────────────────────────────────────────────
    actions: {

        setFilters(filters: Partial<ReservationFilters>): void {
            this.filters = { ...this.filters, ...filters };
        },

        resetFilters(): void {
            this.filters = { ...DEFAULT_FILTERS };
        },

        async fetchAll(page: number = 1): Promise<void> {
            this.loadingList = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.get('/front-desk/reservations', {
                    params: { 
                        ...this.filters, 
                        page,
                        per_page: this.filters.per_page 
                    },
                });
                this.reservations = data.data as Reservation[];
                this.pagination = {
                    current_page: data.current_page,
                    per_page: data.per_page,
                    total: data.total,
                    last_page: data.last_page,
                };

            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to fetch reservations');
                throw err;
            } finally {
                this.loadingList = false;
            }
        },

        async fetchById(id: number): Promise<void> {
            this.loadingDetail = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.get(`/front-desk/reservations/${id}`);
                this.selectedReservation = data.data as Reservation;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to fetch reservation');
                throw err;
            } finally {
                this.loadingDetail = false;
            }
        },

        async create(payload: CreateReservationDto): Promise<ApiResponse<Reservation>> {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.post('/front-desk/reservations', payload);
                const response = data as ApiResponse<Reservation>;
                
                // Add to store
                if (response.status === 1 && response.data) {
                    this.addReservation(response.data);
                }
                
                return response;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to create reservation');
                throw err;
            } finally {
                this.loading = false;
            }
        },
 
        async update(id: number, payload: UpdateReservationDto): Promise<ApiResponse<Reservation>> {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.put(`/front-desk/reservations/${id}`, payload);
                const response = data as ApiResponse<Reservation>;
                
                // Update store
                if (response.status === 1 && response.data) {
                    this.updateReservation(id, response.data);
                }
                
                return response;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to update reservation');
                throw err;
            } finally {
                this.loading = false;
            }
        },
 
        async cancel(id: number): Promise<ApiResponse<void>> {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.patch(`/front-desk/reservations/${id}/cancel`);
                const response = data as ApiResponse<void>;
                
                // Update store only if successful
                if (response.status === 1) {
                    this.updateReservation(id, { status: 'cancelled' });
                }
                
                return response;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to cancel reservation');
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async delete(id: number): Promise<ApiResponse<void>> {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.delete(`/front-desk/reservations/${id}`);
                const response = data as ApiResponse<void>;
                
                // Update store only if successful
                if (response.status === 1) {
                    this.removeReservation(id);
                }
                
                return response;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to delete reservation');
                throw err;
            } finally {
                this.loading = false;
            }
        },

        // ── Helpers (internal) ───────────────────────────

        addReservation(reservation: Reservation): void {
            this.reservations.unshift(reservation);
            this.pagination.total++;
        },

        updateReservation(id: number, data: Partial<Reservation>): void {
            const index = this.reservations.findIndex(r => r.id === id);
            if (index !== -1) {
                this.reservations[index] = { ...this.reservations[index], ...data };
            }
            if (this.selectedReservation?.id === id) {
                this.selectedReservation = { ...this.selectedReservation, ...data };
            }
        },

        removeReservation(id: number): void {
            const index = this.reservations.findIndex(r => r.id === id);
            if (index !== -1) {
                this.reservations.splice(index, 1);
                this.pagination.total--;
            }
        },

        clearError(): void {
            this.error = null;
        },

        $reset(): void {
            this.$patch({
                reservations: [],
                selectedReservation: null,
                loading: false,
                loadingList: false,
                loadingDetail: false,
                error: null,
                filters: { ...DEFAULT_FILTERS },
                pagination: { ...DEFAULT_PAGINATION },
            });
        },
    },
});
