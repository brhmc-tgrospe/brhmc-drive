<template>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 h-full">
    
    <!-- 1. AVAILABLE FOR DISPATCH -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-sm flex flex-col justify-center relative overflow-hidden group">
      <div class="absolute -right-6 -top-6 w-24 h-24 bg-teal-50 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700 pointer-events-none"></div>
      <div class="flex justify-between items-start mb-2 relative z-10">
        <div>
          <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-widest">Available</p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight mt-1">{{ readyCount }}</h3>
        </div>
        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-teal-50 flex items-center justify-center text-teal-600 border border-teal-100 shadow-sm shrink-0">
          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
        </div>
      </div>
      <p class="text-[9px] sm:text-[10px] font-bold text-teal-600 uppercase tracking-wide relative z-10">Ready for scheduling</p>
    </div>

    <!-- 2. SCHEDULED SHIFTS -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-sm flex flex-col justify-center relative overflow-hidden group">
      <div class="absolute -right-6 -top-6 w-24 h-24 bg-purple-50 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700 pointer-events-none"></div>
      <div class="flex justify-between items-start mb-2 relative z-10">
        <div>
          <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-widest">Scheduled</p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight mt-1">{{ scheduledCount }}</h3>
        </div>
        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-600 border border-purple-100 shadow-sm shrink-0">
          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
      </div>
      <p class="text-[9px] sm:text-[10px] font-bold text-purple-600 uppercase tracking-wide relative z-10">Pending deployment</p>
    </div>

    <!-- 3. ACTIVELY DEPLOYED -->
    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 border border-blue-800 rounded-xl p-4 sm:p-5 shadow-lg shadow-blue-900/20 flex flex-col justify-center relative overflow-hidden group">
      <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700 pointer-events-none"></div>
      <div class="flex justify-between items-start mb-2 relative z-10">
        <div>
          <p class="text-[10px] sm:text-xs font-bold text-blue-200 uppercase tracking-widest">Active / In Use</p>
          <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1 drop-shadow-sm">{{ inUseCount }}</h3>
        </div>
        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/10 flex items-center justify-center text-white border border-white/20 shadow-inner shrink-0 backdrop-blur-sm">
          <svg class="w-4 h-4 sm:w-5 sm:h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
      </div>
      <p class="text-[9px] sm:text-[10px] font-bold text-blue-100 uppercase tracking-wide relative z-10">Live on the road</p>
    </div>

    <!-- 4. GROUNDED / BREAKDOWN -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-sm flex flex-col justify-center relative overflow-hidden group">
      <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-50 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700 pointer-events-none"></div>
      <div class="flex justify-between items-start mb-2 relative z-10">
        <div>
          <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-widest">Grounded</p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight mt-1" :class="{'text-red-600': groundedCount > 0}">{{ groundedCount }}</h3>
        </div>
        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-red-50 flex items-center justify-center text-red-600 border border-red-100 shadow-sm shrink-0">
          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
      </div>
      <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wide relative z-10" :class="groundedCount > 0 ? 'text-red-500' : 'text-slate-400'">Needs maintenance</p>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useFleetStore } from '../../stores/fleet';

const fleetStore = useFleetStore();

// Reactively calculate the KPIs directly from the live store!
const readyCount = computed(() => {
    return fleetStore.vehicles ? fleetStore.vehicles.filter(v => v.status === 'READY').length : 0;
});

const scheduledCount = computed(() => {
    return fleetStore.vehicles ? fleetStore.vehicles.filter(v => v.status === 'SCHEDULED').length : 0;
});

const inUseCount = computed(() => {
    return fleetStore.vehicles ? fleetStore.vehicles.filter(v => v.status === 'IN_USE').length : 0;
});

const groundedCount = computed(() => {
    return fleetStore.vehicles ? fleetStore.vehicles.filter(v => v.status === 'BREAKDOWN' || v.status === 'MAINTENANCE').length : 0;
});
</script>