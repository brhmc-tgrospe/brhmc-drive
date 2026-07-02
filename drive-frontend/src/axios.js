import axios from 'axios';
import { useAuthStore } from './stores/auth'; 

const apiClient = axios.create({
    baseURL: window.location.origin, 
    withCredentials: true, // Still required for CSRF protection
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// ==========================================
// 1. REQUEST INTERCEPTOR (The Missing Piece)
// ==========================================
// This intercepts every outgoing request BEFORE it goes to Laravel
// and physically attaches your logged-in token to the header.
apiClient.interceptors.request.use(
    (config) => {
        // Grab the token that auth.js saved during login
        const token = localStorage.getItem('token');
        
        // If the token exists, attach it as a Bearer authorization header
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
            
            // Background polling endpoints that should NOT reset the inactivity timer
            const backgroundUrls = [
                '/api/logout-timeout',
                '/api/shifts/my-shifts',
                '/api/telemetry/ping',
                '/sanctum/csrf-cookie'
            ];
            
            const isBackground = backgroundUrls.some(url => config.url?.includes(url));
            
            if (!isBackground) {
                const authStore = useAuthStore();
                if (authStore.isAuthenticated) {
                    authStore.resetTimeout();
                }
            }
        }
        
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// ==========================================
// 2. RESPONSE INTERCEPTOR (The Bouncer)
// ==========================================
apiClient.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        // Check for Maintenance Mode
        if (error.response && error.response.status === 503 && error.response.data?.code === 'MAINTENANCE_MODE') {
            const authStore = useAuthStore();
            authStore.clearAuth();
            window.location.href = '/maintenance';
            return Promise.reject(error);
        }

        // If Laravel rejects the token (expired or invalid), kick the user out safely
        if (error.response && error.response.status === 401) {
            const authStore = useAuthStore();
            authStore.clearAuth(); // Wipes local state and token
            
            // Redirect smoothly without breaking the router
            if (window.location.pathname !== '/login') {
                window.location.href = '/login'; 
            }
        }
        
        return Promise.reject(error);
    }
);

export default apiClient;