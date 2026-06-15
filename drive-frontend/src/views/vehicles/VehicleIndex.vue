<template>
  <div class="max-w-[1600px] mx-auto h-full flex flex-col gap-4 sm:gap-6 pt-2 sm:pt-4">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center shrink-0 gap-3 sm:gap-0">
      <div>
        <h2 class="text-lg sm:text-2xl font-extrabold text-slate-800 tracking-tight">Vehicles Masterlist</h2>
        <p class="text-[11px] sm:text-sm text-slate-500 font-medium mt-0.5 sm:mt-1">Manage the complete hospital fleet</p>
      </div>
      
      <div class="flex items-center gap-2 w-full sm:w-auto">
        <!-- ADDED: Quick Refresh Button -->
        <button @click="refreshFleet" title="Sync Latest Data" class="flex items-center justify-center p-2 sm:p-2.5 bg-white border border-slate-200 text-slate-500 rounded-lg hover:bg-slate-50 hover:text-teal-600 transition-colors shadow-sm active:scale-95">
          <svg :class="{'animate-spin': isRefreshing}" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        </button>

        <button v-if="hasPermission('vehicle.add')" @click="openAddModal" class="flex-1 sm:flex-none flex items-center justify-center px-4 py-2 sm:py-2.5 bg-teal-600 text-white font-bold text-xs sm:text-sm rounded-lg hover:bg-teal-700 transition-colors shadow-md shadow-teal-200">
          <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
          Add New Vehicle
        </button>
      </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex-1 overflow-hidden flex flex-col">
      
      <!-- SEARCH & FILTER BAR -->
      <div class="bg-slate-50 p-3 border-b border-slate-200 flex flex-col sm:flex-row gap-3 items-center justify-between shrink-0">
        <div class="flex items-center gap-3 w-full sm:w-auto">
          <div class="relative w-full sm:w-64">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" v-model="searchQuery" placeholder="Search vehicles..." class="pl-9 pr-3 py-1.5 w-full border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 outline-none transition-shadow" />
          </div>
          
          <select v-model="perPage" class="py-1.5 px-2 border border-slate-200 rounded-lg text-xs bg-white font-bold text-slate-600 outline-none">
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
        </div>

        <div class="flex items-center space-x-2">
          <button @click="currentPage--" :disabled="currentPage === 1" class="p-1.5 rounded border border-slate-200 disabled:opacity-30 bg-white hover:bg-slate-50 transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
          </button>
          <span class="text-xs font-bold text-slate-600">Page {{ currentPage }} of {{ totalPages || 1 }}</span>
          <button @click="currentPage++" :disabled="currentPage >= totalPages" class="p-1.5 rounded border border-slate-200 disabled:opacity-30 bg-white hover:bg-slate-50 transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
          </button>
        </div>
      </div>

      <!-- TABLE -->
      <div class="overflow-x-auto flex-1 relative">


        <table class="w-full text-left text-xs sm:text-sm text-slate-600 border-collapse">
          <thead class="bg-white border-b border-slate-200 sticky top-0 z-10 text-[9px] sm:text-xs text-slate-500 uppercase">
            <tr>
              <!-- MASTER CHECKBOX -->
              <th v-if="hasPermission('vehicle.delete')" class="py-2 px-3 sm:py-3 sm:px-4 w-10">
                <input 
                  type="checkbox" 
                  class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 transition-all cursor-pointer"
                  :checked="isAllSelected"
                  :indeterminate="isPartiallySelected"
                  @change="toggleSelectAll"
                >
              </th>
              <th @click="toggleSort('unit_id')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Unit ID
                  <svg v-if="sortBy === 'unit_id'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 4 -4 -4"></path></svg>
                </div>
              </th>
              <th @click="toggleSort('plate_number')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Plate Number
                  <svg v-if="sortBy === 'plate_number'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 4 -4 -4"></path></svg>
                </div>
              </th>

              <th @click="toggleSort('base_location')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Base Location
                  <svg v-if="sortBy === 'base_location'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 4 -4 -4"></path></svg>
                </div>
              </th>
              
              <th class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap">Odometer</th>
              <th @click="toggleSort('status')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Status
                  <svg v-if="sortBy === 'status'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 4 -4 -4"></path></svg>
                </div>
              </th>

              <th class="py-2 px-3 sm:py-3 sm:px-4 font-bold text-right whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <TableSkeleton v-if="isLoading" :columns="7" :rows="5" />
          <tbody v-else class="divide-y divide-slate-100">
            <tr v-if="!paginatedVehicles?.length">
              <td colspan="7" class="py-8 text-center text-slate-500 font-medium whitespace-nowrap text-xs sm:text-sm">No vehicles found.</td>
            </tr>
            <tr v-for="vehicle in paginatedVehicles" :key="vehicle.id" class="hover:bg-slate-50 transition-colors" :class="{'bg-teal-50/30': selectedItems.includes(vehicle.id)}">
              <td v-if="hasPermission('vehicle.delete')" class="py-2 px-3 sm:py-3 sm:px-4">
                 <input 
                  type="checkbox" 
                  class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 transition-all cursor-pointer"
                  :value="vehicle.id"
                  v-model="selectedItems"
                >
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 font-bold text-slate-800 whitespace-nowrap text-[11px] sm:text-sm">{{ vehicle.unit_id }}</td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 font-semibold whitespace-nowrap text-[11px] sm:text-sm">{{ vehicle.plate_number }}</td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap">
                <div class="font-bold text-slate-700 text-[11px] sm:text-sm">{{ vehicle.make_model }}</div>
                <div class="text-[9px] sm:text-xs text-slate-500">{{ vehicle.vehicle_type }}</div>
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap text-[11px] sm:text-sm">{{ vehicle.base_location }}</td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap text-[11px] sm:text-sm">{{ vehicle.odometer.toLocaleString() }} km</td>
              <td class="py-2 px-3 sm:py-3 sm:px-4">
                  <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 text-[9px] sm:text-[10px] font-bold rounded-full uppercase tracking-wider"
                      :class="{
                         'bg-teal-50 text-teal-700 border border-teal-200': vehicle.status === 'READY',
                         'bg-purple-50 text-purple-700 border border-purple-200': vehicle.status === 'SCHEDULED',
                         'bg-blue-50 text-blue-700 border border-blue-200': vehicle.status === 'IN_USE',
                         'bg-orange-50 text-orange-700 border border-orange-200': vehicle.status === 'MAINTENANCE',
                         'bg-red-50 text-red-700 border border-red-200': vehicle.status === 'BREAKDOWN'
                      }">
                  {{ vehicle.status.replace('_', ' ') }}
                </span>
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 text-right space-x-1 sm:space-x-2 whitespace-nowrap">
                <button @click="openViewModal(vehicle)" class="p-1 sm:p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors relative group" title="View Details">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </button>
                <template v-if="hasPermission('vehicle.edit') || hasPermission('vehicle.delete')">
                  <button v-if="hasPermission('vehicle.edit')" @click="openEditModal(vehicle)" class="p-1 sm:p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded transition-colors relative group" title="Edit Vehicle">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                  </button>
                  <button v-if="hasPermission('vehicle.delete')" @click="openDeleteModal(vehicle)" class="p-1 sm:p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors relative group" title="Delete Vehicle">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </template>
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
        <span class="text-sm font-medium tracking-wide">vehicles selected</span>
        <div class="h-5 w-px bg-slate-600 mx-1"></div>
        <button @click="confirmBatchDelete" class="text-sm font-bold text-red-400 hover:text-red-300 transition-colors flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
          Delete Selection
        </button>
      </div>
    </transition>

    <!-- MODULAR COMPONENTS -->
    <VehicleAdd v-if="isAddModalOpen" 
    @close="isAddModalOpen = false" 
    @saved="handleSaveAction" />

    <VehicleEdit v-if="isEditModalOpen" 
    :vehicle="targetVehicle" 
    @close="isEditModalOpen = false"
    @saved="handleSaveAction" />

    <VehicleView v-if="isViewModalOpen" 
    :vehicle="targetVehicle" 
    @close="isViewModalOpen = false" 
    @updated="handleSaveAction" />

    <VehicleDelete v-if="isDeleteModalOpen" 
    :vehicle="targetVehicle" 
    :batch-ids="selectedItems"
    @close="isDeleteModalOpen = false" 
    @deleted="handleSaveAction" />

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import TableSkeleton from '../../components/ui/TableSkeleton.vue';
import { useFleetStore } from '../../stores/fleet';
import { useToastStore } from '../../stores/toast';

