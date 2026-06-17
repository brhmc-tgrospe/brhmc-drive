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
                <option v-for="v in vehicles" :key="v.id" :value="v.unit_id">{{ v.unit_id }} ({{ v.plate_number }})</option>
              </select>
            </div>

            <div>
              <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Inspection Type</label>
              <div class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-bold bg-slate-100 text-slate-500 cursor-not-allowed">
                Routine Maintenance Check
              </div>
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
                  <button type="button" @click="devToolunClear(form)" class="text-[9px] font-bold text-teal-600 bg-teal-50 border border-teal-100 px-2 py-1 rounded uppercase hover:bg-teal-100 transition-colors">Check All</button>
                  <button type="button" @click="devToolClear(form)" class="text-[9px] font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2 py-1 rounded uppercase hover:bg-slate-200 transition-colors">Uncheck All</button>
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
                        
                        <!-- Engine Remarks & Legend -->
                        <div class="col-span-1 sm:col-span-2 flex-1 flex flex-col pt-4 mt-2 border-t border-slate-100">
                            <h4 class="text-[10px] font-bold text-slate-500 uppercase mb-2">Engine Remarks</h4>
                            <div class="bg-blue-50 border border-blue-200 p-3 rounded-xl mb-3 shadow-inner">
                                <p class="text-[9px] font-bold text-blue-800 uppercase mb-1 tracking-widest">Legend:</p>
                                <div class="text-[9px] text-blue-900 font-medium grid grid-cols-1 sm:grid-cols-2 gap-1">
                                    <span><strong class="font-black text-red-600">NR:</strong> Need Repair</span>
                                    <span><strong class="font-black text-red-600">NRP:</strong> Need Replacement</span>
                                    <span><strong class="font-black text-red-600">C:</strong> For Condem</span>
                                    <span><strong class="font-black text-red-600">RF:</strong> For Refueling</span>
                                    <span><strong class="font-black text-red-600">AA:</strong> Additional Air</span>
                                    <span><strong class="font-black text-red-600">E:</strong> Empty &nbsp;&nbsp; <strong class="font-black text-red-600">F:</strong> Full</span>
                                </div>
                            </div>
                            <textarea v-model="form.engine_remarks" rows="2" placeholder="Engine Remarks..." class="w-full flex-1 px-4 py-3 border border-slate-200 rounded-md text-sm font-bold bg-slate-50 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all text-slate-700"></textarea>
                        </div>
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
                       
                       <div class="flex-1 flex flex-col mt-4">
                         <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Fuel Gauge Photo Evidence <span class="text-red-500">*</span></label>
                         
                         <div v-if="!form.fuel_image" class="relative w-full flex-1 min-h-[160px] border-2 border-slate-300 border-dashed rounded-xl p-6 flex flex-col items-center justify-center hover:bg-slate-50 hover:border-teal-400 transition-colors cursor-pointer group">
                           <input type="file" accept="image/*" capture="environment" @change="handleFuelImageUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                           <svg class="w-8 h-8 text-slate-300 group-hover:text-teal-500 mb-2 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                           <span class="text-xs font-bold text-slate-500 group-hover:text-teal-600 transition-colors text-center">Tap to capture or upload fuel gauge image</span>
                         </div>
                         
                         <div v-else class="relative w-full flex-1 min-h-[160px] rounded-xl overflow-hidden border-2 border-slate-200 shadow-sm bg-black">
                           <img :src="form.fuel_image" class="w-full h-full object-contain" />
                           <button @click="removeFuelImage" type="button" class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg shadow-md transition-colors" title="Remove image">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                           </button>
                         </div>
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
                                     <button @click="removeSelectedPin('right', form)" type="button" class="text-[9px] font-bold text-red-500 uppercase bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded transition-colors">Remove</button>
                                  </div>
                               </div>
                               <div class="relative w-full aspect-[2/1] bg-white rounded-lg overflow-hidden border-2 border-slate-300 border-dashed cursor-crosshair hover:bg-slate-50 transition-colors flex items-center justify-center group" @click="dropPin($event, 'right', form)">
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
                                     <button @click="removeSelectedPin('left', form)" type="button" class="text-[9px] font-bold text-red-500 uppercase bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded transition-colors">Remove</button>
                                  </div>
                               </div>
                               <div class="relative w-full aspect-[2/1] bg-white rounded-lg overflow-hidden border-2 border-slate-300 border-dashed cursor-crosshair hover:bg-slate-50 transition-colors flex items-center justify-center group" @click="dropPin($event, 'left', form)">
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
                                         <button @click="removeSelectedPin('front', form)" type="button" class="text-[9px] font-bold text-red-500 uppercase bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded transition-colors">Remove</button>
                                      </div>
                                   </div>
                                   <div class="relative w-full aspect-square bg-white rounded-lg overflow-hidden border-2 border-slate-300 border-dashed cursor-crosshair hover:bg-slate-50 transition-colors flex items-center justify-center group" @click="dropPin($event, 'front', form)">
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
                                         <button @click="removeSelectedPin('rear', form)" type="button" class="text-[9px] font-bold text-red-500 uppercase bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded transition-colors">Remove</button>
                                      </div>
                                   </div>
                                   <div class="relative w-full aspect-square bg-white rounded-lg overflow-hidden border-2 border-slate-300 border-dashed cursor-crosshair hover:bg-slate-50 transition-colors flex items-center justify-center group" @click="dropPin($event, 'rear', form)">
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
                <button v-if="hasSignature" @click="$refs.sigPad.clearSignature()" type="button" class="text-[9px] font-bold text-red-500 uppercase bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded transition-colors">Clear Signature</button>
             </div>
             <div class="border-2 border-dashed border-slate-300 rounded-lg bg-slate-50 relative h-32 touch-none group hover:border-teal-400 transition-colors">
                <SignaturePad ref="sigPad" @update:hasSignature="val => hasSignature = val" class="absolute inset-0 z-10" />
                <div v-if="!hasSignature" class="absolute inset-0 flex items-center justify-center opacity-40 select-none text-sm font-bold text-slate-400 pointer-events-none group-hover:opacity-20 transition-opacity z-0">Sign Here</div>
             </div>
          </div>
          
          <div class="pt-3 sm:pt-4 border-t border-slate-100 mt-4 sm:mt-6">
            <button type="submit" :disabled="!hasSignature || !form.fuel_image" class="w-full py-3 sm:py-3.5 bg-teal-600 text-white font-black uppercase tracking-wider text-sm rounded-lg hover:bg-teal-700 transition-colors shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
              Submit Official Inspection
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useChecklist } from '../../composables/useChecklist';
import SignaturePad from '../../components/SignaturePad.vue';
import api from '../../axios';

