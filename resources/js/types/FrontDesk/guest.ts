export interface Guest {
    id: number;
    name: string;
    email: string;
    phone: string;
    nid?: string;
    address?: string;
    country?: string;
    nationality?: string;
    passport_number?: string;
    date_of_birth?: string;
    gender?: 'male' | 'female' | 'other';
    loyalty_points?: number;
    notes?: string;
    created_at?: string;
    updated_at?: string;
}

export interface GuestProfile {
    id: number;
    guest_id: number;
    preferences?: {
        room_type?: string;
        floor_preference?: number;
        smoking_preference?: 'smoking' | 'non-smoking';
        bed_type?: 'single' | 'double' | 'king';
    };
    dietary_restrictions?: string[];
    special_requests?: string;
}
