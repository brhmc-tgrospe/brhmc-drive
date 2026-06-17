<template>
  <div class="w-full h-full flex flex-col gap-4 sm:gap-6 relative">
    
    <div class="mb-2 flex justify-between items-start sm:items-center shrink-0">
      <div>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight text-center sm:text-left drop-shadow-sm">Driver Dashboard</h2>
        <p class="text-xs sm:text-sm text-slate-500 font-bold mt-1 text-center sm:text-left tracking-wide">
          <span v-if="!isTripActive" class="uppercase text-indigo-500">Assigned Dispatch Console</span>
          <span v-else class="uppercase text-teal-600 flex items-center justify-center sm:justify-start gap-1.5">
             <span class="flex h-2 w-2 relative"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span></span>
             Active Trip Execution
          </span>
        </p>
      </div>
      
      <button v-if="isTripActive && !isShiftComplete" @click="isIssueModalOpen = true" title="Report Emergency" class="p-2 sm:p-2.5 text-red-600 bg-red-50 border border-red-200 shadow-[0_4px_15px_-3px_rgba(239,68,68,0.3)] rounded-full hover:bg-red-100 transition-all flex items-center justify-center group active:scale-95">
        <svg class="w-4.5 h-4.5 animate-[ping_1s_cubic-bezier(0.4,0,0.6,1)_infinite] group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
           <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>

    <!-- ========================================================== -->
    <!-- CRITICAL FIX: EMERGENCY LOCKDOWN OVERLAY -->
    <!-- Uses both local optimistic state AND database state to guarantee lockdown! -->
    <!-- ========================================================== -->
    <div v-if="localShift?.active_emergency || hardEmergencyLock" class="absolute inset-0 z-20 bg-slate-900/90 backdrop-blur-md rounded-3xl flex flex-col items-center justify-center p-6 text-center animate-fade-in-up border border-slate-700 shadow-2xl">
      <div class="w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center mb-6 animate-pulse border border-red-500/50 shadow-[0_0_30px_rgba(239,68,68,0.5)]">
        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
      </div>
      <h2 class="text-2xl sm:text-3xl font-black text-white tracking-wide uppercase mb-2">Emergency Reported</h2>
      <p class="text-red-400 font-bold uppercase tracking-widest text-sm mb-6">{{ localShift?.active_emergency?.issue_type || hardEmergencyLock?.issue_type }}</p>
      <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 max-w-sm w-full">
         <p class="text-slate-300 text-sm leading-relaxed mb-3">Your incident report has been securely transmitted to the Action Center.</p>
         <div class="flex items-center justify-center gap-2 text-amber-500 font-bold text-xs uppercase tracking-widest bg-amber-500/10 py-2 px-4 rounded-lg">
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Awaiting Dispatcher Acknowledgment...
         </div>
      </div>
    </div>

    <!-- REFRESHING OVERLAY -->
    <div v-if="isReloading" class="absolute inset-0 bg-white/90 backdrop-blur-xl border border-slate-200/60 rounded-3xl z-[50] flex flex-col items-center justify-center min-h-[400px]">
       <div class="relative w-16 h-16 mb-4">
           <svg class="w-full h-full animate-spin text-teal-500/20" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle></svg>
           <svg class="w-full h-full animate-spin text-teal-500 absolute inset-0" style="animation-direction: reverse; animation-duration: 2s;" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M12 2v4m0 12v4M4 12H2m20 0h-2"></path></svg>
       </div>
       <span class="font-black text-sm tracking-[0.2em] uppercase text-slate-500">Updating Sync State...</span>
    </div>

    <!-- STATE 1: PRE-TRIP -->
    <div v-else-if="!isTripActive" class="bg-white/90 backdrop-blur-xl border border-slate-200/60 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 sm:p-10 flex flex-col items-center transition-all min-h-[400px] justify-center relative overflow-hidden">
      <div class="absolute -top-32 -right-32 w-72 h-72 bg-teal-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
      <div class="absolute -bottom-32 -left-32 w-72 h-72 bg-indigo-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

      <div v-if="loading" class="text-slate-400 py-12 flex flex-col items-center relative z-10">
        <div class="relative w-16 h-16 mb-4">
           <svg class="w-full h-full animate-spin text-teal-500/20" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle></svg>
           <svg class="w-full h-full animate-spin text-teal-500 absolute inset-0" style="animation-direction: reverse; animation-duration: 2s;" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M12 2v4m0 12v4M4 12H2m20 0h-2"></path></svg>
        </div>
        <span class="font-black text-sm tracking-[0.2em] uppercase text-slate-500">Syncing Dispatch...</span>
      </div>

      <div v-else-if="!localShift" class="py-12 flex flex-col items-center text-center relative z-10">
        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mb-6 border-4 border-dashed border-slate-200 shadow-sm relative">
           <div class="absolute inset-0 bg-slate-50 rounded-full animate-pulse opacity-50"></div>
           <svg class="w-10 h-10 text-slate-300 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 12H4m16 0l-4-4m4 4l-4 4"></path></svg>
        </div>
        <h3 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">No Active Dispatch</h3>
        <p class="text-sm mt-3 text-slate-500 max-w-sm leading-relaxed font-medium">You are currently unassigned. Relax, grab a coffee, and wait for the dispatcher to allocate your next vehicle.</p>
      </div>

      <div v-else class="w-full max-w-lg animate-fade-in-up text-center relative z-10">
        <div class="bg-white rounded-2xl p-6 mb-8 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.06)] relative overflow-hidden group flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
           <div class="absolute inset-0 bg-gradient-to-br from-teal-500/5 to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="w-24 h-24 sm:w-32 sm:h-24 bg-slate-100 rounded-2xl shrink-0 flex items-center justify-center border-4 border-white shadow-md relative overflow-hidden">
              <img v-if="localShift?.vehicle?.image_path" :src="localShift.vehicle.image_path.startsWith('data:image') ? localShift.vehicle.image_path : `http://localhost:8000/storage/${localShift.vehicle.image_path}`" alt="Assigned Vehicle" class="w-full h-full object-cover" />
              <svg v-else class="w-12 h-12 text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M19 17h2c.6 0 1 -.4 1 -1v-3c0 -.9 -.7 -1.7 -1.5 -1.9C18.7 10.6 16 10 16 10s-1.3 -1.4 -2.2 -2.3c-.5 -.4 -1.1 -.7 -1.8 -.7H5c-1.1 0 -2 .9 -2 2v8c0 .6 .4 1 1 1h2m13 0a2 2 0 1 1 -4 0m4 0a2 2 0 1 0 -4 0m-9 0h4m-4 0a2 2 0 1 1 -4 0m4 0a2 2 0 1 0 -4 0"></path></svg>
            </div>
           <div class="flex-1 relative z-10">
              <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Vehicle Assigned</h3>
              <div class="text-3xl font-black text-slate-800 tracking-tighter drop-shadow-sm">{{ localShift?.vehicle?.unit_id || 'UNKNOWN' }}</div>
              <p class="text-xs font-bold text-teal-600 uppercase tracking-widest mt-0.5">{{ localShift?.vehicle?.plate_number || 'N/A' }} • {{ localShift?.vehicle?.vehicle_type || 'Vehicle' }}</p>
           </div>
        </div>

        <div class="bg-slate-50 rounded-xl p-5 mb-8 border border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-3 shadow-inner relative z-10">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Dispatch Window</span>
            <span class="text-sm font-black text-indigo-700 bg-indigo-50 px-4 py-2 rounded-lg border border-indigo-100/50 shadow-sm">
               {{ formatTimeForDisplay(localShift?.start_time) }} - 
               {{ formatTimeForDisplay(localShift?.end_time) }}
            </span>
        </div>

        <div class="flex flex-col items-center relative z-10">
           <button v-if="!isReady" disabled class="w-full py-4 bg-slate-100 text-slate-400 font-bold rounded-2xl border-2 border-slate-200/50 cursor-not-allowed uppercase tracking-wider text-sm flex items-center justify-center gap-2">
              <svg class="w-5 h-5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              Inspection unlocks in {{ unlockTime }}
           </button>
           
           <button v-else @click="advancePhase" :disabled="isAdvancingPhase" class="w-full py-4.5 bg-gradient-to-r from-teal-500 to-emerald-500 text-white font-black text-lg uppercase tracking-wider rounded-2xl shadow-[0_10px_25px_-5px_rgba(20,184,166,0.4)] hover:shadow-[0_15px_35px_-5px_rgba(20,184,166,0.5)] hover:from-teal-400 hover:to-emerald-400 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2 group disabled:opacity-75 disabled:cursor-not-allowed">
              <svg v-if="isAdvancingPhase" class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              <span v-else>Accept Dispatch & Start</span>
              <svg v-if="!isAdvancingPhase" class="w-6 h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
           </button>
        </div>
      </div>
    </div>

    <!-- STATE 2: ACTIVE TRIP ENGINE -->
    <div v-if="isTripActive && !isReloading" class="w-full flex flex-col gap-4 sm:gap-6 flex-1 animate-fade-in-up">
      
      <!-- Top Status Card -->
      <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-3xl p-6 sm:p-8 shadow-2xl text-white border-b-4 border-teal-500 relative overflow-hidden shrink-0">
         <div class="absolute -right-20 -top-20 w-64 h-64 bg-gradient-to-br from-teal-500/20 to-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
         <div class="absolute left-0 bottom-0 w-full h-1/2 bg-gradient-to-t from-slate-900 to-transparent pointer-events-none"></div>
         
         <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end relative z-10 gap-6">
            <div>
               <div class="flex items-center gap-3 mb-2">
                 <p class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-[0.2em]">Active Unit</p>
                 <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-white/10 text-white border border-white/20 uppercase tracking-widest backdrop-blur-sm shadow-sm">{{ localShift?.vehicle?.plate_number }}</span>
               </div>
               <h2 class="text-4xl sm:text-5xl font-black tracking-tighter drop-shadow-md bg-clip-text text-transparent bg-gradient-to-r from-white to-slate-300">{{ localShift?.vehicle?.unit_id }}</h2>
            </div>
            
            <div class="flex flex-col items-end gap-2 w-full sm:w-auto">
               <div v-if="isGpsActive" class="flex items-center gap-1.5 bg-green-500/10 border border-green-500/20 px-2 py-1 rounded text-green-400 text-[9px] uppercase font-black tracking-widest backdrop-blur-sm shadow-sm">
                  <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span></span>
                  GPS Connected
               </div>
               <div v-else class="flex items-center gap-1.5 bg-red-500/10 border border-red-500/20 px-2 py-1 rounded text-red-400 text-[9px] uppercase font-black tracking-widest backdrop-blur-sm shadow-sm">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                  GPS Offline
               </div>

               <div class="text-left sm:text-right bg-white/5 p-4 rounded-2xl border border-white/10 backdrop-blur-md shadow-inner w-full sm:w-auto">
                  <p class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-1.5">Current Trip Phase</p>
                  <div class="inline-flex items-center gap-2.5">
                     <span v-if="!isShiftComplete" class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-teal-500 shadow-[0_0_10px_rgba(20,184,166,0.8)]"></span>
                     </span>
                     <span class="text-sm sm:text-base font-black uppercase tracking-widest text-teal-50 drop-shadow-sm">{{ currentPhaseData.title }}</span>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Action Area -->
      <div class="bg-white border border-slate-200/80 rounded-3xl shadow-lg p-6 sm:p-10 flex-1 flex flex-col justify-center min-h-[350px] relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 24px 24px;"></div>
        
        <div class="p-6 sm:p-8 text-center relative z-10 border-b border-slate-100 mb-6">
            <div class="flex items-center justify-center gap-2 mb-2">
              <span v-if="!isShiftComplete" class="flex h-2.5 w-2.5 relative">
                 <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                 <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-teal-500 shadow-[0_0_10px_rgba(20,184,166,0.8)]"></span>
              </span>
              <span class="text-teal-600 font-black text-[10px] sm:text-xs tracking-[0.2em] uppercase">Live Execution Tracker</span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-black tracking-tight uppercase drop-shadow-sm" :class="currentPhaseData.textColor">{{ currentPhaseData.title }}</h2>
        </div>

        <div class="p-5 sm:p-8 bg-slate-50/50 rounded-3xl flex flex-col items-center relative z-10 border border-slate-100 shadow-inner">
          <div class="w-full text-center">
              <button v-if="currentPhaseIndex === 1 && !isDispatcherCleared" disabled class="w-full max-w-xl mx-auto py-5 sm:py-6 rounded-2xl font-black text-lg sm:text-xl uppercase tracking-wider transition-all duration-300 shadow-xl flex items-center justify-center cursor-not-allowed bg-slate-800 text-slate-500 border border-slate-700">
                 <span class="flex items-center gap-3">
                     <svg class="animate-spin h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                     <span class="truncate">Awaiting Clearance...</span>
                 </span>
              </button>
              
              <button v-else-if="currentPhaseIndex === 7 && isPostTripInspectionCompleted" disabled class="w-full max-w-xl mx-auto py-5 sm:py-6 rounded-2xl font-black text-lg sm:text-xl uppercase tracking-wider transition-all duration-300 shadow-xl flex items-center justify-center cursor-not-allowed bg-slate-800 text-slate-500 border border-slate-700 px-4">
                 <span class="flex items-center gap-3 w-full justify-center">
                     <svg class="animate-spin h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                     <span class="truncate">Awaiting Final Dispatcher Approval...</span>
                 </span>
              </button>
              
              <button v-else @click="advancePhase" :disabled="isAdvancingPhase" class="w-full max-w-xl mx-auto py-5 sm:py-6 rounded-2xl font-black text-lg sm:text-xl uppercase tracking-wider transition-all duration-300 transform active:scale-95 shadow-xl flex items-center justify-center disabled:opacity-75 disabled:cursor-not-allowed" :class="currentPhaseData.buttonClass">
                 <span class="flex items-center gap-3">
                     <svg v-if="isAdvancingPhase" class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                     <template v-else>
                         {{ currentPhaseData.buttonText }}
                         <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                     </template>
                 </span>
              </button>
          </div>
        </div>
      </div>

      <!-- Compact Phase Indicator -->
      <div class="bg-white border border-slate-200/80 rounded-3xl shadow-sm p-4 sm:p-6 flex items-center justify-between transition-all overflow-hidden">
        <div class="flex items-center gap-4 sm:gap-5 w-full min-w-0">
          <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 text-blue-600 shadow-inner shrink-0 relative overflow-hidden group">
             <div class="absolute inset-0 bg-blue-500/10 animate-pulse"></div>
             <span class="text-xl font-black relative z-10">{{ currentPhaseIndex + 1 }}</span>
          </div>
          <div class="flex-1 min-w-0 pr-4">
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Current Phase</span>
            <h4 class="text-base sm:text-xl font-black text-slate-800 uppercase leading-none tracking-tight truncate">{{ currentPhaseData.title }}</h4>
          </div>
          
          <div class="hidden sm:flex items-center gap-5 border-l border-slate-100 pl-6 shrink-0">
             <div class="text-right min-w-[120px]">
               <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Up Next</span>
               <h4 class="text-sm font-black text-slate-600 uppercase tracking-wide truncate max-w-[150px]">{{ currentPhaseData.buttonText }}</h4>
             </div>
             
             <div class="shrink-0 flex items-center">
                <button v-if="currentPhaseIndex === 1 && !isDispatcherCleared" disabled class="px-3 py-1.5 bg-slate-100 border border-slate-200 text-slate-400 text-[10px] font-bold uppercase rounded-lg flex items-center gap-2 cursor-not-allowed">
                   <svg class="animate-spin h-3 w-3 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                   <span class="hidden lg:inline">Awaiting</span>
                </button>
                <button v-else-if="currentPhaseIndex === 7 && isPostTripInspectionCompleted" disabled class="px-3 py-1.5 bg-slate-100 border border-slate-200 text-slate-400 text-[10px] font-bold uppercase rounded-lg flex items-center gap-2 cursor-not-allowed">
                   <svg class="animate-spin h-3 w-3 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                   <span class="hidden lg:inline">Awaiting</span>
                </button>
                
                <button v-else @click="advancePhase" :disabled="isAdvancingPhase" class="px-4 py-2 text-xs font-bold uppercase rounded-lg transition-colors shadow-sm whitespace-nowrap disabled:opacity-75 disabled:cursor-not-allowed" :class="currentPhaseData.buttonClass">
                   <span v-if="isAdvancingPhase">Loading...</span>
                   <span v-else>{{ currentPhaseData.buttonText }}</span>
                </button>
             </div>
          </div>
        </div>
      </div>
    </div>

    <DriverChecklistModal 
      :show="showChecklistModal"
      :type="checklistType"
      :shift="localShift"
      :isSubmitting="isSubmittingChecklist"
      @close="showChecklistModal = false"
      @submit="submitChecklist"
      @cancel-submit="cancelChecklistSubmit"
    />
    
    <ChecklistIssue 
      v-if="isIssueModalOpen" 
      @close="isIssueModalOpen = false" 
      @reported="handleIssueReported" 
      :vehicleUnit="localShift?.vehicle?.unit_id"
      :isDispatcher="false" 
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import ChecklistIssue from '../../views/checklists/ChecklistIssue.vue';
import DriverChecklistModal from '../modals/DriverChecklistModal.vue';
import { useToastStore } from '../../stores/toast';
import api from '../../axios'; 

