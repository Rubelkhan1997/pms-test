/**
 * Authentication Helper Functions
 */

/**
 * Check if user has authentication token
 * Checks both localStorage and cookies
 */
export function hasToken(): boolean {
    // Check localStorage
    if (localStorage.getItem('auth_token')) return true;
    
    // Check cookie
    const cookie = document.cookie.split(';').find(c => c.trim().startsWith('auth_token='));
    if (cookie) return true;
    
    return false;
}

/**
 * Get authentication token from localStorage or cookie
 */
export function getToken(): string | null {
    // Check localStorage first
    const localToken = localStorage.getItem('auth_token');
    if (localToken) return localToken;
    
    // Check cookie
    const cookie = document.cookie.split(';').find(c => c.trim().startsWith('auth_token='));
    if (cookie) {
        return cookie.split('=')[1];
    }
    
    return null;
}

/**
 * Remove authentication token
 */
export function removeToken(): void {
    localStorage.removeItem('auth_token');
    // Remove cookie by setting expired date
    document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
}
