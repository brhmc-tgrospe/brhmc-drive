<template>
  <tbody class="divide-y divide-slate-100 bg-white w-full">
    <tr v-for="i in rows" :key="i" class="animate-pulse">
      <td v-for="j in columns" :key="j" class="py-2 px-3 sm:py-3 sm:px-4">
        <!-- Randomly varying widths for a more organic look -->
        <div class="h-4 bg-slate-200 rounded" :class="getRandomWidthClass(i, j)"></div>
      </td>
    </tr>
  </tbody>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  columns: {
    type: Number,
    default: 5
  },
  rows: {
    type: Number,
    default: 10
  }
});

// Deterministic random width based on row and column index so it doesn't jitter
const getRandomWidthClass = (row, col) => {
  const seed = (row * 7) + (col * 13);
  const remainder = seed % 4;
  
  if (remainder === 0) return 'w-1/3';
  if (remainder === 1) return 'w-1/2';
  if (remainder === 2) return 'w-3/4';
  return 'w-full';
};
</script>
