// ============================================================================
// Reservation Core Types
// ============================================================================
export type ReservationStatus =
    | 'pending'
    | 'draft'
    | 'confirmed'
    | 'checked_in'
    | 'checked_out'
    | 'cancelled'
    | 'no_show';
     
export interface Reservation {
    id: number;
    hotelId: number;
    guestId: number;
    roomId: number;
    reference?: string;
    checkInDate: string;
    checkOutDate: string;
    totalAmount: number;
    status: ReservationStatus;
    notes?: string;
    adults?: number;
    children?: number;
    meta?: Record<string, any>;
    createdAt?: string;
    
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
    checkInDate: string;
    checkOutDate: string;
    search: string;
    perPage?: number;
}

export interface ReservationPagination {
    currentPage: number;
    perPage: number;
    total: number;
    lastPage: number;
}

// ============================================================================
// DTO Types (API Payload)
// ============================================================================
export interface CreateReservationDto {
    hotelId: number;
    guestId: number;        
    roomId: number;
    checkInDate: string;
    checkOutDate: string;
    totalAmount: number;
    adults?: number;
    children?: number;
    status?: ReservationStatus;
    notes?: string;
}
 
export interface UpdateReservationDto {
    hotelId: number;
    guestId: number;
    roomId: number;
    checkInDate?: string;
    checkOutDate?: string;
    totalAmount?: number;
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
    firstName: string;
    lastName: string;
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
