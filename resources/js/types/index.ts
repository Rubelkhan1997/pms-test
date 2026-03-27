/**
 * Types Barrel Export
 * Central export file for all TypeScript types
 */

// Common types
export type { User, UserRole } from './common';

// API types
export type { ApiResponse, PaginatedResponse } from './api';

// Payment types
export type { Payment, PaymentMethod, PaymentStatus } from './payment';

// FrontDesk module types
export type {
    Reservation,
    ReservationStatus,
    Guest,
    Room
} from './FrontDesk/reservation';

export type { RoomStatus, RoomType } from './FrontDesk/room';
export type { GuestProfile, GuestType } from './FrontDesk/guest';

// Housekeeping module types
export type { HousekeepingTask, TaskStatus, TaskPriority } from './Housekeeping/task';

// ============================================================================
// Global PMS Namespace (for backward compatibility & template type hints)
// ============================================================================
declare global {
    namespace PMS {
        // Common types
        export type { User, UserRole } from './common';
        export type { ApiResponse, PaginatedResponse } from './api';

        // FrontDesk types
        export type { Reservation, ReservationStatus } from './FrontDesk/reservation';
        export type { Guest } from './FrontDesk/reservation';
        export type { Room } from './FrontDesk/reservation';

        // POS types
        export interface PosOrder {
            id: number;
            reference: string;
            status: string;
            outlet: string;
        }

        // HR types
        export interface Employee {
            id: number;
            reference: string;
            department: string;
            status: string;
        }
    }
}
