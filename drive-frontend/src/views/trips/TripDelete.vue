<template>
  <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-2xl flex flex-col border border-slate-200 overflow-hidden animate-fade-in-up max-h-[90vh]">
      <div class="px-4 py-3 sm:px-5 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
        <div>
          <h3 class="text-base font-bold text-slate-800">Trip Execution Log (TRP-{{ tripId }})</h3>
        </div>
        <button @click="$emit('close')" class="text-slate-400 hover:text-red-500 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      
      <div v-if="loading" class="p-10 flex justify-center">
         <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
      </div>

      <div v-else-if="tripDetails" class="p-4 sm:p-5 flex-1 overflow-y-auto custom-scrollbar space-y-5">
        
        <!-- SHIFT OVERVIEW -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
           <div>
               <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Driver</span>
               <span class="text-xs font-black text-slate-700">{{ tripDetails.driver_name }}</span>
           </div>
           <div>
               <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Vehicle</span>
               <span class="text-xs font-black text-slate-700">{{ tripDetails.vehicle_unit }}</span>
           </div>
           <div>
               <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</span>
               <span class="text-[10px] px-2 py-0.5 rounded font-bold uppercase" :class="tripDetails.current_phase >= 7 ? 'bg-teal-100 text-teal-700' : 'bg-blue-100 text-blue-700'">
                 {{ tripDetails.current_phase >= 7 ? 'COMPLETED' : `PHASE ${tripDetails.current_phase}` }}
               </span>
           </div>
           <div>
               <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Time Active</span>
               <span class="text-xs font-bold text-slate-700">{{ tripDuration }}</span>
           </div>
        </div>

        <!-- GPS TIMELINE -->
        <div>
            <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest border-b border-slate-200 pb-2 mb-3">GPS Execution Timeline</h4>
            
            <table class="w-full text-left text-xs border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 border-b border-slate-200 text-[10px] uppercase text-slate-500">
                    <tr>
                        <th class="p-2.5">Phase Action</th>
                        <th class="p-2.5">Timestamp</th>
                        <th class="p-2.5">GPS Location</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="!tripDetails.logs || tripDetails.logs.length === 0">
                        <td colspan="3" class="p-4 text-center text-slate-400 italic">No GPS coordinates recorded yet.</td>
                    </tr>
                    <tr v-for="log in tripDetails.logs" :key="log.id" class="hover:bg-slate-50">
                        <td class="p-2.5 font-bold text-slate-700">{{ getPhaseName(log.phase) }}</td>
                        <td class="p-2.5 text-slate-600">{{ formatTimeOnly(log.created_at) }}</td>
                        <td class="p-2.5">
                            <a v-if="log.latitude && log.longitude" 
                               :href="`https://www.google.com/maps/search/?api=1&query=${log.latitude},${log.longitude}`" 
                               target="_blank" 
                               class="text-blue-600 hover:text-blue-800 font-mono font-medium underline decoration-blue-300 underline-offset-2 flex items-center gap-1.5">
                               <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                               View on Map
                            </a>
                            <span v-else class="text-slate-400 italic">GPS Offline</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MID-SHIFT INCIDENTS -->
        <div v-if="tripDetails.incidents && tripDetails.incidents.length > 0" class="mt-4">
            <h4 class="text-xs font-black text-red-600 uppercase tracking-widest border-b border-red-200 pb-2 mb-3">Mid-Shift Emergency Incidents</h4>
            <div v-for="inc in tripDetails.incidents" :key="inc.id" class="bg-red-50 border border-red-200 p-3 rounded-lg mb-2">
                <div class="flex justify-between items-start mb-1">
                    <span class="font-bold text-red-800 text-xs">{{ inc.issue_type }}</span>
                    <span class="text-[9px] font-bold text-red-600 uppercase">{{ inc.status }}</span>
                </div>
                <div class="flex justify-between items-center text-[10px] text-slate-600">
                    <span>{{ formatTimeOnly(inc.created_at) }}</span>
                    <a v-if="inc.latitude" :href="`https://www.google.com/maps/search/?api=1&query=${inc.latitude},${inc.longitude}`" target="_blank" class="text-blue-600 underline">View Location</a>
                </div>
            </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useTripLogStore } from '../../stores/tripLog';

const props = defineProps({ show: Boolean, tripId: Number });
defineEmits(['close']);

const tripLogStore = useTripLogStore();
const loading = ref(false);
const tripDetails = ref(null);

watch(() => props.show, async (newVal) => {
    if (newVal && props.tripId) {
        loading.value = true;
        try {
            tripDetails.value = await tripLogStore.getTripDetails(props.tripId);
        } catch (e) {
            console.error(e);
        } finally {
            loading.value = false;
        }
    } else {
        tripDetails.value = null;
    }
});

const getPhaseName = (phase) => {
    const titles = { 1: "Pre-Trip Verified", 2: "Dispatched from Base", 3: "Arrived at Scene", 4: "Dispatched from Scene", 5: "Arrived at Hospital", 6: "Proceeding to Base", 7: "Post-Trip Submitted" };
    return titles[phase] || `Phase ${phase}`;
};

const tripDuration = computed(() => {
    if (!tripDetails.value?.started_at) return 'Not Started';
    const start = new Date(tripDetails.value.started_at);
    const end = tripDetails.value.ended_at ? new Date(tripDetails.value.ended_at) : new Date();
    const diffMins = Math.round((end - start) / 60000);
    const hrs = Math.floor(diffMins / 60);
    const mins = diffMins % 60;
    return `${hrs}h ${mins}m`;
});

const formatTimeOnly = (dateString) => {
    if (!dateString) return '';
    const utcDate = dateString.endsWith('Z') ? dateString : dateString.replace(' ', 'T') + 'Z';
    return new Date(utcDate).toLocaleString('en-US', { timeZone: 'Asia/Manila', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
};
</script>