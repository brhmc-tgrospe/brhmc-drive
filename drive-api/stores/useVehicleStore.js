import { defineStore } from 'pinia';
import apiClient from '../api/axios';

export const useVehicleStore = defineStore('vehicle', {
    state: () => ({
        vehicles: [],
        selectedVehicle: null,
        loading: false,
        error: null,
        
        // Pagination state
        currentPage: 1,
        lastPage: 1,
        total: 0,
    }),

    getters: {
        availableVehicles: (state) => state.vehicles.filter(v => v.status === 'READY'),
    },

    actions: {
        // Fetch paginated or searched list
        async fetchVehicles(page = 1, search = '', sortBy = 'id', sortDir = 'desc') {
            this.loading = true;
            this.error = null;
            try {
                const response = await apiClient.get('/api/vehicles', {
                    params: {
                        page: page,
                        search: search,
                        sort_by: sortBy,
                        sort_dir: sortDir,
                        per_page: 10
                    }
                });
                
                // Handle Laravel Pagination JSON structure
                this.vehicles = response.data.data;
                this.currentPage = response.data.current_page;
                this.lastPage = response.data.last_page;
                this.total = response.data.total;
                
            } catch (err) {
                this.error = 'Failed to load vehicles.';
                console.error(err);
            } finally {
                this.loading = false;
            }
        },

        // Fetch ALL vehicles (used for dropdowns when scheduling)
        async fetchAllVehicles() {
             try {
                const response = await apiClient.get('/api/vehicles', { params: { all: true } });
                return response.data; // Returns raw array, doesn't mutate paginated state
             } catch (err) {
                 console.error("Failed to fetch all vehicles", err);
                 return [];
             }
        },

        selectVehicle(vehicle) {
            this.selectedVehicle = vehicle;
        },

        clearSelection() {
            this.selectedVehicle = null;
        }
    }
});