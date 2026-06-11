import { defineStore } from 'pinia';
import apiClient from '../api/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        isAuthenticated: false,
        loading: false,
        error: null,
    }),

    actions: {
        // Initialization check (runs when app loads to see if a session exists)
        async fetchUser() {
            try {
                const response = await apiClient.get('/api/user');
                this.user = response.data;
                this.isAuthenticated = true;
            } catch (err) {
                this.clearAuth();
            }
        },

        async login(credentials) {
            this.loading = true;
            this.error = null;
            try {
                // 1. Fetch CSRF Cookie FIRST (Sanctum Requirement)
                await apiClient.get('/sanctum/csrf-cookie');

                // 2. Attempt Login
                await apiClient.post('/api/login', credentials);

                // 3. If successful, fetch user data
                await this.fetchUser();
                
            } catch (err) {
                this.error = err.response?.data?.message || 'Login failed. Please check your credentials.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                await apiClient.post('/api/logout');
            } catch (err) {
                console.error('Logout error', err);
            } finally {
                this.clearAuth();
            }
        },

        clearAuth() {
            this.user = null;
            this.isAuthenticated = false;
        }
    },
});