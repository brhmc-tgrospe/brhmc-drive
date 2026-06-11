import { defineStore } from 'pinia';
import api from '../axios';

export const useShiftStore = defineStore('shift', {
    state: () => ({
        shifts: [],
        loading: false,
        error: null,
        
        // Pagination state
        currentPage: 1,
        lastPage: 1,
        total: 0,
    }),

    actions: {
        async fetchShifts(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/api/shifts', { params });
                
                // Maps the nested DB payload from our VehicleShiftController
                this.shifts = response.data.data;
                this.currentPage = response.data.current_page;
                this.lastPage = response.data.last_page;
                this.total = response.data.total;
            } catch (err) {
                this.error = 'Failed to load schedules.';
                console.error(err);
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async addShift(payload) {
            try {
                const response = await api.post('/api/shifts', payload);
                return response.data;
            } catch (err) {
                // Pass the strict 422 double-booking error message up to the component
                throw err;
            }
        },

        async updateShift(id, payload) {
            try {
                const response = await api.put(`/api/shifts/${id}`, payload);
                return response.data;
            } catch (err) {
                throw err;
            }
        },

        async deleteShift(id) {
            try {
                await api.delete(`/api/shifts/${id}`);
            } catch (err) {
                throw err;
            }
        }
    }
});