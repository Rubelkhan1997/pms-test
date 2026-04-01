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
    return {
        id: api.id,
        hotelId: api['hotel_id'],
        guestId: api['guest_id'],
        roomId: api['room_id'],
        reference: api.reference,
        checkInDate: api['check_in_date'],
        checkOutDate: api['check_out_date'],
        totalAmount: api['total_amount'],
        status: api.status,
        notes: api.notes,
        adults: api.adults,
        children: api.children,
        meta: api.meta,
        createdAt: api['created_at'],
        hotel: api.hotel ? {
            id: api.hotel.id,
            name: api.hotel.name,
            code: api.hotel.code,
        } : undefined,
        room: api.room ? {
            id: api.room.id,
            number: api.room.number,
            type: api.room.type,
            price: api.room.price,
            status: api.room.status,
        } : undefined,
        guest: api.guest ? {
            id: api.guest.id,
            firstName: api.guest['first_name'],
            lastName: api.guest['last_name'],
            email: api.guest.email,
            phone: api.guest.phone,
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
