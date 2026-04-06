// ============================================================================
// Hotel Core Types
// ============================================================================
export interface Hotel {
    id: number;
    name: string;
    code: string;
    timezone?: string;
    currency?: string;
    email?: string;
    phone?: string;
    address?: string;
    createdAt?: string;
    updatedAt?: string;
}

// ============================================================================
// Store Types
// ============================================================================
export interface HotelFilters {
    search: string;
    perPage?: number;
}

export interface HotelPagination {
    currentPage: number;
    perPage: number;
    total: number;
    lastPage: number;
}

// ============================================================================
// DTO Types (API Payload)
// ============================================================================
export interface CreateHotelDto {
    name: string;
    code: string;
    timezone?: string;
    currency?: string;
    email?: string;
    phone?: string;
    address?: string;
}

export interface UpdateHotelDto {
    name?: string;
    code?: string;
    timezone?: string;
    currency?: string;
    email?: string;
    phone?: string;
    address?: string;
}
