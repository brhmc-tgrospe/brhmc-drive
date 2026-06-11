<template>
  <div class="fixed inset-0 z-[60] overflow-hidden flex justify-end">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="relative w-full max-w-xl bg-white h-full shadow-2xl flex flex-col animate-slide-in-right border-l border-slate-200">
      <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
        <h3 class="text-base sm:text-lg font-bold text-slate-800">New Inspection Checklist</h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50">
          <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      
      <div class="p-4 sm:p-6 flex-1 overflow-y-auto">
        <form @submit.prevent="submitInspection" class="space-y-4 sm:space-y-5">
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <div>
              <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Vehicle Unit</label>
              <select v-model="form.vehicle_unit" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 bg-white">
                <option value="" disabled>Select Vehicle...</option>
                <option value="BRHMC-A101">BRHMC-A101 (Ambulance)</option>
                <option value="BRHMC-A102">BRHMC-A102 (Ambulance)</option>
                <option value="BRHMC-A103">BRHMC-A103 (Ambulance)</option>
                <option value="SJA-5555">SJA-5555 (Service Vehicle)</option>
              </select>
            </div>

            <div>
              <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Inspection Type</label>
              <select v-model="form.type" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 bg-white">
                <option value="Pre-Trip">Pre-Trip Inspection</option>
                <option value="Post-Trip">Post-Trip Turn Over</option>
                <option value="Routine">Routine Maintenance Check</option>
              </select>
            </div>
          </div>
          
          <div>
             <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Current Odometer (km)</label>
             <input type="number" v-model="form.odometer" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500" placeholder="e.g. 45000" />
          </div>

          <div class="border-t border-slate-100 pt-4">
            <div class="flex justify-between items-end mb-3">
               <h4 class="font-extrabold text-slate-700 text-sm">Comprehensive Checklist</h4>
               <div class="flex gap-2">
                  <button type="button" @click="devToolunClear" class="text-[9px] font-bold text-teal-600 bg-teal-50 border border-teal-100 px-2 py-1 rounded uppercase hover:bg-teal-100 transition-colors">Check All</button>
                  <button type="button" @click="devToolClear" class="text-[9px] font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2 py-1 rounded uppercase hover:bg-slate-200 transition-colors">Uncheck All</button>
               </div>
            </div>
            
            <div class="space-y-3">
                <!-- Checklist 1: Engine & Cabin -->
                <div class="border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                    <button type="button" @click="toggleSection('engineCabin')" class="w-full flex justify-between items-center p-3 bg-slate-50 hover:bg-slate-100 transition-colors">
                        <span class="font-bold text-sm text-slate-700">1. Engine & Cabin</span>
                        <svg class="w-4 h-4 text-slate-500 transition-transform" :class="{'rotate-180': openSections.engineCabin}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div v-show="openSections.engineCabin" class="p-3 bg-white grid grid-cols-1 sm:grid-cols-2 gap-2 border-t border-slate-100">
                        <label v-for="(item, key) in displayNames.engineCabin" :key="key" class="flex items-center space-x-3 cursor-pointer p-2 hover:bg-slate-50 rounded-md transition-colors border border-transparent hover:border-slate-100">
                            <input type="checkbox" v-model="form.engineCabin[key]" class="w-4 h-4 text-teal-600 rounded border-slate-300 focus:ring-teal-500">
                            <span class="text-[11px] sm:text-xs font-bold text-slate-700">{{ item }}</span>
                        </label>
                    </div>
                </div>

                <!-- Checklist 2: Lights -->
                <div class="border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                    <button type="button" @click="toggleSection('lights')" class="w-full flex justify-between items-center p-3 bg-slate-50 hover:bg-slate-100 transition-colors">
                        <span class="font-bold text-sm text-slate-700">2. Lights</span>
                        <svg class="w-4 h-4 text-slate-500 transition-transform" :class="{'rotate-180': openSections.lights}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div v-show="openSections.lights" class="p-3 bg-white grid grid-cols-1 sm:grid-cols-2 gap-2 border-t border-slate-100">
                        <label v-for="(item, key) in displayNames.lights" :key="key" class="flex items-center space-x-3 cursor-pointer p-2 hover:bg-slate-50 rounded-md transition-colors border border-transparent hover:border-slate-100">
                            <input type="checkbox" v-model="form.lights[key]" class="w-4 h-4 text-teal-600 rounded border-slate-300 focus:ring-teal-500">
                            <span class="text-[11px] sm:text-xs font-bold text-slate-700">{{ item }}</span>
                        </label>
                    </div>
                </div>

                <!-- Checklist 3: Response Equipment -->
                <div class="border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                    <button type="button" @click="toggleSection('equipment')" class="w-full flex justify-between items-center p-3 bg-slate-50 hover:bg-slate-100 transition-colors">
                        <span class="font-bold text-sm text-slate-700">3. Response Equipment</span>
                        <svg class="w-4 h-4 text-slate-500 transition-transform" :class="{'rotate-180': openSections.equipment}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div v-show="openSections.equipment" class="p-3 bg-white grid grid-cols-1 sm:grid-cols-2 gap-2 border-t border-slate-100">
                        <label v-for="(item, key) in displayNames.equipment" :key="key" class="flex items-center space-x-3 cursor-pointer p-2 hover:bg-slate-50 rounded-md transition-colors border border-transparent hover:border-slate-100">
                            <input type="checkbox" v-model="form.equipment[key]" class="w-4 h-4 text-teal-600 rounded border-slate-300 focus:ring-teal-500">
                            <span class="text-[11px] sm:text-xs font-bold" :class="key.includes('condition') || key.includes('clean') ? 'text-blue-600' : 'text-slate-700'">{{ item }}</span>
                        </label>
                    </div>
                </div>

                <!-- Checklist 4: Tires & Fuel -->
                <div class="border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                    <button type="button" @click="toggleSection('tiresFuel')" class="w-full flex justify-between items-center p-3 bg-slate-50 hover:bg-slate-100 transition-colors">
                        <span class="font-bold text-sm text-slate-700">4. Tires & Fuel</span>
                        <svg class="w-4 h-4 text-slate-500 transition-transform" :class="{'rotate-180': openSections.tiresFuel}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div v-show="openSections.tiresFuel" class="p-4 bg-white space-y-5 border-t border-slate-100">
                       <div>
                          <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Fuel Level ({{ form.fuel_level }}%)</label>
                          <input type="range" v-model="form.fuel_level" min="0" max="100" class="w-full h-2.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-teal-600">
                       </div>
                       <div class="grid grid-cols-2 gap-3 sm:gap-4 border-t border-slate-100 pt-4">
                          <div v-for="tire in ['front_left', 'front_right', 'rear_left', 'rear_right']" :key="tire">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">{{ tire.replace('_', ' ') }} (PSI)</label>
                            <input type="number" v-model="form['tire_psi_' + tire]" class="w-full px-3 py-2 border border-slate-200 rounded text-xs sm:text-sm bg-slate-50 focus:border-teal-500 outline-none" />
                          </div>
                       </div>
                    </div>
                </div>

                <!-- Checklist 5: Exterior Damage Pins -->
                <div class="border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                    <button type="button" @click="toggleSection('damage')" class="w-full flex justify-between items-center p-3 bg-slate-50 hover:bg-slate-100 transition-colors">
                        <span class="font-bold text-sm text-slate-700">5. Exterior Damage</span>
                        <svg class="w-4 h-4 text-slate-500 transition-transform" :class="{'rotate-180': openSections.damage}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div v-show="openSections.damage" class="p-3 sm:p-4 bg-white border-t border-slate-100 space-y-4">
                         <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                             <p class="text-[10px] font-bold text-blue-800 uppercase mb-2 tracking-wider">Tap image to drop a pin. Legend Guide:</p>
                             <div class="grid grid-cols-2 sm:grid-cols-4 gap-y-1 text-[9px] sm:text-[10px] text-blue-900 font-medium">
                                 <span><strong class="font-black text-red-600">WC:</strong> Window Crack</span>
                                 <span><strong class="font-black text-red-600">PF:</strong> Paint Faded</span>
                                 <span><strong class="font-black text-red-600">PC:</strong> Paint Chipped</span>
                                 <span><strong class="font-black text-red-600">PS:</strong> Paint Scratched</span>
                                 <span><strong class="font-black text-red-600">FB/RB:</strong> Bumper</span>
                                 <span><strong class="font-black text-red-600">D:</strong> Dented</span>
                                 <span><strong class="font-black text-red-600">DC:</strong> Discoloration</span>
                                 <span><strong class="font-black text-red-600">TM:</strong> Tire Mug</span>
                                 <span><strong class="font-black text-red-600">SML/R:</strong> Side Mirror</span>
                                 <span><strong class="font-black text-red-600">SD/DD/BD:</strong> Door</span>
                                 <span><strong class="font-black text-red-600">HL/SL/PL/BL:</strong> Lights</span>
                                 <span><strong class="font-black text-red-600">T:</strong> Top</span>
                             </div>
                         </div>

                         <div class="grid grid-cols-1 gap-6">
                            <!-- Right Side -->
                            <div class="flex flex-col gap-2">
                               <div class="flex justify-between items-end mb-1">
                                  <label class="block text-[10px] font-bold text-slate-700 uppercase">Right Side View</label>
                                  <div v-if="pins.right.length > 0" class="flex items-center gap-1.5">
                                     <select v-model="selectedPinToRemove.right" class="text-[9px] font-bold text-slate-600 bg-white border border-slate-200 rounded px-1 py-0.5 outline-none cursor-pointer">
                                        <option v-for="(pin, idx) in pins.right" :key="idx" :value="idx">Pin {{ idx + 1 }}</option>
                                     </select>
                                     <button @click="removeSelectedPin('right')" type="button" class="text-[9px] font-bold text-red-500 uppercase bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded transition-colors">Remove</button>
                                  </div>
                               </div>
                               <div class="relative w-full aspect-[2/1] bg-white rounded-lg overflow-hidden border-2 border-slate-300 border-dashed cursor-crosshair hover:bg-slate-50 transition-colors flex items-center justify-center group" @click="dropPin($event, 'right')">
                                  <img src="/images/ambulance/right-view.png" alt="Right View Outline" class="absolute inset-0 w-full h-full object-contain pointer-events-none p-2 opacity-80 group-hover:opacity-100 transition-opacity" />
                                  <span class="text-slate-400 font-bold uppercase tracking-widest text-xs pointer-events-none group-hover:opacity-0 transition-opacity z-10 bg-white/50 px-2 py-1 rounded">Tap to drop pin</span>
                                  <div v-for="(pin, idx) in pins.right" :key="idx" class="absolute w-5 h-5 bg-red-500 rounded-full text-white text-[10px] font-bold flex items-center justify-center -translate-x-1/2 -translate-y-1/2 shadow-lg ring-2 ring-white pointer-events-none z-20" :style="{ left: pin.x + '%', top: pin.y + '%' }">{{ idx + 1 }}</div>
                               </div>
                               <textarea v-model="form.damage_findings_right" rows="2" placeholder="Findings..." class="w-full mt-1 px-3 py-2 border border-slate-200 rounded-md text-sm bg-slate-50 outline-none focus:border-teal-500"></textarea>
                            </div>

                            <!-- Left Side -->
                            <div class="flex flex-col gap-2">
                               <div class="flex justify-between items-end mb-1">
                                  <label class="block text-[10px] font-bold text-slate-700 uppercase">Left Side View</label>
                                  <div v-if="pins.left.length > 0" class="flex items-center gap-1.5">
                                     <select v-model="selectedPinToRemove.left" class="text-[9px] font-bold text-slate-600 bg-white border border-slate-200 rounded px-1 py-0.5 outline-none cursor-pointer">
                                        <option v-for="(pin, idx) in pins.left" :key="idx" :value="idx">Pin {{ idx + 1 }}</option>
                                     </select>
                                     <button @click="removeSelectedPin('left')" type="button" class="text-[9px] font-bold text-red-500 uppercase bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded transition-colors">Remove</button>
                                  </div>
                               </div>
                               <div class="relative w-full aspect-[2/1] bg-white rounded-lg overflow-hidden border-2 border-slate-300 border-dashed cursor-crosshair hover:bg-slate-50 transition-colors flex items-center justify-center group" @click="dropPin($event, 'left')">
                                  <img src="/images/ambulance/left-view.png" alt="Left View Outline" class="absolute inset-0 w-full h-full object-contain pointer-events-none p-2 opacity-80 group-hover:opacity-100 transition-opacity" />
                                  <span class="text-slate-400 font-bold uppercase tracking-widest text-xs pointer-events-none group-hover:opacity-0 transition-opacity z-10 bg-white/50 px-2 py-1 rounded">Tap to drop pin</span>
                                  <div v-for="(pin, idx) in pins.left" :key="idx" class="absolute w-5 h-5 bg-red-500 rounded-full text-white text-[10px] font-bold flex items-center justify-center -translate-x-1/2 -translate-y-1/2 shadow-lg ring-2 ring-white pointer-events-none z-20" :style="{ left: pin.x + '%', top: pin.y + '%' }">{{ idx + 1 }}</div>
                               </div>
                               <textarea v-model="form.damage_findings_left" rows="2" placeholder="Findings..." class="w-full mt-1 px-3 py-2 border border-slate-200 rounded-md text-sm bg-slate-50 outline-none focus:border-teal-500"></textarea>
                            </div>

                            <!-- Front & Rear Container (Side by side on SM screens) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Front View -->
                                <div class="flex flex-col gap-2">
                                   <div class="flex justify-between items-end mb-1">
                                      <label class="block text-[10px] font-bold text-slate-700 uppercase">Front View</label>
                                      <div v-if="pins.front.length > 0" class="flex items-center gap-1.5">
                                         <!-- FIX: Updated P to Pin, Removed custom width constraint -->
                                         <select v-model="selectedPinToRemove.front" class="text-[9px] font-bold text-slate-600 bg-white border border-slate-200 rounded px-1 py-0.5 outline-none cursor-pointer">
                                            <option v-for="(pin, idx) in pins.front" :key="idx" :value="idx">Pin {{ idx + 1 }}</option>
                                         </select>
                                         <!-- FIX: Updated X to Remove -->
                                         <button @click="removeSelectedPin('front')" type="button" class="text-[9px] font-bold text-red-500 uppercase bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded transition-colors">Remove</button>
                                      </div>
                                   </div>
                                   <div class="relative w-full aspect-square bg-white rounded-lg overflow-hidden border-2 border-slate-300 border-dashed cursor-crosshair hover:bg-slate-50 transition-colors flex items-center justify-center group" @click="dropPin($event, 'front')">
                                      <img src="/images/ambulance/front-view.png" alt="Front View Outline" class="absolute inset-0 w-full h-full object-contain pointer-events-none p-2 opacity-80 group-hover:opacity-100 transition-opacity" />
                                      <span class="text-slate-400 font-bold uppercase tracking-widest text-[10px] pointer-events-none group-hover:opacity-0 transition-opacity z-10 bg-white/50 px-2 py-0.5 rounded">Tap</span>
                                      <div v-for="(pin, idx) in pins.front" :key="idx" class="absolute w-5 h-5 bg-red-500 rounded-full text-white text-[10px] font-bold flex items-center justify-center -translate-x-1/2 -translate-y-1/2 shadow-lg ring-2 ring-white pointer-events-none z-20" :style="{ left: pin.x + '%', top: pin.y + '%' }">{{ idx + 1 }}</div>
                                   </div>
                                   <textarea v-model="form.damage_findings_front" rows="2" placeholder="Findings..." class="w-full mt-1 px-3 py-2 border border-slate-200 rounded-md text-sm bg-slate-50 outline-none focus:border-teal-500"></textarea>
                                </div>

                                <!-- Rear View -->
                                <div class="flex flex-col gap-2">
                                   <div class="flex justify-between items-end mb-1">
                                      <label class="block text-[10px] font-bold text-slate-700 uppercase">Rear View</label>
                                      <div v-if="pins.rear.length > 0" class="flex items-center gap-1.5">
                                         <!-- FIX: Updated P to Pin, Removed custom width constraint -->
                                         <select v-model="selectedPinToRemove.rear" class="text-[9px] font-bold text-slate-600 bg-white border border-slate-200 rounded px-1 py-0.5 outline-none cursor-pointer">
                                            <option v-for="(pin, idx) in pins.rear" :key="idx" :value="idx">Pin {{ idx + 1 }}</option>
                                         </select>
                                         <!-- FIX: Updated X to Remove -->
                                         <button @click="removeSelectedPin('rear')" type="button" class="text-[9px] font-bold text-red-500 uppercase bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded transition-colors">Remove</button>
                                      </div>
                                   </div>
                                   <div class="relative w-full aspect-square bg-white rounded-lg overflow-hidden border-2 border-slate-300 border-dashed cursor-crosshair hover:bg-slate-50 transition-colors flex items-center justify-center group" @click="dropPin($event, 'rear')">
                                      <img src="/images/ambulance/rear-view.png" alt="Rear View Outline" class="absolute inset-0 w-full h-full object-contain pointer-events-none p-2 opacity-80 group-hover:opacity-100 transition-opacity" />
                                      <span class="text-slate-400 font-bold uppercase tracking-widest text-[10px] pointer-events-none group-hover:opacity-0 transition-opacity z-10 bg-white/50 px-2 py-0.5 rounded">Tap</span>
                                      <div v-for="(pin, idx) in pins.rear" :key="idx" class="absolute w-5 h-5 bg-red-500 rounded-full text-white text-[10px] font-bold flex items-center justify-center -translate-x-1/2 -translate-y-1/2 shadow-lg ring-2 ring-white pointer-events-none z-20" :style="{ left: pin.x + '%', top: pin.y + '%' }">{{ idx + 1 }}</div>
                                   </div>
                                   <textarea v-model="form.damage_findings_rear" rows="2" placeholder="Findings..." class="w-full mt-1 px-3 py-2 border border-slate-200 rounded-md text-sm bg-slate-50 outline-none focus:border-teal-500"></textarea>
                                </div>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
          </div>

          <div class="pt-2">
             <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">General Remarks</label>
             <textarea v-model="form.remarks" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 bg-slate-50" placeholder="List any general notes..."></textarea>
          </div>
          
          <div>
            <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Overall Condition Declaration</label>
            <select v-model="form.condition" required class="w-full px-3 py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 bg-white font-bold">
              <option value="Good">Vehicle is Good & Ready for Dispatch</option>
              <option value="Minor Issues">Has Minor Issues but Usable</option>
              <option value="Needs Maintenance" class="text-red-600">Needs Maintenance - Do Not Dispatch</option>
            </select>
          </div>
          
          <div class="pt-4 border-t border-slate-100">
             <div class="flex justify-between items-end mb-2">
                <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase">Inspector Signature <span class="text-red-500">*</span></label>
                <button v-if="hasSignature" @click="clearSignature" type="button" class="text-[9px] font-bold text-red-500 uppercase bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded transition-colors">Clear Signature</button>
             </div>
             <div class="border-2 border-dashed border-slate-300 rounded-lg bg-slate-50 relative h-32 touch-none group hover:border-teal-400 transition-colors">
                <canvas ref="signaturePad" class="absolute inset-0 w-full h-full cursor-crosshair"
                        @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"
                        @touchstart.prevent="startDrawing" @touchmove.prevent="draw" @touchend.prevent="stopDrawing"></canvas>
                <div v-if="!hasSignature" class="absolute inset-0 flex items-center justify-center opacity-40 select-none text-sm font-bold text-slate-400 pointer-events-none group-hover:opacity-20 transition-opacity">Sign Here</div>
             </div>
          </div>
          
          <div class="pt-3 sm:pt-4 border-t border-slate-100 mt-4 sm:mt-6">
            <button type="submit" class="w-full py-3 sm:py-3.5 bg-teal-600 text-white font-black uppercase tracking-wider text-sm rounded-lg hover:bg-teal-700 transition-colors shadow-md disabled:opacity-50">
              Submit Official Inspection
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';

