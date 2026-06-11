<template>
  <!-- Fixed container so toasts sit on top of everything -->
  <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none">
    <TransitionGroup name="toast">
      <div v-for="toast in toastStore.toasts" :key="toast.id" 
           class="pointer-events-auto flex items-center shadow-lg rounded-lg px-4 py-3 min-w-[300px] border transform transition-all duration-300"
           :class="{
              'bg-teal-50 border-teal-200 text-teal-800': toast.type === 'success',
              'bg-red-50 border-red-200 text-red-800': toast.type === 'error',
              'bg-blue-50 border-blue-200 text-blue-800': toast.type === 'info'
           }">
        
        <!-- Success Icon -->
        <svg v-if="toast.type === 'success'" class="w-5 h-5 mr-3 text-teal-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <!-- Error Icon -->
        <svg v-if="toast.type === 'error'" class="w-5 h-5 mr-3 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        
        <span class="text-sm font-bold flex-1">{{ toast.message }}</span>
        
        <!-- Close Button -->
        <button @click="toastStore.remove(toast.id)" class="ml-4 text-slate-400 hover:text-slate-600 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToastStore } from '../stores/toast';
const toastStore = useToastStore();
</script>

<style scoped>
/* Smooth slide and fade animation */
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from { opacity: 0; transform: translateX(30px); }
.toast-leave-to { opacity: 0; transform: scale(0.95); }
</style>