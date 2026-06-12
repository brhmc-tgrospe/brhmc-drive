<template>
  <div class="max-w-7xl mx-auto h-full flex flex-col gap-4 sm:gap-6 pt-2 sm:pt-4 relative">
    
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center shrink-0 gap-3 sm:gap-0">
      <div>
        <h2 class="text-lg sm:text-2xl font-extrabold text-slate-800 tracking-tight">Incident Reports</h2>
        <p class="text-[11px] sm:text-sm text-slate-500 font-medium mt-0.5 sm:mt-1">Manage, acknowledge, and resolve mid-shift emergencies</p>
      </div>
      
      <button v-if="hasPermission('incident.generate_report')" @click="isPrintModalOpen = true" class="flex items-center justify-center gap-2 px-4 py-2 sm:py-2.5 bg-slate-800 text-white hover:bg-slate-700 text-xs sm:text-sm font-bold rounded-lg shadow-md transition-all">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
        Generate PDF Report
      </button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex-1 overflow-hidden flex flex-col">
      <div class="bg-slate-50 p-3 sm:p-4 border-b border-slate-200 flex flex-col lg:flex-row gap-3 shrink-0 lg:items-center lg:justify-between">
        
        <div class="flex flex-col lg:flex-row gap-3 w-full lg:w-auto lg:items-center">
          <div class="relative w-full lg:w-64 shrink-0">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" v-model="searchQuery" @input="debounceSearch" placeholder="Search vehicle, issue, or driver..." class="pl-9 pr-3 py-1.5 sm:py-2 w-full border border-slate-200 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 outline-none transition-shadow bg-white shadow-sm" />
          </div>

          <div class="flex flex-row items-center justify-between lg:justify-start gap-2 sm:gap-3 w-full lg:w-auto">
            <div class="flex items-center bg-white border border-slate-200 rounded-lg overflow-hidden shrink-0 focus-within:ring-2 focus-within:ring-teal-500 shadow-sm">
              <input type="date" v-model="dateFrom" class="py-1.5 px-1.5 sm:px-2 w-full max-w-[110px] sm:max-w-[130px] text-[10px] sm:text-xs bg-transparent focus:outline-none text-slate-600" />
              <span class="text-slate-300 text-[9px] sm:text-[10px] font-bold uppercase px-0.5 sm:px-1 shrink-0">to</span>
              <input type="date" v-model="dateTo" class="py-1.5 px-1.5 sm:px-2 w-full max-w-[110px] sm:max-w-[130px] text-[10px] sm:text-xs bg-transparent focus:outline-none text-slate-600" />
              <button v-if="dateFrom || dateTo" @click="dateFrom = ''; dateTo = ''" class="px-1.5 sm:px-2 py-1.5 text-slate-400 hover:text-red-500 bg-slate-50 border-l border-slate-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>

            <select v-model="filterStatus" class="py-1.5 sm:py-2 px-2 border border-slate-200 rounded-lg text-[10px] sm:text-xs bg-white font-bold text-slate-600 outline-none shadow-sm">
              <option value="">All Statuses</option>
              <option value="PENDING">Pending</option>
              <option value="ACKNOWLEDGED">Acknowledged</option>
              <option value="RESOLVED">Resolved</option>
            </select>

            <select v-model="perPage" class="py-1.5 sm:py-2 px-2 border border-slate-200 rounded-lg text-[10px] sm:text-xs bg-white font-bold text-slate-600 outline-none shadow-sm">
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
          </div>
        </div>

        <div class="flex flex-row justify-center lg:justify-end items-center w-full lg:w-auto pt-1 lg:pt-0 shrink-0">
          <div class="flex items-center space-x-2 shrink-0">
            <button @click="currentPage--" :disabled="currentPage === 1" class="p-1.5 rounded border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-30">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <span class="text-[11px] sm:text-xs font-bold text-slate-600 px-3">Page {{ currentPage }} of {{ incidentStore.lastPage || 1 }}</span>
            <button @click="currentPage++" :disabled="currentPage >= incidentStore.lastPage" class="p-1.5 rounded border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-30">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto flex-1 custom-scrollbar relative">
        <div v-if="incidentStore.loading" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] z-20 flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
        </div>

        <table class="w-full text-left text-xs sm:text-sm text-slate-600 border-collapse">
          <thead class="bg-white border-b border-slate-200 sticky top-0 z-10 text-[9px] sm:text-xs text-slate-500 uppercase">
            <tr>
              <th v-if="hasPermission('incident.delete')" class="py-2 px-3 sm:py-3 sm:px-4 w-10">
                <input 
                  type="checkbox" 
                  class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 transition-all cursor-pointer"
                  :checked="isAllSelected"
                  :indeterminate="isPartiallySelected"
                  @change="toggleSelectAll"
                >
              </th>
              
              <th @click="toggleSort('id')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  ID & Date
                  <svg v-if="sortBy === 'id'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 -4 -4 -4"></path></svg>
                </div>
              </th>
              
              <th class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap">Reporter</th>
              
              <th @click="toggleSort('vehicle_unit')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Vehicle / Target
                  <svg v-if="sortBy === 'vehicle_unit'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 -4 -4 -4"></path></svg>
                </div>
              </th>
              
              <th @click="toggleSort('issue_type')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Nature of Issue
                  <svg v-if="sortBy === 'issue_type'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 -4 -4 -4"></path></svg>
                </div>
              </th>
              
              <th @click="toggleSort('acknowledged_at')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Acknowledged At
                  <svg v-if="sortBy === 'acknowledged_at'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 -4 -4 -4"></path></svg>
                </div>
              </th>
              
              <th @click="toggleSort('status')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Status
                  <svg v-if="sortBy === 'status'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 -4 -4 -4"></path></svg>
                </div>
              </th>
              
              <th class="py-2 px-3 sm:py-3 sm:px-4 font-bold text-right whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="!incidentStore.incidents?.length && !incidentStore.loading">
              <td colspan="7" class="py-8 text-center text-slate-500 font-medium">No incidents found.</td>
            </tr>
            <tr v-for="inc in incidentStore.incidents" :key="inc.id" class="hover:bg-slate-50 transition-colors" :class="{'bg-teal-50/30': selectedItems.includes(inc.id)}">
              
              <td v-if="hasPermission('incident.delete')" class="py-2 px-3 sm:py-3 sm:px-4">
                 <input 
                  type="checkbox" 
                  class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 transition-all cursor-pointer"
                  :value="inc.id"
                  v-model="selectedItems"
                >
              </td>

              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap">
                <div class="font-bold text-slate-800">INC-{{ inc.id }}</div>
                <div class="text-[10px] text-slate-500">{{ formatDateTime(inc.created_at) }}</div>
              </td>
              
              <td class="py-2 px-3 sm:py-3 sm:px-4 text-slate-700 whitespace-nowrap font-bold">
                  {{ inc.reporter_name }}
              </td>

              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap">
                <div class="font-bold text-slate-800">{{ inc.vehicle_unit }}</div>
                <div class="text-[9px] uppercase font-black" :class="inc.incident_target === 'Vehicle' ? 'text-orange-600' : 'text-blue-600'">Target: {{ inc.incident_target }}</div>
              </td>
              
              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap">
                <div class="font-bold text-slate-800">{{ inc.issue_type }}</div>
              </td>

              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap">
                <div v-if="inc.acknowledged_at">
                    <div class="font-bold text-slate-700">{{ formatDateTime(inc.acknowledged_at) }}</div>
                    <div class="text-[10px] text-slate-500">By: {{ inc.dispatcher_name }}</div>
                </div>
                <div v-else class="text-[10px] italic text-slate-400">Not yet acknowledged</div>
              </td>

              <td class="py-2 px-3 sm:py-3 sm:px-4">
                <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 text-[9px] sm:text-[10px] font-bold rounded-full uppercase tracking-wider"
                      :class="{
                          'bg-red-50 text-red-700 border border-red-200': inc.status === 'PENDING',
                          'bg-amber-50 text-amber-700 border border-amber-200': inc.status === 'ACKNOWLEDGED',
                          'bg-green-50 text-green-700 border border-green-200': inc.status === 'RESOLVED'
                      }">
                  {{ inc.status }}
                </span>
              </td>

              <td class="py-2 px-3 sm:py-3 sm:px-4 text-right space-x-1 whitespace-nowrap">
                
                <button v-if="inc.status === 'PENDING' && hasPermission('incident.acknowledge')" @click="openAcknowledgeModal(inc)" class="p-1 sm:p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded transition-colors" title="Acknowledge">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </button>

                <button v-if="inc.status === 'ACKNOWLEDGED' && hasPermission('incident.edit')" @click="openResolveModal(inc)" class="p-1 sm:p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded transition-colors" title="Mark as Resolved">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </button>

                <button @click="openViewModal(inc)" class="p-1 sm:p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors" title="View Evidence">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </button>
                
                <button v-if="inc.status !== 'RESOLVED' && hasPermission('incident.edit')" @click="openEditModal(inc)" class="p-1 sm:p-1.5 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded transition-colors" title="Edit Remarks">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </button>

                <button v-if="hasPermission('incident.delete')" @click="openDeleteModal(inc)" class="p-1 sm:p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Delete">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- FLOATING BATCH ACTIONS BAR -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform translate-y-10 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform translate-y-10 opacity-0"
    >
      <div v-if="selectedItems.length > 0" class="absolute bottom-6 left-1/2 -translate-x-1/2 bg-slate-800 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-4 z-50">
        <span class="text-sm font-bold bg-white/20 px-2 py-0.5 rounded">{{ selectedItems.length }}</span>
        <span class="text-sm font-medium tracking-wide">incidents selected</span>
        <div class="h-5 w-px bg-slate-600 mx-1"></div>
        <button @click="confirmBatchDelete" class="text-sm font-bold text-red-400 hover:text-red-300 transition-colors flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
          Delete Selection
        </button>
      </div>
    </transition>

