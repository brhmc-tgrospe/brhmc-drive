<template>
  <div class="relative">
    <button @click="isOpen = !isOpen" class="flex items-center gap-2 p-1.5 pr-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-full transition-all duration-200 z-50 relative focus:outline-none focus:ring-2 focus:ring-teal-500/30">
      <div class="w-7 h-7 rounded-full bg-teal-600 text-white flex items-center justify-center text-xs font-bold shadow-inner">
        {{ userInitials }}
      </div>
      <span class="text-sm font-semibold text-slate-700 hidden sm:block">
        {{ authStore.user?.first_name || 'User' }}
      </span>
      <!-- Chevron rotates smoothly when opened -->
      <svg class="w-4 h-4 text-slate-400 transition-transform duration-300 ease-in-out" :class="isOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </button>

    <!-- Transparent Overlay for clicking outside -->
    <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-40"></div>

    <!-- Vue Transition for Premium Slide & Fade Effect -->
    <transition
      enter-active-class="transition duration-200 ease-out origin-top-right"
      enter-from-class="transform opacity-0 scale-95 -translate-y-2"
      enter-to-class="transform opacity-100 scale-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in origin-top-right"
      leave-from-class="transform opacity-100 scale-100 translate-y-0"
      leave-to-class="transform opacity-0 scale-95 -translate-y-2"
    >
      <div v-if="isOpen" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-100 overflow-hidden z-50">
        <div class="p-3 border-b border-slate-100 bg-slate-50/50">
          <p class="text-sm font-bold text-slate-800">{{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
          <p class="text-xs text-slate-500 font-medium truncate">{{ authStore.user?.email || authStore.user?.username }}</p>
        </div>
        <div class="p-2">
           <router-link to="/profile" @click="isOpen = false" class="flex items-center gap-2 w-full text-left px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-teal-600 rounded-lg transition-colors font-medium">
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
             My Profile
           </router-link>
           <router-link to="/settings" @click="isOpen = false" class="flex items-center gap-2 w-full text-left px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-teal-600 rounded-lg transition-colors font-medium">
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
             Account Settings
           </router-link>
        </div>
        <div class="p-2 border-t border-slate-100">
          <button @click="logout" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 font-bold rounded-lg transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Sign Out
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';

const router = useRouter();
const authStore = useAuthStore();
const toastStore = useToastStore();

const isOpen = ref(false);

const userInitials = computed(() => {
  const first = authStore.user?.first_name?.charAt(0) || '';
  const last = authStore.user?.last_name?.charAt(0) || '';
  return (first + last).toUpperCase();
});

const logout = async () => {
  isOpen.value = false;
  await authStore.logout();
  router.push('/login').catch(() => {});
  toastStore.show('Logged out successfully', 'success');
};
</script>