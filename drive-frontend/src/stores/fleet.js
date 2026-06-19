import { defineStore } from 'pinia';
import api from '../axios';

export const useFleetStore = defineStore('fleet', {
  state: () => ({
    vehicles: [],
    total: 0,
    ready: 0,
    in_use: 0,
    maintenance: 0,
    breakdown: 0,
    isLoading: false,
  }),
  actions: {
    async fetchFleet() {
      this.isLoading = true;
      try {
        const response = await api.get('/api/vehicles?all=true');
        // Safely extract data whether it's inside a 'data' object or just a flat array
        this.vehicles = response.data.data ? response.data.data : response.data;
      } catch (error) {
        console.error("Error fetching vehicles:", error);
      } finally {
        this.isLoading = false;
      }
    },
    
    // ... ADDED THIS FUNCTION ...
    async addVehicle(vehicleData) {
        try {
            const res = await api.post('/api/vehicles', vehicleData);
            await this.fetchFleet();
            return res.data;
        } catch (error) {
            console.error('Failed to add vehicle:', error);
            throw error;
        }
    },

    async updateVehicle(id, vehicleData) {
        try {
            await api.put(`/api/vehicles/${id}`, vehicleData);
            await this.fetchFleet();
            return true;
        } catch (error) {
            console.error('Failed to update vehicle:', error);
            throw error;
        }
    },

    async deleteVehicle(id) {
        try {
            await api.delete(`/api/vehicles/${id}`);
            await this.fetchFleet();
            return true;
        } catch (error) {
            console.error('Failed to delete vehicle:', error);
            throw error;
        }
    },
    
// Updated to accept and send Base64 strings safely
    async uploadVehicleImage(id, base64String) {
        try {
            // CRITICAL FIX: Renamed the key to 'base64_image' and explicitly cast as String
            const response = await api.post(`/api/vehicles/${id}/image`, { 
                base64_image: String(base64String) 
            });
            return response.data;
        } catch (err) {
            console.error("Upload Error:", err);
            throw err;
        }
    },
  },
});