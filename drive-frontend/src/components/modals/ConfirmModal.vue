<template>
  <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 animate-fade-in-up">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="cancel"></div>
    
    <!-- Modal -->
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md flex flex-col overflow-hidden">
      <div class="p-5 sm:p-6 flex flex-col items-center text-center">
        <!-- Icon -->
        <div :class="iconBgClass" class="w-12 h-12 rounded-full flex items-center justify-center mb-4">
          <svg v-if="type === 'warning'" class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          <svg v-else-if="type === 'danger'" class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          <svg v-else-if="type === 'info'" class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <svg v-else class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        
        <h3 class="text-lg font-extrabold text-slate-800 mb-2">{{ title }}</h3>
        <p class="text-sm text-slate-500 whitespace-pre-line">{{ message }}</p>
      </div>
      
      <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex justify-center space-x-3">
        <button @click="cancel" :disabled="isLoading" class="px-4 py-2 text-sm font-bold text-slate-600 hover:text-slate-800 hover:bg-slate-200 rounded-lg transition-colors disabled:opacity-50">
          {{ cancelText }}
        </button>
        <button @click="confirm" :disabled="isLoading" :class="confirmBtnClass" class="px-5 py-2 text-white text-sm font-bold rounded-lg shadow-sm transition-colors active:scale-95 flex items-center justify-center disabled:opacity-50">
          <svg v-if="isLoading" class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, required: true },
  message: { type: String, required: true },
  type: { type: String, default: 'primary', validator: (v) => ['primary', 'warning', 'danger', 'info'].includes(v) },
  confirmText: { type: String, default: 'Confirm' },
  cancelText: { type: String, default: 'Cancel' },
  isLoading: { type: Boolean, default: false }
});

const emit = defineEmits(['confirm', 'cancel']);

const iconBgClass = computed(() => {
  switch (props.type) {
    case 'warning': return 'bg-amber-100';
    case 'danger': return 'bg-red-100';
    case 'info': return 'bg-blue-100';
    default: return 'bg-teal-100';
  }
});

const confirmBtnClass = computed(() => {
  switch (props.type) {
    case 'warning': return 'bg-amber-500 hover:bg-amber-600';
    case 'danger': return 'bg-red-600 hover:bg-red-700';
    case 'info': return 'bg-blue-600 hover:bg-blue-700';
    default: return 'bg-teal-600 hover:bg-teal-700';
  }
});

const cancel = () => {
  if (!props.isLoading) emit('cancel');
};

const confirm = () => {
  if (!props.isLoading) emit('confirm');
};
</script>
