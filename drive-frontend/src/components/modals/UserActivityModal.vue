<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl flex flex-col max-h-[90vh] overflow-hidden animate-slide-up">
      
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-slate-100 shrink-0">
        <div>
          <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Your Activity History</h2>
          <p class="text-sm text-slate-500 mt-0.5">A complete log of your actions and modifications.</p>
        </div>
        <button @click="$emit('close')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-full transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-slate-50 p-4 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between shrink-0 gap-4">
        <div class="flex items-center gap-3 w-full sm:w-auto">
          <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">From</label>
            <input type="date" v-model="filters.date_from" @change="fetchActivities" class="px-2 py-1.5 border border-slate-200 rounded-lg text-sm bg-white font-medium text-slate-600 outline-none focus:ring-2 focus:ring-teal-500" />
          </div>
          <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">To</label>
            <input type="date" v-model="filters.date_to" @change="fetchActivities" class="px-2 py-1.5 border border-slate-200 rounded-lg text-sm bg-white font-medium text-slate-600 outline-none focus:ring-2 focus:ring-teal-500" />
          </div>
        </div>
        <button v-if="filters.date_from || filters.date_to" @click="clearFilters" class="text-xs font-bold text-slate-500 hover:text-red-500 transition-colors px-2">Clear Dates</button>
      </div>

      <!-- Body (Logs) -->
      <div class="flex-1 overflow-y-auto p-6 space-y-4 relative custom-scrollbar bg-white">
        <div v-if="loading" class="flex items-center justify-center py-10">
          <svg class="animate-spin h-6 w-6 text-teal-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </div>
        <div v-else-if="activities.length === 0" class="text-center py-10 flex flex-col items-center">
          <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          <p class="text-slate-500 text-sm font-medium">No activity found for this time period.</p>
        </div>
        <div v-else class="space-y-4">
          <div v-for="log in activities" :key="log.id" class="flex gap-4 items-start pb-4 border-b border-slate-100 last:border-0 last:pb-0">
            <div class="mt-1 shrink-0">
               <span :class="getActionBadgeClass(log.event)" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border inline-block w-16 text-center">
                 {{ log.event || 'action' }}
               </span>
            </div>
            <div class="flex-1">
              <p class="text-sm font-semibold text-slate-700 leading-snug">{{ formatDetailedDescription(log) }}</p>
              <p class="text-[11px] text-slate-400 font-medium mt-1">{{ formatDate(log.created_at) }} at {{ formatTime(log.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer (Pagination) -->
      <div v-if="totalPages > 1" class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
        <span class="text-xs text-slate-500 font-medium">Page {{ currentPage }} of {{ totalPages }}</span>
        <div class="flex items-center gap-1">
          <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1 || loading" class="p-1.5 rounded bg-white border border-slate-200 text-slate-600 disabled:opacity-50 hover:bg-slate-50 transition-colors">
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
          </button>
          <button @click="changePage(currentPage + 1)" :disabled="currentPage >= totalPages || loading" class="p-1.5 rounded bg-white border border-slate-200 text-slate-600 disabled:opacity-50 hover:bg-slate-50 transition-colors">
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, watch, reactive } from 'vue';
import axios from '../../axios';
import { useAuthStore } from '../../stores/auth';
import { formatDetailedDescription, getActionBadgeClass } from '../../utils/activityFormatter';

const props = defineProps({
  show: { type: Boolean, default: false }
});
const emit = defineEmits(['close']);

const authStore = useAuthStore();
const activities = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);

const filters = reactive({
  date_from: '',
  date_to: ''
});

const fetchActivities = async () => {
  if (!authStore.user?.id) return;
  loading.value = true;
  try {
    const response = await axios.get('/api/activity-logs', {
      params: {
        causer_id: authStore.user.id,
        date_from: filters.date_from,
        date_to: filters.date_to,
        page: currentPage.value,
        per_page: 10
      }
    });
    activities.value = response.data.data || response.data;
    if (response.data.last_page) {
      totalPages.value = response.data.last_page;
    }
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    const today = new Date().toISOString().split('T')[0];
    filters.date_from = today;
    filters.date_to = today;
    currentPage.value = 1;
    fetchActivities();
  }
});

const clearFilters = () => {
  filters.date_from = '';
  filters.date_to = '';
  currentPage.value = 1;
  fetchActivities();
};

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    fetchActivities();
  }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const formatTime = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.2s ease-out;
}
.animate-slide-up {
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.5);
  border-radius: 3px;
}
</style>
