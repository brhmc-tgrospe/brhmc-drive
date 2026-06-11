<template>
  <div class="p-4 md:p-8 space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">System Health Dashboard</h1>
        <p class="text-sm text-slate-500">Diagnostic metrics and status overview</p>
      </div>
      <div class="flex gap-2">
        <div class="flex items-center gap-1 bg-indigo-50/50 rounded-xl p-1 border border-indigo-100">
          <select v-model="backupType" class="bg-transparent text-indigo-700 text-sm font-medium outline-none px-2 cursor-pointer border-r border-indigo-200" :disabled="loading || backingUp">
            <option value="database">Database Only</option>
            <option value="files">Files Only</option>
            <option value="both">DB & Files</option>
          </select>
          <button 
            @click="createBackup" 
            :disabled="loading || backingUp"
            class="px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg font-medium transition-colors disabled:opacity-50 flex items-center gap-2"
          >
            <svg v-if="backingUp" class="animate-spin w-4 h-4" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-else>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
            </span>
            {{ backingUp ? 'Backing up...' : 'Create' }}
          </button>
          <button 
            @click="openBackupsList" 
            :disabled="loading"
            class="px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg font-medium transition-colors disabled:opacity-50 flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path></svg>
            Backups
          </button>
        </div>
        <button 
          @click="clearLog" 
          :disabled="loading || backingUp"
          class="px-4 py-2 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl font-medium transition-colors disabled:opacity-50"
        >
          Clear Log
        </button>
        <button 
          @click="fetchMetrics" 
          :disabled="loading"
          class="px-4 py-2 bg-slate-800 text-white hover:bg-slate-700 rounded-xl font-medium transition-colors disabled:opacity-50 flex items-center gap-2"
        >
          <svg v-if="loading" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span v-else>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
          </span>
          Refresh Data
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !metrics" class="flex flex-col items-center justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-slate-800 mb-4"></div>
      <p class="text-slate-500 animate-pulse">Gathering system metrics...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-rose-50 border border-rose-100 rounded-2xl p-6 text-center shadow-sm">
      <svg class="w-12 h-12 text-rose-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
      <h3 class="text-lg font-bold text-rose-800">Failed to load metrics</h3>
      <p class="text-rose-600 mt-1">{{ error }}</p>
    </div>

    <!-- Metrics Content -->
    <div v-else-if="metrics" class="space-y-6">
      <!-- Health Score Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center justify-between">
        <div>
          <h2 class="text-lg font-bold text-slate-800 mb-1">Overall Health Score</h2>
          <p class="text-sm text-slate-500">Based on system constraints and errors</p>
        </div>
        <div class="flex items-center gap-4">
          <div class="text-right">
            <div class="text-3xl font-black" :class="scoreColorText">{{ metrics.score }}<span class="text-xl text-slate-400">/100</span></div>
            <div class="text-xs font-bold uppercase tracking-wider" :class="scoreColorText">{{ scoreLabel }}</div>
          </div>
          <!-- Progress Ring -->
          <div class="relative w-16 h-16">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
              <path class="text-slate-100" stroke-width="4" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
              <path :class="scoreColorStroke" stroke-width="4" stroke-dasharray="100, 100" :stroke-dashoffset="100 - metrics.score" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
            </svg>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Database -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
          <div class="flex items-center gap-2 mb-4">
            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
            </div>
            <h3 class="font-bold text-slate-800">Database</h3>
          </div>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">Total Rows</span>
              <span class="font-semibold text-slate-800">{{ formatNumber(metrics.database.rows) }}</span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">Data Size</span>
              <span class="font-semibold text-slate-800">{{ formatBytes(metrics.database.size) }}</span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">Connections</span>
              <span class="font-semibold text-slate-800">{{ metrics.database.activeConnections }} / {{ metrics.database.maxConnections }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500">Root User</span>
              <span class="px-2 py-0.5 rounded text-xs font-bold" :class="metrics.database.isRoot ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'">{{ metrics.database.isRoot ? 'YES (DANGER)' : 'NO' }}</span>
            </div>
          </div>
        </div>

        <!-- Storage & Disk -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
          <div class="flex items-center gap-2 mb-4">
            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
            </div>
            <h3 class="font-bold text-slate-800">Storage & Disk</h3>
          </div>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">Total Disk Space</span>
              <span class="font-semibold text-slate-800">{{ formatBytes(metrics.disk.total) }}</span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">Free Space</span>
              <span class="font-semibold" :class="diskUsagePercent > 90 ? 'text-rose-600' : 'text-emerald-600'">{{ formatBytes(metrics.disk.free) }} ({{ (100 - diskUsagePercent).toFixed(1) }}%)</span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">Uploads Folder</span>
              <span class="font-semibold text-slate-800">{{ formatBytes(metrics.disk.uploadsSize) }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500">Reports Folder</span>
              <span class="font-semibold text-slate-800">{{ formatBytes(metrics.disk.reportsSize) }}</span>
            </div>
          </div>
        </div>

        <!-- Services & Connectivity -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
          <div class="flex items-center gap-2 mb-4">
            <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <h3 class="font-bold text-slate-800">Connectivity</h3>
          </div>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">MySQL Connection</span>
              <span class="w-3 h-3 rounded-full" :class="metrics.services.mysql ? 'bg-emerald-500' : 'bg-rose-500'"></span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">External HTTP (Google)</span>
              <span class="w-3 h-3 rounded-full" :class="metrics.services.google ? 'bg-emerald-500' : 'bg-rose-500'"></span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">SMTP Server</span>
              <span class="w-3 h-3 rounded-full" :class="metrics.services.smtp ? 'bg-emerald-500' : 'bg-rose-500'"></span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500">SSL Expiry</span>
              <span class="font-semibold text-slate-800">{{ metrics.sslExpiry }}</span>
            </div>
          </div>
        </div>

        <!-- Environment -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
          <div class="flex items-center gap-2 mb-4">
            <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <h3 class="font-bold text-slate-800">PHP Environment</h3>
          </div>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">Extensions (mysqli, gd, curl)</span>
              <span class="w-3 h-3 rounded-full" :class="metrics.environment.mysqli && metrics.environment.gd && metrics.environment.curl ? 'bg-emerald-500' : 'bg-rose-500'"></span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">Memory Limit</span>
              <span class="font-semibold text-slate-800">{{ metrics.environment.memory_limit }}</span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">Upload Max</span>
              <span class="font-semibold text-slate-800">{{ metrics.environment.upload_max_filesize }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500">Display Errors</span>
              <span class="px-2 py-0.5 rounded text-xs font-bold" :class="metrics.environment.display_errors ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'">{{ metrics.environment.display_errors ? 'ON' : 'OFF' }}</span>
            </div>
          </div>
        </div>

        <!-- File Permissions -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
          <div class="flex items-center gap-2 mb-4">
            <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h3 class="font-bold text-slate-800">Permissions & Backups</h3>
          </div>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">Storage Writable</span>
              <span class="w-3 h-3 rounded-full" :class="metrics.permissions.storage ? 'bg-emerald-500' : 'bg-rose-500'"></span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">Bootstrap Cache Writable</span>
              <span class="w-3 h-3 rounded-full" :class="metrics.permissions.bootstrap_cache ? 'bg-emerald-500' : 'bg-rose-500'"></span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">Latest Backup</span>
              <span class="font-semibold text-slate-800">{{ metrics.backups.latest || 'None' }}</span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-50 pb-4">
              <span class="text-slate-500">Backups Healthy</span>
              <span class="px-2 py-0.5 rounded text-xs font-bold" :class="metrics.backups.isStale ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'">{{ metrics.backups.isStale ? 'STALE' : 'OK' }}</span>
            </div>
            
            <div class="pt-2">
              <h4 class="font-semibold text-slate-800 text-xs uppercase tracking-wider mb-2">Automated Schedule</h4>
              <div class="flex items-center gap-4 mb-2">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="scheduleConfig.enabled" class="sr-only peer">
                  <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                  <span class="ml-2 text-sm font-medium text-slate-600">Enable</span>
                </label>
              </div>
              <div class="space-y-2 mt-2" :class="{ 'opacity-50 pointer-events-none': !scheduleConfig.enabled }">
                <div class="flex items-center gap-2">
                  <select v-model="scheduleConfig.frequency" class="text-sm bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 outline-none focus:border-indigo-500 flex-1">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                  </select>
                  <input type="time" v-model="scheduleConfig.time" class="text-sm bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 outline-none focus:border-indigo-500 w-28">
                </div>
                
                <div v-if="scheduleConfig.frequency === 'weekly'" class="flex items-center gap-2">
                  <span class="text-xs text-slate-500">Every</span>
                  <select v-model="scheduleConfig.dayOfWeek" class="text-sm bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 outline-none focus:border-indigo-500 flex-1">
                    <option :value="1">Monday</option>
                    <option :value="2">Tuesday</option>
                    <option :value="3">Wednesday</option>
                    <option :value="4">Thursday</option>
                    <option :value="5">Friday</option>
                    <option :value="6">Saturday</option>
                    <option :value="7">Sunday</option>
                  </select>
                </div>
                
                <div v-if="scheduleConfig.frequency === 'monthly'" class="flex items-center gap-2">
                  <span class="text-xs text-slate-500">Date of the month</span>
                  <input type="number" min="1" max="31" v-model="scheduleConfig.dayOfMonth" class="text-sm bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 outline-none focus:border-indigo-500 w-16 text-center">
                </div>
                
                <button @click="saveSchedule" :disabled="savingSchedule" class="w-full text-xs bg-slate-800 text-white px-3 py-1.5 rounded-lg hover:bg-slate-700 disabled:opacity-50 font-medium">
                  {{ savingSchedule ? 'Saving...' : 'Save Schedule' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Logs Summary -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
          <div class="flex items-center gap-2 mb-4">
            <div class="p-2 bg-slate-100 text-slate-600 rounded-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h3 class="font-bold text-slate-800">Error Logs</h3>
          </div>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">Log File Size</span>
              <span class="font-semibold text-slate-800">{{ formatBytes(metrics.logs.size) }}</span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
              <span class="text-slate-500">OOM Errors</span>
              <span class="font-semibold" :class="metrics.logs.oomErrors > 0 ? 'text-rose-600' : 'text-emerald-600'">{{ metrics.logs.oomErrors }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500">Slow Queries</span>
              <span class="font-semibold" :class="metrics.logs.slowQueries > 0 ? 'text-amber-600' : 'text-slate-800'">{{ metrics.logs.slowQueries }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Recent Log Lines -->
      <div class="bg-slate-900 rounded-2xl shadow-sm p-5 mt-6 border border-slate-800">
        <h3 class="font-bold text-white mb-4 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
          Recent Log Output
        </h3>
        <div class="bg-black/50 rounded-xl p-4 font-mono text-xs text-slate-300 h-64 overflow-y-auto overflow-x-auto whitespace-pre">
          <div v-if="metrics.logs.recentLines.length === 0" class="text-slate-500 italic">No recent log entries.</div>
          <div v-for="(line, idx) in metrics.logs.recentLines" :key="idx" class="mb-1 opacity-80 hover:opacity-100">
            {{ line }}
          </div>
        </div>
      </div>
    </div>

    <!-- Custom Modal -->
    <div v-if="modalState.isOpen" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4 transition-opacity">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden transform transition-all">
        <div class="p-6">
          <div class="flex items-center gap-3 mb-4">
            <div :class="{
              'bg-blue-100 text-blue-600': modalState.type === 'info',
              'bg-rose-100 text-rose-600': modalState.type === 'error' || modalState.type === 'confirm',
              'bg-emerald-100 text-emerald-600': modalState.type === 'success'
            }" class="w-10 h-10 rounded-full flex items-center justify-center shrink-0">
              <svg v-if="modalState.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              <svg v-else-if="modalState.type === 'error' || modalState.type === 'confirm'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">{{ modalState.title }}</h3>
          </div>
          <p class="text-slate-600 text-sm mb-6">{{ modalState.message }}</p>
          <div class="flex gap-3 justify-end">
            <button v-if="modalState.type === 'confirm'" @click="closeModal" class="px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg text-sm font-medium transition-colors">Cancel</button>
            <button @click="handleModalConfirm" :class="{
              'bg-rose-600 hover:bg-rose-700 text-white': modalState.type === 'confirm' || modalState.type === 'error',
              'bg-emerald-600 hover:bg-emerald-700 text-white': modalState.type === 'success',
              'bg-blue-600 hover:bg-blue-700 text-white': modalState.type === 'info'
            }" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              {{ modalState.type === 'confirm' ? 'Confirm' : 'OK' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Backups List Modal -->
    <div v-if="backupsModalOpen" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4 transition-opacity">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all">
        <div class="p-6">
          <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
              <div class="bg-indigo-100 text-indigo-600 w-10 h-10 rounded-full flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path></svg>
              </div>
              <h3 class="text-lg font-bold text-slate-800">Backup Files</h3>
            </div>
            <button @click="backupsModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          </div>
          
          <div v-if="loadingBackups" class="flex items-center justify-center py-10">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          </div>
          
          <div v-else-if="backupFiles.length === 0" class="text-center py-10">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            <p class="text-sm text-slate-500 font-medium">No backup files found</p>
            <p class="text-xs text-slate-400 mt-1">Create a backup using the button above</p>
          </div>
          
          <div v-else class="max-h-80 overflow-y-auto space-y-2">
            <div v-for="file in backupFiles" :key="file.path" 
              class="flex items-center justify-between bg-slate-50 hover:bg-slate-100 rounded-xl px-4 py-3 transition-colors group">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-800 truncate">{{ file.name }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ file.date }} · {{ formatBytes(file.size) }}</p>
              </div>
              <button @click="downloadBackup(file)" 
                :disabled="file.downloading"
                class="ml-3 shrink-0 px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 flex items-center gap-1.5">
                <svg v-if="file.downloading" class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                {{ file.downloading ? 'Downloading...' : 'Download' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../axios';

const modalState = ref({
  isOpen: false,
  title: '',
  message: '',
  type: 'info',
  onConfirm: null,
});

const showModal = (title, message, type = 'info', onConfirm = null) => {
  modalState.value = { isOpen: true, title, message, type, onConfirm };
};

const closeModal = () => {
  modalState.value.isOpen = false;
};

const handleModalConfirm = () => {
  if (modalState.value.onConfirm) {
    modalState.value.onConfirm();
  }
  closeModal();
};

const metrics = ref(null);
const loading = ref(true);
const error = ref('');
const backingUp = ref(false);
const backupType = ref('database');
const savingSchedule = ref(false);
const backupsModalOpen = ref(false);
const loadingBackups = ref(false);
const backupFiles = ref([]);
const scheduleConfig = ref({
  enabled: false,
  frequency: 'daily',
  dayOfWeek: 1,
  dayOfMonth: 1,
  time: '02:00'
});

const diskUsagePercent = computed(() => {
  if (!metrics.value || metrics.value.disk.total === 0) return 0;
  return ((metrics.value.disk.total - metrics.value.disk.free) / metrics.value.disk.total) * 100;
});

const scoreColorText = computed(() => {
  if (!metrics.value) return 'text-slate-800';
  const score = metrics.value.score;
  if (score >= 90) return 'text-emerald-500';
  if (score >= 70) return 'text-amber-500';
  return 'text-rose-500';
});

const scoreColorStroke = computed(() => {
  if (!metrics.value) return 'text-slate-800';
  const score = metrics.value.score;
  if (score >= 90) return 'text-emerald-500';
  if (score >= 70) return 'text-amber-500';
  return 'text-rose-500';
});

const scoreLabel = computed(() => {
  if (!metrics.value) return '';
  const score = metrics.value.score;
  if (score >= 90) return 'Grade A (Healthy)';
  if (score >= 80) return 'Grade B (Good)';
  if (score >= 70) return 'Grade C (Fair)';
  if (score >= 60) return 'Grade D (Poor)';
  return 'Grade F (Critical)';
});

const fetchMetrics = async () => {
  loading.value = true;
  error.value = '';
  try {
    const res = await api.get('/api/system-health');
    metrics.value = res.data;
    if (res.data.backups && res.data.backups.schedule) {
      scheduleConfig.value = {
        enabled: Boolean(res.data.backups.schedule.enabled),
        frequency: res.data.backups.schedule.frequency || 'daily',
        dayOfWeek: res.data.backups.schedule.dayOfWeek || 1,
        dayOfMonth: res.data.backups.schedule.dayOfMonth || 1,
        time: res.data.backups.schedule.time || '02:00'
      };
    }
  } catch (err) {
    console.error(err);
    error.value = err.response?.data?.error || err.message || 'An error occurred fetching metrics';
  } finally {
    loading.value = false;
  }
};

const clearLog = () => {
  showModal('Clear Log', 'Are you sure you want to clear the server log?', 'confirm', async () => {
    loading.value = true;
    try {
      await api.post('/api/system-health/clear-log');
      await fetchMetrics();
      showModal('Success', 'Server log cleared successfully.', 'success');
    } catch (err) {
      console.error(err);
      showModal('Error', 'Failed to clear log.', 'error');
      loading.value = false;
    }
  });
};

const createBackup = async () => {
  backingUp.value = true;
  try {
    await api.post('/api/system-health/backup', { type: backupType.value });
    await fetchMetrics();
    showModal('Success', 'Backup created successfully!', 'success');
  } catch (err) {
    console.error(err);
    showModal('Error', 'Failed to create backup: ' + (err.response?.data?.error || err.message), 'error');
  } finally {
    backingUp.value = false;
  }
};

const saveSchedule = async () => {
  savingSchedule.value = true;
  try {
    await api.post('/api/system-health/backup-schedule', scheduleConfig.value);
    showModal('Success', 'Backup schedule saved successfully!', 'success');
  } catch (err) {
    console.error(err);
    showModal('Error', 'Failed to update backup schedule: ' + (err.response?.data?.error || err.message), 'error');
  } finally {
    savingSchedule.value = false;
  }
};

const openBackupsList = async () => {
  backupsModalOpen.value = true;
  loadingBackups.value = true;
  try {
    const res = await api.get('/api/system-health/backups');
    backupFiles.value = (res.data.backups || []).map(f => ({ ...f, downloading: false }));
  } catch (err) {
    console.error(err);
    backupFiles.value = [];
  } finally {
    loadingBackups.value = false;
  }
};

const downloadBackup = async (file) => {
  file.downloading = true;
  try {
    const res = await api.get('/api/system-health/backups/download', {
      params: { path: file.path },
      responseType: 'blob'
    });
    const url = window.URL.createObjectURL(new Blob([res.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', file.name);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (err) {
    console.error(err);
    showModal('Error', 'Failed to download backup file.', 'error');
  } finally {
    file.downloading = false;
  }
};

const formatBytes = (bytes, decimals = 2) => {
  if (!+bytes) return '0 Bytes';
  const k = 1024;
  const dm = decimals < 0 ? 0 : decimals;
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
};

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num);
};

onMounted(() => {
  fetchMetrics();
});
</script>
