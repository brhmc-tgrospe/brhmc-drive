<template>
  <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col animate-fade-in-up max-h-[95vh]">
      
      <!-- Modal Header -->
      <div class="p-5 border-b border-slate-100 flex items-center justify-between shrink-0" :class="isRejecting ? 'bg-red-50' : 'bg-slate-50'">
        <div>
          <h3 class="text-lg font-bold" :class="isRejecting ? 'text-red-800' : 'text-slate-800'">
            {{ isRejecting ? 'Reject & Ground Vehicle' : 'Review & Sign Checklist' }}
          </h3>
          <p class="text-xs" :class="isRejecting ? 'text-red-600' : 'text-slate-500'">
            Checklist #{{ checklist?.id || '---' }} • {{ checklist?.vehicle_unit || 'Unknown Unit' }}
          </p>
        </div>
        <button @click="$emit('close')" class="p-2 hover:bg-black/5 rounded-full transition-colors" :class="isRejecting ? 'text-red-400 hover:text-red-600' : 'text-slate-400 hover:text-slate-600'">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      
      <!-- Modal Body -->
      <div class="p-5 flex-1 overflow-y-auto">
        
        <!-- VIEW CHECKLIST DETAILS TOGGLE -->
        <button v-if="!isRejecting" @click="isViewingDetails = !isViewingDetails" class="w-full mb-5 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-bold rounded-xl transition-colors flex items-center justify-center gap-2 border border-blue-200 shadow-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
          {{ isViewingDetails ? 'Hide Checklist Details' : 'Review Submitted Checklist' }}
        </button>

        <!-- CHECKLIST DETAILS EXPANDED VIEW -->
        <div v-if="isViewingDetails && !isRejecting" class="mb-5 p-4 border border-slate-200 rounded-xl bg-slate-50 animate-fade-in-up">
           <h4 class="font-bold text-slate-700 mb-3 border-b border-slate-200 pb-2 text-sm">Checklist Summary</h4>
           <div class="grid grid-cols-2 gap-3 text-xs mb-3">
             <div><span class="text-slate-500 block">Driver</span><span class="font-bold text-slate-800">{{ checklist?.outgoing_driver || 'N/A' }}</span></div>
             <div><span class="text-slate-500 block">Odometer</span><span class="font-bold text-slate-800">{{ checklist?.odometer ? checklist.odometer + ' km' : 'N/A' }}</span></div>
             <div><span class="text-slate-500 block">Fuel Level</span><span class="font-bold text-slate-800">{{ checklist?.fuel_level || 'N/A' }}</span></div>
             <div><span class="text-slate-500 block">Condition</span><span class="font-bold text-slate-800">{{ checklist?.condition || 'N/A' }}</span></div>
           </div>
           <div>
             <span class="text-slate-500 text-xs block">Driver Remarks</span>
             <p class="font-medium text-slate-700 text-xs italic bg-white p-2 rounded border border-slate-100 mt-1">{{ checklist?.remarks || 'No issues reported.' }}</p>
           </div>
        </div>
        
        <!-- REJECTION REASON SECTION -->
        <div v-if="isRejecting" class="mb-5 space-y-2 animate-fade-in-up">
          <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800 mb-3">
            <p class="font-bold mb-1">Warning: Grounding Vehicle</p>
            <p>You are about to reject this checklist and ground <b>{{ checklist?.vehicle_unit || 'this vehicle' }}</b>. Please provide a clear reason.</p>
          </div>
          <label class="block text-sm font-bold text-slate-700">Reason for Rejection <span class="text-red-500">*</span></label>
          <textarea 
            v-model="rejectionReason"
            rows="3" 
            class="w-full p-3 border rounded-xl text-sm focus:outline-none focus:ring-2 transition-colors"
            :class="rejectionReasonError ? 'border-red-400 focus:ring-red-500 bg-red-50' : 'border-slate-300 focus:ring-teal-500 bg-white'"
            placeholder="E.g., Brakes failing, missing critical equipment..."
          ></textarea>
          <p v-if="rejectionReasonError" class="text-xs text-red-500 font-medium">Please enter a reason before confirming.</p>
        </div>

        <!-- INSTRUCTIONS -->
        <div v-if="!isRejecting" class="mb-5 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
          <p class="font-bold mb-1">Attention Dispatcher:</p>
          <p>By signing below, you verify that you have reviewed the checklist details above and approve the vehicle's status for dispatch or handoff.</p>
        </div>

        <!-- STEP 1: SIGNATURE SECTION -->
        <div class="space-y-2">
          <label class="block text-sm font-bold text-slate-700 flex justify-between items-end">
            <span>1. Digital Signature <span class="text-red-500">*</span></span>
            <span v-if="signatureError" class="text-xs text-red-500 font-medium animate-pulse">Signature is required!</span>
          </label>
          
          <div class="relative border-2 rounded-xl bg-slate-50 h-40 flex flex-col items-center justify-center group transition-colors overflow-hidden"
               :class="signatureError ? 'border-red-400 border-solid' : 'border-slate-300 border-dashed hover:border-teal-300 cursor-crosshair'">
            
            <span v-if="!hasSignature" class="absolute text-slate-400 text-sm font-medium select-none pointer-events-none transition-opacity duration-200">Draw signature here</span>
            
            <!-- Real HTML5 Canvas for drawing -->
            <SignaturePad ref="sigPad" :show="show" class="absolute inset-0 z-10" @update:hasSignature="val => { hasSignature = val; if(val) signatureError = false; }" />

            <div class="absolute bottom-6 left-8 right-8 border-b-2 border-slate-200 pointer-events-none z-0"></div>
            
            <!-- Signature Controls -->
            <div v-if="hasSignature" class="absolute top-2 right-2 flex gap-2 z-20 animate-fade-in-up">
              <button type="button" @click="$refs.sigPad.clearSignature()" class="px-3 py-1.5 text-xs font-bold text-slate-500 hover:text-red-600 bg-white border border-slate-200 rounded-lg shadow-sm transition-colors">Clear</button>
            </div>
          </div>
          <p class="text-xs text-slate-500 text-right mt-1">Dispatcher: {{ authStore.user?.first_name || 'Current User' }}</p>
        </div>
      </div>

      <!-- STEP 2: ACTION FOOTER -->
      <div class="p-5 border-t border-slate-100 bg-slate-50 shrink-0">
        <label class="block text-sm font-bold text-slate-700 mb-3" :class="{'text-slate-400': !hasSignature}">2. Final Action</label>
        
        <div class="flex items-center justify-between gap-3">
          <div v-if="!isRejecting" class="flex-1">
            <button @click="isRejecting = true" class="px-4 py-2.5 text-red-600 hover:bg-red-50 hover:border-red-200 border border-transparent rounded-lg text-sm font-bold transition-colors">
              Reject & Ground
            </button>
          </div>
          <div v-else class="flex-1">
            <button @click="cancelRejection" class="px-4 py-2.5 text-slate-600 hover:bg-slate-200 rounded-lg text-sm font-bold transition-colors">
              Back to Approval
            </button>
          </div>
          
          <div class="flex gap-2">
            <button v-if="!isRejecting" @click="$emit('close')" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">Cancel</button>
            
            <button v-if="!isRejecting" @click="handleApprove" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              Approve & Release
            </button>
            <button v-if="isRejecting" @click="handleReject" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
              Confirm Rejection
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useAuthStore } from '../../stores/auth';
import SignaturePad from '../../components/SignaturePad.vue';