const props = defineProps({
  shift: { type: Object, default: null }
});

const emit = defineEmits(['close', 'saved']);

// DISPLAY NAMES (Synced with DriverChecklistModal)
const displayNames = {
    engineCabin: { 
      battery: 'Battery', oil_level: 'Oil Level', water_coolant: 'Water Coolant', brakes: 'Brakes', 
      siren: 'Siren', horn: 'Horn', power_locks: 'Power Locks', windshield_wipers: 'Windshield Wipers', 
      front_ac: 'Front A/C', rear_ac: 'Rear A/C', exhaust_vent: 'Exhaust Vent' 
    },
    lights: { 
      dashboard: 'Dashboard', front_interior: 'Front Interior', rear_interior: 'Rear Interior', 
      headlights_high: 'Headlights (High)', headlights_low: 'Headlights (Low)', foglamps: 'Foglamps', 
      park_light: 'Park Light', tail_light: 'Tail Light', signal_front: 'Signal (Front)', signal_rear: 'Signal (Rear)', 
      hazard: 'Hazard Lights', reverse: 'Reverse Lights', brake_light: 'Brake Lights', 
      overhead_warning: 'Warning Light Bar', front_scene: 'Front Scene', rear_scene: 'Rear Scene' 
    },
    equipment: { 
      vhf_base_radio: 'VHF Base Radio', mech_vent: 'Mech Vent', mech_vent_battery: 'Mech Vent (Battery)', 
      cardiac_monitor: 'Cardiac Monitor (Defib)', cardiac_monitor_battery: 'Cardiac Monitor (Battery)', 
      aed: 'AED', aed_battery: 'AED (Battery)', suction_machine: 'Suction Machine', ecg_machine: 'ECG Machine', 
      ac_inverter: 'AC Inverter', o2_regulator: 'O2 Regulator/Humidifier', o2_pressure_gauge: 'O2 Pressure Gauge', 
      splint_adult: 'Splint Adult', splints_child: 'Splints Child', scoop_stretcher: 'Scoop Stretcher', 
      spine_board: 'Spine Board', traction_splint: 'Traction Splint', kendricks_extrication: 'Kendricks Extrication', 
      cpr_machine: 'CPR Machine', seats_condition: 'Seats Condition', dashboard_condition: 'Dashboard Condition', 
      driver_compartment_clean: 'Driver Compartment Clean', back_compartment_clean: 'Back Compartment Clean' 
    }
};

