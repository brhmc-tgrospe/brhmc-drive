<template>
  <router-link to="/vehicles" class="relative group flex items-center p-2 md:justify-center w-full md:w-10 md:h-10 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-md md:rounded-xl transition-all" active-class="bg-teal-50 text-teal-600 shadow-sm border border-teal-100">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
    <span class="ml-3 font-semibold text-sm md:hidden">Vehicles Masterlist</span>
    <span class="hidden md:block absolute left-full ml-3 top-1/2 transform -translate-y-1/2 px-2 py-1 bg-slate-800 text-white text-[10px] font-bold rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 shadow-sm">Vehicles Masterlist</span>
  </router-link>
</template>