<!-- ========================================== -->
    <!-- CRITICAL FIX: Removed the "|| {}" hack. -->
    <!-- This allows the modals to properly catch Null objects! -->
    <!-- ========================================== -->
    <IncidentView 
      :show="isViewModalOpen" 
      :incident="targetIncident" 
      @close="isViewModalOpen = false" 
    />
    
    <IncidentEdit 
      :show="isEditModalOpen" 
      :incident="targetIncident" 
      @close="isEditModalOpen = false" 
      @saved="handleEditSaved" 
    />
    
    <IncidentAcknowledge 
      :show="isAcknowledgeModalOpen" 
      :incident="targetIncident" 
      @close="isAcknowledgeModalOpen = false" 
      @acknowledged="handleAcknowledge" 
    />

    <IncidentDelete
      :show="isDeleteModalOpen"
      :incident="targetIncident"
      @close="isDeleteModalOpen = false"
      @deleted="handleDeleted"
    />
    
    <IncidentBatchReport 
      v-if="isPrintModalOpen"
      :show="isPrintModalOpen" 
      :incidents="incidentStore.incidents || []" 
      @close="isPrintModalOpen = false" 
    />

    <div v-if="isResolveModalOpen" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="isResolveModalOpen = false"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-5 sm:p-6 text-center animate-fade-in-up border border-slate-200">
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-green-500">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">Resolve Incident?</h3>
        <p class="text-sm text-slate-500 mb-6">Are you sure this incident has been completely handled and resolved?</p>
        <div class="flex space-x-3">
          <button @click="isResolveModalOpen = false" class="flex-1 py-2.5 text-sm bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
          <button @click="executeResolve" class="flex-1 py-2.5 text-sm bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition-colors shadow-md shadow-green-200">Yes, Resolve</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useIncidentStore } from '../../stores/incident';