// Accordion State
const openSections = ref({
    engineCabin: true,
    lights: false,
    equipment: false,
    tiresFuel: false,
    damage: false
});

const toggleSection = (section) => {
    openSections.value[section] = !openSections.value[section];
};

// Form State with Checkboxes and Exact Inputs synced
const form = ref({
    vehicle_unit: '',
    type: 'Pre-Trip',
    odometer: null,
    fuel_level: 100,
    tire_psi_front_left: 32, tire_psi_front_right: 32, tire_psi_rear_left: 32, tire_psi_rear_right: 32,
    condition: 'Good',
    engineCabin: Object.keys(displayNames.engineCabin).reduce((acc, key) => ({ ...acc, [key]: true }), {}),
    lights: Object.keys(displayNames.lights).reduce((acc, key) => ({ ...acc, [key]: true }), {}),
    equipment: Object.keys(displayNames.equipment).reduce((acc, key) => ({ ...acc, [key]: true }), {}),
    damage_findings_right: '',
    damage_findings_left: '',
    damage_findings_front: '',
    damage_findings_rear: '',
    remarks: '',
    signature: ''
});

// INTERACTIVE PIN LOGIC
const pins = ref({ right: [], left: [], front: [], rear: [] });
const selectedPinToRemove = ref({ right: '', left: '', front: '', rear: '' });

