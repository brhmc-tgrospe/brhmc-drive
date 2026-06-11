<template>
  <div class="border-t-4 rounded-xl shadow-sm p-5 sm:p-6 flex flex-col relative overflow-hidden h-full min-h-[220px]"
       :class="{
          'border-teal-500': selected?.status === 'READY',
          'border-purple-500': selected?.status === 'SCHEDULED',
          'border-blue-500': selected?.status === 'IN_USE',
          'border-red-500': selected?.status === 'MAINTENANCE' || selected?.status === 'BREAKDOWN',
          'border-slate-700': !selected,
          'bg-[#111827]': !selected || !selected.image_path /* Fallback solid color */
       }">
    
    <!-- DYNAMIC BACKGROUND IMAGE -->
    <div v-if="selected && selected.image_path" class="absolute inset-0 z-0">
       <!-- The actual vehicle image -->
       <img :src="selected.image_path.startsWith('data:image') ? selected.image_path : `http://localhost:8000/storage/${selected.image_path}`" alt="Vehicle" class="w-full h-full object-cover" />
       <!-- Dark gradient overlay for text readability -->
       <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/65 to-slate-900/20"></div>
    </div>

    <!-- Fallback decorative SVG if no image is present -->
    <svg v-if="!selected || !selected.image_path" class="absolute -right-10 -bottom-10 w-48 h-48 text-white opacity-5 pointer-events-none z-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19 17h2c.6 0 1 -.4 1 -1v-3c0 -.9 -.7 -1.7 -1.5 -1.9C18.7 10.6 16 10 16 10s-1.3 -1.4 -2.2 -2.3c-.5 -.4 -1.1 -.7 -1.8 -.7H5c-1.1 0 -2 .9 -2 2v8c0 .6 .4 1 1 1h2m13 0a2 2 0 1 1 -4 0m4 0a2 2 0 1 0 -4 0m-9 0h4m-4 0a2 2 0 1 1 -4 0m4 0a2 2 0 1 0 -4 0"></path></svg>
    
    <template v-if="selected">
      <div class="flex justify-between items-start mb-6 relative z-10">
        <div>
          <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight drop-shadow-md">{{ selected.unit_id }}</h2>
          <p class="text-slate-300 text-xs font-mono mt-0.5 tracking-widest uppercase drop-shadow-sm">{{ selected.plate_number }}</p>
        </div>
      </div>
      
      <div class="mt-auto relative z-10 flex justify-between items-end">
        <div>
          <h3 class="text-base font-bold text-slate-100 drop-shadow-sm">{{ selected.make_model }}</h3>
          <p class="text-slate-300 text-xs mt-0.5 drop-shadow-sm">{{ selected.vehicle_type }}</p>
          <p class="text-blue-400 text-sm font-bold mt-3 drop-shadow-sm">Odo: {{ selected.odometer?.toLocaleString() }} km</p>
        </div>
        
        <span class="px-2.5 py-1 text-[10px] font-bold rounded uppercase tracking-wider shadow-md"
              :class="{
                 'bg-teal-500 text-white': selected.status === 'READY',
                 'bg-purple-500 text-white': selected.status === 'SCHEDULED',
                 'bg-blue-500 text-white': selected.status === 'IN_USE',
                 'bg-red-500 text-white': selected.status === 'MAINTENANCE' || selected.status === 'BREAKDOWN'
              }">
          {{ selected.status.replace('_', ' ') }}
        </span>
      </div>
    </template>
    
    <template v-else>
      <div class="flex flex-col items-center justify-center h-full text-slate-500 relative z-10">
        <!-- SPACED SVG TO PREVENT PARSER ERROR -->
        <svg class="w-12 h-12 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4 -4m4 4l-4 4m0 6H4m0 0l4 4m-4 -4l4 -4"></path></svg>
        <p class="text-sm font-bold text-slate-300">No Vehicle Selected</p>
        <p class="text-xs mt-1 text-center text-slate-400">Select a vehicle from the tracking list.</p>
      </div>
    </template>
  </div>
</template>

<script setup>
defineProps({
  selected: Object
});
</script>