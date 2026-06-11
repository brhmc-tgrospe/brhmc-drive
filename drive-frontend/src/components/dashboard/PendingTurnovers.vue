<template>
  <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex flex-col overflow-hidden w-full h-full relative">
    
    <div class="p-4 sm:p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4 -4m6 2a9 9 0 1 1 -18 0 9 9 0 0 1 18 0z"></path></svg>
        </div>
        <div>
          <h3 class="font-bold text-slate-800 text-base">Action Center</h3>
          <p class="text-xs text-slate-500 font-medium">Pending Turnovers</p>
        </div>
      </div>
      <span class="bg-amber-500 text-white text-sm font-bold px-3 py-0.5 rounded-full shadow-sm">{{ pendingItems.length }}</span>
    </div>

    <div v-if="isLoading" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] flex items-center justify-center z-10">
        <svg class="animate-spin h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
    </div>

    <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar">
      
      <div v-if="!isLoading && pendingItems.length === 0" class="flex flex-col items-center justify-center h-full text-slate-400 p-4 text-center">
        <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4 -4m6 2a9 9 0 1 1 -18 0 9 9 0 0 1 18 0z"></path></svg>
        <p class="text-base font-medium">All clear!</p>
        <p class="text-sm">No pending signatures required.</p>
        
        <button @click="() => fetchPending(false)" class="mt-4 text-xs font-bold text-slate-500 hover:text-amber-600 transition-colors flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            Refresh
        </button>
      </div>

      <div v-for="item in pendingItems" :key="item.id" class="bg-white border border-slate-100 hover:border-amber-200 hover:shadow-md p-4 rounded-xl transition-all group">
        <div class="flex justify-between items-center">
          <div>
            <!-- CRITICAL FIX: Shows ID next to unit so you can easily spot accidental duplicates -->
            <div class="text-sm sm:text-base font-extrabold text-slate-800 flex items-center gap-2">
              {{ item.vehicle_unit }} <span class="text-[10px] text-slate-400 font-bold bg-slate-100 px-1.5 py-0.5 rounded">#{{ item.id }}</span>
              <span v-if="item.condition && item.condition.includes('Maintenance')" class="bg-red-100 text-red-700 text-[9px] font-bold px-2 py-0.5 rounded uppercase tracking-wider border border-red-200 animate-pulse">
                Flagged Unfit
              </span>
            </div>
            <div class="text-xs sm:text-sm text-slate-500 mt-0.5">From: <span class="font-medium text-slate-700">{{ item.outgoing_driver || 'System' }}</span></div>
          </div>
          
          <button @click="$emit('review', item)" class="text-[10px] sm:text-xs font-bold text-amber-700 bg-amber-100 hover:bg-amber-500 hover:text-white px-3 py-1.5 rounded-lg uppercase tracking-wide border border-amber-200 hover:border-amber-500 transition-colors shadow-sm active:scale-95 cursor-pointer flex items-center gap-1.5">
            Needs Review
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036 -5.036a2.5 2.5 0 1 1 3.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import api from '../../axios';
import { useAuthStore } from '../../stores/auth';

defineEmits(['review']);

const authStore = useAuthStore();
const pendingItems = ref([]);
const isLoading = ref(false);

const fetchPending = async (isBackground = false) => {
    if (!authStore.isAuthenticated) return; 

    if (!isBackground) isLoading.value = true;
    try {
        const response = await api.get('/api/checklists', {
            params: { 
                status: 'PENDING', 
                per_page: 50,
                t: new Date().getTime() 
            } 
        });
        pendingItems.value = response.data.data || [];
    } catch (error) {
        console.error("Failed to load pending turnovers");
    } finally {
        if (!isBackground) isLoading.value = false;
    }
};

const removeChecklistLocally = (id) => {
    pendingItems.value = pendingItems.value.filter(item => item.id !== id);
};

onMounted(() => {
    fetchPending(false); 
    
    if (window.Echo) {
        window.Echo.channel('fleet-updates')
            .listen('ChecklistSubmitted', (e) => {
                fetchPending(true); 
            });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave('fleet-updates');
    }
});

defineExpose({ fetchPending, removeChecklistLocally });
</script>