const props = defineProps({
  shift: { type: Object, default: null }
});

const emit = defineEmits(['close', 'saved']);

const { displayNames, pins, selectedPinToRemove, dropPin, removeSelectedPin, devToolClear, devToolunClear } = useChecklist();

// Accordion State
const openSections = ref({
    engineCabin: false,
    lights: false,
    equipment: false,
    tiresFuel: true,
    damage: false
});

const toggleSection = (section) => {
    openSections.value[section] = !openSections.value[section];
};

// Form State with Checkboxes and Exact Inputs synced
const form = ref({
    vehicle_unit: '',
    type: 'Routine Maintenance Check',
    odometer: null,
    fuel_level: 100,
    fuel_image: '',
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
    engine_remarks: '',
    signature: ''
});

const vehicles = ref([]);

onMounted(async () => {
    try {
        const res = await api.get('/api/vehicles?all=true');
        vehicles.value = res.data.data || res.data || [];
    } catch (e) {
        console.error('Failed to load vehicles', e);
    }
});

const handleFuelImageUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        const img = new Image();
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const MAX_WIDTH = 800; // Resize to 800px width for fast mobile data transfer
            let width = img.width;
            let height = img.height;

            if (width > MAX_WIDTH) {
                height = Math.round((height * MAX_WIDTH) / width);
                width = MAX_WIDTH;
            }

            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            // Compress to JPEG at 70% quality (Massively reduces Base64 string size!)
            form.value.fuel_image = canvas.toDataURL('image/jpeg', 0.7);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
};

const removeFuelImage = () => {
    form.value.fuel_image = '';
};

// INTERACTIVE PIN LOGIC

const populateVehicleData = (vehicle) => {
    if (!vehicle) return;
    if (vehicle.unit_id) form.value.vehicle_unit = vehicle.unit_id;
    if (vehicle.odometer !== undefined) form.value.odometer = vehicle.odometer;
    if (vehicle.fuel_level !== undefined) form.value.fuel_level = vehicle.fuel_level;
    
    if (vehicle.tire_psi_front_left !== undefined) form.value.tire_psi_front_left = vehicle.tire_psi_front_left;
    if (vehicle.tire_psi_front_right !== undefined) form.value.tire_psi_front_right = vehicle.tire_psi_front_right;
    if (vehicle.tire_psi_rear_left !== undefined) form.value.tire_psi_rear_left = vehicle.tire_psi_rear_left;
    if (vehicle.tire_psi_rear_right !== undefined) form.value.tire_psi_rear_right = vehicle.tire_psi_rear_right;

    // Populate previously active damage pins!
    if (vehicle.active_pins) {
        pins.value = {
            right: vehicle.active_pins.right || [],
            left: vehicle.active_pins.left || [],
            front: vehicle.active_pins.front || [],
            rear: vehicle.active_pins.rear || []
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
};

// CRITICAL FIX: PREPOPULATE FROM MASTER VEHICLE STATE
watch(() => props.shift, (newShift) => {
    if (newShift && newShift.vehicle) {
        populateVehicleData(newShift.vehicle);
    }
}, { immediate: true });

// ALSO POPULATE WHEN VEHICLE IS MANUALLY SELECTED
watch(() => form.value.vehicle_unit, (newUnitId) => {
    if (newUnitId) {
        const vehicle = vehicles.value.find(v => v.unit_id === newUnitId);
        if (vehicle) {
            populateVehicleData(vehicle);
        }
    }
});

const sigPad = ref(null);
const hasSignature = ref(false);

const submitInspection = () => {
    form.value.signature = (hasSignature.value && sigPad.value && sigPad.value.getSignatureData()) ? sigPad.value.getSignatureData() : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
    emit('saved', { ...form.value, pins: pins.value });
};
</script>