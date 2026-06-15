<template>
  <router-link v-if="authStore.user?.role === 'developer'" to="/activity-logs" class="relative group flex items-center p-2 md:justify-center w-full md:w-10 md:h-10 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-md md:rounded-xl transition-all" active-class="bg-teal-50 text-teal-600 shadow-sm border border-teal-100">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    <span class="ml-3 font-semibold text-sm md:hidden">Activity Logs</span>
    
  </router-link>
</template>

<script setup>
import { useAuthStore } from '../../../stores/auth';
const authStore = useAuthStore();
</script>

