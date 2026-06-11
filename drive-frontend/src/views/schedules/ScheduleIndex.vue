<template>
  <div class="max-w-7xl mx-auto h-full flex flex-col gap-4 sm:gap-6 pt-2 sm:pt-4">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center shrink-0 gap-3 sm:gap-0">
      <div>
        <h2 class="text-lg sm:text-2xl font-extrabold text-slate-800 tracking-tight">Shift Schedules</h2>
        <p class="text-[11px] sm:text-sm text-slate-500 font-medium mt-0.5 sm:mt-1">Manage driver dispatch times and vehicle assignments</p>
      </div>
      
      <button v-if="hasPermission('schedule.add')" @click="openAddModal" class="flex items-center justify-center px-4 py-2 sm:py-2.5 bg-teal-600 text-white font-bold text-xs sm:text-sm rounded-lg hover:bg-teal-700 transition-colors shadow-md shadow-teal-200 w-full sm:w-auto">
        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        Schedule New Shift
      </button>
    </div>

    <!-- Schedules Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex-1 overflow-hidden flex flex-col">
      
      <!-- STRICT 3-TIER HIERARCHY (Mobile) -> SINGLE ROW (Desktop) -->
      <div class="bg-slate-50 p-3 sm:p-4 border-b border-slate-200 flex flex-col lg:flex-row gap-3 shrink-0 lg:items-center lg:justify-between">
        
        <!-- LEFT/MIDDLE GROUP ON DESKTOP: Search & Filters -->
        <div class="flex flex-col lg:flex-row gap-3 w-full lg:w-auto lg:items-center">
          
          <!-- TOP TIER (Mobile) / LEFT (Desktop): The Search Bar -->
          <div class="relative w-full lg:w-64 shrink-0">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" v-model="searchQuery" @input="debounceSearch" placeholder="Search driver or vehicle..." class="pl-9 pr-3 py-1.5 sm:py-2 w-full border border-slate-200 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 outline-none transition-shadow bg-white shadow-sm" />
          </div>

          <!-- ROW 1 (Mobile) / MIDDLE (Desktop): Date Range & Row Selector -->
          <div class="flex flex-row items-center justify-between lg:justify-start gap-2 sm:gap-3 w-full lg:w-auto">
            
            <!-- UNIFIED DATE RANGE FILTER -->
            <div class="flex items-center bg-white border border-slate-200 rounded-lg overflow-hidden shrink-0 focus-within:ring-2 focus-within:ring-teal-500 transition-shadow shadow-sm">
              <input type="date" v-model="dateFrom" class="py-1.5 px-1.5 sm:px-2 w-full max-w-[110px] sm:max-w-[130px] text-[10px] sm:text-xs bg-transparent focus:outline-none text-slate-600" />
              <span class="text-slate-300 text-[9px] sm:text-[10px] font-bold uppercase px-0.5 sm:px-1 shrink-0">to</span>
              <input type="date" v-model="dateTo" class="py-1.5 px-1.5 sm:px-2 w-full max-w-[110px] sm:max-w-[130px] text-[10px] sm:text-xs bg-transparent focus:outline-none text-slate-600" />
              
              <button v-if="dateFrom || dateTo" @click="dateFrom = ''; dateTo = ''" class="px-1.5 sm:px-2 py-1.5 text-slate-400 hover:text-red-500 transition-colors bg-slate-50 border-l border-slate-200 shrink-0" title="Clear Dates">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>

            <!-- ROW SELECTOR -->
            <select v-model="perPage" class="py-1.5 px-2 border border-slate-200 rounded-lg text-[10px] sm:text-xs bg-white font-bold text-slate-600 outline-none shrink-0 cursor-pointer shadow-sm">
              <option :value="5">5 Rows</option>
              <option :value="10">10 Rows</option>
              <option :value="25">25 Rows</option>
              <option :value="50">50 Rows</option>
            </select>

          </div>
        </div>

        <!-- ROW 2 (Mobile) / RIGHT ALIGNED (Desktop): Pagination -->
        <div class="flex flex-row justify-center lg:justify-end items-center w-full lg:w-auto pt-1 lg:pt-0 shrink-0">
          <div class="flex items-center space-x-2 shrink-0">
            <button @click="currentPage--" :disabled="currentPage === 1" class="p-1.5 rounded border border-slate-200 disabled:opacity-30 bg-white hover:bg-slate-50 transition-colors cursor-pointer shadow-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <span class="text-[11px] sm:text-xs font-bold text-slate-600 whitespace-nowrap tracking-wide px-3">Page {{ currentPage }} of {{ shiftStore.lastPage || 1 }}</span>
            <button @click="currentPage++" :disabled="currentPage >= shiftStore.lastPage" class="p-1.5 rounded border border-slate-200 disabled:opacity-30 bg-white hover:bg-slate-50 transition-colors cursor-pointer shadow-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
          </div>
        </div>

      </div>

      <!-- TABLE -->
      <div class="overflow-x-auto flex-1 custom-scrollbar">
        <table class="w-full text-left text-xs sm:text-sm text-slate-600 border-collapse">
          <thead class="bg-white border-b border-slate-200 sticky top-0 z-10 text-[9px] sm:text-xs text-slate-500 uppercase">
            <tr>
              <!-- MASTER CHECKBOX -->
              <th v-if="hasPermission('schedule.delete')" class="py-2 px-3 sm:py-3 sm:px-4 w-10">
                <input 
                  type="checkbox" 
                  class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 transition-all cursor-pointer"
                  :checked="isAllSelected"
                  :indeterminate="isPartiallySelected"
                  @change="toggleSelectAll"
                >
              </th>

              <!-- SORTABLE: Driver -->
              <th @click="toggleSort('driver')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Driver
                  <svg v-if="sortBy === 'driver'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 4 -4 -4"></path></svg>
                </div>
              </th>
              
              <!-- SORTABLE: Assigned Vehicle -->
              <th @click="toggleSort('vehicle')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Assigned Vehicle
                  <svg v-if="sortBy === 'vehicle'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 -4 -4 -4"></path></svg>
                </div>
              </th>
              
              <!-- SORTABLE: Start Time -->
              <th @click="toggleSort('start_time')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Start Time
                  <svg v-if="sortBy === 'start_time'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 4 -4 -4"></path></svg>
                </div>
              </th>

              <th class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap">End Time</th>
              
              <!-- SORTABLE: Status -->
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
            <tr v-if="shiftStore.loading">
              <td colspan="6" class="py-12 text-center text-slate-400 font-bold text-xs sm:text-sm animate-pulse">Loading schedules...</td>
            </tr>
            <tr v-else-if="!shiftStore.shifts?.length">
              <td colspan="7" class="py-8 text-center text-slate-500 font-medium whitespace-nowrap text-xs sm:text-sm">No upcoming shifts scheduled.</td>
            </tr>
            <tr v-else v-for="shift in shiftStore.shifts" :key="shift.id" class="hover:bg-slate-50 transition-colors" :class="{'bg-teal-50/30': selectedItems.includes(shift.id)}">
              <td v-if="hasPermission('schedule.delete')" class="py-2 px-3 sm:py-3 sm:px-4">
                 <input 
                  type="checkbox" 
                  class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 transition-all cursor-pointer"
                  :value="shift.id"
                  v-model="selectedItems"
                >
              </td>

              <td class="py-2 px-3 sm:py-3 sm:px-4 font-bold text-slate-800 whitespace-nowrap text-[11px] sm:text-sm">
                {{ shift.driver?.first_name }} {{ shift.driver?.last_name }}
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap text-[11px] sm:text-sm">
                <span class="font-bold text-slate-700">{{ shift.vehicle?.unit_id }}</span>
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap text-[11px] sm:text-sm font-medium text-slate-700">
                {{ formatDateTime(shift.start_time) }}
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap text-[11px] sm:text-sm font-medium text-slate-500">
                {{ formatDateTime(shift.end_time) }}
                <span class="ml-1 text-[9px] bg-slate-100 px-1.5 rounded">{{ shift.shift_duration }}H</span>
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4">
                <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 text-[9px] sm:text-[10px] font-bold rounded-full uppercase tracking-wider"
                      :class="{
                          'bg-slate-100 text-slate-600 border border-slate-200': shift.status === 'SCHEDULED',
                          'bg-blue-50 text-blue-700 border border-blue-200': shift.status === 'ACTIVE',
                          'bg-teal-50 text-teal-700 border border-teal-200': shift.status === 'COMPLETED'
                      }">
                  {{ shift.status }}
                </span>
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 text-right space-x-1 sm:space-x-2 whitespace-nowrap">
                <button @click="openViewModal(shift)" class="p-1 sm:p-1.5 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded transition-colors" title="View Shift">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </button>
                <button v-if="hasPermission('schedule.edit')" @click="openEditModal(shift)" :disabled="shift.status === 'COMPLETED' || shift.status === 'ACTIVE'" class="p-1 sm:p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors disabled:opacity-30 disabled:hover:bg-transparent" title="Edit Shift">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </button>
                <button @click="openDeleteModal(shift)" v-if="hasPermission('schedule.delete')" class="p-1 sm:p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Delete Shift">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
                <button @click="openForceStartModal(shift)" v-if="hasPermission('execute_shifts') && shift.status === 'SCHEDULED'" class="p-1 sm:p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded transition-colors" title="Force Start Shift (Override)">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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
        <span class="text-sm font-medium tracking-wide">shifts selected</span>
        <div class="h-5 w-px bg-slate-600 mx-1"></div>
        <button @click="confirmBatchDelete" class="text-sm font-bold text-red-400 hover:text-red-300 transition-colors flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
          Delete Selection
        </button>
      </div>
    </transition>

    <!-- MODULAR COMPONENTS -->
    <ScheduleAdd 
      v-if="isAddModalOpen" 
      :drivers="users" 
      :vehicles="vehicles" 
      @close="isAddModalOpen = false" 
      @saved="handleScheduleChange" 
    />
    <ScheduleEdit 
      v-if="isEditModalOpen" 
      :shift="targetShift" 
      :drivers="users" 
      :vehicles="vehicles" 
      @close="isEditModalOpen = false" 
      @saved="handleScheduleChange" 
    />
    <ScheduleView 
      v-if="isViewModalOpen" 
      :shift="targetShift" 
      @close="isViewModalOpen = false" 
    />
    <ScheduleDelete 
      v-if="isDeleteModalOpen" 
      :shift="targetShift" 
      :batch-ids="selectedItems"
      @close="isDeleteModalOpen = false" 
      @deleted="handleScheduleChange" 
    />

    <!-- Force Start Modal -->
    <div v-if="isForceStartModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="isForceStartModalOpen = false"></div>
      <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-sm p-5 sm:p-6 flex flex-col animate-fade-in-up">
        <h3 class="text-lg font-extrabold text-slate-800 mb-2 border-b border-slate-100 pb-2 text-red-600">Override Shift</h3>
        <p class="text-sm text-slate-600 mb-4">You are forcefully starting the shift for {{ targetShift?.driver?.first_name }} {{ targetShift?.driver?.last_name }}. Please provide a reason.</p>
        <textarea v-model="forceStartRemark" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 mb-4" placeholder="Reason for override..." required></textarea>
        <div class="flex gap-3 mt-auto">
          <button @click="isForceStartModalOpen = false" class="flex-1 py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
          <button @click="executeForceStart" :disabled="!forceStartRemark || isForceStarting" class="flex-1 py-2 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 transition-colors shadow-md shadow-red-200 disabled:opacity-50">
            <span v-if="isForceStarting">Processing...</span>
            <span v-else>Force Start</span>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '../../axios';
