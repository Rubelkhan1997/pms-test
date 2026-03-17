/**
 * Permission Directive
 * Role-based permission directive for Vue 3
 *
 * Usage:
 * <button v-permission="['admin', 'manager']">Admin Only</button>
 * <button v-permission="'admin'">Admin Only</button>
 */

import { App, Directive } from 'vue';

export interface PermissionOptions {
    getUserRoles?: () => string[];
    onDenied?: (el: HTMLElement) => void;
}

const defaultOptions: Required<PermissionOptions> = {
    getUserRoles: () => {
        const user = localStorage.getItem('pms_user');
        if (user) {
            try {
                const userData = JSON.parse(user);
                return userData.roles || [userData.role] || [];
            } catch {
                return [];
            }
        }
        return [];
    },
    onDenied: (el: HTMLElement) => {
        el.style.display = 'none';
    }
};

let options = { ...defaultOptions };

/**
 * Check if user has permission
 */
function hasPermission(requiredRoles: string | string[]): boolean {
    const userRoles = options.getUserRoles();

    if (!userRoles || userRoles.length === 0) {
        return false;
    }

    const roles = Array.isArray(requiredRoles) ? requiredRoles : [requiredRoles];

    // Check if user has any of the required roles
    return roles.some(role => userRoles.includes(role));
}

/**
 * Permission directive definition
 */
export const permissionDirective: Directive<HTMLElement, string | string[]> = {
    mounted(el, binding) {
        const requiredRoles = binding.value;

        if (!hasPermission(requiredRoles)) {
            options.onDenied(el);
            el.setAttribute('data-permission-denied', 'true');
        }
    },
    updated(el, binding) {
        const requiredRoles = binding.value;

        if (!hasPermission(requiredRoles)) {
            options.onDenied(el);
            el.setAttribute('data-permission-denied', 'true');
        } else {
            el.style.display = '';
            el.removeAttribute('data-permission-denied');
        }
    }
};

/**
 * Setup directive with custom options
 */
export function setupPermission(customOptions: PermissionOptions): void {
    options = { ...defaultOptions, ...customOptions };
}

/**
 * Install plugin
 */
export function install(app: App, customOptions?: PermissionOptions): void {
    if (customOptions) {
        setupPermission(customOptions);
    }

    app.directive('permission', permissionDirective);
}

// Composable for programmatic usage
export function usePermission() {
    function check(requiredRoles: string | string[]): boolean {
        return hasPermission(requiredRoles);
    }

    function hasRole(role: string): boolean {
        return hasPermission(role);
    }

    function hasAnyRole(roles: string[]): boolean {
        return hasPermission(roles);
    }

    function hasAllRoles(roles: string[]): boolean {
        const userRoles = options.getUserRoles();
        return roles.every(role => userRoles.includes(role));
    }

    return {
        check,
        hasRole,
        hasAnyRole,
        hasAllRoles
    };
}

export default { install, setupPermission };
