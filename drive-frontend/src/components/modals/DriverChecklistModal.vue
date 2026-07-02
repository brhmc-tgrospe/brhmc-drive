<template>
  <div v-if="show" class="fixed inset-0 z-[100] bg-slate-50 flex flex-col animate-fade-in-up">
    
    <!-- CRITICAL FIX: UPLOADING OVERLAY -->
    <div v-if="isSubmitting" class="absolute inset-0 z-[200] bg-slate-900/80 backdrop-blur-sm flex flex-col items-center justify-center text-white">
      <div class="relative w-20 h-20 mb-6">
         <svg class="w-full h-full animate-spin text-teal-500/20" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle></svg>
         <svg class="w-full h-full animate-spin text-teal-500 absolute inset-0" style="animation-direction: reverse; animation-duration: 2s;" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M12 2v4m0 12v4M4 12H2m20 0h-2"></path></svg>
      </div>
      <h3 class="text-xl font-black tracking-widest uppercase animate-pulse mb-2">Transmitting Data...</h3>
      <p class="text-xs text-slate-400 font-medium mb-8">Please keep this screen open</p>

      <button @click="$emit('cancel-submit')" class="px-6 py-2.5 bg-red-600/20 text-red-400 hover:bg-red-600 hover:text-white border border-red-500/50 rounded-full font-bold text-xs uppercase tracking-widest transition-colors shadow-lg active:scale-95">
        Cancel Upload
      </button>
    </div>

    <!-- Modal Header -->
    <div class="shrink-0 bg-slate-900 text-white p-5 sm:p-6 flex justify-between items-center shadow-lg z-10">
      <div>
        <h2 class="text-lg sm:text-xl font-black uppercase tracking-wide">{{ type }} Inspection</h2>
        <p class="text-teal-400 text-[10px] sm:text-xs font-mono mt-1 tracking-widest">FM-MED-HEMS-01</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="devToolunClear(inspectionForm)" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-[10px] font-bold rounded-xl uppercase border border-slate-600 transition-colors shadow-sm hidden sm:block tracking-widest">Check All</button>
        <button @click="devToolClear(inspectionForm)" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-[10px] font-bold rounded-xl uppercase border border-slate-600 transition-colors shadow-sm hidden sm:block tracking-widest">Uncheck All</button>
        <button @click="$emit('close')" class="text-slate-400 hover:text-white p-2 rounded-full transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
    </div>

    <!-- Modal Body -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 sm:px-8">
      <div class="max-w-4xl mx-auto space-y-6 sm:space-y-8">
        
        <!-- Odometer -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm">
          <label class="block text-xs font-black text-slate-800 uppercase tracking-widest mb-3">Vehicle Odometer (KM) <span class="text-red-500">*</span></label>
          <input type="number" v-model="inspectionForm.odometer" required class="w-full text-xl font-mono font-bold p-4 border-2 border-slate-200 rounded-2xl outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 bg-slate-50 transition-all" placeholder="Current Odo" />
        </div>

        <!-- Section 1: Engine & Cabin -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm">
          <h3 class="font-black text-slate-800 text-xs sm:text-sm uppercase tracking-widest border-b border-slate-100 pb-3 mb-4">1. Engine & Cabin</h3>
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
            <label v-for="(item, key) in displayNames.engineCabin" :key="key" class="flex items-center space-x-3 p-3 sm:p-4 bg-white border-2 border-slate-100 rounded-2xl cursor-pointer hover:border-teal-300 hover:bg-teal-50/50 transition-all shadow-sm group relative overflow-hidden" :class="inspectionForm.engineCabin[key] ? 'border-teal-500 bg-teal-50/30' : ''">
              <div class="relative flex items-center justify-center w-6 h-6 shrink-0">
                <input type="checkbox" v-model="inspectionForm.engineCabin[key]" class="peer appearance-none w-6 h-6 border-2 border-slate-300 rounded-md checked:bg-teal-500 checked:border-teal-500 transition-all cursor-pointer">
                <svg class="absolute w-4 h-4 text-white pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
              </div>
              <span class="text-[11px] sm:text-xs font-bold text-slate-700 group-hover:text-slate-900 transition-colors leading-tight" :class="inspectionForm.engineCabin[key] ? 'text-teal-900' : ''">{{ item }}</span>
            </label>
          </div>
        </div>

        <!-- Section 2: Lights -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm">
          <h3 class="font-black text-slate-800 text-xs sm:text-sm uppercase tracking-widest border-b border-slate-100 pb-3 mb-4">2. Lights</h3>
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
            <label v-for="(item, key) in displayNames.lights" :key="key" class="flex items-center space-x-3 p-3 sm:p-4 bg-white border-2 border-slate-100 rounded-2xl cursor-pointer hover:border-teal-300 hover:bg-teal-50/50 transition-all shadow-sm group relative overflow-hidden" :class="inspectionForm.lights[key] ? 'border-teal-500 bg-teal-50/30' : ''">
              <div class="relative flex items-center justify-center w-6 h-6 shrink-0">
                <input type="checkbox" v-model="inspectionForm.lights[key]" class="peer appearance-none w-6 h-6 border-2 border-slate-300 rounded-md checked:bg-teal-500 checked:border-teal-500 transition-all cursor-pointer">
                <svg class="absolute w-4 h-4 text-white pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
              </div>
              <span class="text-[11px] sm:text-xs font-bold text-slate-700 group-hover:text-slate-900 transition-colors leading-tight" :class="inspectionForm.lights[key] ? 'text-teal-900' : ''">{{ item }}</span>
            </label>
          </div>
        </div>

        <!-- Section 3: Response Equipment -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm">
          <h3 class="font-black text-slate-800 text-xs sm:text-sm uppercase tracking-widest border-b border-slate-100 pb-3 mb-4">3. Response Equipment</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            <label v-for="(item, key) in displayNames.equipment" :key="key" class="flex items-center space-x-3 p-3 sm:p-4 bg-white border-2 border-slate-100 rounded-2xl cursor-pointer hover:border-teal-300 hover:bg-teal-50/50 transition-all shadow-sm group relative overflow-hidden" :class="inspectionForm.equipment[key] ? 'border-teal-500 bg-teal-50/30' : ''">
              <div class="relative flex items-center justify-center w-6 h-6 shrink-0">
                <input type="checkbox" v-model="inspectionForm.equipment[key]" class="peer appearance-none w-6 h-6 border-2 border-slate-300 rounded-md checked:bg-teal-500 checked:border-teal-500 transition-all cursor-pointer">
                <svg class="absolute w-4 h-4 text-white pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
              </div>
              <span class="text-[11px] sm:text-xs font-bold transition-colors leading-tight" :class="key.includes('condition') || key.includes('clean') ? (inspectionForm.equipment[key] ? 'text-blue-800' : 'text-blue-600') : (inspectionForm.equipment[key] ? 'text-teal-900' : 'text-slate-700')">{{ item }}</span>
            </label>
          </div>
        </div>

        <!-- Section 4: Tires & Fuel & Engine Remarks -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm">
           <h3 class="font-black text-slate-800 text-xs sm:text-sm uppercase tracking-widest border-b border-slate-100 pb-3 mb-4">4. Tires & Fuel</h3>
           <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              
              <!-- FUEL GAUGE AND CAMERA -->
              <div class="flex flex-col h-full">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-4 text-center sm:text-left tracking-wider">Fuel Level ({{ inspectionForm.fuel_level }}%)</label>
                <input type="range" v-model="inspectionForm.fuel_level" min="0" max="100" class="w-full h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-teal-600 mb-6">
                
                <div class="flex-1 flex flex-col">
                  <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Fuel Gauge Photo Evidence <span class="text-red-500">*</span></label>
                  
                  <div v-if="!inspectionForm.fuel_image" class="relative w-full flex-1 min-h-[160px] border-2 border-slate-300 border-dashed rounded-xl p-6 flex flex-col items-center justify-center hover:bg-slate-50 hover:border-teal-400 transition-colors cursor-pointer group">
                    <input type="file" accept="image/*" capture="environment" @change="handleFuelImageUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                    <svg class="w-8 h-8 text-slate-300 group-hover:text-teal-500 mb-2 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="text-xs font-bold text-slate-500 group-hover:text-teal-600 transition-colors text-center">Tap to capture or upload fuel gauge image</span>
                  </div>
                  
                  <div v-else class="relative w-full flex-1 min-h-[160px] rounded-xl overflow-hidden border-2 border-slate-200 shadow-sm bg-black">
                    <img :src="inspectionForm.fuel_image" class="w-full h-full object-contain" />
                    <button @click="removeFuelImage" type="button" class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg shadow-md transition-colors" title="Remove image">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- TIRES -->
              <div class="flex flex-col">
                <div class="grid grid-cols-2 gap-3 sm:gap-4 mb-6">
                  <div v-for="tire in ['front_left', 'front_right', 'rear_left', 'rear_right']" :key="tire">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">{{ tire.replace('_', ' ') }} (PSI)</label>
                    <input type="number" v-model="inspectionForm[`tire_psi_${tire}`]" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl text-sm font-bold bg-slate-50 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all outline-none" />
                  </div>
                </div>

                <!-- Engine Remarks & Legend -->
                <div class="flex-1 flex flex-col pt-4 border-t border-slate-200">
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
                    <textarea v-model="inspectionForm.engine_remarks" rows="2" placeholder="Engine notes (e.g. Battery: NRP)" class="w-full flex-1 px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold bg-slate-50 outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-slate-700"></textarea>
                </div>
              </div>
           </div>
        </div>

        <!-- Section 5: Exterior Damage Inspection -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm">
          <h3 class="font-black text-slate-800 text-xs sm:text-sm uppercase tracking-widest border-b border-slate-100 pb-3 mb-4">5. Exterior Damage Inspection</h3>
          
          <div class="bg-blue-50 border border-blue-200 p-4 rounded-2xl mb-6 shadow-inner">
              <p class="text-[10px] font-bold text-blue-800 uppercase mb-2 tracking-widest">Tap image to drop a pin. Legend Guide:</p>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-y-1.5 text-[9px] sm:text-[10px] text-blue-900 font-medium">
                  <span><strong class="font-black text-red-600">WC:</strong> Window Crack</span>
                  <span><strong class="font-black text-red-600">PF:</strong> Paint Faded</span>
                  <span><strong class="font-black text-red-600">PC:</strong> Paint Chipped</span>
                  <span><strong class="font-black text-red-600">PS:</strong> Paint Scratched</span>
                  <span><strong class="font-black text-red-600">FB/RB:</strong> Front/Rear Bumper</span>
                  <span><strong class="font-black text-red-600">D:</strong> Dented</span>
                  <span><strong class="font-black text-red-600">DC:</strong> Discoloration</span>
                  <span><strong class="font-black text-red-600">TM:</strong> Tire Mug</span>
                  <span><strong class="font-black text-red-600">SML/R:</strong> Side Mirror L/R</span>
                  <span><strong class="font-black text-red-600">SD/DD/BD:</strong> Door</span>
                  <span><strong class="font-black text-red-600">HL/SL/PL/BL:</strong> Lights</span>
                  <span><strong class="font-black text-red-600">T:</strong> Top</span>
              </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8">
             <!-- Right Side -->
             <div class="flex flex-col gap-2">
                <div class="flex justify-between items-end mb-1">
                   <label class="block text-[10px] font-black text-slate-700 uppercase tracking-widest">Right Side View</label>
                   <div v-if="pins.right.length > 0" class="flex items-center gap-1.5">
                      <select v-model="selectedPinToRemove.right" class="text-[9px] font-bold text-slate-600 bg-white border border-slate-200 rounded px-1 py-0.5 outline-none cursor-pointer">
                         <option v-for="(pin, idx) in pins.right" :key="idx" :value="idx">Pin {{ idx + 1 }}</option>
                      </select>
                      <button @click="removeSelectedPin('right', inspectionForm)" type="button" class="text-[9px] font-bold text-red-500 uppercase tracking-widest bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded-lg transition-colors">Remove</button>
                   </div>
                </div>
                <div class="relative w-full aspect-[2/1] bg-white rounded-2xl overflow-hidden border-2 border-slate-300 border-dashed cursor-crosshair hover:bg-slate-50 transition-colors flex items-center justify-center group" @click="dropPin($event, 'right', inspectionForm)">
                   <img src="/images/ambulance/right-view.png" alt="Right View Outline" class="absolute inset-0 w-full h-full object-contain pointer-events-none p-2 opacity-80 group-hover:opacity-100 transition-opacity" />
                   <span class="text-slate-400 font-black uppercase tracking-widest text-xs pointer-events-none group-hover:opacity-0 transition-opacity z-10 bg-white/50 px-3 py-1 rounded-full backdrop-blur-sm">Tap to drop pin</span>
                   <div v-for="(pin, idx) in pins.right" :key="idx" class="absolute w-6 h-6 bg-red-500 rounded-full text-white text-[10px] font-black flex items-center justify-center -translate-x-1/2 -translate-y-1/2 shadow-[0_4px_10px_rgba(239,68,68,0.5)] ring-2 ring-white pointer-events-none z-20 transition-all transform scale-100" :style="{ left: pin.x + '%', top: pin.y + '%' }">{{ idx + 1 }}</div>
                </div>
                <textarea v-model="inspectionForm.damage_findings_right" rows="2" placeholder="Findings..." class="w-full mt-1 px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold bg-slate-50 outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-slate-700"></textarea>
             </div>

             <!-- Left Side -->
             <div class="flex flex-col gap-2">
                <div class="flex justify-between items-end mb-1">
                   <label class="block text-[10px] font-black text-slate-700 uppercase tracking-widest">Left Side View</label>
                   <div v-if="pins.left.length > 0" class="flex items-center gap-1.5">
                      <select v-model="selectedPinToRemove.left" class="text-[9px] font-bold text-slate-600 bg-white border border-slate-200 rounded px-1 py-0.5 outline-none cursor-pointer">
                         <option v-for="(pin, idx) in pins.left" :key="idx" :value="idx">Pin {{ idx + 1 }}</option>
                      </select>
                      <button @click="removeSelectedPin('left', inspectionForm)" type="button" class="text-[9px] font-bold text-red-500 uppercase tracking-widest bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded-lg transition-colors">Remove</button>
                   </div>
                </div>
                <div class="relative w-full aspect-[2/1] bg-white rounded-2xl overflow-hidden border-2 border-slate-300 border-dashed cursor-crosshair hover:bg-slate-50 transition-colors flex items-center justify-center group" @click="dropPin($event, 'left', inspectionForm)">
                   <img src="/images/ambulance/left-view.png" alt="Left View Outline" class="absolute inset-0 w-full h-full object-contain pointer-events-none p-2 opacity-80 group-hover:opacity-100 transition-opacity" />
                   <span class="text-slate-400 font-black uppercase tracking-widest text-xs pointer-events-none group-hover:opacity-0 transition-opacity z-10 bg-white/50 px-3 py-1 rounded-full backdrop-blur-sm">Tap to drop pin</span>
                   <div v-for="(pin, idx) in pins.left" :key="idx" class="absolute w-6 h-6 bg-red-500 rounded-full text-white text-[10px] font-black flex items-center justify-center -translate-x-1/2 -translate-y-1/2 shadow-[0_4px_10px_rgba(239,68,68,0.5)] ring-2 ring-white pointer-events-none z-20 transition-all transform scale-100" :style="{ left: pin.x + '%', top: pin.y + '%' }">{{ idx + 1 }}</div>
                </div>
                <textarea v-model="inspectionForm.damage_findings_left" rows="2" placeholder="Findings..." class="w-full mt-1 px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold bg-slate-50 outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-slate-700"></textarea>
             </div>

             <!-- Front View -->
             <div class="flex flex-col gap-2">
                <div class="flex justify-between items-end mb-1">
                   <label class="block text-[10px] font-black text-slate-700 uppercase tracking-widest">Front View</label>
                   <div v-if="pins.front.length > 0" class="flex items-center gap-1.5">
                      <select v-model="selectedPinToRemove.front" class="text-[9px] font-bold text-slate-600 bg-white border border-slate-200 rounded px-1 py-0.5 outline-none cursor-pointer">
                         <option v-for="(pin, idx) in pins.front" :key="idx" :value="idx">Pin {{ idx + 1 }}</option>
                      </select>
                      <button @click="removeSelectedPin('front', inspectionForm)" type="button" class="text-[9px] font-bold text-red-500 uppercase tracking-widest bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded-lg transition-colors">Remove</button>
                   </div>
                </div>
                <div class="relative w-full aspect-[4/3] sm:aspect-square bg-white rounded-2xl overflow-hidden border-2 border-slate-300 border-dashed cursor-crosshair hover:bg-slate-50 transition-colors flex items-center justify-center group" @click="dropPin($event, 'front', inspectionForm)">
                   <img src="/images/ambulance/front-view.png" alt="Front View Outline" class="absolute inset-0 w-full h-full object-contain pointer-events-none p-2 opacity-80 group-hover:opacity-100 transition-opacity" />
                   <span class="text-slate-400 font-black uppercase tracking-widest text-[10px] pointer-events-none group-hover:opacity-0 transition-opacity z-10 bg-white/50 px-3 py-1 rounded-full backdrop-blur-sm">Tap</span>
                   <div v-for="(pin, idx) in pins.front" :key="idx" class="absolute w-6 h-6 bg-red-500 rounded-full text-white text-[10px] font-black flex items-center justify-center -translate-x-1/2 -translate-y-1/2 shadow-[0_4px_10px_rgba(239,68,68,0.5)] ring-2 ring-white pointer-events-none z-20 transition-all transform scale-100" :style="{ left: pin.x + '%', top: pin.y + '%' }">{{ idx + 1 }}</div>
                </div>
                <textarea v-model="inspectionForm.damage_findings_front" rows="2" placeholder="Findings..." class="w-full mt-1 px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold bg-slate-50 outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-slate-700"></textarea>
             </div>

             <!-- Rear View -->
             <div class="flex flex-col gap-2">
                <div class="flex justify-between items-end mb-1">
                   <label class="block text-[10px] font-black text-slate-700 uppercase tracking-widest">Rear View</label>
                   <div v-if="pins.rear.length > 0" class="flex items-center gap-1.5">
                      <select v-model="selectedPinToRemove.rear" class="text-[9px] font-bold text-slate-600 bg-white border border-slate-200 rounded px-1 py-0.5 outline-none cursor-pointer">
                         <option v-for="(pin, idx) in pins.rear" :key="idx" :value="idx">Pin {{ idx + 1 }}</option>
                      </select>
                      <button @click="removeSelectedPin('rear', inspectionForm)" type="button" class="text-[9px] font-bold text-red-500 uppercase tracking-widest bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded-lg transition-colors">Remove</button>
                   </div>
                </div>
                <div class="relative w-full aspect-[4/3] sm:aspect-square bg-white rounded-2xl overflow-hidden border-2 border-slate-300 border-dashed cursor-crosshair hover:bg-slate-50 transition-colors flex items-center justify-center group" @click="dropPin($event, 'rear', inspectionForm)">
                   <img src="/images/ambulance/rear-view.png" alt="Rear View Outline" class="absolute inset-0 w-full h-full object-contain pointer-events-none p-2 opacity-80 group-hover:opacity-100 transition-opacity" />
                   <span class="text-slate-400 font-black uppercase tracking-widest text-[10px] pointer-events-none group-hover:opacity-0 transition-opacity z-10 bg-white/50 px-3 py-1 rounded-full backdrop-blur-sm">Tap</span>
                   <div v-for="(pin, idx) in pins.rear" :key="idx" class="absolute w-6 h-6 bg-red-500 rounded-full text-white text-[10px] font-black flex items-center justify-center -translate-x-1/2 -translate-y-1/2 shadow-[0_4px_10px_rgba(239,68,68,0.5)] ring-2 ring-white pointer-events-none z-20 transition-all transform scale-100" :style="{ left: pin.x + '%', top: pin.y + '%' }">{{ idx + 1 }}</div>
                </div>
                <textarea v-model="inspectionForm.damage_findings_rear" rows="2" placeholder="Findings..." class="w-full mt-1 px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold bg-slate-50 outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-slate-700"></textarea>
             </div>

          </div>
          
          <div class="mt-8">
            <label class="block text-[10px] font-black text-slate-700 uppercase tracking-widest mb-2">General Remarks</label>
            <textarea v-model="inspectionForm.remarks" rows="3" placeholder="Any additional notes..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold bg-slate-50 outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-slate-700"></textarea>
          </div>
          
          <div v-if="type === 'Pre-Trip'" class="mt-6 border-t border-slate-100 pt-6">
            <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-widest mb-2">Driver Condition Declaration <span class="text-red-500">*</span></label>
            <select v-model="inspectionForm.driver_condition" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold bg-white outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-slate-700 cursor-pointer">
              <option value="" disabled>Select Driver Condition...</option>
              <option value="Fit to Drive">Physically Fit & Well Rested</option>
              <option value="Fatigued">Fatigued / Not Feeling Well</option>
            </select>
          </div>
          
          <div class="mt-6 border-t border-slate-100 pt-6">
            <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-widest mb-2">Overall Condition Declaration <span class="text-red-500">*</span></label>
            <select v-model="inspectionForm.condition" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold bg-white outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-slate-700 cursor-pointer">
              <option value="" disabled>Select Condition...</option>
              <option value="Good">Vehicle is Good & Ready for Dispatch</option>
              <option value="Minor Issues">Has Minor Issues but Usable</option>
              <option value="Needs Maintenance" class="text-red-600 font-bold">Needs Maintenance - Do Not Dispatch</option>
            </select>
          </div>
        </div>

        <!-- SIGNATURE -->
         <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-md">
            <div class="flex justify-between mb-3">
               <label class="text-xs font-black text-slate-800 uppercase tracking-widest">{{ type === 'Pre-Trip' ? 'Handwritten Signature' : 'Incoming Driver Signature' }}</label>
               <button @click="$refs.sigPad.clearSignature()" class="text-[10px] text-red-500 hover:text-red-700 font-black uppercase tracking-widest transition-colors bg-red-50 px-2 py-1 rounded-lg">Clear</button>
            </div>
            <div class="border-2 border-dashed border-slate-300 hover:border-teal-400 transition-colors rounded-2xl bg-slate-50 relative h-48 sm:h-56 touch-none group overflow-hidden">
               <SignaturePad ref="sigPad" :show="show" @update:hasSignature="val => hasSignature = val" class="absolute inset-0 z-10" />
               <div v-if="!hasSignature" class="absolute inset-0 flex items-center justify-center opacity-30 select-none text-xl sm:text-2xl font-black uppercase tracking-[0.3em] pointer-events-none group-hover:opacity-20 transition-opacity z-0">Sign Here</div>
            </div>
         </div>
      </div>
    </div>

    <!-- Modal Footer -->
    <div class="shrink-0 bg-white border-t border-slate-200 shadow-[0_-10px_30px_-5px_rgba(0,0,0,0.05)] p-5 sm:p-6 z-20">
       <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
         <div class="flex gap-4 block sm:hidden">
           <button @click="devToolunClear(inspectionForm)" class="text-[10px] font-bold text-slate-400 hover:text-teal-600 uppercase tracking-widest">Check All</button>
           <button @click="devToolClear(inspectionForm)" class="text-[10px] font-bold text-slate-400 hover:text-red-600 uppercase tracking-widest">Uncheck All</button>
         </div>
         
         <div class="flex items-center justify-center space-x-3 w-full sm:w-auto bg-slate-50 sm:bg-transparent p-3 sm:p-0 rounded-xl sm:rounded-none">
            <span v-if="isUnfit" class="text-sm sm:text-base font-black uppercase text-red-600 tracking-wide flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
              Marked as Unfit
            </span>
            <span v-else class="text-sm sm:text-base font-black uppercase text-teal-600 tracking-wide flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              Vehicle is Fit for Use
            </span>
         </div>
         
         <button @click="handleSubmit" :disabled="!isChecklistValid" class="w-full sm:w-auto px-8 py-4 rounded-2xl font-black uppercase tracking-wider shadow-lg transition-all flex items-center justify-center gap-3 disabled:opacity-50" :class="isChecklistValid ? (isUnfit ? 'bg-red-600 text-white hover:bg-red-700 shadow-red-500/30' : 'bg-teal-600 text-white hover:bg-teal-700 shadow-teal-500/30') : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
            {{ isChecklistValid ? (isUnfit ? 'Submit & Ground' : 'Submit Official Log') : 'Complete All Checks & Sign' }}
         </button>
       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import api from '../../axios'; 