// Import Modular Components
import VehicleAdd from './VehicleAdd.vue';
import VehicleEdit from './VehicleEdit.vue';
import VehicleView from './VehicleView.vue';
import VehicleDelete from './VehicleDelete.vue';
import { useACL } from '../../composables/useACL';

const { hasPermission } = useACL();
const toastStore = useToastStore(); 
const fleetStore = useFleetStore();

// --- CLIENT-SIDE TABLE STATE ---
const searchQuery = ref('');
const perPage = ref(10);
const currentPage = ref(1);
const sortBy = ref('id');
const sortDir = ref('desc');
const isRefreshing = ref(false); // UI Loader State
const isLoading = ref(false);

const selectedItems = ref([]);

const isAllSelected = computed(() => {
    return paginatedVehicles.value.length > 0 && selectedItems.value.length === paginatedVehicles.value.length;
});

const isPartiallySelected = computed(() => {
    return selectedItems.value.length > 0 && selectedItems.value.length < paginatedVehicles.value.length;
});

const toggleSelectAll = (event) => {
    if (event.target.checked) {
        selectedItems.value = paginatedVehicles.value.map(v => v.id);
    } else {
        selectedItems.value = [];
    }
};

onMounted(async () => {
    // CRITICAL FIX: Always fetch fresh data when the masterlist is mounted!
    // This ensures that status changes made in the Action Center are immediately visible.
    isRefreshing.value = true;
    isLoading.value = true;
    try {
        await fleetStore.fetchFleet();
    } finally {
        isRefreshing.value = false;
        isLoading.value = false;
    }
});

