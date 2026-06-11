import { defineStore } from 'pinia';
import api from '../axios';

export const useIncidentStore = defineStore('incident', {
    state: () => ({
        incidents: [],
        loading: false,
        currentPage: 1,
        lastPage: 1,
        total: 0,
    }),

    actions: {
        async fetchIncidents(params = {}) {
            this.loading = true;
            try {
                // CRITICAL FIX: Bypass Browser Caching!
                params.t = new Date().getTime();
                
                const response = await api.get('/api/emergencies/report', { params });
                
                this.incidents = response.data.data ? response.data.data : response.data;
                this.currentPage = response.data.current_page || 1;
                this.lastPage = response.data.last_page || 1;
                this.total = response.data.total || 0;
            } catch (err) {
                console.error(err);
            } finally {
                this.loading = false;
            }
        },

        async acknowledgeIncident(id, payload) {
            await api.post(`/api/emergencies/report/${id}/acknowledge`, payload);
        },

        async resolveIncident(id) {
            await api.post(`/api/emergencies/report/${id}/resolve`);
        },

        async updateIncident(id, payload) {
            await api.put(`/api/emergencies/report/${id}`, payload);
        },

        async deleteIncident(id) {
            await api.delete(`/api/emergencies/report/${id}`);
        }
    }
});