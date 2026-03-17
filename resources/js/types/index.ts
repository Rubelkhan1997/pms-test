/**
 * Types Barrel Export
 * Central export file for all TypeScript types
 */

// Common types
export * from './common';

// API types
export * from './api';

// Payment types
export * from './payment';

// FrontDesk module types
export {
    Reservation,
    ReservationStatus,
    Guest,
    Room
} from './FrontDesk/reservation';

export type { RoomStatus } from './FrontDesk/room';
export type { Room as RoomDetail, RoomType } from './FrontDesk/room';
export type { Guest as GuestDetail, GuestProfile } from './FrontDesk/guest';

// Housekeeping module types
export type { HousekeepingTask, TaskStatus, TaskPriority } from './Housekeeping/task';

// Global namespace for backward compatibility
declare global {
    namespace PMS {
        // Common
        export type { User, UserRole } from './common';
        export type { ApiResponse, PaginatedResponse } from './common';

        // FrontDesk
        export type { Reservation, ReservationStatus } from './FrontDesk/reservation';
        export type { Guest } from './FrontDesk/reservation';
        export type { Room } from './FrontDesk/reservation';

        // POS
        export interface PosOrder {
            id: number;
            reference: string;
            status: string;
            outlet: string;
        }

        // HR
        export interface Employee {
            id: number;
            reference: string;
            department: string;
            status: string;
        }
    }
}
