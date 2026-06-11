import { defineStore } from 'pinia';
import axios from '../axios';

export const useArchiveStore = defineStore('archive', {
  state: () => ({
    archivedRecords: [],
    loading: false,
    error: null,
    pagination: {
      currentPage: 1,
      lastPage: 1,
      total: 0,
      perPage: 15
    }
  }),
  actions: {
    async fetchArchivedRecords(type, params = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get(`/api/archive/${type}`, { params });
        this.archivedRecords = response.data.data;
        this.pagination = {
          currentPage: response.data.meta?.current_page || 1,
          lastPage: response.data.meta?.last_page || 1,
          total: response.data.meta?.total || response.data.data.length,
          perPage: response.data.meta?.per_page || 15
        };
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch archived records.';
      } finally {
        this.loading = false;
      }
    },
    async restoreRecord(type, id) {
      try {
        await axios.post(`/api/archive/${type}/${id}/restore`);
        // Remove from list
        this.archivedRecords = this.archivedRecords.filter(record => record.id !== id);
        return true;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to restore record.';
        return false;
      }
    },
    async forceDeleteRecord(type, id) {
      try {
        await axios.delete(`/api/archive/${type}/${id}/force`);
        // Remove from list
        this.archivedRecords = this.archivedRecords.filter(record => record.id !== id);
        return true;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to permanently delete record.';
        return false;
      }
    },
    async batchRestore(type, ids) {
      try {
        await Promise.all(ids.map(id => axios.post(`/api/archive/${type}/${id}/restore`)));
        this.archivedRecords = this.archivedRecords.filter(record => !ids.includes(record.id));
        return true;
      } catch (err) {
        this.error = 'Failed to restore some records.';
        return false;
      }
    },
    async batchForceDelete(type, ids) {
      try {
        await Promise.all(ids.map(id => axios.delete(`/api/archive/${type}/${id}/force`)));
        this.archivedRecords = this.archivedRecords.filter(record => !ids.includes(record.id));
        return true;
      } catch (err) {
        this.error = 'Failed to permanently delete some records.';
        return false;
      }
    }
  }
});