// CRITICAL FIX: PREPOPULATE FROM MASTER VEHICLE STATE
watch(() => props.shift, (newShift) => {
    if (newShift && newShift.vehicle) {
        if (newShift.vehicle.unit_id) form.value.vehicle_unit = newShift.vehicle.unit_id;
        if (newShift.vehicle.odometer !== undefined) form.value.odometer = newShift.vehicle.odometer;
        if (newShift.vehicle.fuel_level !== undefined) form.value.fuel_level = newShift.vehicle.fuel_level;
        
        if (newShift.vehicle.tire_psi_front_left !== undefined) form.value.tire_psi_front_left = newShift.vehicle.tire_psi_front_left;
        if (newShift.vehicle.tire_psi_front_right !== undefined) form.value.tire_psi_front_right = newShift.vehicle.tire_psi_front_right;
        if (newShift.vehicle.tire_psi_rear_left !== undefined) form.value.tire_psi_rear_left = newShift.vehicle.tire_psi_rear_left;
        if (newShift.vehicle.tire_psi_rear_right !== undefined) form.value.tire_psi_rear_right = newShift.vehicle.tire_psi_rear_right;

        // Populate previously active damage pins!
        if (newShift.vehicle.active_pins) {
            pins.value = {
                right: newShift.vehicle.active_pins.right || [],
                left: newShift.vehicle.active_pins.left || [],
                front: newShift.vehicle.active_pins.front || [],
                rear: newShift.vehicle.active_pins.rear || []
            };
            
            // Re-generate the damage_findings text from the loaded pins so the textarea reflects history
            ['right', 'left', 'front', 'rear'].forEach(view => {
                let findings = '';
                pins.value[view].forEach((pin, idx) => {
                    findings += `[Pin ${idx + 1}]: ${pin.remarks || 'Damage reported.'}\n`;
                });
                form.value[`damage_findings_${view}`] = findings.trim();
            });
        }
    }
}, { immediate: true });