import { useShiftStore } from '../../stores/shift';
import { useToastStore } from '../../stores/toast';
import { useFleetStore } from '../../stores/fleet';
import { useAuthStore } from '../../stores/auth';
import { useACL } from '../../composables/useACL';

import ScheduleAdd from './ScheduleAdd.vue';
import ScheduleEdit from './ScheduleEdit.vue';
import ScheduleView from './ScheduleView.vue';
import ScheduleDelete from './ScheduleDelete.vue';

const { hasPermission } = useACL();
const shiftStore = useShiftStore();
const toastStore = useToastStore();
const fleetStore = useFleetStore();
const authStore = useAuthStore();

const selectedItems = ref([]);

const isAllSelected = computed(() => {
    return shiftStore.shifts?.length > 0 && selectedItems.value.length === shiftStore.shifts.length;
});

const isPartiallySelected = computed(() => {
    return selectedItems.value.length > 0 && selectedItems.value.length < shiftStore.shifts?.length;
});

const toggleSelectAll = (event) => {
    if (event.target.checked) {
        selectedItems.value = shiftStore.shifts.map(s => s.id);
    } else {
        selectedItems.value = [];
    }
};

const users = ref([]);
const vehicles = ref([]);

const searchQuery = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const perPage = ref(10);
const sortBy = ref('start_time');
const sortDir = ref('desc');

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isForceStartModalOpen = ref(false);
const forceStartRemark = ref('');
const isForceStarting = ref(false);
const targetShift = ref(null);

