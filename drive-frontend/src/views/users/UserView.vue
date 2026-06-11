<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="$emit('close')"></div>
    <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-sm p-5 sm:p-6 flex flex-col animate-fade-in-up">
      <div class="flex items-center space-x-4 mb-4 pb-4 border-b border-slate-100">
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-lg sm:text-xl">
          {{ user.first_name.charAt(0) }}{{ user.last_name.charAt(0) }}
        </div>
        <div>
          <h3 class="text-base sm:text-lg font-extrabold text-slate-800 leading-tight">{{ user.first_name }} {{ user.last_name }}</h3>
          <span class="text-[10px] sm:text-xs font-bold text-teal-600 uppercase tracking-wider">{{ user.role }}</span>
        </div>
      </div>
      
      <div class="space-y-3 text-xs sm:text-sm text-slate-600 mb-6">
        <div><span class="font-bold text-slate-400 text-[10px] sm:text-xs uppercase block">Username</span> {{ user.username }}</div>
        <div><span class="font-bold text-slate-400 text-[10px] sm:text-xs uppercase block">Email</span> {{ user.email }}</div>
        <div><span class="font-bold text-slate-400 text-[10px] sm:text-xs uppercase block">Contact</span> {{ user.contact_number || 'N/A' }}</div>
      </div>
      
      <button @click="$emit('close')" class="w-full py-2 bg-slate-100 text-slate-700 text-xs sm:text-sm font-bold rounded-lg hover:bg-slate-200 transition-colors">
        Close Profile
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
    user: { type: Object, required: true }
});
defineEmits(['close']);
</script>