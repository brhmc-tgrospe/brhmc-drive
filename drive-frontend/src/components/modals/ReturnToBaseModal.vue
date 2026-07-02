<template>
  <div v-if="show" class="fixed inset-0 z-[90] flex items-center justify-center p-4 animate-fade-in-up">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="!loading && $emit('close')"></div>
    
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
      <div class="bg-gradient-to-r from-orange-600 to-amber-600 px-6 py-5 text-white">
        <h3 class="text-lg font-black uppercase tracking-wider">Return to Base</h3>
        <p class="text-orange-100 text-xs font-medium mt-1">How would you like to proceed?</p>
      </div>

      <div class="p-6 flex flex-col gap-3">
        <!-- Standby Option -->
        <button 
          @click="$emit('confirm', 'standby')" 
          :disabled="loading"
          class="w-full py-4 px-5 rounded-xl font-black text-sm uppercase tracking-wider transition-all duration-300 transform active:scale-95 flex items-center gap-4 bg-amber-50 border-2 border-amber-300 text-amber-800 hover:bg-amber-100 hover:border-amber-400 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <div class="w-10 h-10 rounded-xl bg-amber-200 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <div class="text-left">
            <span class="block text-sm font-black">To Standby</span>
            <span class="block text-[10px] font-medium text-amber-600 normal-case tracking-normal">Await orders at base</span>
          </div>
        </button>

        <!-- End Shift Option -->
        <button 
          @click="$emit('confirm', 'end_shift')" 
          :disabled="loading"
          class="w-full py-4 px-5 rounded-xl font-black text-sm uppercase tracking-wider transition-all duration-300 transform active:scale-95 flex items-center gap-4 bg-slate-50 border-2 border-slate-300 text-slate-700 hover:bg-red-50 hover:border-red-300 hover:text-red-700 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <div class="w-10 h-10 rounded-xl bg-slate-200 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          </div>
          <div class="text-left">
            <span class="block text-sm font-black">End Shift</span>
            <span class="block text-[10px] font-medium text-slate-500 normal-case tracking-normal">Return and complete post-trip</span>
          </div>
        </button>
      </div>

      <div class="px-6 pb-6">
        <button @click="$emit('close')" :disabled="loading" class="w-full py-3 bg-slate-100 text-slate-600 font-bold text-sm rounded-xl hover:bg-slate-200 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  loading: { type: Boolean, default: false },
});

defineEmits(['close', 'confirm']);
</script>
