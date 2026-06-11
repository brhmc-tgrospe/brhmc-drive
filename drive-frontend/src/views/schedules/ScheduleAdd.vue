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
        <form @submit.prevent="submitShift" class="space-y-4">
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- DRIVER DROPDOWN -->
            <div>
              <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Assign Driver</label>
              <select v-model="form.driver_id" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 bg-white">
                <option value="" disabled>Select Driver...</option>
                <!-- CRITICAL FIX: Unlock both READY and SCHEDULED -->
                <option v-for="driver in availableDrivers" 
                        :key="driver.id" 
                        :value="driver.id" 
                        :disabled="!['READY', 'SCHEDULED'].includes(driver.driver_status)"
                        :class="{'text-slate-400 bg-slate-50': !['READY', 'SCHEDULED'].includes(driver.driver_status), 'font-medium text-slate-800': ['READY', 'SCHEDULED'].includes(driver.driver_status)}">
                  {{ driver.first_name }} {{ driver.last_name }} ({{ driver.driver_status }})
                </option>
              </select>
            </div>
            <!-- VEHICLE DROPDOWN -->
            <div>
              <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Assign Vehicle</label>
              <select v-model="form.vehicle_id" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 bg-white">
                <option value="" disabled>Select Vehicle...</option>
                <!-- CRITICAL FIX: Unlock both READY and SCHEDULED -->
                <option v-for="vehicle in availableVehicles" 
                        :key="vehicle.id" 
                        :value="vehicle.id" 
                        :disabled="!['READY', 'SCHEDULED'].includes(vehicle.status)"
                        :class="{'text-slate-400 bg-slate-50': !['READY', 'SCHEDULED'].includes(vehicle.status), 'font-medium text-slate-800': ['READY', 'SCHEDULED'].includes(vehicle.status)}">
                  {{ vehicle.unit_id }} ({{ vehicle.status.replace('_', ' ') }})
                </option>
              </select>
            </div>
          </div>

          <div class="pt-2">
            <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Shift Start Time</label>
            <input type="datetime-local" v-model="form.start_time" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500" />
          </div>

          <div class="pt-2">
            <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-2">Shift Duration</label>
            <div class="flex space-x-3">
              <button type="button" @click="form.shift_duration = 8" :class="form.shift_duration === 8 ? 'bg-teal-600 text-white border-teal-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" class="flex-1 py-2 border rounded-lg font-bold text-sm transition-colors shadow-sm">
                8-Hour Shift
              </button>
              <button type="button" @click="form.shift_duration = 12" :class="form.shift_duration === 12 ? 'bg-teal-600 text-white border-teal-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" class="flex-1 py-2 border rounded-lg font-bold text-sm transition-colors shadow-sm">
                12-Hour Shift
              </button>
            </div>
          </div>
          
          <div class="pt-4 mt-6 border-t border-slate-100 flex justify-end space-x-3">
             <button type="button" @click="$emit('close')" class="px-4 py-2 text-sm font-bold text-slate-600 hover:text-slate-800 transition-colors">Cancel</button>
            <button type="submit" :disabled="isSaving" class="px-6 py-2 bg-teal-600 text-white font-bold text-sm rounded-lg hover:bg-teal-700 transition-colors shadow-md disabled:opacity-50 flex items-center gap-2">
              <svg v-if="isSaving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              {{ isSaving ? 'Saving...' : 'Confirm Schedule' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useShiftStore } from '../../stores/shift';
import { useToastStore } from '../../stores/toast';

const props = defineProps({
    drivers: { type: Array, required: true },
    vehicles: { type: Array, required: true }
});

const emit = defineEmits(['close', 'saved']);
const shiftStore = useShiftStore();
const toastStore = useToastStore();

const isSaving = ref(false);
const form = ref({ driver_id: '', vehicle_id: '', start_time: '', shift_duration: 8 });

const availableDrivers = computed(() => {
    const drvs = props.drivers.filter(u => u.role.toLowerCase() === 'driver');
    return drvs.sort((a, b) => {
        const statusA = a.driver_status === 'READY' ? 1 : 2;
        const statusB = b.driver_status === 'READY' ? 1 : 2;
        if (statusA !== statusB) return statusA - statusB;
        return (a.first_name || '').localeCompare(b.first_name || '');
    });
});

const availableVehicles = computed(() => {
    const statusOrder = { 'READY': 1, 'SCHEDULED': 2, 'IN_USE': 3, 'MAINTENANCE': 4, 'BREAKDOWN': 5 };
    return [...props.vehicles].sort((a, b) => {
        const orderA = statusOrder[a.status?.toUpperCase()] || 99;
        const orderB = statusOrder[b.status?.toUpperCase()] || 99;
        if (orderA !== orderB) return orderA - orderB;
        return (a.unit_id || '').localeCompare(b.unit_id || '');
    });
});

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