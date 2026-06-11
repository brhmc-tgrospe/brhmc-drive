<template>
  <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex flex-col h-full overflow-hidden">
    <div class="p-3 sm:p-4 border-b border-slate-100 bg-slate-50 shrink-0">
      <h3 class="font-extrabold text-slate-800 text-sm sm:text-base">Live Trip Tracking</h3>
      <p class="text-[10px] sm:text-xs text-slate-500 font-medium">Click vehicles to toggle visibility</p>
    </div>
    
    <div class="flex-1 overflow-y-auto custom-scrollbar">
      <table class="w-full text-left">
        <thead class="bg-white sticky top-0 border-b border-slate-100 z-10">
          <tr class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider">
            <th class="py-2 px-3">Trip ID</th>
            <th class="py-2 px-3">Vehicle</th>
            <th class="py-2 px-3 text-center">Phase</th>
            <th class="py-2 px-3">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-xs">
          <tr v-if="!vehicles?.length">
            <td colspan="4" class="py-6 text-center text-slate-400 font-medium text-xs">No active vehicles.</td>
          </tr>
          <tr v-for="vehicle in vehicles" :key="vehicle.id" 
              @click="$emit('select', vehicle)"
              class="transition-colors cursor-pointer"
              :class="isSelected(vehicle) ? 'bg-slate-50' : 'hover:bg-slate-50'">
            
            <td class="py-3 px-3 text-slate-400 font-mono text-[10px]">
                {{ vehicle.trip_id ? `TRP-${vehicle.trip_id}` : '--' }}
            </td>
            
            <!-- CRITICAL FIX: Stacking the Unit ID and Driver Name -->
            <td class="py-2 px-3 border-l-[3px] whitespace-nowrap"
                :class="isSelected(vehicle) ? 'border-blue-400 bg-slate-100' : 'border-transparent'">
              <div class="font-bold text-slate-800">{{ vehicle.unit_id }}</div>
              <div class="text-[10px] text-slate-500 font-medium mt-0.5">
                {{ vehicle.current_driver || vehicle.driver_name || (vehicle.status === 'READY' ? 'Available' : 'Unassigned') }}
              </div>
            </td>
            
            <td class="py-3 px-3 text-center">
              <span v-if="vehicle.current_phase !== undefined && vehicle.current_phase !== null" class="inline-flex items-center justify-center w-5 h-5 rounded-full font-bold text-[10px] bg-indigo-100 text-indigo-700">
                {{ vehicle.current_phase }}
              </span>
              <span v-else class="inline-flex items-center justify-center w-5 h-5 rounded-full font-bold text-[10px] bg-slate-100 text-slate-400">-</span>
            </td>
            
            <td class="py-3 px-3">
              <span class="px-2 py-1 text-[9px] font-bold border rounded-full tracking-wider uppercase"
                    :class="{
                       'border-teal-200 text-teal-700 bg-teal-50': vehicle.status === 'READY',
                       'border-purple-200 text-purple-700 bg-purple-50': vehicle.status === 'SCHEDULED',
                       'border-blue-200 text-blue-700 bg-blue-50': vehicle.status === 'IN_USE',
                       'border-red-200 text-red-700 bg-red-50': vehicle.status === 'MAINTENANCE' || vehicle.status === 'BREAKDOWN'
                    }">
                {{ vehicle.status.replace('_', ' ') }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  vehicles: Array,
  selectedVehicles: {
    type: Array,
    default: () => []
  }
});
defineEmits(['select']);

const isSelected = (vehicle) => {
  return props.selectedVehicles.some(v => v.id === vehicle.id);
};
</script>