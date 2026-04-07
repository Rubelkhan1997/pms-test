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
import {
    mapCreateReservationToApi,
    mapReservationApiToReservation,
    mapReservationFiltersToApi,
    mapReservationPaginationApiToPagination,
    mapUpdateReservationToApi,
} from '@/Utils/Mappers/reservation';

// Keep runtime data shape safe even when API response is inconsistent.
const normalizeReservations = (value: unknown): Reservation[] => (
    Array.isArray(value) ? value : []
);

// Default pagination values used before first API call.
const DEFAULT_PAGINATION: ReservationPagination = {
    currentPage: 1,
    perPage: 15,
    total: 0,
    lastPage: 1,
};

// Default list filters for reservation index page.
const DEFAULT_FILTERS: ReservationFilters = {
    status: '',
    checkInDate: '',
    checkOutDate: '',
    search: '',
    perPage: 15,
};

export const useReservationsStore = defineStore('reservations', {
    // Store state for reservation list/detail + request states.
    state: () => ({
        reservations: [] as Reservation[],
        selectedReservation: null as Reservation | null,
        loading: false,
        loadingList: false,
        loadingDetail: false,
        error: null as string | null,
        filters: { ...DEFAULT_FILTERS } as ReservationFilters,
        pagination: {
            data: [] as Reservation[],
            meta: { ...DEFAULT_PAGINATION },
        },
    }),

    getters: {
        pendingCount: (state): number => normalizeReservations(state.reservations).filter((r) => r.status === 'pending').length,
        confirmedCount: (state): number => normalizeReservations(state.reservations).filter((r) => r.status === 'confirmed').length,
        checkedInCount: (state): number => normalizeReservations(state.reservations).filter((r) => r.status === 'checked_in').length,
        todayCheckIns: (state): Reservation[] => {
            const today = new Date().toISOString().split('T')[0];
            return normalizeReservations(state.reservations).filter((r) => r.checkInDate === today);
        },
    },

    actions: {
        // Merge partial filters without dropping existing values.
        setFilters(filters: Partial<ReservationFilters>): void {
            this.filters = { ...this.filters, ...filters };
        },

        // Restore filter state to initial defaults.
        resetFilters(): void {
            this.filters = { ...DEFAULT_FILTERS };
        },

        // Fetch reservation collection with filters + pagination.
        async fetchAll(page: number = 1): Promise<void> {
            this.loadingList = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.get('/front-desk/reservations', {
                    params: {
                        ...mapReservationFiltersToApi(this.filters),
                        page,
                        per_page: this.filters.perPage,
                    },
                });

                const payload = data?.data ?? {};
                const items = Array.isArray(payload.items)
                    ? payload.items
                    : Array.isArray(payload.data)
                        ? payload.data
                        : [];
                const pagination = payload.pagination ?? payload.meta ?? {};

                this.reservations = normalizeReservations(items).map(mapReservationApiToReservation);
                this.pagination.meta = mapReservationPaginationApiToPagination(pagination);
                this.pagination.data = this.reservations;
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
                this.selectedReservation = data.data
                    ? mapReservationApiToReservation(data.data)
                    : null;
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
                const { data } = await apiClient.v1.post(
                    '/front-desk/reservations',
                    mapCreateReservationToApi(payload)
                );
                const response = data as ApiResponse<Record<string, any>>;

                if (Number(response.status) === 1 && response.data) {
                    this.addReservation(mapReservationApiToReservation(response.data));
                }

                return {
                    ...response,
                    data: response.data ? mapReservationApiToReservation(response.data) : null,
                };
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
                const { data } = await apiClient.v1.put(
                    `/front-desk/reservations/${id}`,
                    mapUpdateReservationToApi(payload)
                );
                const response = data as ApiResponse<Record<string, any>>;

                if (Number(response.status) === 1 && response.data) {
                    this.updateReservation(id, mapReservationApiToReservation(response.data));
                }

                return {
                    ...response,
                    data: response.data ? mapReservationApiToReservation(response.data) : null,
                };
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

                if (Number(response.status) === 1) {
                    this.updateReservation(id, { status: 'cancelled' } as Partial<Reservation>);
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

                if (Number(response.status) === 1) {
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

        // Insert new reservation at top of current list.
        addReservation(reservation: Reservation): void {
            this.reservations = normalizeReservations(this.reservations);
            this.reservations.unshift(reservation);
            this.pagination.data = this.reservations;
            this.pagination.meta.total++;
        },

        // Update existing reservation in list and selected detail if matched.
        updateReservation(id: number, data: Partial<Reservation>): void {
            this.reservations = normalizeReservations(this.reservations);
            const index = this.reservations.findIndex((r) => r.id === id);
            if (index !== -1) {
                this.reservations[index] = { ...this.reservations[index], ...data };
            }

            if (this.selectedReservation?.id === id) {
                this.selectedReservation = { ...this.selectedReservation, ...data };
            }

            this.pagination.data = this.reservations;
        },

        // Remove reservation from local list cache after successful delete.
        removeReservation(id: number): void {
            this.reservations = normalizeReservations(this.reservations);
            const index = this.reservations.findIndex((r) => r.id === id);
            if (index !== -1) {
                this.reservations.splice(index, 1);
                this.pagination.meta.total = Math.max(0, this.pagination.meta.total - 1);
            }

            this.pagination.data = this.reservations;
        },

        // Clear current error message.
        clearError(): void {
            this.error = null;
        },

        // Reset store state to initial values.
        $reset(): void {
            this.$patch({
                reservations: [],
                selectedReservation: null,
                loading: false,
                loadingList: false,
                loadingDetail: false,
                error: null,
                filters: { ...DEFAULT_FILTERS },
                pagination: {
                    data: [],
                    meta: { ...DEFAULT_PAGINATION },
                },
            });
        },
    },
});
