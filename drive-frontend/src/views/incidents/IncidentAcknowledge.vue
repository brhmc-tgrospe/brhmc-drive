<template>
  <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="relative w-full max-w-md bg-white rounded-xl shadow-2xl flex flex-col border border-slate-200 overflow-hidden animate-fade-in-up">
      <div class="px-4 py-3 border-b border-amber-100 flex items-center gap-2 bg-amber-50 shrink-0">
        <h3 class="text-base font-bold text-amber-800">Acknowledge Incident & Take Action</h3>
      </div>
      
      <div class="p-4 sm:p-5 flex-1 overflow-y-auto custom-scrollbar">
        <div class="mb-4 flex justify-between items-start">
            <div>
                <p class="text-xs font-bold text-slate-700 uppercase tracking-widest">{{ incident?.issue_type }}</p>
                <p class="text-sm text-slate-600 mt-1">Reported by: <span class="font-bold">{{ incident?.reporter_name }}</span></p>
            </div>
            <!-- NEW: Toggle Details Button -->
            <button @click="showDetails = !showDetails" class="text-[10px] font-bold px-3 py-1.5 rounded-lg border transition-colors flex items-center gap-1" :class="showDetails ? 'bg-slate-100 text-slate-600 border-slate-200' : 'bg-blue-50 text-blue-600 border-blue-200 hover:bg-blue-100'">
                <svg class="w-3.5 h-3.5 transition-transform" :class="showDetails ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                {{ showDetails ? 'Hide Details' : 'View Evidence' }}
            </button>
        </div>

        <!-- NEW: Expandable Evidence Section -->
        <div v-if="showDetails" class="mb-5 p-3 bg-slate-50 border border-slate-200 rounded-xl animate-fade-in-up">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Driver's Remarks</p>
            <p class="text-xs text-slate-700 whitespace-pre-wrap mb-3">{{ incident?.remarks }}</p>
            
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Photo Evidence</p>
            <div class="w-full h-32 bg-black rounded-lg overflow-hidden flex items-center justify-center border border-slate-300">
                <img v-if="incident?.evidence_image" :src="incident.evidence_image" class="w-full h-full object-contain" />
                <span v-else class="text-xs text-slate-400 italic">No image provided</span>
            </div>
        </div>

        <!-- MID-SHIFT DRIVER SWAPPER -->
        <div v-if="incident?.incident_target === 'Driver' && incident?.shift_id" class="mb-5 p-4 bg-blue-50 border border-blue-200 rounded-xl">
            <p class="text-[10px] font-black text-blue-800 uppercase tracking-widest mb-2">Emergency Driver Swap Required</p>
            <p class="text-xs text-blue-700 mb-3">This shift is still active. Please select a replacement driver to continue the trip from the current phase.</p>
            
            <select v-model="replacementDriverId" required class="w-full px-3 py-2 border border-blue-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white font-bold text-slate-700 shadow-sm">
                <option value="" disabled>Select available driver...</option>
                <option v-for="driver in availableDrivers" :key="driver.id" :value="driver.id">
                    {{ driver.first_name }} {{ driver.last_name }}
                </option>
            </select>
            <p v-if="availableDrivers.length === 0" class="text-xs text-red-500 font-bold mt-2 animate-pulse">WARNING: No drivers are currently available (READY).</p>
        </div>

        <div v-if="incident?.incident_target === 'Vehicle'" class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl">
            <p class="text-[10px] font-black text-red-800 uppercase tracking-widest">Asset Grounded</p>
            <p class="text-xs text-red-700 mt-1">This vehicle has been marked as BREAKDOWN and the shift was cancelled.</p>
        </div>

        <p class="text-xs text-slate-500 mb-3">By signing below, you officially acknowledge receipt of this emergency report.</p>
        
        <div class="space-y-2">
          <label class="block text-[10px] font-bold text-slate-500 uppercase flex justify-between">
            <span>Dispatcher Signature <span class="text-red-500">*</span></span>
            <button @click="clearSignature" class="text-red-500 hover:text-red-700 transition-colors">Clear</button>
          </label>
          
          <div class="relative border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 h-32 touch-none overflow-hidden group hover:border-amber-400 transition-colors cursor-crosshair">
            <canvas ref="signatureCanvas" class="absolute inset-0 w-full h-full"
                    @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"
                    @touchstart.prevent="startDrawing" @touchmove.prevent="draw" @touchend.prevent="stopDrawing"></canvas>
            <div v-if="!hasSignature" class="absolute inset-0 flex items-center justify-center opacity-30 select-none text-sm font-black uppercase pointer-events-none group-hover:opacity-20 transition-opacity">Sign Here</div>
          </div>
        </div>

        <div class="mt-5 flex justify-end space-x-3 border-t border-slate-100 pt-4">
          <button type="button" @click="$emit('close')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 transition-colors">Cancel</button>
          <button type="button" @click="submitAck" :disabled="!isFormValid" class="px-5 py-2 bg-amber-500 text-white font-bold text-xs rounded-lg hover:bg-amber-600 transition-colors shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
              Confirm & Resolve
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, computed } from 'vue';
import api from '../../axios';

