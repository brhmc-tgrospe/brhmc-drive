<template>
  <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="$emit('close')"></div>
    
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col animate-fade-in-up">
      
      <div class="p-5 border-b border-slate-100 flex items-center justify-between" :class="isRejecting ? 'bg-red-50' : 'bg-slate-50'">
        <div>
          <h3 class="text-lg font-bold" :class="isRejecting ? 'text-red-800' : 'text-slate-800'">
            {{ isRejecting ? 'Reject & Ground Vehicle' : 'Dispatcher Verification' }}
          </h3>
          <p class="text-xs" :class="isRejecting ? 'text-red-600' : 'text-slate-500'">
            Checklist #{{ checklist?.id }} • {{ checklist?.vehicle_unit }}
          </p>
        </div>
        <button @click="$emit('close')" class="p-2 hover:bg-black/5 rounded-full transition-colors" :class="isRejecting ? 'text-red-400 hover:text-red-600' : 'text-slate-400 hover:text-slate-600'">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      
      <!-- APPROVAL VIEW -->
      <div v-if="!isRejecting" class="p-5 flex-1 overflow-y-auto">
        
        <!-- NEW: View Checklist Button -->
        <div class="mb-5 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 shadow-sm">
          <div class="text-sm">
            <p class="font-bold mb-1">Verify Submission:</p>
            <p class="text-xs">Please review the checklist submitted by <b>{{ checklist?.outgoing_driver }}</b> before signing.</p>
          </div>
          <button @click="$emit('view-details', checklist)" class="shrink-0 px-3 py-2 bg-white hover:bg-amber-100 border border-amber-300 text-amber-700 font-bold text-xs rounded-lg shadow-sm transition-colors flex items-center gap-2">
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
             View Full Report
          </button>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-bold text-slate-700 flex justify-between">
            <span>Digital Signature <span class="text-red-500">*</span></span>
            <span v-if="signatureError" class="text-xs text-red-500 font-medium animate-pulse">Signature is required!</span>
          </label>
          <div class="relative border-2 rounded-xl bg-slate-50 h-40 flex flex-col items-center justify-center group transition-colors cursor-crosshair overflow-hidden"
               :class="signatureError ? 'border-red-400 border-solid' : 'border-slate-300 border-dashed hover:border-indigo-300'">
            <span v-if="!hasSignature" class="absolute text-slate-400 text-sm font-medium select-none pointer-events-none transition-opacity duration-200">Draw signature here</span>
            <canvas ref="signatureCanvas" class="absolute inset-0 w-full h-full touch-none"
              @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"
              @touchstart.prevent="startDrawing" @touchmove.prevent="draw" @touchend.prevent="stopDrawing"></canvas>
            <div class="absolute bottom-6 left-8 right-8 border-b-2 border-slate-200 pointer-events-none"></div>
            <button v-if="hasSignature" type="button" @click="clearSignature" class="absolute top-2 right-2 px-2 py-1 text-xs font-semibold text-slate-500 hover:text-red-600 bg-white border border-slate-200 rounded shadow-sm z-10 transition-colors">Clear</button>
          </div>
          <p class="text-xs text-slate-500 text-right mt-1">Dispatcher: {{ authStore.user?.first_name || 'Current User' }}</p>
        </div>
      </div>

      <!-- REJECTION VIEW -->
      <div v-else class="p-5 flex-1 overflow-y-auto">
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800 flex justify-between items-center gap-3">
          <div>
              <p class="font-bold mb-1">Warning: Grounding Vehicle</p>
              <p class="text-xs">You are about to reject this checklist and ground <b>{{ checklist?.vehicle_unit }}</b>.</p>
          </div>
          <button @click="$emit('view-details', checklist)" class="shrink-0 px-3 py-2 bg-white hover:bg-red-100 border border-red-300 text-red-700 font-bold text-xs rounded-lg shadow-sm transition-colors">
             View Report
          </button>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-bold text-slate-700">Reason for Rejection <span class="text-red-500">*</span></label>
          <textarea v-model="rejectionReason" rows="4" class="w-full p-3 border rounded-xl text-sm focus:outline-none focus:ring-2 transition-colors"
            :class="rejectionReasonError ? 'border-red-400 focus:ring-red-500 bg-red-50' : 'border-slate-300 focus:ring-red-500 bg-white'"
            placeholder="E.g., Brakes failing, missing critical equipment..."></textarea>
          <p v-if="rejectionReasonError" class="text-xs text-red-500 font-medium">Please enter a reason before confirming.</p>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="p-5 border-t border-slate-100 bg-slate-50 flex items-center justify-between gap-3">
        <div v-if="!isRejecting" class="flex-1">
          <button @click="isRejecting = true" class="px-4 py-2 text-red-600 hover:bg-red-50 hover:border-red-200 border border-transparent rounded-lg text-sm font-bold transition-colors">
            Reject & Ground
          </button>
        </div>
        <div v-else class="flex-1">
          <button @click="cancelRejection" class="px-4 py-2 text-slate-600 hover:bg-slate-200 rounded-lg text-sm font-bold transition-colors">
            Back to Approval
          </button>
        </div>
        
        <div class="flex gap-2">
          <button v-if="!isRejecting" @click="$emit('close')" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">Cancel</button>
          
          <button v-if="!isRejecting" @click="handleApprove" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            Sign & Approve
          </button>
          
          <button v-if="isRejecting" @click="handleReject" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            Confirm Rejection
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { useAuthStore } from '../../stores/auth';