const props = defineProps({
  shift: Object,
  loading: Boolean,
  unlockTime: String,
  isReady: Boolean 
});

const emit = defineEmits(['refresh']);
const toastStore = useToastStore();
const localShift = ref(props.shift);

const formatTimeForDisplay = (dateString) => {
    if (!dateString) return '';
    const safeString = dateString.replace(' ', 'T');
    const cleanDate = safeString.endsWith('Z') ? safeString.slice(0, -1) : safeString;
    const dateObj = new Date(cleanDate);
    if (isNaN(dateObj)) return dateString; 
    return dateObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
};

// --- CORE STATE ---
const isTripActive = ref(false);
const currentPhaseIndex = ref(0);
const isDispatcherCleared = ref(false);
const isShiftComplete = ref(false);
const isPostTripInspectionCompleted = ref(false);
const isReloading = ref(false); 

const isAdvancingPhase = ref(false);
const isSubmittingChecklist = ref(false);
let checklistAbortController = null;

const isGpsActive = ref(false);
const currentLat = ref(null);
const currentLng = ref(null);
let gpsWatchId = null;
let telemetryPingInterval = null;

const showChecklistModal = ref(false);
const checklistType = ref('Pre-Trip');

const phases = ref([
    { title: 'Time of Call', status: 'pending', timestamp: null, buttonClass: 'bg-teal-600 text-white', textColor: 'text-teal-400' },
    { title: 'Dispatch from Base', status: 'pending', timestamp: null, buttonClass: 'bg-indigo-600 text-white', textColor: 'text-indigo-400' },
    { title: 'Arrival at Scene', status: 'pending', timestamp: null, buttonClass: 'bg-purple-600 text-white', textColor: 'text-purple-400' },
    { title: 'Dispatch from Scene', status: 'pending', timestamp: null, buttonClass: 'bg-pink-600 text-white', textColor: 'text-pink-400' },
    { title: 'Arrival at Hospital', status: 'pending', timestamp: null, buttonClass: 'bg-rose-600 text-white', textColor: 'text-rose-400' },
    { title: 'Proceeding to Base', status: 'pending', timestamp: null, buttonClass: 'bg-orange-600 text-white', textColor: 'text-orange-400' },
    { title: 'Arrival at Base', status: 'pending', timestamp: null, buttonClass: 'bg-yellow-600 text-white', textColor: 'text-yellow-500' },
    { title: 'Post-Trip Inspection', status: 'pending', timestamp: null, buttonClass: 'bg-emerald-600 text-white', textColor: 'text-emerald-400' }
]);

