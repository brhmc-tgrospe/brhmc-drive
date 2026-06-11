<template>
  <!-- CRITICAL FIX: Wrapped in a safe root div to prevent Vue Router Transition crashes -->
  <div>
    <Teleport to="body" v-if="show">
      <div class="print-wrapper fixed inset-0 z-[100] bg-slate-800/90 backdrop-blur-sm flex flex-col p-2 sm:p-6 overflow-hidden">
        
        <div class="print-inner flex-1 w-full max-w-[9in] mx-auto bg-slate-300 shadow-2xl rounded-lg flex flex-col overflow-hidden animate-fade-in-up border border-slate-600">
          <div class="bg-slate-900 text-white p-3 flex justify-between items-center shrink-0 print-hide z-10 shadow-md">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-300">Generated Incident Report</span>
            <div class="flex gap-3 items-center">
              <button @click="printReport" class="px-4 py-1.5 bg-teal-600 hover:bg-teal-500 rounded text-xs font-bold transition-colors shadow-sm">Print / Save PDF</button>
              <button @click="$emit('close')" class="p-1.5 hover:bg-red-500 rounded transition-colors text-slate-400 hover:text-white bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>
          </div>

          <div class="flex-1 overflow-y-auto p-4 sm:p-8 custom-scrollbar bg-slate-200" id="incident-report">
            <div class="w-[8.5in] min-h-[11in] bg-white mx-auto p-[0.5in] text-black shadow-lg">
              
              <div class="flex items-center justify-between border-b-2 border-black pb-4 mb-6">
                <img src="/images/brhmc-logo.png" class="w-16 h-16 object-contain hidden sm:block print-show" onerror="this.style.display='none'">
                <div class="text-center flex-1">
                  <p class="text-xs">Republic of the Philippines</p>
                  <p class="text-xs">Department of Health</p>
                  <h1 class="text-sm font-black uppercase">Bicol Regional Hospital and Medical Center</h1>
                  <p class="text-xs">Daraga, Albay</p>
                </div>
                <img src="/images/doh-logo.png" class="w-16 h-16 object-contain hidden sm:block print-show" onerror="this.style.display='none'">
              </div>
              
              <h2 class="text-center text-lg font-black uppercase tracking-widest mb-6">Master Incident Log</h2>
              <div class="text-xs font-bold mb-4 flex justify-between">
                  <span>Date Generated: {{ new Date().toLocaleDateString() }}</span>
                  <span>Total Records: {{ incidents.length }}</span>
              </div>

              <table class="w-full text-left text-[10px] border-collapse border border-black">
                <thead>
                  <tr class="bg-slate-200 border-b border-black">
                    <th class="p-2 border-r border-black uppercase font-black w-24">Date</th>
                    <th class="p-2 border-r border-black uppercase font-black">Reporter</th>
                    <th class="p-2 border-r border-black uppercase font-black">Asset</th>
                    <th class="p-2 border-r border-black uppercase font-black">Issue Nature</th>
                    <th class="p-2 uppercase font-black">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="incidents.length === 0">
                      <td colspan="5" class="p-4 text-center italic text-slate-500">No records found for current filter.</td>
                  </tr>
                  <tr v-else v-for="inc in incidents" :key="inc.id" class="border-b border-black">
                    <td class="p-2 border-r border-black">{{ new Date(inc.created_at).toLocaleDateString() }}</td>
                    <td class="p-2 border-r border-black font-bold">{{ inc.reporter_name }}</td>
                    <td class="p-2 border-r border-black">{{ inc.vehicle_unit }}</td>
                    <td class="p-2 border-r border-black text-red-600 font-bold">{{ inc.issue_type }}</td>
                    <td class="p-2 font-bold">{{ inc.status }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
defineProps({ show: Boolean, incidents: Array });
defineEmits(['close']);
const printReport = () => window.print();
</script>

<style scoped>
@media print {
    @page { size: 8.5in 11in; margin: 0; }
    body > *:not(.print-wrapper) { display: none !important; }
    html, body { height: auto !important; background: white !important; margin: 0 !important; }
    .print-wrapper { position: static !important; display: block !important; }
    .print-hide { display: none !important; }
    .print-show { display: block !important; }
    #incident-report { overflow: visible !important; height: auto !important; background: white !important; padding: 0 !important; }
}
</style>