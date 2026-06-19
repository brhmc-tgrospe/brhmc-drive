<template>
  <div class="fixed inset-0 z-[60] overflow-hidden flex justify-end">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="relative w-full max-w-md bg-white h-full shadow-2xl flex flex-col animate-slide-in-right border-l border-slate-200">
      <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
        <h3 class="text-base sm:text-lg font-bold text-slate-800">{{ isEdit ? 'Edit Vehicle' : 'Register New Vehicle' }}</h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-red-500 transition-colors">
          <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      
      <div class="p-4 sm:p-6 flex-1 overflow-y-auto">
        <form @submit.prevent="submitVehicle" class="space-y-3 sm:space-y-4">
          
          <div class="bg-white p-3 sm:p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col items-center mb-4">
            <div class="w-full h-48 sm:h-64 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 relative mb-3 sm:mb-4 flex items-center justify-center">
              <img v-if="localImage" :src="localImage.startsWith('data:image') ? localImage : `http://localhost:8000/storage/${localImage}?t=${Date.now()}`" alt="Vehicle Image" class="object-cover w-full h-full" />
              <div v-else class="text-slate-400 flex flex-col items-center">
                <svg class="w-8 h-8 sm:w-12 sm:h-12 mb-1 sm:mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="text-xs sm:text-sm font-medium">No Image Uploaded</span>
              </div>
            </div>
            
            <label class="cursor-pointer w-full text-center py-2 sm:py-2.5 bg-blue-50 text-blue-600 font-bold text-xs sm:text-sm rounded-lg border border-blue-200 hover:bg-blue-100 transition-colors">
              {{ localImage ? 'Change Image' : 'Select Vehicle Image' }}
              <input type="file" class="hidden" accept="image/*" @change="handleImageUpload" />
            </label>
            <p v-if="pendingImage" class="text-xs text-amber-600 mt-2 font-bold text-center">Image selected. Click Update/Save to finalize.</p>
          </div>

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
                <option v-if="isEdit && vehicle.status === 'SCHEDULED'" value="SCHEDULED" disabled>SCHEDULED (Automated)</option>
                <option v-if="isEdit && vehicle.status === 'IN_USE'" value="IN_USE" disabled>IN USE (Automated)</option>
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
          <div class="grid grid-cols-2 gap-3 sm:gap-4 mt-3 sm:mt-4">
             <div>
                <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Registration Expiry</label>
                <input type="date" v-model="form.registration_expiry_date" class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500" />
             </div>
             <div>
                <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Insurance Expiry</label>
                <input type="date" v-model="form.insurance_expiry_date" class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500" />
             </div>
          </div>
          
          <div class="pt-3 sm:pt-4 border-t border-slate-100 mt-4 sm:mt-6">
            <button type="submit" :disabled="isSaving" class="w-full py-2 sm:py-2.5 bg-teal-600 text-white font-bold text-sm rounded-md hover:bg-teal-700 transition-colors shadow-sm disabled:opacity-50">
              {{ isSaving ? (isEdit ? 'Updating...' : 'Saving...') : (isEdit ? 'Update Vehicle' : 'Save New Vehicle') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useFleetStore } from '../../stores/fleet';
import { useToastStore } from '../../stores/toast';

const props = defineProps({
    vehicle: { type: Object, default: null }
});
const emit = defineEmits(['close', 'saved']);
const fleetStore = useFleetStore();
const toastStore = useToastStore();

const isSaving = ref(false);
const isEdit = computed(() => !!props.vehicle);
const pendingImage = ref(null);

const form = ref({
    unit_id: '', plate_number: '', vehicle_type: 'Ambulance - Type 1', 
    make_model: '', status: 'READY', odometer: 0, base_location: 'BRHMC Main Base',
    registration_expiry_date: '', insurance_expiry_date: ''
});

const localImage = ref(null);

onMounted(() => {
    if (isEdit.value) {
        form.value = { ...props.vehicle };
        localImage.value = props.vehicle.image_path;
    }
});

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    toastStore.show("Processing image...", "info");
    
    const reader = new FileReader();
    reader.onload = (e) => {
        const img = new Image();
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const MAX_WIDTH = 800; // Compress down to 800px width
            let width = img.width;
            let height = img.height;

            if (width > MAX_WIDTH) {
                height = Math.round((height * MAX_WIDTH) / width);
                width = MAX_WIDTH;
            }

            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            const base64Image = canvas.toDataURL('image/jpeg', 0.8);
            
            // Just stage it, don't upload yet
            pendingImage.value = base64Image;
            localImage.value = base64Image;
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
};

const submitVehicle = async () => {
    isSaving.value = true;
    try {
        let vehicleId = null;
        if (isEdit.value) {
            await fleetStore.updateVehicle(props.vehicle.id, form.value);
            vehicleId = props.vehicle.id;
            toastStore.show(`${form.value.unit_id} has been updated.`, 'success');
        } else {
            const resData = await fleetStore.addVehicle(form.value);
            vehicleId = resData?.vehicle?.id || resData?.id; 
            toastStore.show(`${form.value.unit_id} added to the fleet.`, 'success');
        }

        // Upload image if staged
        if (pendingImage.value && vehicleId) {
            await fleetStore.uploadVehicleImage(vehicleId, pendingImage.value);
        }

        emit('saved');
        emit('close');
    } catch (error) {
        toastStore.show("Action failed. Check for duplicate Plate Numbers.", 'error');
    } finally {
        isSaving.value = false;
    }
};
</script>
