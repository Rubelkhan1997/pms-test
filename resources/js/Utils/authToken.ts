import { getTokenFromCookie, hasTokenInCookie } from '@/Helpers/auth';

const TOKEN_KEY = 'auth_token';

export function hasToken(): boolean {
    return !!localStorage.getItem(TOKEN_KEY) || hasTokenInCookie(document.cookie, TOKEN_KEY);
}

export function getToken(): string | null {
    const localToken = localStorage.getItem(TOKEN_KEY);
    if (localToken) return localToken;
    return getTokenFromCookie(document.cookie, TOKEN_KEY);
}

export function setToken(token: string, rememberDays = 1): void {
    localStorage.setItem(TOKEN_KEY, token);
    document.cookie = `${TOKEN_KEY}=${token}; path=/; max-age=${60 * 60 * 24 * rememberDays}; SameSite=Lax`;
}

export function removeToken(): void {
    localStorage.removeItem(TOKEN_KEY);
    document.cookie = `${TOKEN_KEY}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
}