import { useChecklist } from '../../composables/useChecklist';
import SignaturePad from '../SignaturePad.vue';

const props = defineProps({
  show: Boolean,
  type: { type: String, default: 'Pre-Trip' },
  shift: Object,
  isSubmitting: { type: Boolean, default: false } // CRITICAL FIX: Accepts loading state from parent!
});

const emit = defineEmits(['close', 'submit', 'cancel-submit']); // CRITICAL FIX: Added cancel emit

const { displayNames, pins, selectedPinToRemove, dropPin, removeSelectedPin, devToolClear, devToolunClear } = useChecklist();

const inspectionForm = ref({
    odometer: '',
    fuel_level: 100,
    fuel_image: '', 
    tire_psi_front_left: 32, tire_psi_front_right: 32, tire_psi_rear_left: 32, tire_psi_rear_right: 32,
    driver_condition: '', 
    condition: '', 
    damage_findings_right: '',
    damage_findings_left: '',
    damage_findings_front: '',
    damage_findings_rear: '',
    remarks: '',
    engine_remarks: '', 
    signature: '',
    engineCabin: { battery: true, oil_level: true, water_coolant: true, washer_fluid: true, brakes: true, siren: true, horn: true, power_locks: true, windshield_wipers: true, windshield_washer: true, front_ac: true, rear_ac: true, exhaust_vent: true },
    lights: { dashboard: true, front_interior: true, rear_interior: true, headlights_high: true, headlights_low: true, foglamps: true, park_light: true, tail_light: true, signal_front: true, signal_rear: true, hazard: true, reverse: true, brake_light: true, overhead_warning: true, front_scene: true, rear_scene: true },
    equipment: { vhf_base_radio: true, mech_vent: true, mech_vent_battery: true, cardiac_monitor: true, cardiac_monitor_battery: true, aed: true, aed_battery: true, suction_machine: true, ecg_machine: true, ac_inverter: true, o2_regulator: true, o2_pressure_gauge: true, splint_adult: true, splints_child: true, scoop_stretcher: true, spine_board: true, traction_splint: true, kendricks_extrication: true, cpr_machine: true, seats_condition: true, dashboard_condition: true, driver_compartment_clean: true, back_compartment_clean: true }
});

