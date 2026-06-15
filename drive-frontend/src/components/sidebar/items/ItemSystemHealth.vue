<template>
  <router-link v-if="authStore.user?.role === 'developer'" to="/system-health" class="relative group flex items-center p-2 md:justify-center w-full md:w-10 md:h-10 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-md md:rounded-xl transition-all" active-class="bg-teal-50 text-teal-600 shadow-sm border border-teal-100">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
    <span class="ml-3 font-semibold text-sm md:hidden">System Health</span>
    
  </router-link>
</template>
<script setup>
import { useAuthStore } from '../../../stores/auth';
const authStore = useAuthStore();
</script>

