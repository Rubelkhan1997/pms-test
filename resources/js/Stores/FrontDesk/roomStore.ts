import { defineStore } from 'pinia';
import apiClient from '@/Services/apiClient';
import { getErrorMessage } from '@/Helpers/error';
import type { ApiResponse } from '@/Types/api';
import type {
    Room,
    RoomFilters,
    RoomPagination,
    CreateRoomDto,
    UpdateRoomDto,
} from '@/Types/FrontDesk/room';
import {
    mapToRoom,
    mapToRoomPagination,
    mapToRoomFiltersApi,
    mapCreateRoomToApi,
    mapUpdateRoomToApi,
} from '@/Utils/Mappers/room';

const normalizeRooms = (value: unknown): Room[] => (
    Array.isArray(value) ? value : []
);

const DEFAULT_PAGINATION: RoomPagination = {
    currentPage: 1,
    perPage: 15,
    total: 0,
    lastPage: 1,
};

const DEFAULT_FILTERS: RoomFilters = {
    search: '',
    hotelId: '',
    status: '',
    perPage: 15,
};

export const useRoomsStore = defineStore('rooms', {
    state: () => ({
        rooms: [] as Room[],
        selectedRoom: null as Room | null,
        loading: false,
        loadingList: false,
        loadingDetail: false,
        error: null as string | null,
        filters: { ...DEFAULT_FILTERS } as RoomFilters,
        pagination: {
            data: [] as Room[],
            meta: { ...DEFAULT_PAGINATION },
        },
    }),

    getters: {},

    actions: {
        setFilters(filters: Partial<RoomFilters>): void {
            this.filters = { ...this.filters, ...filters };
        },

        resetFilters(): void {
            this.filters = { ...DEFAULT_FILTERS };
        },

        async fetchAll(page: number = 1): Promise<void> {
            this.loadingList = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.get('/front-desk/rooms', {
                    params: {
                        ...mapToRoomFiltersApi(this.filters),
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

                this.rooms = normalizeRooms(items).map(mapToRoom);
                this.pagination.meta = mapToRoomPagination(pagination);
                this.pagination.data = this.rooms;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to fetch rooms');
                throw err;
            } finally {
                this.loadingList = false;
            }
        },

        async fetchById(id: number): Promise<void> {
            this.loadingDetail = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.get(`/front-desk/rooms/${id}`);
                this.selectedRoom = data.data
                    ? mapToRoom(data.data)
                    : null;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to fetch room');
                throw err;
            } finally {
                this.loadingDetail = false;
            }
        },

        async create(payload: CreateRoomDto): Promise<ApiResponse<Room>> {
            this.loading = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.post(
                    '/front-desk/rooms',
                    mapCreateRoomToApi(payload)
                );
                const response = data as ApiResponse<Record<string, any>>;

                if (Number(response.status) === 1 && response.data) {
                    this.addRoom(mapToRoom(response.data));
                }

                return {
                    ...response,
                    data: response.data ? mapToRoom(response.data) : null,
                };
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to create room');
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async update(id: number, payload: UpdateRoomDto): Promise<ApiResponse<Room>> {
            this.loading = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.put(
                    `/front-desk/rooms/${id}`,
                    mapUpdateRoomToApi(payload)
                );
                const response = data as ApiResponse<Record<string, any>>;

                if (Number(response.status) === 1 && response.data) {
                    this.updateRoom(id, mapToRoom(response.data));
                }

                return {
                    ...response,
                    data: response.data ? mapToRoom(response.data) : null,
                };
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to update room');
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async delete(id: number): Promise<ApiResponse<void>> {
            this.loading = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.delete(`/front-desk/rooms/${id}`);
                const response = data as ApiResponse<void>;

                if (Number(response.status) === 1) {
                    this.removeRoom(id);
                }

                return response;
            } catch (err: unknown) {
                this.error = getErrorMessage(err, 'Failed to delete room');
                throw err;
            } finally {
                this.loading = false;
            }
        },

        addRoom(room: Room): void {
            this.rooms = normalizeRooms(this.rooms);
            this.rooms.unshift(room);
            this.pagination.data = this.rooms;
            this.pagination.meta.total++;
        },

        updateRoom(id: number, data: Partial<Room>): void {
            this.rooms = normalizeRooms(this.rooms);
            const index = this.rooms.findIndex((r) => r.id === id);

            if (index !== -1) {
                this.rooms[index] = { ...this.rooms[index], ...data };
            }

            if (this.selectedRoom?.id === id) {
                this.selectedRoom = { ...this.selectedRoom, ...data };
            }

            this.pagination.data = this.rooms;
        },

        removeRoom(id: number): void {
            this.rooms = normalizeRooms(this.rooms);
            const index = this.rooms.findIndex((r) => r.id === id);

            if (index !== -1) {
                this.rooms.splice(index, 1);
                this.pagination.meta.total = Math.max(0, this.pagination.meta.total - 1);
            }

            this.pagination.data = this.rooms;
        },

        clearError(): void {
            this.error = null;
        },

        $reset(): void {
            this.$patch({
                rooms: [],
                selectedRoom: null,
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