const props = defineProps({
  show: Boolean,
  checklist: Object
});

const emit = defineEmits(['close', 'approve', 'reject']);
const authStore = useAuthStore();

const isRejecting = ref(false);
const isViewingDetails = ref(false);
const rejectionReason = ref('');
const rejectionReasonError = ref(false);

const sigPad = ref(null);
const hasSignature = ref(false);
const signatureError = ref(false);

watch(() => props.show, (newVal) => {
  if (newVal) {
    isRejecting.value = false;
    isViewingDetails.value = false;
    rejectionReason.value = '';
    rejectionReasonError.value = false;
    hasSignature.value = false;
    signatureError.value = false;
  }
});

const cancelRejection = () => {
  isRejecting.value = false;
  rejectionReasonError.value = false;
};

const handleApprove = () => {
  const signatureData = (hasSignature.value && sigPad.value && sigPad.value.getSignatureData()) ? sigPad.value.getSignatureData() : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
  emit('approve', { id: props.checklist.id, signature: signatureData });
};

const handleReject = () => {
  if (!rejectionReason.value.trim()) {
    rejectionReasonError.value = true;
    return;
  }
  
  const signatureData = (hasSignature.value && sigPad.value && sigPad.value.getSignatureData()) ? sigPad.value.getSignatureData() : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
  emit('reject', { id: props.checklist.id, reason: rejectionReason.value, signature: signatureData });
};
</script>