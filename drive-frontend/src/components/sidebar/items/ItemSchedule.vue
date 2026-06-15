<template>
  <router-link to="/schedules" class="relative group flex items-center p-2 md:justify-center w-full md:w-10 md:h-10 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-md md:rounded-xl transition-all" active-class="bg-teal-50 text-teal-600 shadow-sm border border-teal-100">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
    <span class="ml-3 font-semibold text-sm md:hidden">Shift Schedules</span>
    
  </router-link>
</template>
<script setup>
import { useAuthStore } from '../../../stores/auth';
const authStore = useAuthStore();
</script>
