<template>
  <Teleport to="body">
    <div v-if="show" class="print-wrapper fixed inset-0 z-[100] bg-slate-800/90 backdrop-blur-sm flex flex-col p-2 sm:p-6 overflow-hidden">
      
      <!-- Loading State -->
      <div v-if="loading" class="flex-1 flex flex-col items-center justify-center text-white print-hide">
        <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-white mb-4"></div>
        <p class="font-bold tracking-widest uppercase">Generating Official Report...</p>
      </div>

      <!-- Document Viewer -->
      <div v-else class="print-inner flex-1 w-full max-w-[9in] mx-auto bg-slate-300 shadow-2xl rounded-lg flex flex-col overflow-hidden animate-fade-in-up border border-slate-600">
        
        <!-- Toolbar -->
        <div class="bg-slate-900 text-white p-3 flex justify-between items-center shrink-0 print-hide z-10 shadow-md">
          <span class="text-xs font-bold uppercase tracking-widest text-slate-300">Document Viewer • Report #{{ checklistData?.checklist?.id }}</span>
          <div class="flex gap-3 items-center">
            <span class="text-[10px] text-slate-400 hidden sm:block"> • Dynamic Pagination</span>
            <button @click="printReport" class="px-4 py-1.5 bg-teal-600 hover:bg-teal-500 rounded text-xs font-bold transition-colors shadow-sm">Print / Save PDF</button>
            <button @click="$emit('close')" class="p-1.5 hover:bg-red-500 rounded transition-colors text-slate-400 hover:text-white bg-slate-800">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          </div>
        </div>

        <!-- SCROLLABLE DESK -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 custom-scrollbar flex flex-col items-center gap-8" id="printable-report">
          
          <!-- ============================================== -->
          <!-- PAGE 1 (8.5" x 13" White Paper)                -->
          <!-- ============================================== -->
          <div class="folio-page flex flex-col">
            
            <div class="border-2 border-black mb-3 shrink-0">
              <div class="flex items-center justify-between border-b-2 border-black p-2 sm:p-3">
                <img src="/images/brhmc-logo.png" alt="BRHMC" class="w-12 h-12 sm:w-16 sm:h-16 object-contain hidden sm:block print-show" onerror="this.style.display='none'">
                <div class="text-center flex-1">
                  <p class="text-[10px] sm:text-xs">Republic of the Philippines</p>
                  <p class="text-[10px] sm:text-xs">Department of Health</p>
                  <h1 class="text-xs sm:text-sm font-black uppercase">Bicol Regional Hospital and Medical Center</h1>
                  <p class="text-[10px] sm:text-xs">Daraga, Albay</p>
                </div>
                <img src="/images/doh-logo.png" alt="DOH" class="w-12 h-12 sm:w-16 sm:h-16 object-contain hidden sm:block print-show" onerror="this.style.display='none'">
              </div>
              
              <div class="flex border-t border-black bg-slate-200 print-bg-slate-200">
                <div class="flex-1 flex items-center justify-center p-2">
                  <h2 class="text-sm sm:text-lg font-black uppercase tracking-widest text-center">Ambulance Maintenance Checklist</h2>
                </div>
                <div class="border-l-2 border-black flex items-center justify-center px-4 py-2 whitespace-nowrap">
                  <h2 class="text-sm sm:text-lg font-black uppercase">{{ checklistData?.checklist?.type.replace('_', ' ') }}</h2>
                </div>
              </div>
            </div>

            <div class="flex justify-between items-center mb-3 text-[10px] sm:text-xs font-bold px-2 shrink-0">
                <span>AMBULANCE PLATE NUMBER: <span class="underline underline-offset-4 decoration-slate-400 text-sm ml-1">{{ checklistData?.checklist?.vehicle_plate }} ({{ checklistData?.checklist?.vehicle_unit }})</span></span>
                <span>TYPE OF AMBULANCE: <span class="underline underline-offset-4 decoration-slate-400 text-sm ml-1">{{ checklistData?.checklist?.vehicle_type }}</span></span>
            </div>

            <!-- GRID 2 COLUMNS -->
            <div class="flex-1 grid grid-cols-2 gap-4 h-full min-h-0">
              
              <!-- LEFT COLUMN: BLOW BAGETS & LIGHTS -->
              <div class="border-2 border-black flex flex-col h-full">
                <div class="bg-slate-200 print-bg-slate-200 border-b-2 border-black text-center font-black p-1 text-red-600 print-text-red">BLOWBAGETS</div>
                
                <div class="flex-1 flex flex-col divide-y divide-black text-[9px] font-bold">
                  
                  <div class="flex justify-between p-1 px-2"><span>Odometer:</span> <span class="font-mono text-xs">{{ checklistData?.items?.meta?.Odometer }} km</span></div>
                  
                  <div class="flex flex-col justify-between p-1 px-2 min-h-[90px]">
                    <div class="flex justify-between w-full mb-1">
                      <span>Fuel Level:</span>
                      <span class="font-mono text-xs">{{ checklistData?.items?.meta?.FuelLevel }}%</span>
                    </div>
                    <div class="flex-1 flex items-center justify-center bg-slate-50 print-bg-slate-50 border border-slate-300 rounded p-1">
                      <img v-if="checklistData?.items?.meta?.FuelImage" :src="checklistData.items.meta.FuelImage" class="max-h-16 object-contain shadow-sm" />
                      <span v-else class="text-[8px] italic text-slate-400 font-normal">No image provided</span>
                    </div>
                  </div>
                  
                  <div class="flex justify-between p-1 px-2"><span>Battery:</span> <StatusIcon :passed="checklistData?.items?.engineCabin?.battery" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Dashboard Indicator:</span> <StatusIcon :passed="checklistData?.items?.lights?.dashboard" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Front Interior Light:</span> <StatusIcon :passed="checklistData?.items?.lights?.front_interior" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Rear Interior Light:</span> <StatusIcon :passed="checklistData?.items?.lights?.rear_interior" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Headlights (high):</span> <StatusIcon :passed="checklistData?.items?.lights?.headlights_high" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Headlights (low):</span> <StatusIcon :passed="checklistData?.items?.lights?.headlights_low" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Foglamps:</span> <StatusIcon :passed="checklistData?.items?.lights?.foglamps" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Park lights:</span> <StatusIcon :passed="checklistData?.items?.lights?.park_light" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Tail Lights:</span> <StatusIcon :passed="checklistData?.items?.lights?.tail_light" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Signal lights front (R & L):</span> <StatusIcon :passed="checklistData?.items?.lights?.signal_front" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Signal lights rear (R & L):</span> <StatusIcon :passed="checklistData?.items?.lights?.signal_rear" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Hazard Lights:</span> <StatusIcon :passed="checklistData?.items?.lights?.hazard" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Reverse Light:</span> <StatusIcon :passed="checklistData?.items?.lights?.reverse" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Brake light:</span> <StatusIcon :passed="checklistData?.items?.lights?.brake_light" /></div>
                  <div class="flex justify-between p-1 px-2"><span>OverHead Warning Light Bar:</span> <StatusIcon :passed="checklistData?.items?.lights?.overhead_warning" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Front Scene Lights:</span> <StatusIcon :passed="checklistData?.items?.lights?.front_scene" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Rear Scene Lights:</span> <StatusIcon :passed="checklistData?.items?.lights?.rear_scene" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Oil Level:</span> <StatusIcon :passed="checklistData?.items?.engineCabin?.oil_level" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Water in Radiator/Coolant Level:</span> <StatusIcon :passed="checklistData?.items?.engineCabin?.water_coolant" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Washer Fluid:</span> <StatusIcon :passed="checklistData?.items?.engineCabin?.washer_fluid" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Brakes:</span> <StatusIcon :passed="checklistData?.items?.engineCabin?.brakes" /></div>
                  
                  <!-- Tires -->
                  <div class="p-1 px-2 bg-slate-50 print-bg-slate-50">
                    <p class="mb-1">Tire Pressure (PSI):</p>
                    <div class="grid grid-cols-2 gap-1 text-center text-[9px] font-mono border border-black p-1 bg-white">
                      <div>FL: {{ checklistData?.items?.meta?.FrontLeft }}</div>
                      <div>FR: {{ checklistData?.items?.meta?.FrontRight }}</div>
                      <div>RL: {{ checklistData?.items?.meta?.RearLeft }}</div>
                      <div>RR: {{ checklistData?.items?.meta?.RearRight }}</div>
                    </div>
                  </div>

                  <!-- Engine Remarks & Legend (Flex-1 fills remaining space) -->
                  <div class="flex-1 flex flex-col p-1.5 px-2 bg-white">
                    <div class="text-[7.5px] sm:text-[8px] font-medium text-slate-800 leading-tight mb-2 border border-slate-300 p-1 bg-slate-50 print-bg-slate-50 w-full">
                      <span class="font-black text-red-600 print-text-red">Legend:</span> Need Repair (NR), Need Replacement (NRP), For Condem: (C) <br/>
                      For Refueling (RF), Additional Air (AA), Empty (E), Full (F)
                    </div>
                    <span class="font-black uppercase text-slate-800 mb-0.5">Engine Remarks:</span>
                    <p class="font-medium whitespace-pre-wrap text-slate-700 leading-tight flex-1">{{ checklistData?.items?.meta?.EngineRemarks && checklistData?.items?.meta?.EngineRemarks !== 'None' ? checklistData?.items?.meta?.EngineRemarks : '' }}</p>
                  </div>

                  <!-- Overall Condition -->
                  <div class="p-1.5 px-2 border-t-2 border-black bg-slate-100 print-bg-slate-100 mt-auto shrink-0">
                    <span class="uppercase">Overall Condition:</span> 
                    <span class="ml-2 font-black text-xs sm:text-sm" :class="checklistData?.items?.meta?.Condition === 'Good' ? 'text-teal-600 print-text-teal' : 'text-red-600 print-text-red'">{{ checklistData?.items?.meta?.Condition }}</span>
                  </div>
                </div>
              </div>

              <!-- RIGHT COLUMN: ENGINE & RESPONSE EQUIPMENT -->
              <div class="border-2 border-black flex flex-col h-full">
                
                <div class="bg-slate-200 print-bg-slate-200 border-b-2 border-black text-center font-black p-1 text-red-600 print-text-red">ENGINE</div>
                
                <div class="flex flex-col divide-y divide-black text-[9px] font-bold border-b-2 border-black">
                  <div class="flex justify-between p-1 px-2"><span>Siren</span> <StatusIcon :passed="checklistData?.items?.engineCabin?.siren" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Horn</span> <StatusIcon :passed="checklistData?.items?.engineCabin?.horn" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Power Locks</span> <StatusIcon :passed="checklistData?.items?.engineCabin?.power_locks" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Windshield Wipers</span> <StatusIcon :passed="checklistData?.items?.engineCabin?.windshield_wipers" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Windshield Washer</span> <StatusIcon :passed="checklistData?.items?.engineCabin?.windshield_washer" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Front Air Conditioner</span> <StatusIcon :passed="checklistData?.items?.engineCabin?.front_ac" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Rear Air Conditioner</span> <StatusIcon :passed="checklistData?.items?.engineCabin?.rear_ac" /></div>
                  <div class="flex justify-between p-1 px-2"><span>Exhaust/Vent</span> <StatusIcon :passed="checklistData?.items?.engineCabin?.exhaust_vent" /></div>
                </div>

                <div class="bg-slate-200 print-bg-slate-200 border-b-2 border-black text-center font-black p-1 text-red-600 print-text-red">RESPONSE EQUIPMENT</div>
                
                <div class="flex-1 grid grid-cols-1 text-[9px] font-bold divide-y divide-black">
                  <div class="flex justify-between items-center p-1 px-2"><span>VHF Base Radio</span> <StatusIcon :passed="checklistData?.items?.equipment?.vhf_base_radio" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Mech Vent</span> <StatusIcon :passed="checklistData?.items?.equipment?.mech_vent" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Mech Vent (Battery Level)</span> <StatusIcon :passed="checklistData?.items?.equipment?.mech_vent_battery" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Cardiac Monitor with Defib</span> <StatusIcon :passed="checklistData?.items?.equipment?.cardiac_monitor" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Cardiac Monitor (Battery Level)</span> <StatusIcon :passed="checklistData?.items?.equipment?.cardiac_monitor_battery" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Automated External Defib (AED)</span> <StatusIcon :passed="checklistData?.items?.equipment?.aed" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>AED (Battery Level)</span> <StatusIcon :passed="checklistData?.items?.equipment?.aed_battery" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Suction Machine</span> <StatusIcon :passed="checklistData?.items?.equipment?.suction_machine" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>ECG Machine</span> <StatusIcon :passed="checklistData?.items?.equipment?.ecg_machine" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>AC Inverter</span> <StatusIcon :passed="checklistData?.items?.equipment?.ac_inverter" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>O2 Regulator and Humidifier</span> <StatusIcon :passed="checklistData?.items?.equipment?.o2_regulator" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>O2 Pressure Gauge Reading</span> <StatusIcon :passed="checklistData?.items?.equipment?.o2_pressure_gauge" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Splint Adult</span> <StatusIcon :passed="checklistData?.items?.equipment?.splint_adult" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Splints Child</span> <StatusIcon :passed="checklistData?.items?.equipment?.splints_child" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Scoop Stretcher</span> <StatusIcon :passed="checklistData?.items?.equipment?.scoop_stretcher" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Spine Board</span> <StatusIcon :passed="checklistData?.items?.equipment?.spine_board" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Traction Splint</span> <StatusIcon :passed="checklistData?.items?.equipment?.traction_splint" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Kendricks Extrication Device</span> <StatusIcon :passed="checklistData?.items?.equipment?.kendricks_extrication" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>CPR Machine</span> <StatusIcon :passed="checklistData?.items?.equipment?.cpr_machine" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Seats Condition</span> <StatusIcon :passed="checklistData?.items?.equipment?.seats_condition" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Dashboard Condition</span> <StatusIcon :passed="checklistData?.items?.equipment?.dashboard_condition" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Driver Compartment Clean</span> <StatusIcon :passed="checklistData?.items?.equipment?.driver_compartment_clean" /></div>
                  <div class="flex justify-between items-center p-1 px-2"><span>Back Compartment Clean</span> <StatusIcon :passed="checklistData?.items?.equipment?.back_compartment_clean" /></div>
                </div>
              </div>
              
            </div>
          </div>

          <!-- ============================================== -->
          <!-- PAGE 2 (Damage Pins, Remarks, Signatures)      -->
          <!-- ============================================== -->
          <div class="folio-page flex flex-col">
            
            <!-- EXTERIOR DAMAGE -->
            <div class="border-2 border-black p-3 sm:p-4 flex flex-col shrink-0">
              <h3 class="text-center font-black text-red-600 print-text-red border-b-2 border-black pb-2 mb-3">EXTERIOR DAMAGE INSPECTION</h3>
              
              <!-- CRITICAL FIX: EXTERIOR DAMAGE LEGEND (Increased Font & Padding) -->
              <div class="mb-3 border border-slate-300 bg-slate-50 print-bg-slate-50 p-2 sm:p-3 rounded-sm shadow-inner">
                <p class="text-[9px] sm:text-[10px] text-slate-800 leading-snug flex flex-wrap gap-x-4 gap-y-1.5 justify-center">
                  <span><strong class="text-red-600 print-text-red font-black">WC:</strong> Windshield/Window Crack</span>
                  <span><strong class="text-red-600 print-text-red font-black">PC:</strong> Paint Chipped</span>
                  <span><strong class="text-red-600 print-text-red font-black">PF:</strong> Paint Faded</span>
                  <span><strong class="text-red-600 print-text-red font-black">D:</strong> Dented</span>
                  <span><strong class="text-red-600 print-text-red font-black">PS:</strong> Paint Scratched</span>
                  <span><strong class="text-red-600 print-text-red font-black">FB/RB:</strong> Front/Rear Bumper</span>
                  <span><strong class="text-red-600 print-text-red font-black">SML/R:</strong> Side Mirror L/R</span>
                  <span><strong class="text-red-600 print-text-red font-black">DC:</strong> Discoloration</span>
                  <span><strong class="text-red-600 print-text-red font-black">TMFR/FL/RR/RL:</strong> Tire Mug FL/FR/RR/RL</span>
                  <span><strong class="text-red-600 print-text-red font-black">HL:</strong> Headlight</span>
                  <span><strong class="text-red-600 print-text-red font-black">SLD:</strong> Slide Door</span>
                  <span><strong class="text-red-600 print-text-red font-black">SD:</strong> Side Door</span>
                  <span><strong class="text-red-600 print-text-red font-black">SL:</strong> Stop Light</span>
                  <span><strong class="text-red-600 print-text-red font-black">PL:</strong> Park Light</span>
                  <span><strong class="text-red-600 print-text-red font-black">SL:</strong> Signal Light</span>
                  <span><strong class="text-red-600 print-text-red font-black">BD:</strong> Back Door</span>
                  <span><strong class="text-red-600 print-text-red font-black">DD:</strong> Driver’s Door</span>
                  <span><strong class="text-red-600 print-text-red font-black">BL:</strong> Blinker light</span>
                  <span><strong class="text-red-600 print-text-red font-black">T:</strong> Top</span>
                </p>
              </div>

              <div class="grid grid-cols-2 gap-3 sm:gap-4 flex-1">
                <div v-for="view in ['front', 'rear', 'left', 'right']" :key="view" class="relative border border-slate-300 p-2 flex flex-col">
                  <p class="absolute top-1 left-2 text-[10px] font-black uppercase z-10 bg-white/80 px-1 rounded">{{ view }} View</p>
                  
                  <div class="relative w-full border border-slate-200 bg-slate-50 print-bg-slate-50" :class="view === 'front' || view === 'rear' ? 'aspect-[4/3] sm:aspect-square' : 'aspect-[2/1]'">
                     <img :src="`/images/ambulance/${view}-view.png`" class="absolute inset-0 w-full h-full object-contain p-2 opacity-80" />
                     
                     <div v-for="(pin, idx) in checklistData?.pins?.[view]" :key="idx" 
                          class="absolute w-4 h-4 sm:w-5 sm:h-5 bg-red-600 print-bg-red text-white text-[8px] sm:text-[9px] font-black flex items-center justify-center -translate-x-1/2 -translate-y-1/2 rounded-full border border-white" 
                          :style="{ left: pin.x + '%', top: pin.y + '%' }">
                       {{ idx + 1 }}
                     </div>
                  </div>
                  
                  <div class="mt-2 text-[9px] text-slate-800 border-t border-slate-300 pt-1 flex-1 whitespace-pre-wrap">
                    <span class="font-bold">Findings:</span><br/>
                    {{ getDamageFindings(view) || 'No damage reported.' }}
                  </div>
                </div>
              </div>
            </div>

            <!-- GENERAL REMARKS -->
            <div class="mt-4 border-2 border-black p-3 sm:p-4 shrink-0 print-block">
              <h3 class="font-black text-red-600 print-text-red border-b border-black pb-1 mb-2 uppercase text-xs sm:text-sm">General Remarks</h3>
              <p class="text-[10px] sm:text-xs font-medium whitespace-pre-wrap">{{ checklistData?.items?.meta?.Remarks && checklistData?.items?.meta?.Remarks !== 'None' ? checklistData?.items?.meta?.Remarks : 'No general remarks provided.' }}</p>
            </div>

            <!-- SIGNATURES (Anchored to the bottom) -->
            <div class="mt-auto pt-8 pb-4 grid grid-cols-2 gap-8 sm:gap-12 text-[10px] sm:text-xs font-bold text-center px-4 sm:px-10 shrink-0 print-block">
              
              <!-- Driver Signature -->
              <div class="flex flex-col items-center justify-end h-24 sm:h-28 relative">
                 <img v-if="checklistData?.checklist?.signature" :src="checklistData.checklist.signature" class="absolute bottom-8 w-full h-16 sm:h-20 object-contain mix-blend-multiply" />
                 <div class="w-full border-t-2 border-black pt-1 z-10 relative bg-white/50 backdrop-blur-[1px]">
                     <p class="uppercase text-xs sm:text-sm">{{ checklistData?.checklist?.driver_name }}</p>
                     <p class="font-medium text-[9px] sm:text-[10px] text-slate-500">Outgoing Driver</p>
                 </div>
              </div>

              <!-- Dispatcher Signature -->
              <div class="flex flex-col items-center justify-end h-24 sm:h-28 relative">
                 <template v-if="checklistData?.items?.meta?.DispatcherSignature && checklistData.items.meta.DispatcherSignature.startsWith('data:image')">
                    <img :src="checklistData.items.meta.DispatcherSignature" class="absolute bottom-8 w-full h-16 sm:h-20 object-contain mix-blend-multiply" />
                 </template>
                 <template v-else-if="checklistData?.items?.meta?.DispatcherSignature">
                    <span class="absolute bottom-8 w-full flex items-end justify-center pb-1 text-slate-800 font-bold italic">{{ checklistData.items.meta.DispatcherSignature }}</span>
                 </template>
                 <span v-else class="absolute bottom-8 w-full flex items-end justify-center pb-1 text-slate-400 italic">No Signature Found</span>

                 <div class="w-full border-t-2 border-black pt-1 z-10 relative bg-white/50 backdrop-blur-[1px]">
                     <p class="uppercase text-xs sm:text-sm">Dispatcher / Incoming Handover</p>
                     <p class="font-medium text-[9px] sm:text-[10px] text-slate-500">Authorized Receiver</p>
                 </div>
              </div>
              
            </div>

          </div>

        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useChecklistStore } from '../../stores/checklist';

