/**
 * API Client
 * Centralized axios instance for all API requests
 */

import axios, { AxiosInstance } from 'axios';
import { getToken, removeToken } from '@/Utils/authToken';

// Create base axios instance with default config
function createApiClient(baseURL: string): AxiosInstance {
    const apiClient: AxiosInstance = axios.create({
        baseURL,
        timeout: 30000,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    // Request interceptor - Add auth token
    apiClient.interceptors.request.use(
        (config) => {
            const token = getToken();
            if (token) {
                config.headers.Authorization = `Bearer ${token}`;
            }
            return config;
        },
        (error) => {
            return Promise.reject(error);
        }
    );

    // Response interceptor - Handle errors globally
    apiClient.interceptors.response.use(
        (response) => response,
        (error) => {
            if (error.response?.status === 401) {
                removeToken();
                window.location.href = '/login';
            }
            return Promise.reject(error);
        }
    );

    return apiClient;
}

// API instances
const apiClientV1 = createApiClient('/api/v1');
const apiClientV2 = createApiClient('/api/v2');

// Named exports
export { apiClientV1, apiClientV2 };

// Default export - Object with all versions
const apiClient = {
    v1: apiClientV1,
    v2: apiClientV2,
};

export default apiClient;
