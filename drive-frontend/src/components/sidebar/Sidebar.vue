<template>
  <aside :class="[
    'bg-white border-r border-slate-200 h-full flex flex-col justify-between shrink-0 z-30 transition-all duration-300 ease-in-out',
    'absolute inset-y-0 left-0 md:relative md:translate-x-0',
    isMobileOpen ? 'translate-x-0 w-64 px-4' : '-translate-x-full md:w-16 md:items-center'
  ]">
    <div @click="$emit('close-mobile')">
        <SidebarNav />
    </div>
    
  </aside>
</template>

<script setup>
import SidebarNav from './SidebarNav.vue';

defineProps({
    isMobileOpen: Boolean
});
defineEmits(['close-mobile']);
</script>