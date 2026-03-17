/**
 * API Client
 * Axios instance configuration for API requests
 * 
 * Note: This is a base template. Update baseURL and interceptors as needed.
 */

import axios, { AxiosInstance } from 'axios';

// Create axios instance with default config
const apiClient: AxiosInstance = axios.create({
    baseURL: '/api', // Update with your API base URL
    timeout: 30000,  // 30 seconds
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Request interceptor (optional)
apiClient.interceptors.request.use(
    (config) => {
        // Add auth token if available
        // const token = localStorage.getItem('auth_token');
        // if (token) {
        //     config.headers.Authorization = `Bearer ${token}`;
        // }
        
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor (optional)
apiClient.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        // Handle common errors here
        // if (error.response?.status === 401) {
        //     // Handle unauthorized
        // }
        
        return Promise.reject(error);
    }
);

// Export default instance
export default apiClient;

// Export for direct usage
export { apiClient };
