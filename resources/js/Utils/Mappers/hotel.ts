import type {
    Hotel,
    HotelFilters,
    HotelPagination,
    CreateHotelDto,
    UpdateHotelDto,
} from '@/Types/FrontDesk/hotel';

export function mapToHotel(api: Record<string, any>): Hotel {
    return {
        id: api.id,
        name: api.name,
        code: api.code,
        timezone: api.timezone,
        currency: api.currency,
        email: api.email,
        phone: api.phone,
        address: api.address,
        createdAt: api.created_at,
        updatedAt: api.updated_at,
    };
}

export function mapToHotelPayload(payload: Record<string, any>): Record<string, any> {
    return {
        name: payload.name,
        code: payload.code,
        timezone: payload.timezone,
        currency: payload.currency,
        email: payload.email,
        phone: payload.phone,
        address: payload.address,
    };
}

export function mapToHotelPagination(api: Record<string, any>): HotelPagination {
    return {
        currentPage: api.current_page,
        perPage: api.per_page,
        total: api.total,
        lastPage: api.last_page,
    };
}

export function mapToHotelFiltersApi(filters: HotelFilters): Record<string, any> {
    return {
        search: filters.search,
        per_page: filters.perPage,
    };
}

export function mapCreateHotelToApi(payload: CreateHotelDto): Record<string, any> {
    return {
        name: payload.name,
        code: payload.code,
        timezone: payload.timezone,
        currency: payload.currency,
        email: payload.email,
        phone: payload.phone,
        address: payload.address,
    };
}

export function mapUpdateHotelToApi(payload: UpdateHotelDto): Record<string, any> {
    return {
        name: payload.name,
        code: payload.code,
        timezone: payload.timezone,
        currency: payload.currency,
        email: payload.email,
        phone: payload.phone,
        address: payload.address,
    };
}
