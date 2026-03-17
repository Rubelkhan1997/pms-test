export type ReservationStatus = 'pending' | 'confirmed' | 'checked_in' | 'checked_out' | 'cancelled' | 'no_show';

export interface Reservation {
    id: number;
    reference: string;
    status: ReservationStatus;
    check_in_date: string;
    check_out_date: string;
    guest_id: number;
    guest?: Guest;
    room_id: number;
    room?: Room;
    total_amount: number;
    paid_amount: number;
    notes?: string;
    created_at: string;
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
