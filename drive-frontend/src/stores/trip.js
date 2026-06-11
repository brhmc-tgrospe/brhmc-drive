import { defineStore } from 'pinia';
import api from '../axios';

export const useTripStore = defineStore('trip', {
    state: () => ({
        activeShift: null,
        activeTrip: null,
        loading: false,
        error: null,
    }),

    actions: {
        async fetchMyActiveShift() {
            this.loading = true;
            this.error = null;
            try {
                // Fetch the driver's upcoming or currently active shift
                const response = await api.get('/api/shifts/my-shifts');
                
                if (response.data && response.data.length > 0) {
                    this.activeShift = response.data[0];
                    
                    // If the shift already has an active trip attached, store it
                    if (this.activeShift.trip) {
                        this.activeTrip = this.activeShift.trip;
                    }
                } else {
                    this.activeShift = null;
                    this.activeTrip = null;
                }
            } catch (err) {
                this.error = 'Failed to load active assignment.';
                console.error(err);
            } finally {
                this.loading = false;
            }
        },

        // Phase 0: Initialize the Trip
        async initializeTrip(shiftId) {
            try {
                const response = await api.post('/api/trips', { shift_id: shiftId });
                this.activeTrip = { id: response.data.trip_id, current_phase: 0 };
                return this.activeTrip;
            } catch (err) {
                throw err;
            }
        },

        // Advance to the next phase (Phases 2, 3, 4)
        async advanceTripPhase(tripId) {
            try {
                const response = await api.post(`/api/trips/${tripId}/advance`);
                if (this.activeTrip) {
                    this.activeTrip.current_phase = response.data.current_phase;
                }
                return response.data;
            } catch (err) {
                throw err;
            }
        },

        // Submit the complex BLOWBAGETS Checklist
        async submitChecklist(payload) {
            try {
                const response = await api.post('/api/checklists', payload);
                return response.data;
            } catch (err) {
                throw err;
            }
        },
        
        // Report Mid-Shift Emergency
        async reportEmergency(payload) {
             try {
                const response = await api.post('/api/emergencies/report', payload);
                return response.data;
            } catch (err) {
                throw err;
            }
        }
    }
});