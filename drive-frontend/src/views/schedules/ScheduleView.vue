<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="$emit('close')"></div>
    <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-sm p-5 sm:p-6 flex flex-col animate-fade-in-up">
      <h3 class="text-lg font-extrabold text-slate-800 mb-4 border-b border-slate-100 pb-2">Shift Details</h3>
      <div class="space-y-3 text-sm text-slate-600 mb-6">
        <div><span class="font-bold text-slate-400 text-[10px] uppercase block">Driver</span> {{ shift.driver?.first_name }} {{ shift.driver?.last_name }}</div>
        <div><span class="font-bold text-slate-400 text-[10px] uppercase block">Vehicle</span> {{ shift.vehicle?.unit_id }} ({{ shift.vehicle?.plate_number }})</div>
        <div><span class="font-bold text-slate-400 text-[10px] uppercase block">Start Time</span> {{ formatDateTime(shift.start_time) }}</div>
        <div><span class="font-bold text-slate-400 text-[10px] uppercase block">End Time</span> {{ formatDateTime(shift.end_time) }}</div>
        <div><span class="font-bold text-slate-400 text-[10px] uppercase block">Duration</span> {{ shift.shift_duration }} Hours</div>
        <div><span class="font-bold text-slate-400 text-[10px] uppercase block">Current Status</span> <span class="font-bold text-blue-600">{{ shift.status }}</span></div>
        <div><span class="font-bold text-slate-400 text-[10px] uppercase block">Trip Type</span> <span class="font-bold px-2 py-0.5 rounded-full text-[10px] uppercase tracking-wider" :class="shift.trip_type === 'REGULAR' ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-red-50 text-red-700 border border-red-200'">{{ shift.trip_type === 'REGULAR' ? 'Regular Trip' : 'Emergency Response' }}</span></div>
      </div>
      <button @click="$emit('close')" class="w-full py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-lg hover:bg-slate-200 transition-colors">Close</button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
    shift: { type: Object, required: true }
});
defineEmits(['close']);

const formatDateTime = (dateString) => {
    if (!dateString) return '';
    const utcDate = dateString.endsWith('Z') ? dateString : dateString.replace(' ', 'T') + '+08:00';
    return new Date(utcDate).toLocaleString('en-US', { 
        timeZone: 'Asia/Manila', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true 
    });
};
</script>