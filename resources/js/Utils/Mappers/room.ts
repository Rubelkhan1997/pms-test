import type {
    Room,
    RoomFilters,
    RoomPagination,
    CreateRoomDto,
    UpdateRoomDto,
    RoomHotelOption,
} from '@/Types/FrontDesk/room';

export function mapToRoom(api: Record<string, any>): Room {
    const source = api?.data ?? api;

    return {
        id: source.id,
        hotelId: source.hotel_id,
        number: source.number,
        floor: source.floor,
        type: source.type,
        status: source.status,
        statusLabel: source.status_label,
        baseRate: Number(source.base_rate ?? 0),
        createdAt: source.created_at ?? source.createdAt,
        updatedAt: source.updated_at ?? source.updatedAt,
        hotel: source.hotel ? {
            id: source.hotel.id,
            name: source.hotel.name,
        } : undefined,
    };
}

export function mapToRoomPagination(api: Record<string, any>): RoomPagination {
    return {
        currentPage: api.current_page,
        perPage: api.per_page,
        total: api.total,
        lastPage: api.last_page,
    };
}

export function mapToRoomFiltersApi(filters: RoomFilters): Record<string, any> {
    return {
        search: filters.search,
        hotel_id: filters.hotelId || undefined,
        status: filters.status || undefined,
        per_page: filters.perPage,
    };
}

export function mapCreateRoomToApi(payload: CreateRoomDto): Record<string, any> {
    return {
        hotel_id: payload.hotelId,
        number: payload.number,
        floor: payload.floor,
        type: payload.type,
        status: payload.status,
        base_rate: payload.baseRate,
    };
}

export function mapUpdateRoomToApi(payload: UpdateRoomDto): Record<string, any> {
    return {
        hotel_id: payload.hotelId,
        number: payload.number,
        floor: payload.floor,
        type: payload.type,
        status: payload.status,
        base_rate: payload.baseRate,
    };
}

export function mapRoomHotelOptionApi(api: Record<string, any>): RoomHotelOption {
    return {
        id: api.id,
        name: api.name,
    };
}
