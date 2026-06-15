<template>
  <router-link v-if="authStore.can('execute_shifts') || authStore.can('manage_schedules')" to="/active-console" class="w-10 h-10 flex items-center justify-center rounded-xl transition-all text-slate-400 hover:bg-slate-50 hover:text-slate-600" active-class="bg-teal-50 text-teal-600 shadow-sm" title="Active Trip Console">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
  </router-link>
</template>
<script setup>
import { useAuthStore } from '../../../stores/auth';
const authStore = useAuthStore();
</script>