const currentPhaseData = computed(() => {
    switch (currentPhaseIndex.value) {
        case 0: return { title: 'TIME OF CALL (PRE-TRIP)', buttonText: 'Start Inspection', buttonClass: 'bg-teal-600 text-white hover:bg-teal-700 hover:-translate-y-1', textColor: 'text-teal-400' };
        case 1:
            if (!isDispatcherCleared.value) return { title: 'AWAITING APPROVAL', buttonText: 'Awaiting Clearance...', buttonClass: 'bg-slate-800 text-slate-500 cursor-not-allowed border border-slate-700', textColor: 'text-orange-400' };
            return { title: 'CLEARED FOR DEPARTURE', buttonText: 'Log Dispatch from Base', buttonClass: 'bg-indigo-600 text-white hover:bg-indigo-700 hover:-translate-y-1 shadow-lg shadow-indigo-500/30', textColor: 'text-indigo-400' };
        case 2: return { title: 'EN ROUTE TO SCENE', buttonText: 'Log Arrival at Scene', buttonClass: 'bg-purple-600 text-white hover:bg-purple-700 hover:-translate-y-1 shadow-lg shadow-purple-500/30', textColor: 'text-purple-400' };
        case 3: return { title: 'AT SCENE', buttonText: 'Log Dispatch from Scene', buttonClass: 'bg-pink-600 text-white hover:bg-pink-700 hover:-translate-y-1 shadow-lg shadow-pink-500/30', textColor: 'text-pink-400' };
        case 4: return { title: 'TRANSPORTING', buttonText: 'Log Arrival at Hospital', buttonClass: 'bg-rose-600 text-white hover:bg-rose-700 hover:-translate-y-1 shadow-lg shadow-rose-500/30', textColor: 'text-rose-400' };
        case 5: return { title: 'AT HOSPITAL', buttonText: 'Log Return to Base', buttonClass: 'bg-orange-600 text-white hover:bg-orange-700 hover:-translate-y-1 shadow-lg shadow-orange-500/30', textColor: 'text-orange-400' };
        case 6: return { title: 'PROCEEDING TO BASE', buttonText: 'Log Arrival at Base', buttonClass: 'bg-yellow-600 text-white hover:bg-yellow-700 hover:-translate-y-1 shadow-lg shadow-yellow-500/30', textColor: 'text-yellow-500' };
        case 7:
            if (!isPostTripInspectionCompleted.value) return { title: 'ARRIVED AT BASE', buttonText: 'Start Post-Trip Inspection', buttonClass: 'bg-emerald-600 text-white hover:bg-emerald-700 hover:-translate-y-1 shadow-lg shadow-emerald-500/30', textColor: 'text-emerald-500' };
            return { title: 'FINAL TURNOVER PENDING', buttonText: 'Awaiting Final Clearance...', buttonClass: 'bg-slate-800 text-slate-500 cursor-not-allowed border border-slate-700', textColor: 'text-orange-400' };
        default: return { title: 'COMPLETED', buttonText: '', buttonClass: '', textColor: 'text-slate-400' };
    }
});

