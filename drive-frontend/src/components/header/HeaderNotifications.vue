<template>
  <div class="relative">
    <button @click="toggleDropdown" 
            class="p-2 text-slate-500 hover:bg-slate-100 rounded-full relative transition-all duration-200 z-50 focus:outline-none focus:ring-2 focus:ring-teal-500/30"
            :class="isOpen ? 'bg-slate-100 text-teal-600' : ''">
      <!-- Bell subtly scales when active -->
      <svg class="w-5 h-5 transition-transform duration-300" :class="isOpen ? 'scale-110' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
      <span v-if="notificationStore.unreadCount > 0" class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border border-white shadow-sm animate-pulse"></span>
    </button>

    <!-- Transparent Overlay -->
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
      <div v-if="isOpen" class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-100 overflow-hidden z-50 flex flex-col max-h-[80vh]">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 shrink-0">
           <h3 class="font-bold text-slate-800 tracking-tight">Notifications</h3>
           <span v-if="notificationStore.unreadCount > 0" class="text-[10px] uppercase tracking-widest text-teal-600 font-bold bg-teal-100/50 px-2 py-1 rounded-full">{{ notificationStore.unreadCount }} New</span>
        </div>
        
        <div class="overflow-y-auto custom-scrollbar">
          <div v-if="notificationStore.items.length === 0" class="p-6 text-center text-slate-500 text-sm">
            No recent notifications.
          </div>

          <div v-for="item in notificationStore.items" :key="item.id" 
               class="p-4 cursor-pointer transition-colors flex gap-3 border-b border-slate-50"
               :class="!item.read ? 'bg-blue-50/40 hover:bg-blue-50 border-l-4 border-blue-500' : 'hover:bg-slate-50'">
            
            <div v-if="item.color === 'red'" class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0 shadow-inner">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div v-else-if="item.color === 'teal'" class="w-8 h-8 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center shrink-0 shadow-inner">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div v-else-if="item.color === 'yellow'" class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center shrink-0 shadow-inner">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <div v-else-if="item.color === 'orange'" class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center shrink-0 shadow-inner">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
            <div v-else-if="item.color === 'blue'" class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 shadow-inner">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div v-else class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 shadow-inner">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
            </div>
            
            <div class="flex-1 min-w-0">
              <p class="text-sm text-slate-800 leading-tight" v-html="item.message"></p>
              <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-wider">{{ formatTimeAgo(item.timestamp) }}</p>
            </div>
          </div>
        </div>
        
        <div class="p-2 border-t border-slate-100 bg-slate-50/50 shrink-0">
          <button @click="isOpen = false" class="w-full text-center px-3 py-2 text-[11px] font-bold uppercase tracking-widest text-teal-600 hover:bg-teal-50 rounded-lg transition-colors">
            Close Panel
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNotificationStore } from '../../stores/notifications';

const isOpen = ref(false);
const notificationStore = useNotificationStore();

onMounted(() => {
    // Start listening when the header mounts
    notificationStore.listenForAlerts();
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        notificationStore.markAllAsRead();
    }
};

const formatTimeAgo = (dateStr) => {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // in seconds

    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)} mins ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} hours ago`;
    return `${Math.floor(diff / 86400)} days ago`;
};
</script>