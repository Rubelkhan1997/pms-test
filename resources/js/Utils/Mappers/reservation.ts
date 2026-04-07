import type {
    Reservation,
    ReservationFilters,
    ReservationPagination,
    CreateReservationDto,
    UpdateReservationDto,
    HotelOption,
    GuestOption,
    RoomOption,
} from '@/Types/FrontDesk/reservation';

export function mapReservationApiToReservation(api: Record<string, any>): Reservation {
    const source = api?.data ?? api;

    return {
        id: source.id,
        hotelId: source.hotel_id,
        guestId: source.guest_id,
        roomId: source.room_id,
        reference: source.reference,
        checkInDate: source.check_in_date,
        checkOutDate: source.check_out_date,
        totalAmount: source.total_amount,
        status: source.status,
        notes: source.notes,
        adults: source.adults,
        children: source.children,
        meta: source.meta,
        createdAt: source.created_at,
        hotel: source.hotel ? {
            id: source.hotel.id,
            name: source.hotel.name,
            code: source.hotel.code,
        } : undefined,
        room: source.room ? {
            id: source.room.id,
            number: source.room.number,
            type: source.room.type,
            price: source.room.price,
            status: source.room.status,
        } : undefined,
        guest: source.guest ? {
            id: source.guest.id,
            firstName: source.guest.first_name,
            lastName: source.guest.last_name,
            email: source.guest.email,
            phone: source.guest.phone,
        } : undefined,
    };
}

export function mapHotelOptionApi(api: Record<string, any>): HotelOption {
    return {
        id: api.id,
        name: api.name,
        code: api.code,
    };
}

export function mapGuestOptionApi(api: Record<string, any>): GuestOption {
    return {
        id: api.id,
        firstName: api['first_name'],
        lastName: api['last_name'],
        email: api.email,
        phone: api.phone,
    };
}

export function mapRoomOptionApi(api: Record<string, any>): RoomOption {
    return {
        id: api.id,
        number: api.number,
        type: api.type,
        price: api.price,
        status: api.status,
    };
}

export function mapReservationPaginationApiToPagination(api: Record<string, any>): ReservationPagination {
    return {
        currentPage: api['current_page'],
        perPage: api['per_page'],
        total: api.total,
        lastPage: api['last_page'],
    };
}

export function mapReservationFiltersToApi(filters: ReservationFilters): Record<string, any> {
    return {
        status: filters.status,
        check_in_date: filters.checkInDate,
        check_out_date: filters.checkOutDate,
        search: filters.search,
        per_page: filters.perPage,
    };
}

export function mapCreateReservationToApi(payload: CreateReservationDto): Record<string, any> {
    return {
        hotel_id: payload.hotelId,
        guest_id: payload.guestId,
        room_id: payload.roomId,
        check_in_date: payload.checkInDate,
        check_out_date: payload.checkOutDate,
        total_amount: payload.totalAmount,
        adults: payload.adults,
        children: payload.children,
        status: payload.status,
        notes: payload.notes,
    };
}

export function mapUpdateReservationToApi(payload: UpdateReservationDto): Record<string, any> {
    return {
        hotel_id: payload.hotelId,
        guest_id: payload.guestId,
        room_id: payload.roomId,
        check_in_date: payload.checkInDate,
        check_out_date: payload.checkOutDate,
        total_amount: payload.totalAmount,
        adults: payload.adults,
        children: payload.children,
        status: payload.status,
        notes: payload.notes,
    };
}