import { useToastStore } from '../../stores/toast';
import { useAuthStore } from '../../stores/auth';

import IncidentView from './IncidentView.vue';
import IncidentEdit from './IncidentEdit.vue';
import IncidentAcknowledge from './IncidentAcknowledge.vue';
import IncidentDelete from './IncidentDelete.vue';
import IncidentBatchReport from './IncidentBatchReport.vue';
import { useACL } from '../../composables/useACL';

const { hasPermission } = useACL();
const incidentStore = useIncidentStore();
const toastStore = useToastStore();

// SORTING STATE
const sortBy = ref('incidents.id');
const sortDir = ref('desc');

// MULTI-SELECT STATE
const selectedItems = ref([]);

const isAllSelected = computed(() => {
    return incidentStore.incidents?.length > 0 && selectedItems.value.length === incidentStore.incidents.length;
});

const isPartiallySelected = computed(() => {
    return selectedItems.value.length > 0 && selectedItems.value.length < incidentStore.incidents?.length;
});

const toggleSelectAll = (event) => {
    if (event.target.checked) {
        selectedItems.value = incidentStore.incidents.map(c => c.id);
    } else {
        selectedItems.value = [];
    }
};

const toggleSort = (column) => {
    if (sortBy.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDir.value = 'asc';
    }
    loadIncidents();
};

