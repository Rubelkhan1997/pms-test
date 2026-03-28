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
        // Common types (use type alias, not export)
        type User = import('./common').User;
        type UserRole = import('./common').UserRole;
        type ApiResponse = import('./api').ApiResponse;
        type PaginatedResponse = import('./api').PaginatedResponse;

        // FrontDesk types
        type Reservation = import('./FrontDesk/reservation').Reservation;
        type ReservationStatus = import('./FrontDesk/reservation').ReservationStatus;
        type ReservationGuest = import('./FrontDesk/reservation').Guest;
        type ReservationRoom = import('./FrontDesk/reservation').Room;

        // POS types
        interface PosOrder {
            id: number;
            reference: string;
            status: string;
            outlet: string;
        }

        // HR types
        interface Employee {
            id: number;
            reference: string;
            department: string;
            status: string;
        }
    }
}
