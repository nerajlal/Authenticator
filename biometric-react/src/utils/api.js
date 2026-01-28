import axios from 'axios';

/**
 * Get CSRF token from meta tag
 */
export const getCsrfToken = () => {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
};

/**
 * Configure axios instance with CSRF token
 */
const api = axios.create({
    baseURL: window.location.origin,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true,
});

// Add CSRF token to all requests
api.interceptors.request.use((config) => {
    const token = getCsrfToken();
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token;
    }
    return config;
});

export default api;