const props = defineProps({
  show: Boolean,
  checklistId: Number
});

const emit = defineEmits(['close']);
const checklistStore = useChecklistStore();

const loading = ref(true);
const checklistData = ref(null);

watch(() => props.show, async (newVal) => {
    if (newVal && props.checklistId) {
        loading.value = true;
        try {
            checklistData.value = await checklistStore.fetchChecklistDetails(props.checklistId);
        } catch (e) {
            console.error(e);
        } finally {
            loading.value = false;
        }
    }
});

const getDamageFindings = (view) => {
    const findingKey = `Findings${view.charAt(0).toUpperCase() + view.slice(1)}`;
    return checklistData.value?.items?.meta?.[findingKey] || '';
};

const printReport = () => {
    window.print();
};
</script>

<script>
import { h } from 'vue';
export const StatusIcon = (props) => {
    const isPassed = props.passed == 1 || props.passed === true;
    return h('div', { class: 'flex gap-1 sm:gap-2' }, [
        h('span', { class: `w-3 h-3 sm:w-4 sm:h-4 border border-black flex items-center justify-center font-black text-[9px] sm:text-xs ${isPassed ? 'text-green-600 bg-green-50 print-text-green print-bg-green' : 'text-transparent'}` }, isPassed ? '✓' : ''),
        h('span', { class: `w-3 h-3 sm:w-4 sm:h-4 border border-black flex items-center justify-center font-black text-[9px] sm:text-xs ${!isPassed ? 'text-red-600 bg-red-50 print-text-red print-bg-red' : 'text-transparent'}` }, !isPassed ? '✗' : '')
    ]);
};
</script>