let lastPingTime = 0;

const startGpsTelemetry = () => {
    if (!("geolocation" in navigator)) {
        toastStore.show("GPS is not supported by your browser.", "error");
        return;
    }

    if (gpsWatchId) return; 

    gpsWatchId = navigator.geolocation.watchPosition(
        async (position) => {
            currentLat.value = position.coords.latitude;
            currentLng.value = position.coords.longitude;
            isGpsActive.value = true;

            const now = Date.now();
            if (now - lastPingTime > 3000 && localShift.value?.vehicle?.id) {
                lastPingTime = now;
                try {
                    await api.post('/api/telemetry/ping', {
                        vehicle_id: localShift.value.vehicle.id,
                        latitude: currentLat.value,
                        longitude: currentLng.value
                    });
                } catch (e) {}
            }
        },
        (error) => {
            isGpsActive.value = false;
        },
        { enableHighAccuracy: true, maximumAge: 0, timeout: 10000 } 
    );
};

const stopGpsTelemetry = () => {
    if (gpsWatchId !== null) {
        navigator.geolocation.clearWatch(gpsWatchId);
        gpsWatchId = null;
    }
    if (telemetryPingInterval) {
        clearInterval(telemetryPingInterval);
        telemetryPingInterval = null;
    }
    isGpsActive.value = false;
};

