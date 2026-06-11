<template>
  <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-5 sm:p-6 text-center animate-fade-in-up">
      <div class="w-12 h-12 sm:w-16 sm:h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4 text-red-500">
        <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
      </div>
      <h3 class="text-lg sm:text-xl font-bold text-slate-800 mb-2">{{ isBatch ? 'Delete Multiple Vehicles?' : 'Delete Vehicle?' }}</h3>
      <p class="text-xs sm:text-sm text-slate-500 mb-4 sm:mb-6">
        <span v-if="isBatch">Are you absolutely sure you want to permanently remove <strong>{{ batchIds.length }}</strong> selected vehicles from the fleet? This action cannot be undone.</span>
        <span v-else>Are you absolutely sure you want to permanently remove <span class="font-bold text-slate-700">{{ vehicle?.unit_id }}</span> from the fleet? This action cannot be undone.</span>
      </p>
      <div class="flex space-x-3">
        <button @click="$emit('close')" class="flex-1 py-2 sm:py-2.5 text-xs sm:text-sm bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
        <button @click="executeDelete" :disabled="isDeleting" class="flex-1 py-2 sm:py-2.5 text-xs sm:text-sm bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors shadow-md shadow-red-200 disabled:opacity-50">
          {{ isDeleting ? 'Deleting...' : 'Yes, Delete' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useFleetStore } from '../../stores/fleet';
import { useToastStore } from '../../stores/toast';

const props = defineProps({
    vehicle: { type: Object, default: null },
    batchIds: { type: Array, default: () => [] }
});
const emit = defineEmits(['close', 'deleted']);
const fleetStore = useFleetStore();
const toastStore = useToastStore();

const isDeleting = ref(false);
const isBatch = computed(() => props.batchIds && props.batchIds.length > 0);

const executeDelete = async () => {
    isDeleting.value = true;
    try {
        if (isBatch.value) {
            await Promise.all(props.batchIds.map(id => fleetStore.deleteVehicle(id)));
            toastStore.show(`${props.batchIds.length} vehicles removed.`, 'success');
        } else {
            await fleetStore.deleteVehicle(props.vehicle.id);
            toastStore.show(`${props.vehicle.unit_id} was removed.`, 'success');
        }
        emit('deleted');
        emit('close');
    } catch (error) {
        toastStore.show("Failed to delete vehicle(s).", 'error');
    } finally {
        isDeleting.value = false;
    }
};
</script>