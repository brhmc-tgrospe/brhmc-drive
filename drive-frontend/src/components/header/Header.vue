<template>
  <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sm:px-6 shrink-0 z-30 relative">
    
    <div class="flex items-center gap-3">
      <button @click="$emit('toggle-mobile-menu')" class="p-2 -ml-2 text-slate-500 hover:bg-slate-100 rounded-lg lg:hidden transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
      </button>
      
      <!-- Logo and Branding -->
      <div class="flex items-center gap-2">
        <img src="/images/icon3.png" alt="DRIVE Logo" class="h-8 w-auto object-contain" />
        <div class="font-bold text-lg text-slate-800 tracking-tight">
          DRIVE <span class="text-teal-600 font-extrabold">System</span>
        </div>
      </div>
    </div>

    <!-- RIGHT SIDE: Modular Components Only (No raw HTML to prevent duplicates) -->
    <div class="flex items-center gap-2 sm:gap-4">
      <HeaderNotifications />
      <HeaderProfile />
    </div>
    
  </header>
</template>

<script setup>
// Import the separated modules using clean relative paths
import HeaderNotifications from './HeaderNotifications.vue';
import HeaderProfile from './HeaderProfile.vue';

defineEmits(['toggle-mobile-menu']);
</script>