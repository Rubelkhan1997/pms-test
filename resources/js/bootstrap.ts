/**
 * Axios Bootstrap
 * Global axios configuration
 */

import axios from 'axios';

// Set axios as global window property
window.axios = axios;

// Default headers
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.headers.common['Content-Type'] = 'application/json';

// Request timeout
window.axios.defaults.timeout = 30000; // 30 seconds

// Optional: Add request interceptor for auth token
// axios.interceptors.request.use(
//     (config) => {
//         const token = localStorage.getItem('auth_token');
//         if (token) {
//             config.headers.Authorization = `Bearer ${token}`;
//         }
//         return config;
//     },
//     (error) => {
//         return Promise.reject(error);
//     }
// );

// Optional: Add response interceptor for error handling
// axios.interceptors.response.use(
//     (response) => response,
//     (error) => {
//         if (error.response?.status === 401) {
//             // Handle unauthorized access
//             window.location.href = '/login';
//         }
//         return Promise.reject(error);
//     }
// );
