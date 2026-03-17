/**
 * Housekeeping Types
 */

export type TaskStatus = 'pending' | 'in_progress' | 'completed' | 'cancelled' | 'overdue';
export type TaskPriority = 'low' | 'medium' | 'high' | 'urgent';

export interface HousekeepingTask {
    id: number;
    title: string;
    description?: string;
    status: TaskStatus;
    priority: TaskPriority;
    room_id?: number;
    room_number?: string;
    assigned_to?: number;
    assigned_to_name?: string;
    due_date: string;
    completed_at?: string;
    notes?: string;
    created_at?: string;
    updated_at?: string;
}

export interface MaintenanceRequest {
    id: number;
    title: string;
    description: string;
    priority: TaskPriority;
    status: 'reported' | 'assigned' | 'in_progress' | 'completed' | 'cancelled';
    location: string;
    room_id?: number;
    reported_by: number;
    reported_by_name?: string;
    assigned_to?: number;
    assigned_to_name?: string;
    completed_at?: string;
    resolution_notes?: string;
    created_at?: string;
    updated_at?: string;
}

export interface RoomStatusUpdate {
    room_id: number;
    previous_status: string;
    new_status: string;
    updated_by: number;
    updated_by_name?: string;
    reason?: string;
    timestamp: string;
}
