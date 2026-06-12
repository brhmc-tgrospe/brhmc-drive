<template>
  <div class="max-w-7xl mx-auto h-full flex flex-col gap-4 sm:gap-6 pt-2 sm:pt-4 relative">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center shrink-0 gap-3 sm:gap-0">
      <div>
        <h2 class="text-lg sm:text-2xl font-extrabold text-slate-800 tracking-tight">Trip Execution Logs</h2>
        <p class="text-[11px] sm:text-sm text-slate-500 font-medium mt-0.5 sm:mt-1">Audit shift performance, timelines, and GPS history</p>
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex-1 overflow-hidden flex flex-col">
      <div class="bg-slate-50 p-3 sm:p-4 border-b border-slate-200 flex flex-col lg:flex-row gap-3 shrink-0 lg:items-center lg:justify-between">
        
        <div class="flex flex-col lg:flex-row gap-3 w-full lg:w-auto lg:items-center">
          <!-- Search -->
          <div class="relative w-full lg:w-64 shrink-0">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" v-model="searchQuery" @input="debounceSearch" placeholder="Search driver or vehicle..." class="pl-9 pr-3 py-1.5 sm:py-2 w-full border border-slate-200 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 outline-none transition-shadow bg-white shadow-sm" />
          </div>

          <div class="flex flex-row items-center justify-between lg:justify-start gap-2 sm:gap-3 w-full lg:w-auto">
            <!-- Date Filter -->
            <div class="flex items-center bg-white border border-slate-200 rounded-lg overflow-hidden shrink-0 focus-within:ring-2 focus-within:ring-teal-500 shadow-sm">
              <input type="date" v-model="dateFrom" class="py-1.5 px-1.5 sm:px-2 w-full max-w-[110px] sm:max-w-[130px] text-[10px] sm:text-xs bg-transparent focus:outline-none text-slate-600" />
              <span class="text-slate-300 text-[9px] sm:text-[10px] font-bold uppercase px-0.5 sm:px-1 shrink-0">to</span>
              <input type="date" v-model="dateTo" class="py-1.5 px-1.5 sm:px-2 w-full max-w-[110px] sm:max-w-[130px] text-[10px] sm:text-xs bg-transparent focus:outline-none text-slate-600" />
              <button v-if="dateFrom || dateTo" @click="dateFrom = ''; dateTo = ''" class="px-1.5 sm:px-2 py-1.5 text-slate-400 hover:text-red-500 bg-slate-50 border-l border-slate-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>

            <!-- Per Page -->
            <select v-model="perPage" class="py-1.5 sm:py-2 px-2 border border-slate-200 rounded-lg text-[10px] sm:text-xs bg-white font-bold text-slate-600 outline-none shadow-sm">
              <option :value="10">10 Rows</option>
              <option :value="25">25 Rows</option>
              <option :value="50">50 Rows</option>
              <option :value="100">100 Rows</option>
            </select>
          </div>
        </div>

        <div class="flex flex-row justify-center lg:justify-end items-center w-full lg:w-auto pt-1 lg:pt-0 shrink-0">
          <div class="flex items-center space-x-2 shrink-0">
            <button @click="currentPage--" :disabled="currentPage === 1" class="p-1.5 rounded border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-30">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <span class="text-[11px] sm:text-xs font-bold text-slate-600 px-3">Page {{ currentPage }} of {{ tripLogStore.lastPage || 1 }}</span>
            <button @click="currentPage++" :disabled="currentPage >= tripLogStore.lastPage" class="p-1.5 rounded border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-30">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto flex-1 custom-scrollbar relative">
        <div v-if="tripLogStore.loading" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] z-20 flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
        </div>

        <table class="w-full text-left text-xs sm:text-sm text-slate-600 border-collapse">
          <thead class="bg-white border-b border-slate-200 sticky top-0 z-10 text-[9px] sm:text-xs text-slate-500 uppercase">
            <tr>
              <!-- MASTER CHECKBOX -->
              <th v-if="hasPermission('trip.delete')" class="py-2 px-3 sm:py-3 sm:px-4 w-10">
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
                  Trip ID
                  <svg v-if="sortBy === 'id'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 -4 -4 -4"></path></svg>
                </div>
              </th>
              <th @click="toggleSort('created_at')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Date Created
                  <svg v-if="sortBy === 'created_at'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 -4 -4 -4"></path></svg>
                </div>
              </th>
              <th class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap">Driver</th>
              <th @click="toggleSort('vehicles.unit_id')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Vehicle
                  <svg v-if="sortBy === 'vehicles.unit_id'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 -4 -4 -4"></path></svg>
                </div>
              </th>
              <th @click="toggleSort('current_phase')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Phase Status
                  <svg v-if="sortBy === 'current_phase'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 -4 -4 -4"></path></svg>
                </div>
              </th>
              <th class="py-2 px-3 sm:py-3 sm:px-4 font-bold text-right whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="!tripLogStore.trips?.length && !tripLogStore.loading">
              <td colspan="7" class="py-8 text-center text-slate-500 font-medium">No trip logs found.</td>
            </tr>
            <tr v-for="trip in tripLogStore.trips" :key="trip.id" class="hover:bg-slate-50 transition-colors" :class="{'bg-teal-50/30': selectedItems.includes(trip.id)}">
              <!-- ROW CHECKBOX -->
              <td v-if="hasPermission('trip.delete')" class="py-2 px-3 sm:py-3 sm:px-4">
                 <input 
                  type="checkbox" 
                  class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 transition-all cursor-pointer"
                  :value="trip.id"
                  v-model="selectedItems"
                >
              </td>

              <td class="py-2 px-3 sm:py-3 sm:px-4 font-bold text-slate-800 whitespace-nowrap">TRP-{{ trip.id }}</td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 text-slate-500 whitespace-nowrap">{{ formatDateTime(trip.created_at) }}</td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 text-slate-700 whitespace-nowrap font-bold">{{ trip.driver_name }}</td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 font-bold text-slate-800 whitespace-nowrap">{{ trip.vehicle_unit }}</td>
              <td class="py-2 px-3 sm:py-3 sm:px-4">
                <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 text-[9px] sm:text-[10px] font-bold rounded-full uppercase tracking-wider"
                      :class="trip.current_phase >= 7 ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-blue-50 text-blue-700 border border-blue-200'">
                  {{ trip.current_phase >= 8 ? 'COMPLETED' : `PHASE ${trip.current_phase}` }}
                </span>
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 text-right space-x-1 whitespace-nowrap">
                <button @click="openViewModal(trip)" class="p-1 sm:p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors" title="View Trip Details">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </button>
                <button v-if="hasPermission('trip.delete')" @click="confirmDelete(trip)" class="p-1 sm:p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Delete Trip Log">
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
        <span class="text-sm font-medium tracking-wide">logs selected</span>
        <div class="h-5 w-px bg-slate-600 mx-1"></div>
        <button @click="confirmBatchDelete" class="text-sm font-bold text-red-400 hover:text-red-300 transition-colors flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
          Delete Selection
        </button>
      </div>
    </transition>

    <!-- VIEWER MODAL -->
    <TripView 
      :show="isViewModalOpen" 
      :tripId="targetTrip?.id" 
      @close="isViewModalOpen = false" 
    />
    
    <!-- REUSABLE DELETE CONFIRMATION MODAL -->
    <div v-if="isDeleteModalOpen" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="isDeleteModalOpen = false"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-5 sm:p-6 text-center animate-fade-in-up">
        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4 text-red-500">
          <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <h3 class="text-lg sm:text-xl font-bold text-slate-800 mb-2">Delete Trip Log(s)?</h3>
        <p class="text-xs sm:text-sm text-slate-500 mb-4 sm:mb-6">
          Are you sure you want to permanently delete 
          <span class="font-bold text-slate-700">{{ deleteCount }}</span> trip log(s)? This will wipe their GPS coordinate history.
        </p>
        <div class="flex space-x-3">
          <button @click="isDeleteModalOpen = false" class="flex-1 py-2 sm:py-2.5 text-xs sm:text-sm bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
          <button @click="executeDelete" :disabled="isDeleting" class="flex-1 py-2 sm:py-2.5 text-xs sm:text-sm bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors shadow-md shadow-red-200 disabled:opacity-50">
            {{ isDeleting ? 'Deleting...' : 'Yes, Delete' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useTripLogStore } from '../../stores/tripLog';
import { useToastStore } from '../../stores/toast';
import { useAuthStore } from '../../stores/auth';

import TripView from './TripView.vue';
import { useACL } from '../../composables/useACL';

const { hasPermission } = useACL();
const tripLogStore = useTripLogStore();
const toastStore = useToastStore();
const authStore = useAuthStore();

const searchQuery = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const perPage = ref(10);
const currentPage = ref(1);
const sortBy = ref('trips.id');
const sortDir = ref('desc');

// MULTI-SELECT STATE
const selectedItems = ref([]);

const isAllSelected = computed(() => {
    return tripLogStore.trips?.length > 0 && selectedItems.value.length === tripLogStore.trips.length;
});

const isPartiallySelected = computed(() => {
    return selectedItems.value.length > 0 && selectedItems.value.length < tripLogStore.trips?.length;
});

const toggleSelectAll = (event) => {
    if (event.target.checked) {
        selectedItems.value = tripLogStore.trips.map(c => c.id);
    } else {
        selectedItems.value = [];
    }
};

const loadTrips = async () => {
    try {
        await tripLogStore.fetchTrips({
            search: searchQuery.value, 
            start_date: dateFrom.value,
            end_date: dateTo.value,
            per_page: perPage.value, 
            page: currentPage.value,
            sort_by: sortBy.value,
            sort_dir: sortDir.value
        });
        selectedItems.value = []; // Reset checkboxes when data changes
    } catch (e) {
        toastStore.show('Failed to fetch trip logs.', 'error');
    }
};

onMounted(() => loadTrips());

let searchTimeout = null;
const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { currentPage.value = 1; loadTrips(); }, 300);
};