<style scoped>
.folio-page {
  width: 8.5in;
  min-height: 13in;
  height: max-content;
  background-color: white;
  margin: 0 auto;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  padding: 0.5in;
  color: black;
  flex-shrink: 0;
  position: relative;
  overflow: visible;
}

@media screen and (max-width: 9in) {
  #printable-report {
    align-items: flex-start;
  }
}
</style>

<style>
@media print {
    @page {
        size: 8.5in 13in;
        margin: 0; 
    }
    
    #app { display: none !important; }

    html, body {
        height: auto !important;
        min-height: auto !important;
        overflow: visible !important;
        position: static !important;
        background-color: white !important;
        margin: 0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .print-wrapper {
        position: static !important;
        display: block !important;
        height: auto !important;
        width: auto !important;
        background: transparent !important;
        overflow: visible !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .print-inner, #printable-report {
        display: block !important;
        position: static !important;
        height: auto !important;
        max-width: none !important;
        overflow: visible !important;
        box-shadow: none !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .folio-page {
        display: flex !important; 
        width: 8.5in !important;
        min-height: 13in !important;
        height: auto !important;
        padding: 0.4in !important; 
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
        page-break-after: always !important; 
        break-after: page !important;
        box-sizing: border-box !important;
    }

    .print-hide { display: none !important; }
    .print-show { display: block !important; }
    .print-block { display: block !important; page-break-inside: avoid; }

    .print-bg-slate-200 { background-color: #e2e8f0 !important; }
    .print-bg-slate-100 { background-color: #f1f5f9 !important; }
    .print-bg-slate-50 { background-color: #f8fafc !important; }
    .print-bg-red { background-color: #dc2626 !important; }
    .print-text-red { color: #dc2626 !important; }
    .print-bg-green { background-color: #f0fdf4 !important; }
    .print-text-green { color: #16a34a !important; }
    .print-text-teal { color: #0d9488 !important; }
}
</style>