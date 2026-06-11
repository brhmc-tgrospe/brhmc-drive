<template>
  <div class="max-w-7xl mx-auto h-full flex flex-col gap-4 sm:gap-6 pt-2 sm:pt-4 relative">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center shrink-0 gap-3 sm:gap-0">
      <div>
        <h2 class="text-lg sm:text-2xl font-extrabold text-slate-800 tracking-tight">System Activity Logs</h2>
        <p class="text-[11px] sm:text-sm text-slate-500 font-medium mt-0.5 sm:mt-1">Monitor all user actions, system events, and data changes.</p>
      </div>
      <button @click="loadLogs" class="flex items-center justify-center px-4 py-2 sm:py-2.5 bg-slate-800 text-white font-bold text-xs sm:text-sm rounded-lg hover:bg-slate-900 transition-colors shadow-md shadow-slate-200 w-full sm:w-auto">
        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Refresh Logs
      </button>
    </div>

    <!-- Main Content Area -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex-1 overflow-hidden flex flex-col relative z-10">
      
      <!-- Filters Section -->
      <div class="bg-slate-50 p-4 border-b border-slate-200 flex flex-col lg:flex-row gap-4 justify-between shrink-0">
        
        <!-- Search & Advanced Filters -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full lg:w-auto">
          <!-- Global Search -->
          <div class="relative w-full sm:w-72">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" v-model="filters.search" @input="debounceSearch" placeholder="Search..." class="pl-9 pr-3 py-2 w-full border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none transition-shadow" />
          </div>

          <!-- Log Type Dropdown -->
          <div class="relative w-full sm:w-48">
            <select v-model="filters.log_name" @change="applyFilters" class="w-full pl-3 pr-8 py-2 border border-slate-200 rounded-lg text-sm bg-white font-medium text-slate-600 outline-none focus:ring-2 focus:ring-teal-500 appearance-none">
              <option value="">All Log Types</option>
              <option value="login">Login</option>
              <option value="logout">Logout</option>
              <option value="auth">User Accounts</option>
              <option value="vehicle">Vehicles</option>
              <option value="trip">Schedules & Trips</option>
              <option value="incident">Incidents</option>
              <option value="checklist">Checklists</option>
              <option value="maintenance">Maintenance</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
          </div>
        </div>

        <!-- Date Range Filters -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full lg:w-auto">
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider shrink-0">From</label>
            <input type="date" v-model="filters.date_from" @change="applyFilters" class="px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white font-medium text-slate-600 outline-none focus:ring-2 focus:ring-teal-500 w-full" />
          </div>
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider shrink-0">To</label>
            <input type="date" v-model="filters.date_to" @change="applyFilters" class="px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white font-medium text-slate-600 outline-none focus:ring-2 focus:ring-teal-500 w-full" />
          </div>
          <button @click="clearFilters" v-if="hasActiveFilters" class="text-xs font-bold text-slate-500 hover:text-red-500 transition-colors px-2 whitespace-nowrap">Clear</button>
        </div>
      </div>

      <!-- Table Area -->
      <div class="overflow-x-auto flex-1 custom-scrollbar bg-white">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="sticky top-0 bg-white z-10 shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
            <tr class="text-slate-500 font-bold uppercase text-[10px] tracking-wider">
              <th class="py-4 px-4 sm:px-6 w-[200px] sm:w-[250px]">Date & Time</th>
              <th class="py-4 px-4 sm:px-6 w-[250px] sm:w-[300px]">User (Causer)</th>
              <th class="py-4 px-4 sm:px-6 w-auto">Activity Details</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="loading" class="bg-white">
              <td colspan="5" class="py-12 text-center text-slate-400 font-medium">
                <div class="flex items-center justify-center gap-3">
                  <svg class="animate-spin h-5 w-5 text-teal-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  <span>Loading activity logs...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="!logs.length">
              <td colspan="5" class="py-12 text-center text-slate-500 font-medium">
                <div class="flex flex-col items-center gap-2">
                  <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                  <span>No logs found matching your criteria.</span>
                </div>
              </td>
            </tr>
            <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-50/50 transition-colors group">
              <!-- Date & Time -->
              <td class="py-3 px-4 sm:px-6 whitespace-nowrap">
                <div class="font-bold text-slate-700">{{ formatDate(log.created_at) }}</div>
                <div class="text-[11px] text-slate-400 font-medium">{{ formatTime(log.created_at) }}</div>
              </td>
              
              <!-- Causer -->
              <td class="py-3 px-4 sm:px-6 whitespace-nowrap">
                <div v-if="log.causer" class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xs shrink-0">
                    {{ log.causer.first_name?.charAt(0) || '' }}{{ log.causer.last_name?.charAt(0) || '' }}
                  </div>
                  <div class="flex flex-col">
                    <span class="font-bold text-slate-800 text-xs">{{ log.causer.first_name }} {{ log.causer.last_name }}</span>
                    <span class="text-[10px] text-slate-400 font-medium">{{ log.causer.email }}</span>
                  </div>
                </div>
                <div v-else class="text-xs text-slate-400 font-medium italic">System / Unknown</div>
              </td>

              <!-- Activity Details -->
              <td class="py-3 px-4 sm:px-6 whitespace-normal">
                <div class="flex flex-col gap-1.5">
                  <div class="flex items-center gap-2">
                    <span :class="getActionBadgeClass(log.event)" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border shrink-0">
                      {{ formatEventName(log.event || 'action') }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium bg-slate-100 px-2 py-0.5 rounded-md inline-block">
                      {{ log.log_name || 'default' }}
                    </span>
                  </div>
                  <div class="text-sm font-semibold text-slate-700 leading-snug">
                    {{ formatDetailedDescription(log) }}
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div class="bg-slate-50 p-3 sm:p-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between shrink-0 gap-3">
        <div class="text-xs font-medium text-slate-500">
          Showing <span class="font-bold text-slate-700">{{ pagination.from || 0 }}</span> to <span class="font-bold text-slate-700">{{ pagination.to || 0 }}</span> of <span class="font-bold text-slate-700">{{ pagination.total || 0 }}</span> entries
        </div>
        
        <div class="flex items-center gap-2">
          <select v-model="perPage" @change="applyFilters" class="py-1.5 px-2 border border-slate-200 rounded-lg text-xs bg-white font-bold text-slate-600 outline-none focus:ring-2 focus:ring-teal-500">
            <option :value="10">10 per page</option>
            <option :value="25">25 per page</option>
            <option :value="50">50 per page</option>
            <option :value="100">100 per page</option>
          </select>

          <div class="flex items-center space-x-1 border border-slate-200 rounded-lg bg-white overflow-hidden shadow-sm">
            <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-2.5 py-1.5 disabled:opacity-30 disabled:bg-slate-50 hover:bg-slate-50 transition-colors text-slate-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <span class="px-3 py-1.5 text-xs font-bold text-slate-700 border-x border-slate-100">Page {{ currentPage }} of {{ lastPage || 1 }}</span>
            <button @click="changePage(currentPage + 1)" :disabled="currentPage >= lastPage" class="px-2.5 py-1.5 disabled:opacity-30 disabled:bg-slate-50 hover:bg-slate-50 transition-colors text-slate-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
          </div>
        </div>
      </div>
      
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import api from '../../axios';
import { useToastStore } from '../../stores/toast';

const toastStore = useToastStore();

const logs = ref([]);
const loading = ref(false);

const filters = reactive({
    search: '',
    log_name: '',
    date_from: '',
    date_to: ''
});

const perPage = ref(25);
const currentPage = ref(1);
const lastPage = ref(1);
const pagination = reactive({
    from: 0,
    to: 0,
    total: 0
});

const hasActiveFilters = computed(() => {
    return filters.search || filters.log_name || filters.date_from || filters.date_to;
});

const loadLogs = async () => {
    loading.value = true;
    try {
        const response = await api.get('/api/activity-logs', {
            params: {
                search: filters.search,
                log_name: filters.log_name,
                date_from: filters.date_from,
                date_to: filters.date_to,
                per_page: perPage.value,
                page: currentPage.value
            }
        });
        logs.value = response.data.data || response.data; // adjust based on pagination structure
        
        // Handle Laravel pagination wrapper
        if (response.data.data) {
            currentPage.value = response.data.current_page;
            lastPage.value = response.data.last_page;
            pagination.from = response.data.from;
            pagination.to = response.data.to;
            pagination.total = response.data.total;
        }
    } catch (error) {
        toastStore.show("Failed to load activity logs", "error");
        console.error(error);
    } finally {
        loading.value = false;
    }
};

let searchTimeout = null;
const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage.value = 1; 
        loadLogs();
    }, 400);
};