const toggleSort = (column) => {
    if (sortBy.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDir.value = 'asc';
    }
    loadTrips();
};

watch([dateFrom, dateTo, perPage, currentPage], () => loadTrips());

const targetTrip = ref(null);
const isViewModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);

const deleteCount = computed(() => targetTrip.value ? 1 : selectedItems.value.length);

const openViewModal = (trip) => { targetTrip.value = trip; isViewModalOpen.value = true; };

const confirmDelete = (trip) => {
    targetTrip.value = trip;
    isDeleteModalOpen.value = true;
};

const confirmBatchDelete = () => {
    targetTrip.value = null; // null means we are batch deleting
    isDeleteModalOpen.value = true;
};

const executeDelete = async () => {
    isDeleting.value = true;
    try {
        if (targetTrip.value) {
            // Single Delete
            await tripLogStore.deleteTripLog(targetTrip.value.id);
            toastStore.show(`Trip TRP-${targetTrip.value.id} deleted.`, 'success');
        } else {
            // Batch Delete
            await tripLogStore.batchDeleteTripLogs(selectedItems.value);
            toastStore.show(`${selectedItems.value.length} trip logs deleted.`, 'success');
        }
        
        isDeleteModalOpen.value = false;
        selectedItems.value = []; // Clear selection
        loadTrips(); // Refresh the table
    } catch (e) {
        console.error("Delete Error:", e);
        toastStore.show(e.response?.data?.message || 'Failed to delete logs. Server rejected.', 'error');
    } finally {
        isDeleting.value = false;
    }
};

const formatDateTime = (dateString) => {
    if (!dateString) return '';
    const utcDate = dateString.endsWith('Z') ? dateString : dateString.replace(' ', 'T') + '+08:00';
    return new Date(utcDate).toLocaleString('en-US', { timeZone: 'Asia/Manila', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
};
</script>