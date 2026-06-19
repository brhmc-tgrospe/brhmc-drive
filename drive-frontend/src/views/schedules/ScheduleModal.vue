<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="relative w-full max-w-lg bg-white rounded-xl shadow-2xl flex flex-col border border-slate-200 overflow-hidden animate-fade-in-up">
      <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
        <h3 class="text-base sm:text-lg font-bold text-slate-800">{{ isEdit ? 'Edit Shift Schedule' : 'Schedule New Shift' }}</h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-red-500 transition-colors">
          <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      
      <div class="p-4 sm:p-6 flex-1 overflow-y-auto">
        <ScheduleForm 
          :form="form" 
          :drivers="drivers" 
          :vehicles="vehicles" 
          :is-edit="isEdit" 
          :original-driver-id="shift?.driver_id" 
          :original-vehicle-id="shift?.vehicle_id" 
          @submit="submitShift" 
          id="scheduleForm"
        >
          <div class="pt-4 mt-6 border-t border-slate-100 flex justify-end space-x-3">
             <button type="button" @click="$emit('close')" class="px-4 py-2 text-sm font-bold text-slate-600 hover:text-slate-800 transition-colors">Cancel</button>
            <button type="submit" :disabled="isSaving" class="px-6 py-2 bg-teal-600 text-white font-bold text-sm rounded-lg hover:bg-teal-700 transition-colors shadow-md disabled:opacity-50 flex items-center gap-2">
              <svg v-if="isSaving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              {{ isSaving ? 'Saving...' : (isEdit ? 'Update Schedule' : 'Confirm Schedule') }}
            </button>
          </div>
        </ScheduleForm>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useShiftStore } from '../../stores/shift';
import { useToastStore } from '../../stores/toast';
import ScheduleForm from './ScheduleForm.vue';

const props = defineProps({
    shift: { type: Object, default: null },
    drivers: { type: Array, required: true },
    vehicles: { type: Array, required: true }
});

const emit = defineEmits(['close', 'saved']);
const shiftStore = useShiftStore();
const toastStore = useToastStore();

const isSaving = ref(false);
const isEdit = computed(() => !!props.shift);
const form = ref({ driver_id: '', vehicle_id: '', start_time: '', shift_duration: 8 });

onMounted(() => {
    if (isEdit.value) {
        const rawDate = props.shift.start_time || '';
        let localDateTime = '';
        if (rawDate) {
            localDateTime = rawDate.replace(' ', 'T').slice(0, 16);
        }

        let initialDuration = props.shift.shift_duration;
        if (!initialDuration && props.shift.end_time) {
            const start = new Date(rawDate.replace(' ', 'T'));
            const end = new Date(props.shift.end_time.replace(' ', 'T'));
            initialDuration = Math.round((end - start) / 3600000); 
        }

        form.value = { 
            driver_id: props.shift.driver_id, 
            vehicle_id: props.shift.vehicle_id, 
            start_time: localDateTime, 
            shift_duration: initialDuration || 8 
        };
    }
});

const submitShift = async () => {
    isSaving.value = true;
    try {
        if (isEdit.value) {
            const startDateObj = new Date(form.value.start_time);
            const endDateObj = new Date(startDateObj.getTime() + (form.value.shift_duration * 60 * 60 * 1000));
            
            const pad = (n) => n.toString().padStart(2, '0');
            const formattedStart = `${startDateObj.getFullYear()}-${pad(startDateObj.getMonth()+1)}-${pad(startDateObj.getDate())} ${pad(startDateObj.getHours())}:${pad(startDateObj.getMinutes())}:00`;
            const formattedEnd = `${endDateObj.getFullYear()}-${pad(endDateObj.getMonth()+1)}-${pad(endDateObj.getDate())} ${pad(endDateObj.getHours())}:${pad(endDateObj.getMinutes())}:00`;

            const payload = {
                driver_id: form.value.driver_id,
                vehicle_id: form.value.vehicle_id,
                start_time: formattedStart,
                end_time: formattedEnd, 
                shift_duration: form.value.shift_duration
            };

            await shiftStore.updateShift(props.shift.id, payload);
            toastStore.show('Shift updated successfully.', 'success');
        } else {
            await shiftStore.addShift(form.value);
            toastStore.show('Shift successfully scheduled.', 'success');
        }
        emit('saved');
        
    } catch (error) {
        if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            if (errors) {
                toastStore.show(Object.values(errors)[0][0], 'error');
            } else if (error.response.data.message) {
                toastStore.show(error.response.data.message, 'error');
            } else {
                toastStore.show('Validation failed.', 'error');
            }
        } else {
            toastStore.show(isEdit.value ? 'Failed to update shift.' : 'Failed to save shift. Please check input parameters.', 'error');
        }
    } finally {
        isSaving.value = false;
    }
};
</script>