// Helper buttons
const devToolunClear = () => { 
    ['engineCabin', 'lights', 'equipment'].forEach(s => Object.keys(form.value[s]).forEach(k => form.value[s][k] = true)); 
};

const devToolClear = () => { 
    ['engineCabin', 'lights', 'equipment'].forEach(s => Object.keys(form.value[s]).forEach(k => form.value[s][k] = false)); 
};

const dropPin = (event, view) => {
    const rect = event.currentTarget.getBoundingClientRect();
    const x = ((event.clientX - rect.left) / rect.width) * 100;
    const y = ((event.clientY - rect.top) / rect.height) * 100;
    
    pins.value[view].push({ x, y });
    const pinNumber = pins.value[view].length;
    
    selectedPinToRemove.value[view] = pinNumber - 1;
    
    const prefix = `[Pin ${pinNumber}]: `;
    const currentText = form.value[`damage_findings_${view}`] || '';
    
    if (currentText.trim() === '') {
        form.value[`damage_findings_${view}`] = prefix;
    } else {
        form.value[`damage_findings_${view}`] = currentText + `\n${prefix}`;
    }
};

const removeSelectedPin = (view) => {
    const idx = selectedPinToRemove.value[view];
    if (idx === '' || idx === null || idx === undefined) return;
    
    const pinNumToRemove = idx + 1;
    
    pins.value[view].splice(idx, 1);
    
    let text = form.value[`damage_findings_${view}`] || '';
    let lines = text.split('\n');
    
    let startIndex = lines.findIndex(l => l.startsWith(`[Pin ${pinNumToRemove}]:`));
    if (startIndex !== -1) {
        let endIndex = startIndex + 1;
        while(endIndex < lines.length && !lines[endIndex].match(/^\[Pin \d+\]:/)) {
            endIndex++;
        }
        lines.splice(startIndex, endIndex - startIndex);
    }
    
    lines = lines.map(line => {
        const match = line.match(/^\[Pin (\d+)\]:(.*)/);
        if (match) {
            const currentNum = parseInt(match[1], 10);
            if (currentNum > pinNumToRemove) {
                return `[Pin ${currentNum - 1}]:${match[2]}`;
            }
        }
        return line;
    });
    
    form.value[`damage_findings_${view}`] = lines.join('\n').trim();
    selectedPinToRemove.value[view] = pins.value[view].length > 0 ? pins.value[view].length - 1 : '';
};

