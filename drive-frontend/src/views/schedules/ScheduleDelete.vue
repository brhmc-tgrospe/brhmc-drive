<template>
  <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-5 sm:p-6 text-center animate-fade-in-up">
      <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 text-red-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
      </div>
      <h3 class="text-xl font-bold text-slate-800 mb-2">{{ isBatch ? 'Delete Multiple Shifts?' : 'Cancel Shift?' }}</h3>
      <p class="text-sm text-slate-500 mb-6">
        <span v-if="isBatch">Are you sure you want to delete <strong>{{ batchIds.length }}</strong> selected shifts? This action cannot be undone.</span>
        <span v-else>Are you sure you want to delete this scheduled shift for <span class="font-bold text-slate-700">{{ shift?.driver?.first_name }}</span>? The vehicle will be released back to the available pool.</span>
      </p>
      <div class="flex space-x-3">
        <button @click="$emit('close')" class="flex-1 py-2.5 text-sm bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 transition-colors">Go Back</button>
        <button @click="executeDelete" :disabled="isDeleting" class="flex-1 flex items-center justify-center gap-2 py-2.5 text-sm bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50">
          <svg v-if="isDeleting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          {{ isDeleting ? 'Deleting...' : 'Yes, Delete' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useShiftStore } from '../../stores/shift';
import { useToastStore } from '../../stores/toast';

const props = defineProps({
    shift: { type: Object, default: null },
    batchIds: { type: Array, default: () => [] }
});
const emit = defineEmits(['close', 'deleted']);
const shiftStore = useShiftStore();
const toastStore = useToastStore();

const isDeleting = ref(false);
const isBatch = computed(() => props.batchIds && props.batchIds.length > 0);

const executeDelete = async () => {
    isDeleting.value = true;
    try {
        if (isBatch.value) {
            await Promise.all(props.batchIds.map(id => shiftStore.deleteShift(id)));
            toastStore.show(`${props.batchIds.length} shifts deleted successfully.`, 'success');
        } else {
            await shiftStore.deleteShift(props.shift.id);
            toastStore.show('Shift deleted successfully. Vehicle released.', 'success');
        }
        emit('deleted');
    } catch (error) {
        toastStore.show(error.response?.data?.message || 'Failed to delete shift(s).', 'error');
    } finally {
        isDeleting.value = false;
    }
};
</script>