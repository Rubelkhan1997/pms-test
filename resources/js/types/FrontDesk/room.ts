export type RoomStatus = 'available' | 'occupied' | 'maintenance' | 'dirty';

export interface Room {
    id: number;
    number: string;
    status: RoomStatus;
    type: string;
    price: number;
    floor?: number;
    description?: string;
    amenities?: string[];
    created_at?: string;
    updated_at?: string;
}

export interface RoomType {
    id: number;
    name: string;
    code: string;
    description?: string;
    base_price: number;
    capacity: number;
    amenities?: string[];
}