// SIGNATURE PAD LOGIC
const signaturePad = ref(null);
const isDrawing = ref(false);
const hasSignature = ref(false);

onMounted(async () => {
    await nextTick();
    if(signaturePad.value) {
        const rect = signaturePad.value.parentNode.getBoundingClientRect();
        signaturePad.value.width = rect.width;
        signaturePad.value.height = rect.height;
    }
});

const getCoords = (e) => {
  const rect = signaturePad.value.getBoundingClientRect();
  const scaleX = signaturePad.value.width / rect.width;
  const scaleY = signaturePad.value.height / rect.height;
  const clientX = (e.touches ? e.touches[0].clientX : e.clientX);
  const clientY = (e.touches ? e.touches[0].clientY : e.clientY);
  return { x: (clientX - rect.left) * scaleX, y: (clientY - rect.top) * scaleY };
};
const startDrawing = (e) => { isDrawing.value = true; const c = getCoords(e); const ctx = signaturePad.value.getContext('2d'); ctx.beginPath(); ctx.moveTo(c.x, c.y); };
const draw = (e) => { if(!isDrawing.value) return; const c = getCoords(e); const ctx = signaturePad.value.getContext('2d'); ctx.lineWidth = 3; ctx.lineCap='round'; ctx.strokeStyle='#0f172a'; ctx.lineTo(c.x, c.y); ctx.stroke(); };
const stopDrawing = () => { if(!isDrawing.value) return; isDrawing.value = false; hasSignature.value = true; form.value.signature = signaturePad.value.toDataURL('image/png'); };
const clearSignature = () => { 
    if(signaturePad.value) {
        const ctx = signaturePad.value.getContext('2d'); 
        ctx.clearRect(0,0, signaturePad.value.width, signaturePad.value.height); 
    }
    hasSignature.value = false; 
    form.value.signature = '';
};

const submitInspection = () => {
    if (!form.value.signature) {
        form.value.signature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
    }
    emit('saved', { ...form.value, pins: pins.value });
};
</script>