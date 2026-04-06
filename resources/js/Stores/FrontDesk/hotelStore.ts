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

const normalizeHotels = (value: unknown): Hotel[] => (
    Array.isArray(value) ? value : []
);

// ─────────────────────────────────────────────────────────
// Constants
// ─────────────────────────────────────────────────────────
const DEFAULT_PAGINATION: HotelPagination = {
    currentPage: 1,
    perPage: 15,
    total: 0,
    lastPage: 1,
};

const DEFAULT_FILTERS: HotelFilters = {
    search: '',
    perPage: 15,
};

// ─────────────────────────────────────────────────────────
// Store
// ─────────────────────────────────────────────────────────
export const useHotelsStore = defineStore('hotels', {

    // ─────────────────────────────────────────────────────
    // State
    // ─────────────────────────────────────────────────────
    state: () => ({
        items: [] as Hotel[],
        selectedItem: null as Hotel | null,
        loading: false,
        loadingList: false,
        error: null as string | null,
        filters: { ...DEFAULT_FILTERS } as HotelFilters,
        pagination: {
            data: [] as Hotel[],
            meta: { ...DEFAULT_PAGINATION },
        },
    }),

    // ─────────────────────────────────────────────────────
    // Getters
    // ─────────────────────────────────────────────────────
    getters: {},

    // ─────────────────────────────────────────────────────
    // Actions
    // ─────────────────────────────────────────────────────
    actions: {
        setFilters(filters: Partial<HotelFilters>): void {
            this.filters = { ...this.filters, ...filters };
        },

        resetFilters(): void {
            this.filters = { ...DEFAULT_FILTERS };
        },

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
                const items = Array.isArray(payload.items) ? payload.items : Array.isArray(payload.data) ? payload.data : [];
                const pagination = payload.pagination ?? payload.meta ?? {};

                this.items = normalizeHotels(items).map(mapToHotel);
                this.pagination.meta = mapToHotelPagination(pagination);
                this.pagination.data = this.items;

            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to fetch hotels');
                throw err;
            } finally {
                this.loadingList = false;
            }
        },

        async fetchById(id: number): Promise<void> {
            this.loadingList = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.get(`/front-desk/hotels/${id}`);
                this.selectedItem = data.data
                    ? mapToHotel(data.data)
                    : null;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to fetch hotel');
                throw err;
            } finally {
                this.loadingList = false;
            }
        },

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
                    this.items.unshift(mapToHotel(response.data));
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
                    const index = this.items.findIndex(h => h.id === id);
                    if (index !== -1) {
                        this.items[index] = mapToHotel(response.data);
                    }
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

        async delete(id: number): Promise<ApiResponse<void>> {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await apiClient.v1.delete(`/front-desk/hotels/${id}`);
                const response = data as ApiResponse<void>;

                if (Number(response.status) === 1) {
                    const index = this.items.findIndex(h => h.id === id);
                    if (index !== -1) {
                        this.items.splice(index, 1);
                    }
                }

                return response;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to delete hotel');
                throw err;
            } finally {
                this.loading = false;
            }
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
