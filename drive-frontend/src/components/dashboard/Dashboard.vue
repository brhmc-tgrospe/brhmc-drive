<template>
  <div class="h-full px-2 sm:px-0 flex flex-col">
    
    <!-- DISPATCHER VIEW -->
    <div v-if="isDeveloperView || hasPermission('dashboard.status_grids') || hasPermission('dashboard.live_map_tracker') || hasPermission('dashboard.live_trip_tracking')" 
         class="max-w-[1600px] mx-auto w-full h-[calc(100vh-6rem)] flex flex-col pt-2 sm:pt-4 pb-4">
      
      <!-- ======================================================== -->
      <!-- CRITICAL FIX: NEW EMERGENCY BANNER (Shows only if pending incidents exist) -->
      <!-- ======================================================== -->
      <div v-if="activeEmergencies.length > 0" class="mb-4 sm:mb-6 bg-red-600 rounded-2xl p-4 sm:p-5 shadow-lg shadow-red-600/30 text-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 animate-pulse shrink-0 border border-red-500">
        <div class="flex items-center gap-3 sm:gap-4">
          <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-full flex items-center justify-center shrink-0 shadow-inner">
            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          </div>
          <div>
            <h3 class="font-black text-lg sm:text-xl tracking-wide drop-shadow-sm">EMERGENCY ACTION REQUIRED</h3>
            <p class="text-xs sm:text-sm font-medium text-red-100">{{ activeEmergencies.length }} unresolved mid-shift incident(s) require your immediate attention.</p>
          </div>
        </div>
        <router-link to="/incidents" class="w-full sm:w-auto text-center px-5 py-2.5 bg-white text-red-600 font-black tracking-wider uppercase rounded-xl shadow-md hover:bg-red-50 hover:shadow-lg transition-all shrink-0 text-xs sm:text-sm whitespace-nowrap active:scale-95">
          View Incident Reports
        </router-link>
      </div>

      <div class="flex flex-col xl:flex-row gap-4 sm:gap-6 flex-1 min-h-0">
        <!-- LEFT WRAPPER -->
        <div class="contents xl:flex xl:flex-1 xl:flex-col gap-4 sm:gap-6 min-w-0">
          
          <div class="contents xl:grid xl:grid-cols-3 gap-4 sm:gap-6 shrink-0">
            <StatsGrid v-if="hasPermission('dashboard.status_grids')" class="order-1 xl:order-none xl:col-span-2 h-full w-full xl:w-auto" />
            <SelectedVehicleCard v-if="hasPermission('dashboard.live_map_tracker')" class="hidden xl:flex w-full h-full min-w-0 shrink-0" :selected="selectedVehicles[selectedVehicles.length - 1] || null" />
          </div>

          <SelectedVehicleCard v-if="hasPermission('dashboard.live_map_tracker')" class="order-4 xl:hidden w-full" :selected="selectedVehicles[selectedVehicles.length - 1] || null" />
          <LiveMap v-if="hasPermission('dashboard.live_map_tracker')" :selectedVehicles="selectedVehicles" class="order-2 xl:order-none xl:flex-1 w-full min-h-[350px] shadow-sm rounded-xl overflow-hidden" />
        </div>

        <!-- RIGHT WRAPPER -->
        <div class="contents xl:flex w-full xl:w-[380px] xl:flex-col gap-4 sm:gap-6 shrink-0">
          <TripTracking 
            v-if="hasPermission('dashboard.live_trip_tracking')"
            class="order-3 xl:order-none xl:flex-1 w-full min-h-[350px] shadow-sm rounded-xl overflow-hidden flex flex-col"
            :vehicles="activeTrackingVehicles" 
            :selectedVehicles="selectedVehicles"
            @select="toggleVehicleSelection" 
          />
          <PendingTurnovers 
            v-if="hasPermission('checklist.sign_turnovers')"
            ref="pendingTurnoversRef"
            class="order-5 xl:order-none w-full shrink-0 h-[280px] xl:h-[220px] shadow-sm rounded-xl overflow-hidden flex flex-col" 
            @review="openSignatureModal" 
          />
        </div>
      </div>
    </div>

    <!-- DRIVER VIEW -->
    <div v-else-if="hasPermission('execute_shifts')" 
         class="max-w-[1600px] mx-auto h-full pt-2 sm:pt-4 flex flex-col gap-4 sm:gap-6">
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 flex-1">
        <div class="w-full max-w-xl mx-auto lg:mx-0 lg:max-w-none">
          <DriverConsole 
            :shift="myUpcomingShift" 
            :loading="isLoadingShift"
            :unlockTime="timeUntilUnlock"
            :isReady="isInspectionReady"
            @start-inspection="openChecklistModal"
          />
        </div>
        <div v-if="myUpcomingShift" class="w-full min-h-[400px] lg:min-h-0 h-full">
           <LiveMap :selectedVehicles="myUpcomingShift ? [myUpcomingShift.vehicle] : []" />
        </div>
      </div>
    </div>

    <!-- FALLBACK VIEW (No Permissions) -->
    <div v-else class="flex-1 flex items-center justify-center p-8 text-center h-full">
      <div class="max-w-md">
        <h2 class="text-2xl font-bold text-slate-800 mb-2">Welcome to DRIVE</h2>
        <p class="text-slate-500">Your account does not have any active dashboard permissions assigned. Please contact your system administrator to request access.</p>
      </div>
    </div>

    <!-- GLOBAL MODAL: Dispatcher Signature -->
    <DispatcherSignatureModal 
      :show="isSignatureModalOpen" 
      :checklist="activeChecklist"
      @close="isSignatureModalOpen = false"
      @approve="handleApprove"
      @reject="handleReject"
      @view-details="viewChecklistReport"
    />
    <ChecklistViewReport 
    :show="isReportModalOpen" 
    :checklistId="activeChecklist?.id"
    @close="isReportModalOpen = false"
    />
    
    <!-- GLOBAL MODAL: Driver Checklist -->
    <ChecklistAdd
      v-if="isDriverChecklistOpen"
      :type="activeChecklistType"
      :shift="myUpcomingShift"
      @close="isDriverChecklistOpen = false"
      @saved="handleDriverChecklistSubmitted"
    />

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useFleetStore } from '../../stores/fleet';
import { useToastStore } from '../../stores/toast';
import { useTripStore } from '../../stores/trip';
import api from '../../axios'; 
import { useACL } from '../../composables/useACL';

