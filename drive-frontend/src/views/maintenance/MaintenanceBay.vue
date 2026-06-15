<template>
  <div class="max-w-[1600px] mx-auto h-full flex flex-col gap-4 sm:gap-6 pt-2 sm:pt-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center shrink-0 gap-3 sm:gap-0">
      <div>
        <h2 class="text-lg sm:text-2xl font-extrabold text-slate-800 tracking-tight">Maintenance Bay</h2>
        <p class="text-[11px] sm:text-sm text-slate-500 font-medium mt-0.5 sm:mt-1">Manage and repair grounded vehicles</p>
      </div>
      
      <div class="flex items-center gap-2 w-full sm:w-auto">
        <div class="relative w-full sm:w-80">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
          <input v-model="searchQuery" type="text" placeholder="Search for Vehicle Name or Plate Number" class="w-full pl-9 pr-4 py-2 text-[11px] sm:text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none shadow-sm transition-shadow">
        </div>
        <button @click="refreshFleet" title="Sync Latest Data" class="flex items-center justify-center p-2 sm:p-2.5 bg-white border border-slate-200 text-slate-500 rounded-lg hover:bg-slate-50 hover:text-teal-600 transition-colors shadow-sm active:scale-95 shrink-0">
          <svg :class="{'animate-spin': isRefreshing}" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        </button>
      </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex-1 overflow-hidden flex flex-col">
      <!-- DASHBOARD AREA -->
      <div class="overflow-y-auto flex-1 relative p-4 sm:p-6 bg-slate-50/50">
        <!-- Loading Overlay -->
        <div v-if="isSaving" class="absolute inset-0 bg-white/60 backdrop-blur-sm z-20 flex items-center justify-center">
            <svg class="animate-spin h-8 w-8 text-teal-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </div>

        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <CardSkeleton v-for="i in 8" :key="i" />
        </div>
        <div v-else-if="!breakdownVehicles?.length" class="flex flex-col items-center justify-center h-full text-slate-500 py-12">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center">
                <svg class="w-16 h-16 text-teal-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-lg font-bold text-slate-700">All Clear</p>
                <p class="text-sm text-slate-400 mt-1">There are no grounded or breakdown vehicles at the moment.</p>
            </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div v-for="vehicle in breakdownVehicles" :key="vehicle.id" class="border border-red-200 bg-white rounded-xl p-4 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
                <div>
                    <div class="flex justify-between items-start mb-4 pl-2">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-800">{{ vehicle.unit_id }}</h3>
                            <p class="text-xs font-bold text-slate-500">{{ vehicle.plate_number }}</p>
                        </div>
                        <span class="px-2 py-1 text-[10px] font-bold rounded-full uppercase tracking-wider bg-red-50 text-red-700 border border-red-200 shadow-sm">
                            {{ vehicle.status }}
                        </span>
                    </div>
                    
                    <div class="space-y-2.5 mb-6 pl-2">
                        <div class="flex items-center justify-between text-sm border-b border-slate-100 pb-2">
                            <span class="text-slate-500 font-medium flex items-center"><svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>Model:</span>
                            <span class="text-slate-800 font-bold text-right">{{ vehicle.make_model }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm border-b border-slate-100 pb-2">
                            <span class="text-slate-500 font-medium flex items-center"><svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>Type:</span>
                            <span class="text-slate-800 font-bold text-right">{{ vehicle.vehicle_type }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm border-b border-slate-100 pb-2">
                            <span class="text-slate-500 font-medium flex items-center"><svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>Base:</span>
                            <span class="text-slate-800 font-bold text-right">{{ vehicle.base_location }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 font-medium flex items-center"><svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Odometer:</span>
                            <span class="text-slate-800 font-bold text-right">{{ vehicle.odometer.toLocaleString() }} km</span>
                        </div>
                    </div>
                </div>

                <div class="pl-2">
                    <button v-if="vehicle.status === 'BREAKDOWN'" @click="startMaintenance(vehicle)" :disabled="isSaving" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs sm:text-sm rounded-lg transition-colors shadow-sm disabled:opacity-50 flex justify-center items-center active:scale-[0.98]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Accept Repair Form
                    </button>
                    <button v-else-if="vehicle.status === 'MAINTENANCE'" @click="logRepairAndRelease(vehicle)" :disabled="isSaving" class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs sm:text-sm rounded-lg transition-colors shadow-sm disabled:opacity-50 flex justify-center items-center active:scale-[0.98]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Log Repair & Release
                    </button>
                </div>
            </div>
        </div>
      </div>
    </div>

    <ConfirmModal
      :show="showConfirmModal"
      :title="confirmTitle"
      :message="confirmMessage"
      :type="confirmType"
      :confirmText="confirmBtnText"
      :isLoading="isSaving"
      @confirm="handleConfirm"
      @cancel="handleCancel"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useFleetStore } from '../../stores/fleet';
import { useToastStore } from '../../stores/toast';
import api from '../../axios';
import ConfirmModal from '../../components/modals/ConfirmModal.vue';
import CardSkeleton from '../../components/ui/CardSkeleton.vue';

const fleetStore = useFleetStore();
const toastStore = useToastStore();

const isLoading = ref(false);
const isRefreshing = ref(false);
const isSaving = ref(false);

onMounted(async () => {
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
        if(toastStore.show) toastStore.show('Maintenance bay data synchronized!', 'success');
    } finally {
        isRefreshing.value = false;
        isLoading.value = false;
    }
};

const searchQuery = ref('');

// Filter only breakdown vehicles and apply search
const breakdownVehicles = computed(() => {
    let vehicles = (fleetStore.vehicles || []).filter(v => v.status === 'BREAKDOWN' || v.status === 'MAINTENANCE');
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        vehicles = vehicles.filter(v => 
            (v.make_model && v.make_model.toLowerCase().includes(query)) ||
            (v.unit_id && v.unit_id.toLowerCase().includes(query)) ||
            (v.plate_number && v.plate_number.toLowerCase().includes(query))
        );
    }
    
    return vehicles;
});

const showConfirmModal = ref(false);
const confirmTitle = ref('');
const confirmMessage = ref('');
const confirmType = ref('primary');
const confirmBtnText = ref('Confirm');
const confirmAction = ref(null);

const openConfirm = (title, message, type, btnText, action) => {
    confirmTitle.value = title;
    confirmMessage.value = message;
    confirmType.value = type;
    confirmBtnText.value = btnText;
    confirmAction.value = action;
    showConfirmModal.value = true;
};

const handleConfirm = async () => {
    if (confirmAction.value) {
        await confirmAction.value();
    }
    showConfirmModal.value = false;
    confirmAction.value = null;
};

const handleCancel = () => {
    if (isSaving.value) return;
    showConfirmModal.value = false;
    confirmAction.value = null;
};

const changeStatus = async (vehicle, newStatus, message) => {
    isSaving.value = true;
    try {
        const payload = {
            unit_id: vehicle.unit_id,
            plate_number: vehicle.plate_number,
            vehicle_type: vehicle.vehicle_type,
            make_model: vehicle.make_model,
            status: newStatus,
            odometer: vehicle.odometer,
            base_location: vehicle.base_location
        };

        await api.put(`/api/vehicles/${vehicle.id}`, payload);
        toastStore.show(message, 'success');
        await fleetStore.fetchFleet();
    } catch (error) {
        toastStore.show(error.response?.data?.message || `Failed to update vehicle to ${newStatus}.`, 'error');
    } finally {
        isSaving.value = false;
    }
};

const startMaintenance = (vehicle) => {
    openConfirm(
        'Accept Repair Form',
        `Confirm receiving maintenance request form for ${vehicle.unit_id}?\n\nThis will tag the vehicle as UNDER MAINTENANCE.`,
        'warning',
        'Start Maintenance',
        () => changeStatus(vehicle, 'MAINTENANCE', `${vehicle.unit_id} is now UNDER MAINTENANCE!`)
    );
};

const logRepairAndRelease = (vehicle) => {
    openConfirm(
        'Log Repair & Release',
        `Confirm repair release document signed for ${vehicle.unit_id}?\n\nThis will return the vehicle back to the active fleet as READY.`,
        'primary',
        'Release to Fleet',
        () => changeStatus(vehicle, 'READY', `${vehicle.unit_id} has been repaired and released to fleet!`)
    );
};
</script>