const currentPage = ref(1);

const loadSchedules = async () => {
    try {
        await shiftStore.fetchShifts({
            search: searchQuery.value, 
            start_date: dateFrom.value,
            end_date: dateTo.value,
            per_page: perPage.value, 
            page: currentPage.value, 
            sort_by: sortBy.value, 
            sort_dir: sortDir.value
        });
        currentPage.value = shiftStore.currentPage;
    } catch (error) {
        toastStore.show('Failed to load scheduling data.', 'error');
    }
};

const loadDropdownData = async () => {
    try {
        const t = new Date().getTime();
        const [usersRes, vehiclesRes] = await Promise.all([
            api.get(`/api/users?all=true&t=${t}`), 
            api.get(`/api/vehicles?all=true&t=${t}`)
        ]);
        
        users.value = usersRes.data.data ? usersRes.data.data : usersRes.data;
        vehicles.value = vehiclesRes.data.data ? vehiclesRes.data.data : vehiclesRes.data;
    } catch (error) {
        console.error("Failed to load dropdown data");
    }
};

onMounted(() => {
    loadSchedules();
    loadDropdownData();
});

let searchTimeout = null;
const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { 
        currentPage.value = 1; 
        loadSchedules(); 
    }, 300);
};

watch([dateFrom, dateTo, perPage, currentPage], () => {
    loadSchedules();
});