watch(currentPhaseIndex, (phase) => {
    if (phase >= 1 && phase <= 7) {
        startGpsTelemetry();
    } else {
        stopGpsTelemetry();
    }
}, { immediate: true });

onUnmounted(() => {
    stopGpsTelemetry();
    if (pollInterval) clearInterval(pollInterval);
});

const getFreshCoordinates = () => {
    return new Promise((resolve) => {
        if (!("geolocation" in navigator)) resolve({ lat: currentLat.value, lng: currentLng.value });
        navigator.geolocation.getCurrentPosition(
            (pos) => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
            (err) => resolve({ lat: currentLat.value, lng: currentLng.value }), 
            { enableHighAccuracy: true, maximumAge: 0, timeout: 5000 }
        );
    });
};

const advancePhase = async () => {
    if (isAdvancingPhase.value) return; 
    
    if (currentPhaseIndex.value === 0) {
        checklistType.value = 'Pre-Trip';
        showChecklistModal.value = true;
        return;
    }
    
    if (currentPhaseIndex.value === 1 && !isDispatcherCleared.value) return; 

    if (currentPhaseIndex.value === 7) {
        if (!isPostTripInspectionCompleted.value) {
            checklistType.value = 'Post-Trip';
            showChecklistModal.value = true;
        }
        return;
    }

    if (localShift.value && localShift.value.trip && localShift.value.trip.id) {
        isAdvancingPhase.value = true; 
        try {
            const coords = await getFreshCoordinates();
            currentLat.value = coords.lat;
            currentLng.value = coords.lng;

            await api.post(`/api/trips/${localShift.value.trip.id}/advance`, {
                latitude: currentLat.value,
                longitude: currentLng.value
            });
            
            await fetchAndSyncCurrentShift();
        } catch (e) {
            toastStore.show('Network error while advancing phase.', 'error');
        } finally {
            isAdvancingPhase.value = false; 
        }
    }
};

