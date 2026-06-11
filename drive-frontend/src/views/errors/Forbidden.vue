<template>
  <div class="h-screen flex items-center justify-center bg-slate-50 p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden text-center p-8">
      <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6 text-red-500">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
      </div>
      <h1 class="text-3xl font-extrabold text-slate-800 mb-2 tracking-tight">Access Denied</h1>
      <p class="text-slate-500 text-sm mb-8 leading-relaxed">
        You do not have the required permissions to view this module. If you believe this is an error, please contact your system administrator.
      </p>
      
      <router-link to="/dashboard" class="inline-flex items-center justify-center w-full px-6 py-3 bg-teal-600 text-white font-bold rounded-lg hover:bg-teal-700 transition-colors shadow-md shadow-teal-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
        Return to Dashboard
      </router-link>
    </div>
  </div>
</template>

<script setup>
// Error view for Access Control List (ACL) routing blocks
</script>
