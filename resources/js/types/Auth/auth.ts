/**
 * Auth Types
 */

// ============================================================================
// User Types
// ============================================================================
export interface User {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    email_verified_at?: string;
    created_at?: string;
    updated_at?: string;
}

export type UserRole = 'admin' | 'staff' | 'manager';

// ============================================================================
// DTO Types (Form Data)
// ============================================================================
export interface LoginDto {
    email: string;
    password: string;
    remember?: boolean;
}

export interface RegisterDto {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    role?: UserRole;
}

// ============================================================================
// Auth State
// ============================================================================
export interface AuthState {
    user: User | null;
    isAuthenticated: boolean;
    loading: boolean;
    error: string | null;
}

// ============================================================================
// API Response Types
// ============================================================================
export interface AuthResponse {
    success: boolean;
    user?: User;
    message?: string;
}
