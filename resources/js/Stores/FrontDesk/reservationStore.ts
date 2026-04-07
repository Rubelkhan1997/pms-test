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

const normalizeReservations = (value: unknown): Reservation[] => (
    Array.isArray(value) ? value : []
);

const DEFAULT_PAGINATION: ReservationPagination = {
    currentPage: 1,
    perPage: 15,
    total: 0,
    lastPage: 1,
};

const DEFAULT_FILTERS: ReservationFilters = {
    status: '',
    checkInDate: '',
    checkOutDate: '',
    search: '',
    perPage: 15,
};

export const useReservationsStore = defineStore('reservations', {
    state: () => ({
        items: [] as Reservation[],
        selectedItem: null as Reservation | null,
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
        pendingCount: (state): number => normalizeReservations(state.items).filter((r) => r.status === 'pending').length,
        confirmedCount: (state): number => normalizeReservations(state.items).filter((r) => r.status === 'confirmed').length,
        checkedInCount: (state): number => normalizeReservations(state.items).filter((r) => r.status === 'checked_in').length,
        todayCheckIns: (state): Reservation[] => {
            const today = new Date().toISOString().split('T')[0];
            return normalizeReservations(state.items).filter((r) => r.checkInDate === today);
        },
    },

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

                this.items = normalizeReservations(items).map(mapReservationApiToReservation);
                this.pagination.meta = mapReservationPaginationApiToPagination(pagination);
                this.pagination.data = this.items;
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
                this.selectedItem = data.data
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
                    this.addItem(mapReservationApiToReservation(response.data));
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
                    this.updateItem(id, mapReservationApiToReservation(response.data));
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
                    this.updateItem(id, { status: 'cancelled' } as Partial<Reservation>);
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
                    this.removeItem(id);
                }

                return response;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to delete reservation');
                throw err;
            } finally {
                this.loading = false;
            }
        },

        addItem(item: Reservation): void {
            this.items = normalizeReservations(this.items);
            this.items.unshift(item);
            this.pagination.data = this.items;
            this.pagination.meta.total++;
        },

        updateItem(id: number, data: Partial<Reservation>): void {
            this.items = normalizeReservations(this.items);
            const index = this.items.findIndex((r) => r.id === id);
            if (index !== -1) {
                this.items[index] = { ...this.items[index], ...data };
            }

            if (this.selectedItem?.id === id) {
                this.selectedItem = { ...this.selectedItem, ...data };
            }

            this.pagination.data = this.items;
        },

        removeItem(id: number): void {
            this.items = normalizeReservations(this.items);
            const index = this.items.findIndex((r) => r.id === id);
            if (index !== -1) {
                this.items.splice(index, 1);
                this.pagination.meta.total = Math.max(0, this.pagination.meta.total - 1);
            }

            this.pagination.data = this.items;
        },

        clearError(): void {
            this.error = null;
        },

        $reset(): void {
            this.$patch({
                items: [],
                selectedItem: null,
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