const props = defineProps({ show: Boolean, checklist: Object });
const emit = defineEmits(['close', 'approve', 'reject', 'view-details']); // ADDED EMIT
const authStore = useAuthStore();

const isRejecting = ref(false);
const rejectionReason = ref('');
const rejectionReasonError = ref(false);

const signatureCanvas = ref(null);
const isDrawing = ref(false);
const hasSignature = ref(false);
const signatureError = ref(false);
let ctx = null;

watch(() => props.show, async (newVal) => {
  if (newVal) {
    isRejecting.value = false;
    rejectionReason.value = '';
    rejectionReasonError.value = false;
    hasSignature.value = false;
    signatureError.value = false;
    
    await nextTick();
    setTimeout(initCanvas, 150); 
  }
});

const initCanvas = () => {
  const canvas = signatureCanvas.value;
  if (!canvas) return;
  ctx = canvas.getContext('2d');
  const rect = canvas.getBoundingClientRect();
  canvas.width = rect.width;
  canvas.height = rect.height;
  ctx.strokeStyle = '#1e293b'; 
  ctx.lineWidth = 3;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';
};

const getPos = (e) => {
  const canvas = signatureCanvas.value;
  const rect = canvas.getBoundingClientRect();
  const clientX = e.touches ? e.touches[0].clientX : e.clientX;
  const clientY = e.touches ? e.touches[0].clientY : e.clientY;
  return { x: clientX - rect.left, y: clientY - rect.top };
};

const startDrawing = (e) => { if (!ctx) return; isDrawing.value = true; signatureError.value = false; const pos = getPos(e); ctx.beginPath(); ctx.moveTo(pos.x, pos.y); };
const draw = (e) => { if (!isDrawing.value || !ctx) return; const pos = getPos(e); ctx.lineTo(pos.x, pos.y); ctx.stroke(); hasSignature.value = true; };
const stopDrawing = () => { isDrawing.value = false; if (ctx) ctx.closePath(); };
const clearSignature = () => { if (!ctx || !signatureCanvas.value) return; ctx.clearRect(0, 0, signatureCanvas.value.width, signatureCanvas.value.height); hasSignature.value = false; ctx.beginPath(); };
const cancelRejection = () => { isRejecting.value = false; rejectionReasonError.value = false; };

const handleApprove = () => {
  if (!hasSignature.value) { signatureError.value = true; return; }
  const signatureData = signatureCanvas.value ? signatureCanvas.value.toDataURL('image/png') : 'SIGNED_VERIFIED';
  emit('approve', { id: props.checklist.id, signature: signatureData });
};

const handleReject = () => {
  if (!hasSignature.value && isRejecting.value === false) { signatureError.value = true; return; }
  if (!rejectionReason.value.trim()) { rejectionReasonError.value = true; return; }
  const signatureData = (hasSignature.value && signatureCanvas.value) ? signatureCanvas.value.toDataURL('image/png') : 'No Signature (Rejected)';
  emit('reject', { id: props.checklist.id, reason: rejectionReason.value, signature: signatureData });
};
</script>