const syncPhaseState = (shiftObj) => {
    if (shiftObj.trip) {
        isTripActive.value = true;
        currentPhaseIndex.value = shiftObj.trip.current_phase;
        isDispatcherCleared.value = !!shiftObj.trip.is_cleared_by_dispatch;

        phases.value.forEach(p => { p.status = 'pending'; p.timestamp = null; });
        for(let i=0; i < currentPhaseIndex.value; i++) {
            if (phases.value[i]) phases.value[i].status = 'completed';
        }
        if (currentPhaseIndex.value < phases.value.length) {
            phases.value[currentPhaseIndex.value].status = 'active';
        }

        if (currentPhaseIndex.value === 7 && localStorage.getItem(`post_trip_${shiftObj.id}`)) {
            isPostTripInspectionCompleted.value = true;
        }
    } else {
        isTripActive.value = false;
        currentPhaseIndex.value = 0;
        phases.value.forEach(p => { p.status = 'pending'; p.timestamp = null; });
        phases.value[0].status = 'active';
    }
};

watch(() => props.shift, (newShift) => {
    localShift.value = newShift; 
    if (newShift) {
        syncPhaseState(newShift);
    } else {
        isTripActive.value = false;
        currentPhaseIndex.value = 0;
        isShiftComplete.value = false;
        isDispatcherCleared.value = false;
        isPostTripInspectionCompleted.value = false;
        phases.value.forEach(p => { p.status = 'pending'; p.timestamp = null; });
    }
}, { immediate: true });

