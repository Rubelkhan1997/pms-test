// ============================================================================
// Room Core Types
// ============================================================================
export type RoomStatus = 'available' | 'occupied' | 'dirty' | 'out_of_order';
export type RoomType = string;

export interface Room {
    id: number;
    hotelId: number;
    number: string;
    floor?: string | null;
    type: string;
    status: RoomStatus;
    statusLabel?: string;
    baseRate: number;
    createdAt?: string;
    updatedAt?: string;
    hotel?: {
        id: number;
        name: string;
    };
}

// ============================================================================
// Store Types
// ============================================================================
export interface RoomFilters {
    search?: string;
    hotelId?: number | '';
    status?: RoomStatus | '';
    perPage?: number;
}

export interface RoomPagination {
    currentPage: number;
    perPage: number;
    total: number;
    lastPage: number;
}

// ============================================================================
// DTO Types (API Payload)
// ============================================================================
export interface CreateRoomDto {
    hotelId: number;
    number: string;
    floor?: string;
    type: string;
    status: RoomStatus;
    baseRate: number;
}

export interface UpdateRoomDto {
    hotelId?: number;
    number?: string;
    floor?: string;
    type?: string;
    status?: RoomStatus;
    baseRate?: number;
}

export interface RoomHotelOption {
    id: number;
    name: string;
}
