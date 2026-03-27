import { defineStore } from 'pinia';
import { apiClient } from '@/Services';

/**
 * Room Store (Pinia)
 * Global state management for rooms
 *
 * Best Practices Applied:
 * - Minimal state, derive rest with getters
 * - Explicit actions for mutations
 * - Typed state and actions
 * - Use apiClient for API calls
 */
export const useRoomsStore = defineStore('rooms', {
    // ─────────────────────────────────────────────────────────
    // State (Reactive Data) - Best Practice
    // ─────────────────────────────────────────────────────────
    state: () => ({
        // Rooms list
        rooms: [] as any[],

        // Currently selected room
        selectedRoom: null as any | null,

        // Loading states
        loading: false,
        loadingList: false,
        loadingDetail: false,

        // Error states
        error: null as string | null,

        // Filters
        filters: {
            status: '',
            floor: 0,
            type: '',
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
         * Get rooms by status
         */
        byStatus: (state) => {
            return (status: string) => state.rooms.filter(r => r.status === status);
        },

        /**
         * Get available rooms count - Derived state
         */
        availableCount: (state) => {
            return state.rooms.filter(r => r.status === 'available').length;
        },

        /**
         * Get occupied rooms count - Derived state
         */
        occupiedCount: (state) => {
            return state.rooms.filter(r => r.status === 'occupied').length;
        },

        /**
         * Get maintenance rooms count - Derived state
         */
        maintenanceCount: (state) => {
            return state.rooms.filter(r => r.status === 'maintenance').length;
        },

        /**
         * Get dirty rooms count - Derived state
         */
        dirtyCount: (state) => {
            return state.rooms.filter(r => r.status === 'dirty').length;
        },

        /**
         * Get rooms by floor - Derived state
         */
        byFloor: (state) => {
            return (floor: number) => state.rooms.filter(r => r.floor === floor);
        },

        /**
         * Get rooms by type - Derived state
         */
        byType: (state) => {
            return (type: string) => state.rooms.filter(r => r.type === type);
        },

        /**
         * Get filtered rooms - Derived state with multiple criteria
         */
        filteredRooms: (state) => {
            let filtered = [...state.rooms];
            const filters = state.filters;

            // Filter by status
            if (filters.status) {
                filtered = filtered.filter(r => r.status === filters.status);
            }

            // Filter by floor
            if (filters.floor) {
                filtered = filtered.filter(r => r.floor === filters.floor);
            }

            // Filter by type
            if (filters.type) {
                filtered = filtered.filter(r => r.type === filters.type);
            }

            // Search by room number or type
            if (filters.search) {
                const search = filters.search.toLowerCase();
                filtered = filtered.filter(r =>
                    r.number?.toLowerCase().includes(search) ||
                    r.type?.toLowerCase().includes(search)
                );
            }

            return filtered;
        },

        /**
         * Get occupancy rate - Derived state
         */
        occupancyRate: (state) => {
            if (state.rooms.length === 0) return 0;
            const occupied = state.rooms.filter(r => r.status === 'occupied').length;
            return Math.round((occupied / state.rooms.length) * 100);
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
                floor: 0,
                type: '',
                search: ''
            };
        },

        /**
         * Fetch all rooms with pagination
         */
        async fetchAll(page: number = 1) {
            this.loadingList = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.get('/housekeeping/rooms', {
                    params: {
                        ...this.filters,
                        page
                    }
                });

                this.rooms = data.data;
                this.pagination = {
                    current_page: data.current_page,
                    per_page: data.per_page,
                    total: data.total,
                    last_page: data.last_page
                };
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to fetch rooms';
                console.error('Fetch rooms error:', err);
            } finally {
                this.loadingList = false;
            }
        },

        /**
         * Fetch single room
         */
        async fetchById(id: number) {
            this.loadingDetail = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.get(`/housekeeping/rooms/${id}`);
                this.selectedRoom = data.data;
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to fetch room';
                console.error('Fetch room error:', err);
            } finally {
                this.loadingDetail = false;
            }
        },

        /**
         * Set selected room
         */
        setSelectedRoom(room: any | null) {
            this.selectedRoom = room;
        },

        /**
         * Clear selected room
         */
        clearSelectedRoom() {
            this.selectedRoom = null;
        },

        /**
         * Add room to list
         */
        addRoom(room: any) {
            this.rooms.unshift(room);
            this.pagination.total++;
        },

        /**
         * Update room in list
         */
        updateRoom(id: number, data: any) {
            const index = this.rooms.findIndex(r => r.id === id);
            if (index !== -1) {
                this.rooms[index] = { ...this.rooms[index], ...data };
            }

            // Also update selected if it's the same
            if (this.selectedRoom?.id === id) {
                this.selectedRoom = { ...this.selectedRoom, ...data };
            }
        },

        /**
         * Remove room from list
         */
        removeRoom(id: number) {
            const index = this.rooms.findIndex(r => r.id === id);
            if (index !== -1) {
                this.rooms.splice(index, 1);
                this.pagination.total--;
            }
        },

        /**
         * Update room status
         */
        async updateRoomStatus(id: number, status: string) {
            this.loading = true;
            this.error = null;

            try {
                const { data } = await apiClient.v1.patch(`/housekeeping/rooms/${id}/status`, { status });
                
                // Update local state
                this.updateRoom(id, data.data);
                
                // Clear selected if it's the same
                if (this.selectedRoom?.id === id) {
                    this.selectedRoom = data.data;
                }
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to update room status';
                console.error('Update room status error:', err);
            } finally {
                this.loading = false;
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
                rooms: [],
                selectedRoom: null,
                loading: false,
                loadingList: false,
                loadingDetail: false,
                error: null,
                filters: {
                    status: '',
                    floor: 0,
                    type: '',
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