watch(isTripActive, async (newVal) => {
    if (newVal && localShift.value && localShift.value.status !== 'ACTIVE' && !localShift.value.trip) {
        try { await api.post(`/api/shifts/${localShift.value.id}/start`); } catch (error) {}
    }
});

const cancelChecklistSubmit = () => {
    if (checklistAbortController) {
        checklistAbortController.abort();
    }
};

const submitChecklist = async (formPayload) => { 
    isSubmittingChecklist.value = true;
    checklistAbortController = new AbortController();

    try {
        let activeTripId = localShift.value?.trip?.id;
        
        if (checklistType.value === 'Pre-Trip' && !activeTripId) {
            try { 
                const res = await api.post('/api/trips', { shift_id: localShift.value.id }, { signal: checklistAbortController.signal }); 
                activeTripId = res.data.trip_id;
            } catch(e) {}
        }

        const typeEnumRaw = checklistType.value.toUpperCase().replace('-', '_');
        
        const payload = {
            ...formPayload,
            shift_id: localShift.value?.id,
            trip_id: activeTripId || null, 
            vehicle_unit: localShift.value?.vehicle?.unit_id || 'UNKNOWN',
            type: typeEnumRaw,
        };

        const res = await api.post('/api/checklists', payload, {
            signal: checklistAbortController.signal
        });
        
        showChecklistModal.value = false;

        if (res.data && res.data.cancelled) {
             toastStore.show('Shift was automatically aborted due to unfit declarations.', 'error');
             isReloading.value = true;
             setTimeout(() => { emit('refresh'); isReloading.value = false; }, 1500);
             return;
        }

        if (toastStore && toastStore.show) toastStore.show(`${checklistType.value} Checklist safely transmitted!`, 'success');

        if (checklistType.value === 'Post-Trip') {
            isPostTripInspectionCompleted.value = true;
            if (localShift.value && localShift.value.id) {
                localStorage.setItem(`post_trip_${localShift.value.id}`, 'true');
            }
        } else if (checklistType.value === 'Pre-Trip' && activeTripId) {
            try {
                await api.post(`/api/trips/${activeTripId}/advance`);
            } catch(e) {}
        }

        try {
            await fetchAndSyncCurrentShift();
        } catch (syncError) {
            console.error("Failed to resync state", syncError);
        }

    } catch (error) {
        if (error.response?.status === 422) {
            toastStore.show(error.response.data.message || 'Duplicate submission rejected.', 'error');
            showChecklistModal.value = false;
        } 
        else if (error.name === 'CanceledError' || error.message === 'canceled') {
            toastStore.show('Upload cancelled. You can now edit your form.', 'info');
        } else {
            toastStore.show('Failed to submit checklist. Check connection.', 'error');
        }
    } finally {
        isSubmittingChecklist.value = false;
        checklistAbortController = null;
    }
};

