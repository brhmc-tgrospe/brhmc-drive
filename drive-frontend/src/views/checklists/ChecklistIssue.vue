<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="relative w-full max-w-lg bg-white rounded-xl shadow-2xl flex flex-col border border-slate-200 overflow-hidden animate-fade-in-up">
      <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-red-100 flex items-center gap-3 bg-red-50 shrink-0">
        <div class="w-8 h-8 rounded-full animate-ping bg-red-100 flex items-center justify-center text-red-600">
           <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <h3 class="text-base sm:text-lg font-bold text-red-800">Report Mid-Shift Incident</h3>
      </div>
      
      <div class="p-4 sm:p-6 flex-1 overflow-y-auto custom-scrollbar">
        <form @submit.prevent="submitIssue" class="space-y-5">
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- TARGET SELECTOR -->
              <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Incident Target <span class="text-red-500">*</span></label>
                <select v-model="form.incident_target" @change="form.issue_type = ''" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-bold focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 bg-white">
                  <option value="Vehicle">Vehicle Problem</option>
                  <option value="Driver">Driver Emergency</option>
                </select>
              </div>

              <!-- VEHICLE UNIT (Auto-filled for Drivers, Selectable for Dispatchers) -->
              <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Vehicle Unit <span class="text-red-500">*</span></label>
                <select v-if="isDispatcher" v-model="form.vehicle_unit" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 bg-white">
                  <option value="" disabled>Select active vehicle...</option>
                  <option v-for="v in activeVehicles" :key="v.id" :value="v.unit_id">{{ v.unit_id }} ({{ v.plate_number }})</option>
                </select>
                <input v-else type="text" :value="vehicleUnit" disabled class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm font-bold bg-slate-100 text-slate-500 cursor-not-allowed" />
              </div>
          </div>

          <!-- NATURE OF INCIDENT (Dynamic Options) -->
          <div>
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nature of Incident <span class="text-red-500">*</span></label>
            <select v-model="form.issue_type" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 bg-white">
              <option value="" disabled>Select issue type...</option>
              
              <template v-if="form.incident_target === 'Vehicle'">
                  <option value="Accident / Collision">Accident / Collision</option>
                  <option value="Mechanical Breakdown">Mechanical Breakdown (Engine/Transmission)</option>
                  <option value="Flat Tire">Flat Tire</option>
                  <option value="Medical Equipment Failure">Medical Equipment Failure</option>
                  <option value="Other Vehicle Issue">Other Vehicle Issue</option>
              </template>
              
              <template v-else>
                  <option value="Sudden Illness">Sudden Illness</option>
                  <option value="Severe Fatigue">Severe Fatigue</option>
                  <option value="Physical Injury">Physical Injury</option>
                  <option value="Personal Emergency">Personal Emergency</option>
                  <option value="Other Driver Issue">Other Driver Issue</option>
              </template>
            </select>
          </div>

          <!-- CRITICAL FIX: DYNAMIC EVIDENCE UPLOAD -->
          <div class="flex flex-col">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">
              Photographic Evidence 
              <span v-if="form.incident_target === 'Vehicle'" class="text-red-500">*</span>
              <span v-else class="text-slate-400 font-normal lowercase ml-1">(Optional)</span>
            </label>
            
            <div v-if="!form.evidence_image" class="relative w-full h-32 border-2 border-slate-300 border-dashed rounded-xl flex flex-col items-center justify-center hover:bg-slate-50 hover:border-red-400 transition-colors cursor-pointer group">
              <!-- Required attribute is dynamically tied to the incident target -->
              <input type="file" accept="image/*" capture="environment" @change="handleImageUpload" :required="form.incident_target === 'Vehicle'" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
              <svg class="w-6 h-6 text-slate-400 group-hover:text-red-500 mb-2 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
              <span class="text-[10px] font-bold text-slate-500 group-hover:text-red-600 transition-colors">Tap to capture photo</span>
            </div>
            <div v-else class="relative w-full h-32 rounded-xl overflow-hidden border-2 border-slate-200 shadow-sm bg-black">
              <img :src="form.evidence_image" class="w-full h-full object-contain" />
              <button @click="removeImage" type="button" class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white p-1.5 rounded-lg shadow-md transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>
          </div>

          <div>
             <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Detailed Remarks <span class="text-red-500">*</span></label>
             <textarea v-model="form.remarks" required rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="Please describe exactly what happened and your current location..."></textarea>
          </div>

          <div class="pt-2 flex justify-end space-x-3 border-t border-slate-100 mt-4 pt-4">
             <button type="button" @click="$emit('close')" class="px-4 py-2 text-sm font-bold text-slate-600 hover:text-slate-800 transition-colors">Cancel</button>
            <button type="submit" :disabled="!isFormValid" class="px-6 py-2 bg-red-600 text-white font-bold text-sm rounded-lg hover:bg-red-700 transition-colors shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
              Submit Incident Report
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../axios';

const props = defineProps({
    vehicleUnit: { type: String, default: '' },
    isDispatcher: { type: Boolean, default: false }
});

const emit = defineEmits(['close', 'reported']);

const activeVehicles = ref([]);

const form = ref({
    vehicle_unit: props.vehicleUnit,
    incident_target: 'Vehicle',
    issue_type: '',
    remarks: '',
    evidence_image: ''
});

onMounted(async () => {
    if (props.isDispatcher) {
        try {
            const res = await api.get('/api/vehicles?all=true');
            activeVehicles.value = res.data.data || res.data;
        } catch (e) {
            console.error('Failed to load vehicles for dispatch incident report.');
        }
    }
});

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        const img = new Image();
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const MAX_WIDTH = 800; // Compress down to 800px
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

            // Compress to JPEG at 70% quality
            form.value.evidence_image = canvas.toDataURL('image/jpeg', 0.7);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
};

const removeImage = () => {
    form.value.evidence_image = '';
};

// CRITICAL FIX: If Target is Driver, evidence_image is NOT required to validate the form!
const isFormValid = computed(() => {
    const baseValid = form.value.vehicle_unit && form.value.issue_type && form.value.remarks;
    if (form.value.incident_target === 'Vehicle') {
        return baseValid && form.value.evidence_image;
    }
    return baseValid;
});

const submitIssue = () => {
    emit('reported', { ...form.value });
};
</script>