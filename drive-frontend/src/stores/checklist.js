import { defineStore } from 'pinia';
import api from '../axios';

export const useChecklistStore = defineStore('checklist', {
    state: () => ({
        checklists: [],
        loading: false,
        error: null,
        
        currentPage: 1,
        lastPage: 1,
        total: 0,
    }),

    actions: {
        async fetchChecklists(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/api/checklists', { params });
                
                this.checklists = response.data.data;
                this.currentPage = response.data.current_page;
                this.lastPage = response.data.last_page;
                this.total = response.data.total;
            } catch (err) {
                this.error = 'Failed to load checklists.';
                console.error(err);
            } finally {
                this.loading = false;
            }
        },

        // --- NEW ACTION: Fetch full payload for the Official Report ---
        async fetchChecklistDetails(id) {
            try {
                const response = await api.get(`/api/checklists/${id}`);
                return response.data;
            } catch (err) {
                console.error("Fetch Checklist Details Error", err);
                throw err;
            }
        },

        async reviewChecklist(id, payload) {
            try {
                const response = await api.post(`/api/checklists/${id}/review`, payload);
                return response.data;
            } catch (err) {
                throw err;
            }
        },

        async deleteChecklist(id) {
            try {
                const targetId = parseInt(id, 10);
                await api.delete(`/api/checklists/${targetId}`);
                this.checklists = this.checklists.filter(c => c.id !== targetId);
                this.total--;
            } catch (err) {
                throw err;
            }
        },

        async batchDeleteChecklists(ids) {
            try {
                const cleanIds = Array.from(ids).map(id => parseInt(id, 10));
                for (const id of cleanIds) {
                    await api.delete(`/api/checklists/${id}`);
                }
                this.checklists = this.checklists.filter(c => !cleanIds.includes(c.id));
                this.total -= cleanIds.length;
            } catch (err) {
                throw err;
            }
        }
    }
});