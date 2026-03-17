/**
 * API Client
 * Axios instance configuration for API requests
 */

import axios, { AxiosInstance, AxiosRequestConfig, AxiosResponse, AxiosError } from 'axios';
import { API_CONFIG } from '@/Utils/constants';

// Create axios instance
const apiClient: AxiosInstance = axios.create({
    baseURL: `${API_CONFIG.BASE_URL}/${API_CONFIG.VERSION}`,
    timeout: API_CONFIG.TIMEOUT,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Request interceptor
apiClient.interceptors.request.use(
    (config) => {
        // Add auth token if available
        const token = localStorage.getItem('pms_auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        // Add request ID for tracking
        config.headers['X-Request-ID'] = generateRequestId();

        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor
apiClient.interceptors.response.use(
    (response: AxiosResponse) => {
        return response;
    },
    async (error: AxiosError) => {
        // Handle common errors
        if (error.response) {
            const status = error.response.status;

            switch (status) {
                case 401:
                    // Handle unauthorized - redirect to login
                    handleUnauthorized();
                    break;
                case 403:
                    // Handle forbidden
                    handleForbidden();
                    break;
                case 404:
                    // Handle not found
                    break;
                case 422:
                    // Handle validation errors
                    break;
                case 500:
                    // Handle server error
                    handleServerError();
                    break;
            }
        }

        return Promise.reject(error);
    }
);

// Generate unique request ID
function generateRequestId(): string {
    return `req_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
}

// Error handlers
function handleUnauthorized(): void {
    // Clear auth data
    localStorage.removeItem('pms_auth_token');
    localStorage.removeItem('pms_user');

    // Redirect to login
    if (typeof window !== 'undefined') {
        window.location.href = '/login';
    }
}

function handleForbidden(): void {
    console.error('Access forbidden');
    // Show forbidden message
}

function handleServerError(): void {
    console.error('Server error occurred');
    // Show server error message
}

// Export default instance
export default apiClient;

// Export for direct usage
export { apiClient };

// Type exports
export type { AxiosInstance, AxiosRequestConfig, AxiosResponse, AxiosError };