const refreshFleet = async () => {
    isRefreshing.value = true;
    isLoading.value = true;
    try {
        await fleetStore.fetchFleet();
        if(toastStore.show) toastStore.show('Fleet data synchronized!', 'success');
    } finally {
        isRefreshing.value = false;
        isLoading.value = false;
    }
};

const toggleSort = (column) => {
    if (sortBy.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDir.value = 'asc';
    }
};

// Filter, Sort, and Paginate computed properties using REAL store data
const filteredAndSortedVehicles = computed(() => {
    let result = [...(fleetStore.vehicles || [])];
    
    // Search
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(v => 
            v.unit_id.toLowerCase().includes(q) ||
            v.plate_number.toLowerCase().includes(q) ||
            v.make_model.toLowerCase().includes(q) ||
            v.base_location.toLowerCase().includes(q)
        );
    }
    
    // Sort
    result.sort((a, b) => {
        let valA = a[sortBy.value];
        let valB = b[sortBy.value];
        
        if (typeof valA === 'string') valA = valA.toLowerCase();
        if (typeof valB === 'string') valB = valB.toLowerCase();
        
        if (valA < valB) return sortDir.value === 'asc' ? -1 : 1;
        if (valA > valB) return sortDir.value === 'asc' ? 1 : -1;
        return 0;
    });
    
    return result;
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredAndSortedVehicles.value.length / perPage.value)));

const paginatedVehicles = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredAndSortedVehicles.value.slice(start, end);
});

// Reset page when search or perPage changes
watch([searchQuery, perPage], () => {
    currentPage.value = 1;
});


// --- ACTIONS ---
const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const targetVehicle = ref(null);

const openAddModal = () => isAddModalOpen.value = true;

const openEditModal = (vehicle) => {
    targetVehicle.value = vehicle;
    isEditModalOpen.value = true;
};

const openViewModal = (vehicle) => {
    targetVehicle.value = vehicle;
    isViewModalOpen.value = true;
};

const openDeleteModal = (vehicle) => {
    targetVehicle.value = vehicle;
    isDeleteModalOpen.value = true;
};

const confirmBatchDelete = () => {
    targetVehicle.value = null;
    isDeleteModalOpen.value = true;
};

const handleSaveAction = async () => {
    // Re-fetch table quietly after an edit/add
    await fleetStore.fetchFleet();
    isAddModalOpen.value = false;
    isEditModalOpen.value = false;
    isDeleteModalOpen.value = false;
    selectedItems.value = [];
};
</script>