// CRITICAL FIX: Image Compression applied to Driver Form!
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
            inspectionForm.value.fuel_image = canvas.toDataURL('image/jpeg', 0.7);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
};

const removeFuelImage = () => {
    inspectionForm.value.fuel_image = '';
};

watch(() => props.show, async (newVal) => {
    if (newVal) {
        await nextTick(); 
        
        if (props.shift && props.shift.vehicle) {
            const v = props.shift.vehicle;
            if (v.odometer !== undefined && v.odometer !== null) inspectionForm.value.odometer = v.odometer;
            if (v.fuel_level !== undefined && v.fuel_level !== null) inspectionForm.value.fuel_level = v.fuel_level;
            
            if (v.tire_psi_front_left !== undefined) inspectionForm.value.tire_psi_front_left = v.tire_psi_front_left;
            if (v.tire_psi_front_right !== undefined) inspectionForm.value.tire_psi_front_right = v.tire_psi_front_right;
            if (v.tire_psi_rear_left !== undefined) inspectionForm.value.tire_psi_rear_left = v.tire_psi_rear_left;
            if (v.tire_psi_rear_right !== undefined) inspectionForm.value.tire_psi_rear_right = v.tire_psi_rear_right;

            if (v.active_pins) {
                pins.value = {
                    right: Array.isArray(v.active_pins.right) ? JSON.parse(JSON.stringify(v.active_pins.right)) : [],
                    left: Array.isArray(v.active_pins.left) ? JSON.parse(JSON.stringify(v.active_pins.left)) : [],
                    front: Array.isArray(v.active_pins.front) ? JSON.parse(JSON.stringify(v.active_pins.front)) : [],
                    rear: Array.isArray(v.active_pins.rear) ? JSON.parse(JSON.stringify(v.active_pins.rear)) : []
                };
                
                ['right', 'left', 'front', 'rear'].forEach(view => {
                    let findings = '';
                    if (pins.value[view].length > 0) {
                        pins.value[view].forEach((pin, idx) => {
                            findings += `[Pin ${idx + 1}]: ${pin.remarks || 'Damage reported.'}\n`;
                        });
                    }
                    inspectionForm.value[`damage_findings_${view}`] = findings.trim();
                });
            }
        }

        if (props.type === 'Post-Trip' && props.shift?.trip?.id) {
            try {
                const response = await api.get(`/api/checklists`, {
                    params: { trip_id: props.shift.trip.id, type: 'PRE_TRIP', per_page: 1 }
                });
                const preTrip = response.data.data?.[0];
                if (preTrip) {
                    inspectionForm.value.remarks = preTrip.remarks && preTrip.remarks !== 'No issues reported.' ? preTrip.remarks : '';
                    inspectionForm.value.engine_remarks = preTrip.engine_remarks && preTrip.engine_remarks !== 'None' ? preTrip.engine_remarks : '';
                }
            } catch (e) {}
        }
    } else {
        if(sigPad.value) sigPad.value.clearSignature();
        inspectionForm.value.fuel_image = '';
        inspectionForm.value.engine_remarks = '';
        if (props.type === 'Post-Trip') {
            inspectionForm.value.driver_condition = '';
            inspectionForm.value.condition = '';
        }
    }
});

const sigPad = ref(null);
const hasSignature = ref(false);

const isUnfit = computed(() => {
    return inspectionForm.value.condition === 'Needs Maintenance' || 
           (props.type === 'Pre-Trip' && inspectionForm.value.driver_condition === 'Fatigued');
});

const isChecklistValid = computed(() => {
    const hasOdo = inspectionForm.value.odometer !== '' && inspectionForm.value.odometer !== null;
    const hasCondition = inspectionForm.value.condition !== '';
    const hasDriverCond = props.type === 'Pre-Trip' ? inspectionForm.value.driver_condition !== '' : true;
    const hasFuelImg = inspectionForm.value.fuel_image !== ''; 
    
    return hasOdo && hasCondition && hasDriverCond && hasFuelImg && hasSignature.value;
});

const handleSubmit = () => {
    if (!hasSignature.value || !sigPad.value) return;
    inspectionForm.value.signature = sigPad.value.getSignatureData();
    
    emit('submit', {
        ...inspectionForm.value,
        pins: pins.value 
    });
};
</script>