const applyFilters = () => {
    currentPage.value = 1;
    loadLogs();
};

const clearFilters = () => {
    filters.search = '';
    filters.log_name = '';
    filters.date_from = '';
    filters.date_to = '';
    applyFilters();
};

const changePage = (page) => {
    if (page >= 1 && page <= lastPage.value) {
        currentPage.value = page;
        loadLogs();
    }
};

// Utilities
const formatDate = (dateString) => {
    if (!dateString) return '';
    let parsedDate;
    if (dateString.endsWith('Z')) {
        parsedDate = new Date(dateString);
    } else {
        parsedDate = new Date(dateString.replace(' ', 'T') + '+08:00');
    }
    return parsedDate.toLocaleDateString('en-US', { 
        timeZone: 'Asia/Manila', 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
};

const formatTime = (dateString) => {
    if (!dateString) return '';
    let parsedDate;
    if (dateString.endsWith('Z')) {
        parsedDate = new Date(dateString);
    } else {
        parsedDate = new Date(dateString.replace(' ', 'T') + '+08:00');
    }
    return parsedDate.toLocaleTimeString('en-US', { 
        timeZone: 'Asia/Manila', 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    });
};

const formatSubjectType = (type) => {
    if (!type) return '';
    // E.g., App\Models\User -> User
    const parts = type.split('\\');
    return parts[parts.length - 1];
};

const getActionBadgeClass = (event) => {
    switch (event?.toLowerCase()) {
        case 'created': return 'bg-emerald-500 text-white border-emerald-600';
        case 'updated': return 'bg-blue-50 text-blue-600 border-blue-200';
        case 'deleted': return 'bg-orange-50 text-orange-600 border-orange-200';
        case 'force_deleted': return 'bg-red-50 text-red-600 border-red-200';
        case 'restored': return 'bg-purple-50 text-purple-600 border-purple-200';
        case 'login': return 'bg-green-50 text-green-700 border-green-300';
        case 'logout': return 'bg-amber-50 text-amber-700 border-amber-300';
        default: return 'bg-slate-100 text-slate-600 border-slate-200';
    }
};

const formatEventName = (event) => {
    if (event === 'deleted') return 'soft deleted';
    if (event === 'force_deleted') return 'force deleted';
    return event;
};

const formatDetailedDescription = (log) => {
    const rawEvent = log.event || log.description;
    const event = formatEventName(rawEvent);
    let subjectType = formatSubjectType(log.subject_type);
    
    // Extract attributes from properties if subject is missing or deleted
    const attrs = log.properties?.attributes || log.properties?.old || {};

    // Explicit / Custom Log Messages
    if (rawEvent !== 'created' && rawEvent !== 'updated' && rawEvent !== 'deleted' && rawEvent !== 'restored' && rawEvent !== 'force_deleted') {
        let msg = log.description;
        if (log.subject || Object.keys(attrs).length > 0) {
             const unit = log.subject?.plate_number || log.subject?.unit_id || attrs.plate_number || attrs.unit_id || log.subject?.id || attrs.id;
             const fname = log.subject?.first_name || attrs.first_name;
             const lname = log.subject?.last_name || attrs.last_name;
             
             if (subjectType === 'Vehicle') msg += ` (Vehicle: ${unit})`;
             else if (subjectType === 'User' && fname) msg += ` (User: ${fname} ${lname})`;
             else if (subjectType === 'Trip' || subjectType === 'Shift') {
                 const driverStr = log.subject?.shift?.driver || log.subject?.driver;
                 const dName = driverStr ? `${driverStr.first_name} ${driverStr.last_name}` : 'Unknown Driver';
                 const vInfo = log.subject?.shift?.vehicle || log.subject?.vehicle;
                 const vName = vInfo ? (vInfo.unit_id || vInfo.plate_number) : 'Unknown Vehicle';
                 msg += ` (Vehicle: ${vName}, Driver: ${dName})`;
             }
             else if (subjectType === 'AmbulanceInspection') {
                 const vInfo = log.subject?.vehicle;
                 const vName = vInfo ? (vInfo.unit_id || vInfo.plate_number) : 'Unknown Vehicle';
                 msg += ` (Vehicle: ${vName})`;
             }
        }
        return msg;
    }

    // Default CRUD actions translation
    let details = `${subjectType} ${event}`;
    
    if (log.subject || Object.keys(attrs).length > 0) {
        if (subjectType === 'Shift' || subjectType === 'VehicleShift') {
            const dName = log.subject?.driver ? `${log.subject.driver.first_name} ${log.subject.driver.last_name}` : attrs.driver_name;
            const dId = log.subject?.driver_id || attrs.driver_id;
            const driverName = dName ? dName : (dId ? `Driver ID ${dId}` : 'Unknown');
            
            const vName = log.subject?.vehicle ? (log.subject.vehicle.unit_id || log.subject.vehicle.plate_number) : (attrs.unit_id || attrs.plate_number);
            const vId = log.subject?.vehicle_id || attrs.vehicle_id;
            const vehicleInfo = vName ? vName : (vId ? `Vehicle ID ${vId}` : 'Unknown');
            
            details = `Schedule ${event} for ${driverName}, Vehicle ${vehicleInfo}`;
        } else if (subjectType === 'Vehicle') {
            const vInfo = log.subject?.unit_id || log.subject?.plate_number || attrs.unit_id || attrs.plate_number || log.subject?.id || attrs.id;
            details = `Vehicle ${vInfo} was ${event}`;
        } else if (subjectType === 'User') {
            const fname = log.subject?.first_name || attrs.first_name;
            const lname = log.subject?.last_name || attrs.last_name;
            const userName = fname ? `${fname} ${lname}` : (log.subject?.id || attrs.id);
            details = `User account for ${userName} was ${event}`;
        } else if (subjectType === 'Incident') {
            const vInfo = log.subject?.vehicle_id || attrs.vehicle_id;
            const issueType = log.subject?.issue_type || attrs.issue_type || 'Unknown';
            const id = log.subject?.id || attrs.id || log.subject_id;
            details = `Incident report #${id} (${issueType}) was ${event} for Vehicle ID ${vInfo}`;
        } else if (subjectType === 'Checklist') {
            const tripInfo = log.subject?.trip_id || attrs.trip_id;
            const typeInfo = log.subject?.type || attrs.type || 'Unknown';
            details = `Checklist (${typeInfo}) was ${event} for Trip ID ${tripInfo}`;
        } else if (subjectType === 'TripLog') {
            const phase = log.subject?.phase || attrs.phase;
            details = `Driver tapped to record location (Trip Phase ${phase})`;
        } else if (subjectType === 'AmbulanceInspection') {
            const vInfo = log.subject?.vehicle ? (log.subject.vehicle.unit_id || log.subject.vehicle.plate_number) : (attrs.vehicle_id || 'Unknown');
            const inspType = log.subject?.inspection_type || attrs.inspection_type || 'Routine';
            details = `Ambulance Inspection (${inspType}) was ${event} for Vehicle ${vInfo}`;
        } else {
            const id = log.subject?.id || attrs.id || log.subject_id;
            details = `${subjectType} (ID: ${id}) was ${event}`;
        }
    } else if (log.subject_type) {
         details = `${subjectType} (ID: ${log.subject_id}) was ${event}`;
    }

    // Append properties changes if it's an update
    if (event === 'updated' && log.properties && log.properties.attributes && log.properties.old) {
        const changedKeys = Object.keys(log.properties.attributes).filter(k => k !== 'updated_at');
        if (changedKeys.length > 0) {
            const changes = changedKeys.map(k => {
                let oldVal = log.properties.old[k];
                let newVal = log.properties.attributes[k];
                // Handle booleans/nulls better
                if (oldVal === null) oldVal = 'empty';
                if (newVal === null) newVal = 'empty';
                if (oldVal === true) oldVal = 'Yes';
                if (oldVal === false) oldVal = 'No';
                if (newVal === true) newVal = 'Yes';
                if (newVal === false) newVal = 'No';
                return `[${k}] from '${oldVal}' to '${newVal}'`;
            }).join(', ');
            details += `. Modifications: ${changes}`;
        }
    }

    return details;
};

onMounted(() => {
    loadLogs();
});
</script>

<style scoped>
/* Custom scrollbar for properties box to match dark theme */
.group\/props div::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.group\/props div::-webkit-scrollbar-track {
    background: transparent;
}
.group\/props div::-webkit-scrollbar-thumb {
    background: rgba(45, 212, 191, 0.3); /* teal-400 with opacity */
    border-radius: 4px;
}
.group\/props div::-webkit-scrollbar-thumb:hover {
    background: rgba(45, 212, 191, 0.6);
}
</style>
