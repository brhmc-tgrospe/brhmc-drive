<template>
  <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="relative w-full max-w-sm bg-white rounded-xl shadow-2xl flex flex-col border border-slate-200 overflow-hidden animate-fade-in-up">
      <div class="px-4 py-3 border-b border-orange-100 flex items-center gap-2 bg-orange-50 shrink-0">
        <h3 class="text-base font-bold text-orange-800">Edit Incident Details</h3>
      </div>
      
      <div class="p-4 sm:p-5">
        <form @submit.prevent="submitEdit" class="space-y-4">
          
          <div>
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Update Nature of Incident</label>
            <input type="text" v-model="form.issue_type" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500" />
          </div>

          <div>
             <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Update Remarks</label>
             <textarea v-model="form.remarks" required rows="4" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"></textarea>
          </div>

          <div class="pt-2 flex justify-end space-x-2">
            <button type="button" @click="$emit('close')" class="px-3 py-1.5 text-xs font-bold text-slate-600 hover:text-slate-800 transition-colors">Cancel</button>
            <button type="submit" class="px-4 py-1.5 bg-orange-500 text-white font-bold text-xs rounded-lg hover:bg-orange-600 transition-colors shadow-sm">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    incident: Object
});
const emit = defineEmits(['close', 'saved']);

const form = ref({ issue_type: '', remarks: '' });

watch(() => props.show, (newVal) => {
    if (newVal && props.incident) {
        form.value.issue_type = props.incident.issue_type;
        form.value.remarks = props.incident.remarks;
    }
});

const submitEdit = () => {
    emit('saved', { id: props.incident.id, ...form.value });
};
</script>