let pollInterval = null;

const fetchAndSyncCurrentShift = async () => {
    if (!localShift.value?.id) return null;
    try {
        const response = await api.get(`/api/shifts/my-shifts?t=${new Date().getTime()}`);
        const myShift = response.data.find(s => s.id === localShift.value.id);
        if (myShift) {
            localShift.value = myShift;
            syncPhaseState(myShift);
            return myShift;
        }
    } catch (e) {
        console.error("Failed to fetch shift", e);
    }
    return null;
};

const finalizeShift = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }

    const wasPostTrip = isPostTripInspectionCompleted.value;
    if (localShift.value && localShift.value.id) {
        localStorage.removeItem(`post_trip_${localShift.value.id}`);
    }

    isTripActive.value = false;
    isPostTripInspectionCompleted.value = false;
    isShiftComplete.value = false;
    currentPhaseIndex.value = 0;
    phases.value.forEach(p => { p.status = 'pending'; p.timestamp = null; });
    
    localShift.value = null; 
    
    if (wasPostTrip && toastStore && toastStore.show) {
        toastStore.show("Turnover approved by Dispatcher. Shift finalized.", "success");
    }
    
    setTimeout(() => { emit('refresh'); }, 500);
};

onMounted(() => {
    pollInterval = setInterval(async () => {
        if (isTripActive.value && localShift.value && !isShiftComplete.value) {
            try {
                const response = await api.get(`/api/shifts/my-shifts?t=${new Date().getTime()}`);
                const myShift = response.data.find(s => s.id === localShift.value.id);
                
                if (myShift) {
                    localShift.value = myShift;

                    if (myShift.status === 'COMPLETED') {
                        isShiftComplete.value = true;
                        finalizeShift();
                        return;
                    }

                    if (myShift.trip) {
                        if (currentPhaseIndex.value === 1 && myShift.trip.is_cleared_by_dispatch && !isDispatcherCleared.value) {
                            isDispatcherCleared.value = true;
                            if (toastStore && toastStore.show) toastStore.show("Dispatcher has cleared the vehicle for deployment!", "success");
                        }
                        
                        if (myShift.trip.current_phase >= currentPhaseIndex.value || myShift.trip.is_cleared_by_dispatch !== isDispatcherCleared.value) {
                            syncPhaseState(myShift);
                        }
                    }
                } else {
                    finalizeShift();
                }
            } catch (e) {
                console.error("Polling failed", e);
            }
        }
    }, 5000); 
});

const isIssueModalOpen = ref(false);
// ==========================================
// CRITICAL FIX: HARD EMERGENCY LOCKDOWN
// ==========================================
const hardEmergencyLock = ref(null);

const handleIssueReported = async (issueData) => {
    isIssueModalOpen.value = false;
    
    // Instantly lock the screen with fake optimistic data so it's impossible to bypass!
    hardEmergencyLock.value = { issue_type: issueData.issue_type };

    try {
        await api.post('/api/emergencies/report', {
            ...issueData,
            latitude: currentLat.value,
            longitude: currentLng.value
        });
        toastStore.show('Emergency Alert sent to Dispatch.', 'error');
        
        // Force a rapid resync to get the official database object
        await fetchAndSyncCurrentShift();
        
    } catch (e) {
        toastStore.show('Failed to send emergency alert.', 'error');
        hardEmergencyLock.value = null; // Release lock if it failed to send
    }
};
</script>