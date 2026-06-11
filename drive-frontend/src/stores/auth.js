import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const role = ref(null);
    const authErrors = ref({});
    const isImpersonating = ref(localStorage.getItem('original_token') !== null);

    const isAuthenticated = computed(() => !!user.value);

    const getToken = async () => {
        // This sets the XSRF-TOKEN cookie in the browser
        await api.get('/sanctum/csrf-cookie');
    };

    const fetchUser = async () => {
        try {
            const response = await api.get('/api/user');
            user.value = response.data.user || response.data; // Accommodate different API shapes
            role.value = response.data.role || response.data.user?.role;
        } catch (error) {
            clearAuth(); // Call the cleanup function on failure
        }
    };

    const login = async (credentials) => {
        authErrors.value = {}; // Reset errors on new attempt
        try {
            // Get CSRF cookie first
            await api.get('/sanctum/csrf-cookie');
            
            const response = await api.post('/api/login', credentials);
            
            // SAVE THE TOKEN TO LOCAL STORAGE
            const token = response.data.token || response.data.access_token;
            if (token) {
                localStorage.setItem('token', token);
            }
            
            user.value = response.data.user;
            role.value = response.data.user?.role;
            
            return true; // Tell Login.vue it was successful
        } catch (error) {
            // FORMAT ERRORS FOR THE PREMIUM LOGIN UI
            if (error.response?.status === 422) {
                authErrors.value = error.response.data.errors;
            } else {
                authErrors.value = { general: [error.response?.data?.message || 'Login failed. Invalid credentials.'] };
            }
            return false; // Tell Login.vue it failed
        }
    };

    const logout = async () => {
        try {
            await api.post('/api/logout');
        } catch (error) {
            console.error('Logout failed', error);
        } finally {
            clearAuth(); // Always wipe state, even if API fails
        }
    };

    // --- CRITICAL FIX: The missing function expected by axios.js ---
    const clearAuth = () => {
        user.value = null;
        role.value = null;
        localStorage.removeItem('token');
        
        // Also clear impersonation stash just in case
        localStorage.removeItem('original_token');
        localStorage.removeItem('original_user');
        isImpersonating.value = false;
    };

    // --- IMPOSTOR LOGIC ---
    const impersonate = async (userId) => {
        try {
            const response = await api.post(`/api/users/${userId}/impersonate`);
            
            // 1. Stash the real developer token and user data
            localStorage.setItem('original_token', localStorage.getItem('token'));
            localStorage.setItem('original_user', JSON.stringify(user.value));

            // 2. Apply the fake target token and update state
            localStorage.setItem('token', response.data.token);
            user.value = response.data.user;
            isImpersonating.value = true;
            
            return response;
        } catch (error) {
            console.error('Impersonation failed:', error);
            throw error;
        }
    };

    const stopImpersonating = () => {
        // 1. Retrieve the stashed developer data
        const originalToken = localStorage.getItem('original_token');
        const originalUserStr = localStorage.getItem('original_user');

        // 2. Safely restore them if they exist
        if (originalToken && originalUserStr) {
            localStorage.setItem('token', originalToken);
            localStorage.setItem('user', originalUserStr); 
            
            // Reactively update the state
            user.value = JSON.parse(originalUserStr);
        }

        // 3. Clean up the stash
        localStorage.removeItem('original_token');
        localStorage.removeItem('original_user');
        isImpersonating.value = false;
    };

    // --- CAPABILITY SECURITY CHECK ---
    const can = (permission) => {
        if (!user.value) return false;
        // God Mode: Developers bypass all checks
        if (user.value.role === 'developer') return true;
        // Check if the permission exists in their array
        return user.value.permissions?.includes(permission);
    };
    
    // EXPOSE EVERYTHING TO VUE COMPONENTS
    return { 
        user, role, authErrors, isAuthenticated, isImpersonating, 
        login, logout, fetchUser, getToken, clearAuth, 
        impersonate, stopImpersonating, can 
    };
});