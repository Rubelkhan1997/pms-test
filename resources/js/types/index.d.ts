declare namespace PMS {
    interface Hotel {
        id: number;
        name: string;
        code: string;
    }

    interface Guest {
        id: number;
        name: string;
        email: string;
        phone: string;
        nid?: string;
    }

    interface Room {
        id: number;
        number: string;
        status: 'available' | 'occupied' | 'maintenance' | 'dirty';
        type: string;
        price: number;
        floor?: number;
    }

    interface Reservation {
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

    type ReservationStatus = 'pending' | 'confirmed' | 'checked_in' | 'checked_out' | 'cancelled' | 'no_show';

    interface PosOrder {
        id: number;
        reference: string;
        status: string;
        outlet: string;
    }

    interface Employee {
        id: number;
        reference: string;
        department: string;
        status: string;
    }
}
