<template>
  <div class="fixed inset-0 z-[60] overflow-hidden flex justify-end">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="relative w-full max-w-md bg-white h-full shadow-2xl flex flex-col animate-slide-in-right border-l border-slate-200">
      <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
        <h3 class="text-base sm:text-lg font-bold text-slate-800">Register New Vehicle</h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-red-500 transition-colors">
          <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      
      <div class="p-4 sm:p-6 flex-1 overflow-y-auto">
        <form @submit.prevent="submitVehicle" class="space-y-3 sm:space-y-4">
          <div>
            <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Unit ID</label>
            <input type="text" v-model="form.unit_id" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500" />
          </div>
          <div>
            <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Plate Number</label>
            <input type="text" v-model="form.plate_number" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500" />
          </div>
          <div class="grid grid-cols-2 gap-3 sm:gap-4">
            <div>
              <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Vehicle Type</label>
              <select v-model="form.vehicle_type" class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                <option>Ambulance - Type 1</option>
                <option>Ambulance - Type 2</option>
                <option>Service Vehicle</option>
              </select>
            </div>
            <div>
              <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Status</label>
              <select v-model="form.status" class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                <option value="READY">READY</option>
                <option value="MAINTENANCE">MAINTENANCE</option>
                <option value="BREAKDOWN">BREAKDOWN</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Make & Model</label>
            <input type="text" v-model="form.make_model" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500" />
          </div>
          <div class="grid grid-cols-2 gap-3 sm:gap-4">
             <div>
                <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Odometer (km)</label>
                <input type="number" v-model="form.odometer" required min="0" class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500" />
             </div>
             <div>
                <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Base Location</label>
                <input type="text" v-model="form.base_location" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500" />
             </div>
          </div>
          
          <div class="pt-3 sm:pt-4 border-t border-slate-100 mt-4 sm:mt-6">
            <button type="submit" :disabled="isSaving" class="w-full py-2 sm:py-2.5 bg-teal-600 text-white font-bold text-sm rounded-md hover:bg-teal-700 transition-colors shadow-sm disabled:opacity-50">
              {{ isSaving ? 'Saving...' : 'Save New Vehicle' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useFleetStore } from '../../stores/fleet';
import { useToastStore } from '../../stores/toast';

const emit = defineEmits(['close', 'saved']);
const fleetStore = useFleetStore();
const toastStore = useToastStore();

const isSaving = ref(false);
const form = ref({
    unit_id: '', plate_number: '', vehicle_type: 'Ambulance - Type 1', 
    make_model: '', status: 'READY', odometer: 0, base_location: 'BRHMC Main Base'
});

const submitVehicle = async () => {
    isSaving.value = true;
    try {
        await fleetStore.addVehicle(form.value);
        toastStore.show(`${form.value.unit_id} added to the fleet.`, 'success');
        emit('saved');
        emit('close');
    } catch (error) {
        toastStore.show("Action failed. Check for duplicate Plate Numbers.", 'error');
    } finally {
        isSaving.value = false;
    }
};
</script>