const searchQuery = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const filterStatus = ref('');
const perPage = ref(10);
const currentPage = ref(1);

const loadIncidents = async () => {
    await incidentStore.fetchIncidents({
        search: searchQuery.value, 
        start_date: dateFrom.value,
        end_date: dateTo.value,
        status: filterStatus.value,
        per_page: perPage.value, 
        page: currentPage.value,
        sort_by: sortBy.value,
        sort_dir: sortDir.value
    });
    selectedItems.value = [];
};

onMounted(() => loadIncidents());

let searchTimeout = null;
const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { currentPage.value = 1; loadIncidents(); }, 300);
};

watch([dateFrom, dateTo, filterStatus, perPage, currentPage], () => loadIncidents());

const targetIncident = ref(null);
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isAcknowledgeModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isResolveModalOpen = ref(false);
const isPrintModalOpen = ref(false);

const openViewModal = (inc) => { targetIncident.value = inc; isViewModalOpen.value = true; };
const openEditModal = (inc) => { targetIncident.value = inc; isEditModalOpen.value = true; };
const openAcknowledgeModal = (inc) => { targetIncident.value = inc; isAcknowledgeModalOpen.value = true; };
const openDeleteModal = (inc) => { targetIncident.value = inc; isDeleteModalOpen.value = true; };
const openResolveModal = (inc) => { targetIncident.value = inc; isResolveModalOpen.value = true; };

const confirmBatchDelete = () => {
    targetIncident.value = null; // null means we are batch deleting
    isDeleteModalOpen.value = true;
};

const handleEditSaved = async (payload) => {
    try {
        await incidentStore.updateIncident(payload.id, payload);
        toastStore.show('Incident updated successfully.', 'success');
        isEditModalOpen.value = false;
        loadIncidents();
    } catch (e) {
        toastStore.show('Failed to update incident.', 'error');
    }
};

const handleAcknowledge = async (payload) => {
    try {
        await incidentStore.acknowledgeIncident(payload.id, payload);
        toastStore.show('Incident officially acknowledged.', 'success');
        isAcknowledgeModalOpen.value = false;
        loadIncidents();
    } catch (e) {
        toastStore.show('Failed to acknowledge incident.', 'error');
    }
};

const executeResolve = async () => {
    if (!targetIncident.value) return;
    try {
        await incidentStore.resolveIncident(targetIncident.value.id);
        toastStore.show('Incident marked as resolved.', 'success');
        isResolveModalOpen.value = false;
        loadIncidents();
    } catch (e) {
        toastStore.show('Failed to resolve incident.', 'error');
    }
};

const handleDeleted = async () => {
    try {
        if (targetIncident.value) {
            await incidentStore.deleteIncident(targetIncident.value.id);
            toastStore.show('Incident deleted.', 'success');
        } else {
            await incidentStore.deleteIncident(selectedItems.value.join(','));
            toastStore.show(`${selectedItems.value.length} incidents deleted.`, 'success');
        }
        isDeleteModalOpen.value = false;
        selectedItems.value = [];
        loadIncidents();
    } catch (e) {
        toastStore.show('Failed to delete incident(s).', 'error');
    }
};

const formatDateTime = (dateString) => {
    if (!dateString) return '';
    const utcDate = dateString.endsWith('Z') ? dateString : dateString.replace(' ', 'T') + '+08:00';
    return new Date(utcDate).toLocaleString('en-US', { timeZone: 'Asia/Manila', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
};
</script>