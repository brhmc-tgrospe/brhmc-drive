<template>
  <!-- Using fixed inset-0 to prevent the CSS borders/scrolling bug we fixed earlier -->
  <div class="fixed inset-0 flex flex-col bg-slate-50 overflow-hidden font-sans text-slate-800">
    
    <!-- HEADER COMPONENT -->
    <Header @toggle-mobile-menu="isMobileMenuOpen = !isMobileMenuOpen" />

    <!-- IMPOSTOR BANNER -->
    <div v-if="authStore.isImpersonating" class="w-full bg-red-600 text-white px-4 py-2 flex flex-col sm:flex-row items-center justify-center sm:space-x-6 text-sm font-bold z-50 shadow-md shrink-0 gap-2 sm:gap-0 text-center">
      <div class="flex items-center space-x-2">
        <svg class="w-5 h-5 animate-pulse shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <span>IMPOSTOR MODE: Viewing as {{ authStore.user?.role.toUpperCase() }}</span>
      </div>
      <button @click="handleReturnToDeveloper" class="bg-white text-red-600 px-3 py-1 rounded hover:bg-red-50 transition-colors shadow-sm w-full sm:w-auto">
        Return to Developer
      </button>
    </div>

    <!-- MAIN CONTENT AREA -->
    <div class="flex flex-1 overflow-hidden relative">
      
      <!-- Mobile Sidebar Backdrop -->
      <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-slate-900/50 z-20 md:hidden transition-opacity"></div>

      <!-- SIDEBAR COMPONENT -->
      <Sidebar :is-mobile-open="isMobileMenuOpen" @close-mobile="isMobileMenuOpen = false" />

      <!-- PAGE CONTENT -->
      <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 custom-scrollbar w-full">
        <router-view></router-view>
      </main>

    </div>
    
    <Toast />

    <!-- GLOBAL SIDEBAR TOOLTIP -->
    <div v-if="tooltip.show" :style="{ top: tooltip.y + 'px', left: tooltip.x + 'px' }" class="fixed z-[9999] px-2 py-1 bg-slate-800 text-white text-[10px] font-bold rounded shadow-sm pointer-events-none whitespace-nowrap transform -translate-y-1/2 transition-opacity">
      {{ tooltip.text }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Toast from '../components/Toast.vue'; 

// Import our segregated layout components
import Header from '../components/header/Header.vue';
import Sidebar from '../components/sidebar/Sidebar.vue';

const router = useRouter();
const authStore = useAuthStore();
const isMobileMenuOpen = ref(false); 

const tooltip = ref({ show: false, text: '', x: 0, y: 0 });

const handleMouseOver = (e) => {
  if (window.innerWidth < 768) return; 
  const el = e.target.closest('a.group'); 
  if (el && el.closest('aside')) { 
    const textEl = el.querySelector('.md\\:hidden');
    if (textEl) {
      const rect = el.getBoundingClientRect();
      tooltip.value = {
        show: true,
        text: textEl.textContent.trim(),
        x: rect.right + 12,
        y: rect.top + rect.height / 2
      };
    }
  }
};

const handleMouseOut = (e) => {
  const el = e.target.closest('a.group');
  if (el && el.closest('aside')) {
    tooltip.value.show = false;
  }
};

onMounted(() => {
  document.body.addEventListener('mouseover', handleMouseOver);
  document.body.addEventListener('mouseout', handleMouseOut);
});

onUnmounted(() => {
  document.body.removeEventListener('mouseover', handleMouseOver);
  document.body.removeEventListener('mouseout', handleMouseOut);
});

const handleReturnToDeveloper = () => {
    authStore.stopImpersonating();
    router.push('/dashboard').catch(() => {}); 
};
</script>

<style>
/* Failsafe to prevent browser margins */
html, body, #app { margin: 0 !important; padding: 0 !important; overflow: hidden !important; }
</style>