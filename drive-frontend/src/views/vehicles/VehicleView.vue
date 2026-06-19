<template>
  <div class="fixed inset-0 z-[60] overflow-hidden flex justify-end">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="relative w-full max-w-xl bg-slate-50 h-full shadow-2xl flex flex-col animate-slide-in-right border-l border-slate-200">
      <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-slate-200 flex justify-between items-center bg-white shadow-sm z-10">
        <h3 class="text-base sm:text-lg font-bold text-slate-800">Vehicle Profile: <span class="text-blue-600">{{ vehicle.unit_id }}</span></h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 transition-colors">
          <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      
      <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 sm:space-y-6">
        <div class="bg-white p-3 sm:p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col items-center">
          <div class="w-full h-48 sm:h-64 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 relative flex items-center justify-center">
            <img v-if="vehicle.image_path" :src="vehicle.image_path.startsWith('data:image') ? vehicle.image_path : `http://localhost:8000/storage/${vehicle.image_path}?t=${Date.now()}`" alt="Vehicle Image" class="object-cover w-full h-full" />
            <div v-else class="text-slate-400 flex flex-col items-center">
              <svg class="w-8 h-8 sm:w-12 sm:h-12 mb-1 sm:mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
              <span class="text-xs sm:text-sm font-medium">No Image Uploaded</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="px-4 py-2 sm:px-5 sm:py-3 border-b border-slate-100 bg-slate-50"><h4 class="font-bold text-slate-700 text-xs sm:text-sm">Vehicle Specifications</h4></div>
          <div class="divide-y divide-slate-100 text-[11px] sm:text-sm">
            <div class="flex justify-between p-3 sm:p-4"><span class="text-slate-500 font-medium">Plate Number</span><span class="font-bold text-slate-800">{{ vehicle.plate_number }}</span></div>
            <div class="flex justify-between p-3 sm:p-4"><span class="text-slate-500 font-medium">Make & Model</span><span class="font-bold text-slate-800">{{ vehicle.make_model }}</span></div>
            <div class="flex justify-between p-3 sm:p-4"><span class="text-slate-500 font-medium">Vehicle Type</span><span class="font-bold text-slate-800">{{ vehicle.vehicle_type }}</span></div>
            <div class="flex justify-between p-3 sm:p-4"><span class="text-slate-500 font-medium">Current Status</span><span class="font-bold text-blue-600">{{ vehicle.status.replace('_', ' ') }}</span></div>
            <div class="flex justify-between p-3 sm:p-4"><span class="text-slate-500 font-medium">Odometer</span><span class="font-bold text-slate-800">{{ vehicle.odometer.toLocaleString() }} km</span></div>
            <div class="flex justify-between p-3 sm:p-4"><span class="text-slate-500 font-medium">Base Location</span><span class="font-bold text-slate-800">{{ vehicle.base_location }}</span></div>
            <div class="flex justify-between p-3 sm:p-4"><span class="text-slate-500 font-medium">Registration Expiry</span><span :class="getExpiryColor(vehicle.registration_expiry_date)">{{ formatDate(vehicle.registration_expiry_date) }}</span></div>
            <div class="flex justify-between p-3 sm:p-4"><span class="text-slate-500 font-medium">Insurance Expiry</span><span :class="getExpiryColor(vehicle.insurance_expiry_date)">{{ formatDate(vehicle.insurance_expiry_date) }}</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useFleetStore } from '../../stores/fleet';
import { useToastStore } from '../../stores/toast';

const props = defineProps({
    vehicle: { type: Object, required: true }
});

const emit = defineEmits(['close', 'updated']);
const fleetStore = useFleetStore();
const toastStore = useToastStore();

const getExpiryColor = (dateString) => {
    if (!dateString) return 'text-slate-400';
    const expiry = new Date(dateString);
    const now = new Date();
    const diffTime = expiry - now;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays < 0) return 'text-red-600 font-bold'; // Expired
    if (diffDays <= 30) return 'text-orange-500 font-bold'; // < 1 month
    if (diffDays <= 90) return 'text-amber-500 font-bold'; // < 3 months
    return 'text-slate-600 font-bold'; // Safe
};

const formatDate = (dateString) => {
    if (!dateString) return 'Not Set';
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>