import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../axios';

export const useInspectionStore = defineStore('inspection', () => {
    const inspections = ref([]);
    const isLoading = ref(false);

    const fetchInspections = async () => {
        isLoading.value = true;
        try {
            const response = await api.get('/api/inspections');
            inspections.value = response.data;
        } catch (error) {
            console.error('Failed to fetch inspections:', error);
        } finally {
            isLoading.value = false;
        }
    };

    const submitInspection = async (inspectionData) => {
        try {
            const response = await api.post('/api/inspections', inspectionData);
            await fetchInspections(); // Refresh the list
            return response.data;
        } catch (error) {
            console.error('Failed to submit inspection:', error);
            throw error;
        }
    };

    return { inspections, isLoading, fetchInspections, submitInspection };
});