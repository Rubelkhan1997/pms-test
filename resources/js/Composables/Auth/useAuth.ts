import { computed, inject } from 'vue';
import { useAuthStore } from '@/Stores/Auth/authStore';
import { useForm } from '@inertiajs/vue3';
import { useLoading } from '@/Helpers';
import type { toast as ToastType } from '@/Plugins/toast';
import type { LoginDto, RegisterDto, User } from '@/Types/Auth/auth';

// ─────────────────────────────────────────────────────────
// Composable
// ─────────────────────────────────────────────────────────

export function useAuth() {

    // ─── Store (internal) ────────────────────────────────
    const store = useAuthStore();

    // ─── Inject Toast ────────────────────────────────────
    const toast = inject('toast') as typeof ToastType;

    // ─── UI Helpers ──────────────────────────────────────
    const { loading, start, stop } = useLoading();

    // ─── Inertia Forms ───────────────────────────────────
    const loginForm = useForm<LoginDto>({
        email: '',
        password: '',
        remember: false,
    });

    const registerForm = useForm<RegisterDto>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'staff',
    });

    // ─────────────────────────────────────────────────────
    // Reactive State
    // ─────────────────────────────────────────────────────

    const user = computed(() => store.user);
    const isAuthenticated = computed(() => store.isAuthenticated);
    const error = computed(() => store.error);

    // ─── Derived (Store Getters) ─────────────────────────
    const isAdmin = computed(() => store.isAdmin);
    const isStaff = computed(() => store.isStaff);
    const isManager = computed(() => store.isManager);
    const userName = computed(() => store.userName);
    const userEmail = computed(() => store.userEmail);
    const userRole = computed(() => store.userRole);

    // ─────────────────────────────────────────────────────
    // Actions
    // ─────────────────────────────────────────────────────

    /**
     * Login user
     */
    async function login(dto: LoginDto): Promise<void> {
        start();
        try {
            await new Promise<void>((resolve, reject) => {
                loginForm.post('/login', {
                    preserveScroll: true,
                    onSuccess: () => {
                        toast.success('Welcome back!');
                        resolve();
                    },
                    onError: (errors) => {
                        toast.error(errors.email || 'Login failed. Please check your credentials.');
                        reject(new Error('Login failed'));
                    },
                    onFinish: () => {
                        stop();
                    },
                });
            });
        } catch {
            // Error handled in onError
        }
    }

    /**
     * Register new user
     */
    async function register(dto: RegisterDto): Promise<void> {
        start();
        try {
            await new Promise<void>((resolve, reject) => {
                registerForm.post('/register', {
                    preserveScroll: true,
                    onSuccess: () => {
                        toast.success('Account created successfully!');
                        resolve();
                    },
                    onError: (errors) => {
                        const firstError = Object.values(errors)[0] || 'Registration failed.';
                        toast.error(firstError as string);
                        reject(new Error('Registration failed'));
                    },
                    onFinish: () => {
                        stop();
                    },
                });
            });
        } catch {
            // Error handled in onError
        }
    }

    /**
     * Logout user
     */
    function logout(): void {
        start();
        registerForm.post('/logout', {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Logged out successfully.');
            },
            onError: () => {
                toast.error('Logout failed.');
            },
            onFinish: () => {
                stop();
            },
        });
    }

    /**
     * Check if user has specific role
     */
    function hasRole(role: string): boolean {
        return store.user?.role === role;
    }

    /**
     * Check if user has any of the given roles
     */
    function hasAnyRole(roles: string[]): boolean {
        return roles.includes(store.user?.role ?? '');
    }

    /**
     * Initialize user from Inertia props
     */
    function initializeFromInertia(user: User | null): void {
        store.initializeFromInertia(user);
    }

    /**
     * Clear login form errors
     */
    function clearLoginErrors(): void {
        loginForm.clearErrors();
        store.clearError();
    }

    /**
     * Clear register form errors
     */
    function clearRegisterErrors(): void {
        registerForm.clearErrors();
        store.clearError();
    }

    /**
     * Reset forms
     */
    function resetForms(): void {
        loginForm.reset();
        registerForm.reset();
        clearLoginErrors();
        clearRegisterErrors();
    }

    // ─────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────

    return {
        // State
        user,
        isAuthenticated,
        loading,
        error,

        // Forms
        loginForm,
        registerForm,

        // Derived
        isAdmin,
        isStaff,
        isManager,
        userName,
        userEmail,
        userRole,

        // Actions
        login,
        register,
        logout,
        hasRole,
        hasAnyRole,
        initializeFromInertia,
        clearLoginErrors,
        clearRegisterErrors,
        resetForms,
    };
}
