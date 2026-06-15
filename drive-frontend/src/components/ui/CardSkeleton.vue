<template>
  <div class="grid gap-4 sm:gap-6 w-full" :class="gridClass">
    <div v-for="i in count" :key="i" class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-slate-100 p-4 sm:p-5 flex flex-col h-full animate-pulse">
      <!-- Header -->
      <div class="flex items-start justify-between mb-4 gap-3">
        <div class="flex items-center gap-2 flex-1">
          <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-slate-200 shrink-0"></div>
          <div class="space-y-2 flex-1">
            <div class="h-4 bg-slate-200 rounded w-1/2"></div>
            <div class="h-3 bg-slate-100 rounded w-1/3"></div>
          </div>
        </div>
        <div v-if="showStatusBadge" class="h-5 w-16 bg-slate-200 rounded-full shrink-0"></div>
      </div>
      
      <!-- Content Lines -->
      <div class="space-y-3 mt-auto">
        <div v-for="line in lines" :key="line" class="flex justify-between items-center border-b border-slate-50 pb-2 last:border-0 last:pb-0">
          <div class="h-3 bg-slate-100 rounded" :style="`width: ${randomWidth(30, 50, i + line)}%`"></div>
          <div class="h-3 bg-slate-200 rounded" :style="`width: ${randomWidth(20, 40, i * line)}%`"></div>
        </div>
      </div>

      <!-- Action Button -->
      <div v-if="showAction" class="mt-4 pt-4 border-t border-slate-50">
        <div class="h-9 sm:h-10 bg-slate-200 rounded-lg w-full"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  count: {
    type: Number,
    default: 4
  },
  lines: {
    type: Number,
    default: 4
  },
  showStatusBadge: {
    type: Boolean,
    default: true
  },
  showAction: {
    type: Boolean,
    default: false
  },
  gridClass: {
    type: String,
    default: "grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
  }
});

const randomWidth = (min, max, seed) => {
    const random = Math.sin(seed) * 10000;
    const decimal = random - Math.floor(random);
    return Math.floor(decimal * (max - min + 1)) + min;
};
</script>
