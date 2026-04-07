import { defineStore } from 'pinia';
import apiClient from '@/Services/apiClient';
import { getErrorMessage } from '@/Helpers/error';
import type { ApiResponse } from '@/Types/api';
import type {
    Hotel,
    HotelFilters,
    HotelPagination,
    CreateHotelDto,
    UpdateHotelDto,
} from '@/Types/FrontDesk/hotel';
import {
    mapToHotel,
    mapToHotelPagination,
    mapToHotelFiltersApi,
    mapCreateHotelToApi,
    mapUpdateHotelToApi,
} from '@/Utils/Mappers/hotel';

// Keep runtime data shape safe even when API response is inconsistent.
const normalizeHotels = (value: unknown): Hotel[] => (
    Array.isArray(value) ? value : []
);

// Default pagination values used before first API call.
const DEFAULT_PAGINATION: HotelPagination = {
    currentPage: 1,
    perPage: 15,
    total: 0,
    lastPage: 1,
};

// Default list filters for hotel index page.
const DEFAULT_FILTERS: HotelFilters = {
    search: '',
    perPage: 15,
};

export const useHotelsStore = defineStore('hotels', {
    // Store state for hotel list/detail + request states.
    state: () => ({
        hotels: [] as Hotel[],
        selectedHotel: null as Hotel | null,
        loading: false,
        loadingList: false,
        loadingDetail: false,
        error: null as string | null,
        filters: { ...DEFAULT_FILTERS } as HotelFilters,
        pagination: {
            data: [] as Hotel[],
            meta: { ...DEFAULT_PAGINATION },
        },
    }),

    getters: {},

    actions: {
        // Merge partial filters without dropping existing values.
        setFilters(filters: Partial<HotelFilters>): void {
            this.filters = { ...this.filters, ...filters };
        },

        // Restore filter state to initial defaults.
        resetFilters(): void {
            this.filters = { ...DEFAULT_FILTERS };
        },

        // Fetch hotel collection with filters + pagination.
        async fetchAll(page: number = 1): Promise<void> {
            this.loadingList = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.get('/front-desk/hotels', {
                    params: {
                        ...mapToHotelFiltersApi(this.filters),
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

                this.hotels = normalizeHotels(items).map(mapToHotel);
                this.pagination.meta = mapToHotelPagination(pagination);
                this.pagination.data = this.hotels;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to fetch hotels');
                throw err;
            } finally {
                this.loadingList = false;
            }
        },

        // Fetch a single hotel by id.
        async fetchById(id: number): Promise<void> {
            this.loadingDetail = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.get(`/front-desk/hotels/${id}`);
                this.selectedHotel = data.data
                    ? mapToHotel(data.data)
                    : null;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to fetch hotel');
                throw err;
            } finally {
                this.loadingDetail = false;
            }
        },

        // Create a new hotel.
        async create(payload: CreateHotelDto): Promise<ApiResponse<Hotel>> {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.post(
                    '/front-desk/hotels',
                    mapCreateHotelToApi(payload)
                );
                const response = data as ApiResponse<Record<string, any>>;

                if (Number(response.status) === 1 && response.data) {
                    this.addHotel(mapToHotel(response.data));
                }

                return {
                    ...response,
                    data: response.data ? mapToHotel(response.data) : null,
                };
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to create hotel');
                throw err;
            } finally {
                this.loading = false;
            }
        },

        // Update an existing hotel.
        async update(id: number, payload: UpdateHotelDto): Promise<ApiResponse<Hotel>> {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.put(
                    `/front-desk/hotels/${id}`,
                    mapUpdateHotelToApi(payload)
                );
                const response = data as ApiResponse<Record<string, any>>;

                if (Number(response.status) === 1 && response.data) {
                    this.updateHotel(id, mapToHotel(response.data));
                }

                return {
                    ...response,
                    data: response.data ? mapToHotel(response.data) : null,
                };
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to update hotel');
                throw err;
            } finally {
                this.loading = false;
            }
        },

        // Delete a hotel by id.
        async delete(id: number): Promise<ApiResponse<void>> {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.delete(`/front-desk/hotels/${id}`);
                const response = data as ApiResponse<void>;

                if (Number(response.status) === 1) {
                    this.removeHotel(id);
                }

                return response;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to delete hotel');
                throw err;
            } finally {
                this.loading = false;
            }
        },

        // Insert new hotel at top of current list.
        addHotel(hotel: Hotel): void {
            this.hotels = normalizeHotels(this.hotels);
            this.hotels.unshift(hotel);
            this.pagination.data = this.hotels;
            this.pagination.meta.total++;
        },

        // Update existing hotel in list and selected detail if matched.
        updateHotel(id: number, data: Partial<Hotel>): void {
            this.hotels = normalizeHotels(this.hotels);
            const index = this.hotels.findIndex((h) => h.id === id);
            if (index !== -1) {
                this.hotels[index] = { ...this.hotels[index], ...data };
            }

            if (this.selectedHotel?.id === id) {
                this.selectedHotel = { ...this.selectedHotel, ...data };
            }

            this.pagination.data = this.hotels;
        },

        // Remove hotel from local list cache after successful delete.
        removeHotel(id: number): void {
            this.hotels = normalizeHotels(this.hotels);
            const index = this.hotels.findIndex((h) => h.id === id);
            if (index !== -1) {
                this.hotels.splice(index, 1);
                this.pagination.meta.total = Math.max(0, this.pagination.meta.total - 1);
            }
            this.pagination.data = this.hotels;
        },

        // Clear current error message.
        clearError(): void {
            this.error = null;
        },

        // Reset store state to initial values.
        $reset(): void {
            this.$patch({
                hotels: [],
                selectedHotel: null,
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
