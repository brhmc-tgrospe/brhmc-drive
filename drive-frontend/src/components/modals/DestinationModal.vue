<template>
  <div v-if="show" class="fixed inset-0 z-[90] flex items-center justify-center p-4 animate-fade-in-up">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="!loading && $emit('close')"></div>
    
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
      <div class="bg-gradient-to-r from-teal-600 to-emerald-600 px-6 py-5 text-white">
        <h3 class="text-lg font-black uppercase tracking-wider">{{ title }}</h3>
        <p class="text-teal-100 text-xs font-medium mt-1">{{ subtitle }}</p>
      </div>

      <div class="p-6">
        <label class="block text-[10px] font-black text-slate-700 uppercase tracking-widest mb-2">Destination <span class="text-red-500">*</span></label>
        <input 
          ref="inputRef"
          type="text" 
          v-model="destination" 
          @keyup.enter="submit"
          :disabled="loading"
          placeholder="e.g. OB Ward, Surgery, Emergency, etc..."
          class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold bg-slate-50 outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all disabled:opacity-60 disabled:cursor-not-allowed"
        />
        <p v-if="error" class="text-red-500 text-xs font-bold mt-2">{{ error }}</p>
      </div>

      <div class="px-6 pb-6 flex gap-3">
        <button @click="$emit('close')" :disabled="loading" class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold text-sm rounded-xl hover:bg-slate-200 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
          Cancel
        </button>
        <button @click="submit" :disabled="!destination.trim() || loading" class="flex-1 py-3 bg-teal-600 text-white font-bold text-sm rounded-xl hover:bg-teal-700 transition-colors shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
          <svg v-if="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          {{ loading ? 'Dispatching...' : 'Confirm' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';

const props = defineProps({
  show: Boolean,
  loading: { type: Boolean, default: false },
  title: { type: String, default: 'Enter Destination' },
  subtitle: { type: String, default: 'Where is the vehicle heading?' },
});

const emit = defineEmits(['close', 'confirm']);

const destination = ref('');
const error = ref('');
const inputRef = ref(null);

watch(() => props.show, async (val) => {
  if (val) {
    destination.value = '';
    error.value = '';
    await nextTick();
    inputRef.value?.focus();
  }
});

const submit = () => {
  if (props.loading) return;
  if (!destination.value.trim()) {
    error.value = 'Destination is required.';
    return;
  }
  emit('confirm', destination.value.trim());
};
</script>
