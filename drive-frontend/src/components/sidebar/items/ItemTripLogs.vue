<template>
  <router-link to="/trips" 
               class="relative group flex items-center p-2 md:justify-center w-full md:w-10 md:h-10 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-md md:rounded-xl transition-all" 
               active-class="bg-teal-50 text-teal-600 shadow-sm border border-teal-100">
    
    <!-- Map / Route Icon -->
    <svg class="w-5 h-5 shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
    </svg>

    <!-- MOBILE TEXT -->
    <span class="ml-3 text-sm md:hidden">Trip Logs</span>
    
    <!-- DESKTOP TOOLTIP -->
    <span class="hidden md:block absolute left-full ml-3 top-1/2 transform -translate-y-1/2 px-2 py-1 bg-slate-800 text-white text-[10px] font-bold rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 shadow-sm">
      Trip Logs
    </span>
    
  </router-link>
</template>