import { defineStore } from 'pinia';
import type { User, AuthState } from '@/Types/Auth';

// ─────────────────────────────────────────────────────────
// Initial State
// ─────────────────────────────────────────────────────────
const createInitialState = (): AuthState => ({
    user: null,
    isAuthenticated: false,
    loading: false,
    error: null,
});

// ─────────────────────────────────────────────────────────
// Store
// ─────────────────────────────────────────────────────────
export const useAuthStore = defineStore('auth', {

    state: (): AuthState => createInitialState(),

    // ─────────────────────────────────────────────────────
    // Getters
    // ─────────────────────────────────────────────────────
    getters: {
        isAdmin: (state): boolean => state.user?.role === 'admin',
        isStaff: (state): boolean => state.user?.role === 'staff',
        isManager: (state): boolean => state.user?.role === 'manager',

        userName: (state): string => state.user?.name ?? 'Guest',
        userEmail: (state): string => state.user?.email ?? '',
        userRole: (state): string => state.user?.role ?? 'guest',
    },

    // ─────────────────────────────────────────────────────
    // Actions
    // ─────────────────────────────────────────────────────
    actions: {

        /**
         * Set authenticated user
         */
        setUser(user: User | null): void {
            this.user = user;
            this.isAuthenticated = !!user;
            this.error = null;
        },

        /**
         * Clear user data (logout)
         */
        clearUser(): void {
            this.$patch({
                user: null,
                isAuthenticated: false,
                error: null,
            });
        },

        /**
         * Set loading state
         */
        setLoading(loading: boolean): void {
            this.loading = loading;
        },

        /**
         * Set error
         */
        setError(error: string | null): void {
            this.error = error;
        },

        /**
         * Clear error
         */
        clearError(): void {
            this.error = null;
        },

        /**
         * Initialize auth state from Inertia props
         */
        initializeFromInertia(user: User | null): void {
            this.setUser(user);
        },

        /**
         * Reset store to initial state
         */
        $reset(): void {
            this.$patch(createInitialState());
        },
    },
});