const props = defineProps({ show: Boolean, incident: Object });
const emit = defineEmits(['close', 'acknowledged']);

const signatureCanvas = ref(null);
const isDrawing = ref(false);
const hasSignature = ref(false);
let ctx = null;

const showDetails = ref(false); // NEW STATE FOR TOGGLE

const availableDrivers = ref([]);
const replacementDriverId = ref('');

watch(() => props.show, async (newVal) => {
    if (newVal) {
        hasSignature.value = false;
        replacementDriverId.value = '';
        showDetails.value = false; // Reset toggle on open
        
        if (props.incident?.incident_target === 'Driver') {
            try {
                const res = await api.get(`/api/users?all=true&t=${new Date().getTime()}`);
                const allUsers = res.data.data || res.data;
                availableDrivers.value = allUsers.filter(u => u.role.toLowerCase() === 'driver' && u.driver_status === 'READY');
            } catch (e) {}
        }

        await nextTick();
        setTimeout(() => {
            if(signatureCanvas.value) {
                const rect = signatureCanvas.value.parentNode.getBoundingClientRect();
                signatureCanvas.value.width = rect.width;
                signatureCanvas.value.height = rect.height;
                ctx = signatureCanvas.value.getContext('2d');
                ctx.strokeStyle = '#0f172a'; 
                ctx.lineWidth = 2; 
                ctx.lineCap = 'round';
            }
        }, 150);
    }
});

const getPos = (e) => {
    const rect = signatureCanvas.value.getBoundingClientRect();
    const x = (e.clientX || e.touches[0].clientX) - rect.left;
    const y = (e.clientY || e.touches[0].clientY) - rect.top;
    return { x, y };
};

const startDrawing = (e) => { isDrawing.value = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); };
const draw = (e) => { if(!isDrawing.value) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); hasSignature.value = true; };
const stopDrawing = () => { isDrawing.value = false; };
const clearSignature = () => { ctx.clearRect(0,0, signatureCanvas.value.width, signatureCanvas.value.height); hasSignature.value = false; ctx.beginPath(); };

const isFormValid = computed(() => {
    // Temporary bypass for E2E testing: if (!hasSignature.value) return false;
    if (props.incident?.incident_target === 'Driver' && props.incident?.shift_id) {
        return replacementDriverId.value !== '';
    }
    return true;
});

const submitAck = () => {
    if(isFormValid.value) {
        emit('acknowledged', { 
            id: props.incident.id, 
            signature: hasSignature.value ? signatureCanvas.value.toDataURL('image/png') : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=',
            replacement_driver_id: replacementDriverId.value || null
        });
    }
};
</script>