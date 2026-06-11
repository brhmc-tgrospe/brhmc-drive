<template>
  <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <!-- CRITICAL FIX: The v-if="incident" prevents the 'null' crash! -->
    <div v-if="incident" class="relative w-full max-w-lg bg-white rounded-xl shadow-2xl flex flex-col border border-slate-200 overflow-hidden animate-fade-in-up max-h-[90vh]">
      
      <!-- Modal Header -->
      <div class="px-4 py-3 sm:px-4 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
        <div>
          <h3 class="text-base font-bold text-slate-800">Incident Details (INC-{{ incident.id }})</h3>
          <p class="text-[10px] text-slate-500 font-medium">Reported by: {{ incident.reporter_name || 'Unknown' }}</p>
        </div>
        <button @click="$emit('close')" class="text-slate-400 hover:text-red-500 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      
      <!-- Modal Body -->
      <div class="p-4 sm:p-5 flex-1 overflow-y-auto custom-scrollbar space-y-4">
        
        <div class="grid grid-cols-2 gap-4">
           <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
               <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Asset / Target</span>
               <span class="text-xs font-bold text-slate-700">
                 {{ incident.vehicle_unit || 'Unknown' }} 
                 <span class="text-[10px] text-slate-500 ml-1">({{ incident.incident_target || 'N/A' }})</span>
               </span>
           </div>
           <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
               <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Nature of Issue</span>
               <span class="text-xs font-bold text-red-600">{{ incident.issue_type || 'Unspecified' }}</span>
           </div>
        </div>

        <div>
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Detailed Remarks</span>
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs text-slate-700 whitespace-pre-wrap min-h-[60px]">{{ incident.remarks || 'No remarks provided.' }}</div>
        </div>

        <div>
            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Photographic Evidence</span>
            <div class="bg-black border border-slate-300 rounded-lg overflow-hidden h-48 flex items-center justify-center">
                <img v-if="incident.evidence_image" :src="incident.evidence_image" class="w-full h-full object-contain" />
                <span v-else class="text-xs text-slate-500 italic">No image provided</span>
            </div>
        </div>

        <!-- Acknowledgment Signature Block -->
        <div v-if="incident.status && incident.status !== 'PENDING'" class="border-t border-slate-200 pt-3">
             <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Acknowledgment Info</span>
             <p class="text-xs text-slate-700">Acknowledged by <span class="font-bold">{{ incident.dispatcher_name || 'System' }}</span></p>
             <div class="mt-2 bg-slate-50 border border-slate-200 rounded-lg p-2 flex justify-center h-16">
                 <img v-if="incident.dispatcher_signature && incident.dispatcher_signature.startsWith('data:image')" :src="incident.dispatcher_signature" class="h-full object-contain mix-blend-multiply" />
                 <span v-else-if="incident.dispatcher_signature" class="font-bold italic text-slate-600 flex items-center">{{ incident.dispatcher_signature }}</span>
             </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
    show: Boolean,
    incident: {
        type: Object,
        default: null // CRITICAL FIX: Defaults to null so the v-if wrapper hides the modal until data arrives!
    }
});
defineEmits(['close']);
</script>