const toggleSort = (column) => {
    if (sortBy.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDir.value = 'asc';
    }
    loadSchedules();
};

const openAddModal = () => {
    loadDropdownData(); 
    isAddModalOpen.value = true;
};

const openEditModal = (shift) => { 
    loadDropdownData(); 
    targetShift.value = shift; 
    isEditModalOpen.value = true; 
};

const openViewModal = (shift) => { targetShift.value = shift; isViewModalOpen.value = true; };
const openDeleteModal = (shift) => { targetShift.value = shift; isDeleteModalOpen.value = true; };
const confirmBatchDelete = () => { targetShift.value = null; isDeleteModalOpen.value = true; };
const openForceStartModal = (shift) => { targetShift.value = shift; forceStartRemark.value = ''; isForceStartModalOpen.value = true; };

const executeForceStart = async () => {
    isForceStarting.value = true;
    try {
        await api.post(`/api/shifts/${targetShift.value.id}/start`, { remark: forceStartRemark.value });
        toastStore.show('Shift forcefully started successfully.', 'success');
        isForceStartModalOpen.value = false;
        handleScheduleChange();
    } catch (error) {
        toastStore.show(error.response?.data?.message || 'Failed to force start shift.', 'error');
    } finally {
        isForceStarting.value = false;
    }
};

const handleScheduleChange = async () => {
    isAddModalOpen.value = false;
    isEditModalOpen.value = false;
    isDeleteModalOpen.value = false;
    selectedItems.value = [];
    
    await loadSchedules();
    await loadDropdownData();
    await fleetStore.fetchFleet(); 
};

// ==========================================
// CRITICAL FIX: STRIP 'Z' TO PREVENT DOUBLE-TZ SHIFT
// ==========================================
const formatDateTime = (dateString) => {
    if (!dateString) return '';
    
    // Convert safe spaces to 'T' for iOS/Safari parsing
    const safeString = dateString.replace(' ', 'T');
    
    // Strip the trailing 'Z' if it exists. 
    // This tells JS to treat the string as the EXACT LOCAL TIME it was originally saved as,
    // bypassing the automatic +8 hour timezone conversion.
    const cleanDate = safeString.endsWith('Z') ? safeString.slice(0, -1) : safeString;
    
    return new Date(cleanDate).toLocaleString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        hour: 'numeric', 
        minute: '2-digit', 
        hour12: true 
    });
};
</script>