import StatsGrid from './StatsGrid.vue';
import SelectedVehicleCard from './SelectedVehicleCard.vue';
import LiveMap from './LiveMap.vue';
import TripTracking from './TripTracking.vue';
import DriverConsole from './DriverConsole.vue';
import PendingTurnovers from './PendingTurnovers.vue';
import DispatcherSignatureModal from '../modals/DispatcherSignatureModal.vue';
import ChecklistAdd from '../../views/checklists/ChecklistAdd.vue'; 
import ChecklistViewReport from '../../views/checklists/ChecklistViewReport.vue';

const isReportModalOpen = ref(false);

const viewChecklistReport = (checklist) => {
    activeChecklist.value = checklist;
    isReportModalOpen.value = true;
};

const authStore = useAuthStore();
const fleetStore = useFleetStore();
const tripStore = useTripStore();
const toastStore = useToastStore();
const { hasPermission } = useACL();

const selectedVehicles = ref([]);
const myUpcomingShift = ref(null);
const isLoadingShift = ref(false);
const timeUntilUnlock = ref('');
const isInspectionReady = ref(false);
let timerInterval = null;

const pendingTurnoversRef = ref(null);
const isSignatureModalOpen = ref(false);
const activeChecklist = ref(null);
const isDriverChecklistOpen = ref(false);
const activeChecklistType = ref('Pre-Trip');

// ==========================================
// CRITICAL FIX: EMERGENCY DASHBOARD BANNER
// ==========================================
const isDeveloperView = computed(() => authStore.user?.role === 'developer');

const activeEmergencies = ref([]);

const fetchActiveEmergencies = async () => {
    if (!hasPermission('incident.view') && !isDeveloperView.value) return;
    try {
        const res = await api.get('/api/emergencies/report', {
            params: { status: 'PENDING', per_page: 10, t: new Date().getTime() }
        });
        activeEmergencies.value = res.data.data || [];
    } catch (e) {
        console.error("Failed to load active emergencies");
    }
};

const openSignatureModal = (checklist) => {
  activeChecklist.value = checklist;
  isSignatureModalOpen.value = true;
};

const handleApprove = async (data) => {
  try {
     await api.post(`/api/checklists/${data.id}/review`, { action: 'APPROVE', signature: data.signature, vehicle_status: data.vehicle_status || null });
     isSignatureModalOpen.value = false;
     toastStore.show(`Checklist #${data.id} reviewed successfully.`, 'success');
     if (pendingTurnoversRef.value) pendingTurnoversRef.value.removeChecklistLocally(data.id);
     await fleetStore.fetchFleet();
  } catch (e) {
     toastStore.show('Failed to approve checklist.', 'error');
  }
};

