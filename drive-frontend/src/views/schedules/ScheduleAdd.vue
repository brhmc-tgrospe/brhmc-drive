<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="relative w-full max-w-lg bg-white rounded-xl shadow-2xl flex flex-col border border-slate-200 overflow-hidden animate-fade-in-up">
      <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
        <h3 class="text-base sm:text-lg font-bold text-slate-800">Schedule New Shift</h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-red-500 transition-colors">
          <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      
      <div class="p-4 sm:p-6 flex-1 overflow-y-auto">
        <ScheduleForm :form="form" :drivers="drivers" :vehicles="vehicles" @submit="submitShift" id="scheduleForm">
          <div class="pt-4 mt-6 border-t border-slate-100 flex justify-end space-x-3">
             <button type="button" @click="$emit('close')" class="px-4 py-2 text-sm font-bold text-slate-600 hover:text-slate-800 transition-colors">Cancel</button>
            <button type="submit" :disabled="isSaving" class="px-6 py-2 bg-teal-600 text-white font-bold text-sm rounded-lg hover:bg-teal-700 transition-colors shadow-md disabled:opacity-50 flex items-center gap-2">
              <svg v-if="isSaving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              {{ isSaving ? 'Saving...' : 'Confirm Schedule' }}
            </button>
          </div>
        </ScheduleForm>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useShiftStore } from '../../stores/shift';
import { useToastStore } from '../../stores/toast';
import ScheduleForm from './ScheduleForm.vue';

const props = defineProps({
    drivers: { type: Array, required: true },
    vehicles: { type: Array, required: true }
});

const emit = defineEmits(['close', 'saved']);
const shiftStore = useShiftStore();
const toastStore = useToastStore();

const isSaving = ref(false);
const form = ref({ driver_id: '', vehicle_id: '', start_time: '', shift_duration: 8 });

const submitShift = async () => {
    isSaving.value = true;
    try {
        await shiftStore.addShift(form.value);
        toastStore.show('Shift successfully scheduled.', 'success');
        emit('saved');
    } catch (error) {
        // Will correctly catch our 422 Double Booking Message
        if (error.response?.status === 422 && error.response?.data?.message) {
            toastStore.show(error.response.data.message, 'error');
        } else {
            toastStore.show('Failed to save shift. Please check input parameters.', 'error');
        }
    } finally {
        isSaving.value = false;
    }
};
</script>