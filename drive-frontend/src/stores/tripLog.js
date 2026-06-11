import { defineStore } from 'pinia';
import api from '../axios';

export const useTripLogStore = defineStore('tripLog', {
    state: () => ({
        trips: [],
        loading: false,
        currentPage: 1,
        lastPage: 1,
        total: 0,
    }),

    actions: {
        async fetchTrips(params = {}) {
            this.loading = true;
            try {
                const response = await api.get('/api/trips/logs', { params });
                this.trips = response.data.data;
                this.currentPage = response.data.current_page;
                this.lastPage = response.data.last_page;
                this.total = response.data.total;
            } catch (err) {
                console.error('Failed to load trip logs', err);
            } finally {
                this.loading = false;
            }
        },

        async getTripDetails(id) {
            try {
                const response = await api.get(`/api/trips/logs/${id}`);
                return response.data;
            } catch (err) {
                console.error('Failed to load trip details', err);
                throw err;
            }
        },

        // Single delete mirrors checklist.js
        async deleteTripLog(id) {
            try {
                const targetId = parseInt(id, 10);
                await api.delete(`/api/trips/logs/${targetId}`);
                
                // Optimistically remove from state
                this.trips = this.trips.filter(c => c.id !== targetId);
                this.total--;
            } catch (err) {
                throw err;
            }
        },

        // CRITICAL FIX: Loop deletion perfectly mirrors checklist.js
        async batchDeleteTripLogs(ids) {
            try {
                const cleanIds = Array.from(ids).map(id => parseInt(id, 10));
                
                // Process sequentially
                for (const id of cleanIds) {
                    await api.delete(`/api/trips/logs/${id}`);
                }
                
                // Optimistically remove from state
                this.trips = this.trips.filter(c => !cleanIds.includes(c.id));
                this.total -= cleanIds.length;
            } catch (err) {
                console.error('Batch Delete Error:', err);
                throw err;
            }
        }
    }
});