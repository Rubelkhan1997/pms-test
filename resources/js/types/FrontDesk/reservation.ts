// ============================================================================
// Reservation Core Types
// ============================================================================
export type ReservationStatus =
    | 'pending'
    | 'confirmed'
    | 'checked_in'
    | 'checked_out'
    | 'cancelled'
    | 'no_show';

export interface Reservation {
    id: number;
    hotel_id: number;
    guest_id: number;
    room_id: number;
    reference?: string;
    check_in_date: string;
    check_out_date: string;
    total_amount: number;
    status: ReservationStatus;
    notes?: string;
    adults?: number;
    children?: number;
    meta?: Record<string, any>;
    created_at?: string;
    
    // ✅ Relations (optional - loaded separately)
    hotel?: HotelOption;
    room?: RoomOption;
    guest?: GuestOption;
}

export interface Guest {
    id: number;
    name: string;
    email: string;
    phone: string;
    nid?: string;
}

export interface Room {
    id: number;
    number: string;
    status: 'available' | 'occupied' | 'maintenance' | 'dirty';
    type: string;
    price: number;
    floor?: number;
}

// ============================================================================
// Store Types
// ============================================================================
export interface ReservationFilters {
    status: ReservationStatus | '';
    check_in_date: string;
    check_out_date: string;
    search: string;
    per_page?: number;  // ✅ Add per_page filter
}

export interface ReservationPagination {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
}

// ============================================================================
// DTO Types (API Payload)
// ============================================================================
export interface CreateReservationDto {
    hotel_id: number;
    guest_id: number;        
    room_id: number;
    check_in_date: string;
    check_out_date: string;
    total_amount: number;
    adults?: number;
    children?: number;
    status?: ReservationStatus;
    notes?: string;
}
 
export interface UpdateReservationDto {
    hotel_id: number;
    guest_id: number;
    room_id: number;
    check_in_date?: string;
    check_out_date?: string;
    total_amount?: number;
    status?: ReservationStatus;
    adults?: number;
    children?: number;
    notes?: string;
}

// ============================================================================
// Page Props Types
// ============================================================================
export interface HotelOption {
    id: number;
    name: string;
    code: string;
}

export interface GuestOption {
    id: number;
    first_name: string;
    last_name: string;
    email?: string;
    phone?: string;
}

export interface RoomOption {
    id: number;
    number: string;
    type: string;
    price: number;
    status: string;
}