<template>
  <div class="min-h-screen bg-slate-900 flex flex-col items-center justify-center p-4 sm:p-6 overflow-hidden relative">
    
    <!-- Cool Background Effects -->
    <div class="absolute inset-0 z-0">
      <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-teal-600/20 rounded-full blur-3xl mix-blend-screen animate-pulse"></div>
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-cyan-600/20 rounded-full blur-3xl mix-blend-screen animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Main Content Container -->
    <div class="relative z-10 w-full max-w-lg bg-slate-800/60 backdrop-blur-xl border border-slate-700/50 rounded-3xl p-8 sm:p-12 shadow-2xl text-center">
      
      <!-- Icon -->
      <div class="mb-8 flex justify-center animate-bounce" style="animation-duration: 3s;">
        <div class="w-24 h-24 rounded-full bg-slate-800/80 border border-slate-700 flex items-center justify-center shadow-[0_0_40px_rgba(13,148,136,0.3)]">
          <svg class="w-12 h-12 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
        </div>
      </div>

      <h1 class="text-3xl font-extrabold text-white tracking-tight mb-4">System Update in Progress</h1>
      
      <p class="text-slate-300 text-lg leading-relaxed mb-8">
        We're currently performing scheduled maintenance to improve your experience. The system will be back online shortly.
      </p>

      <!-- Status Indicator -->
      <div class="flex items-center justify-center gap-3 mb-10">
        <span class="relative flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-teal-500"></span>
        </span>
        <span class="text-sm font-medium text-teal-400 uppercase tracking-wider">Engineers at work</span>
      </div>

      <!-- Dev Login link -->
      <div class="pt-6 border-t border-slate-700/50">
        <button @click="$router.push('/login')" class="text-sm text-slate-500 hover:text-slate-300 transition-colors bg-transparent border border-slate-700 rounded-lg px-4 py-2 hover:border-slate-500">
          Developer Login
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
// Minimal script for the maintenance view
</script>