const handleReject = async (data) => {
  try {
     await api.post(`/api/checklists/${data.id}/review`, { action: 'REJECT', reason: data.reason, signature: data.signature, vehicle_status: data.vehicle_status || null });
     isSignatureModalOpen.value = false;
     toastStore.show(`Checklist #${data.id} rejected. Reason: ${data.reason}`, 'error');
     if (pendingTurnoversRef.value) pendingTurnoversRef.value.removeChecklistLocally(data.id);
     await fleetStore.fetchFleet();
  } catch (e) {
     toastStore.show('Failed to reject checklist.', 'error');
  }
};

const openChecklistModal = (data) => {
    activeChecklistType.value = data.type;
    isDriverChecklistOpen.value = true;
};

const handleDriverChecklistSubmitted = async (payload) => {
    isDriverChecklistOpen.value = false;
    try {
        await tripStore.submitChecklist(payload);
        toastStore.show('Checklist submitted successfully.', 'success');
        await tripStore.fetchMyActiveShift();
    } catch (error) {
        toastStore.show('Failed to submit checklist. Please check your network.', 'error');
    }
};

const activeTrackingVehicles = computed(() => {
    return (fleetStore.vehicles || [])
        .filter(v => ['IN_USE', 'READY', 'SCHEDULED'].includes(v.status))
        .sort((a, b) => {
            const order = { 'IN_USE': 1, 'SCHEDULED': 2, 'READY': 3 };
            return (order[a.status] || 99) - (order[b.status] || 99);
        });
});

const toggleVehicleSelection = (vehicle) => {
    const index = selectedVehicles.value.findIndex(v => v.id === vehicle.id);
    if (index > -1) {
        selectedVehicles.value.splice(index, 1);
    } else {
        selectedVehicles.value.push(vehicle);
    }
};

watch(activeTrackingVehicles, (newV) => {
    if (newV.length > 0 && selectedVehicles.value.length === 0) {
        selectedVehicles.value = [newV[0]];
    }
}, { immediate: true });

const checkDriverShift = async () => {
    if (hasPermission('execute_shifts')) {
        isLoadingShift.value = true;
        try {
            await tripStore.fetchMyActiveShift();
            myUpcomingShift.value = tripStore.activeShift;
            if (myUpcomingShift.value) startTimer();
        } catch (error) {}
        finally { isLoadingShift.value = false; }
    }
};

const startTimer = () => {
    updateTimer(); 
    timerInterval = setInterval(updateTimer, 60000); 
};

const updateTimer = () => {
    if (!myUpcomingShift.value) return;
    if (tripStore.activeTrip && tripStore.activeTrip.current_phase > 0) {
        isInspectionReady.value = true;
        clearInterval(timerInterval);
        return;
    }

    const safeString = myUpcomingShift.value.start_time.replace(' ', 'T');
    const cleanDate = safeString.endsWith('Z') ? safeString.slice(0, -1) : safeString;
    const diffMins = Math.floor((new Date(cleanDate) - new Date()) / 60000);
    
    if (diffMins <= 15) { 
        isInspectionReady.value = true; 
        clearInterval(timerInterval); 
    } else { 
        const h = Math.floor(diffMins / 60); 
        const m = diffMins % 60;
        timeUntilUnlock.value = h > 0 ? `${h}h ${m}m` : `${m}m`;
    }
};

watch(() => authStore.user, (user) => { if(user) checkDriverShift(); }, { deep: true });

onMounted(async () => {
    const isDashboardUser = isDeveloperView.value || hasPermission('dashboard.status_grids') || hasPermission('dashboard.live_map_tracker') || hasPermission('dashboard.live_trip_tracking');

    if (isDashboardUser) {
        await fleetStore.fetchFleet();
        await fetchActiveEmergencies(); // Fetch alerts immediately on load
        
        if (window.Echo) {
            window.Echo.channel('fleet-updates')
                .listen('TripPhaseAdvanced', (e) => {
                    fleetStore.fetchFleet(); 
                })
                .listen('IncidentReported', (e) => {
                    fleetStore.fetchFleet(); 
                    fetchActiveEmergencies(); // Re-fetch alerts to trigger the Red Banner
                    toastStore.show(`EMERGENCY ALERT: ${e.issue_type}`, 'error'); 
                });
        }
    }
    checkDriverShift();
});

onUnmounted(() => { 
    if (timerInterval) clearInterval(timerInterval); 
    if (window.Echo) window.Echo.leave('fleet-updates');
});
</script>