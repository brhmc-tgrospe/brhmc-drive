<template>
  <div class="max-w-7xl mx-auto h-full flex flex-col gap-4 sm:gap-6 pt-2 sm:pt-4 relative">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center shrink-0 gap-3 sm:gap-0">
      <div>
        <h2 class="text-lg sm:text-2xl font-extrabold text-slate-800 tracking-tight">Vehicle Checklists</h2>
        <p class="text-[11px] sm:text-sm text-slate-500 font-medium mt-0.5 sm:mt-1">Review and manage vehicle handoff checklists</p>
      </div>
      
      <!-- ACTIONS -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
        <button v-if="hasPermission('checklist.add')" @click="isIssueModalOpen = true" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 sm:py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold rounded-lg shadow-sm hover:shadow transition-all">
          <svg class="w-4 h-4 sm:w-5 sm:h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          Report Issue
        </button>
        <button v-if="hasPermission('checklist.add')" @click="isAddModalOpen = true" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 sm:py-2.5 bg-teal-600 text-white hover:bg-teal-700 text-xs sm:text-sm font-bold rounded-lg shadow-md shadow-teal-200 transition-all">
          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          New Inspection
        </button>
      </div>
    </div>

    <!-- Checklists Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex-1 overflow-hidden flex flex-col">
      
      <!-- SEARCH & FILTER BAR -->
      <div class="bg-slate-50 p-3 border-b border-slate-200 flex flex-col sm:flex-row gap-3 items-center justify-between shrink-0">
        <div class="flex items-center gap-3 w-full sm:w-auto">
          <div class="relative w-full sm:w-64">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" v-model="searchQuery" @input="debounceSearch" placeholder="Search checklists..." class="pl-9 pr-3 py-1.5 w-full border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 outline-none transition-shadow" />
          </div>
          
          <select v-model="perPage" class="py-1.5 px-2 border border-slate-200 rounded-lg text-xs bg-white font-bold text-slate-600 outline-none">
            <option :value="5">5 Rows</option>
            <option :value="10">10 Rows</option>
            <option :value="25">25 Rows</option>
            <option :value="50">50 Rows</option>
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

      <div class="overflow-x-auto flex-1 relative custom-scrollbar">
        <!-- Loading Overlay -->
        <div v-if="checklistStore.loading" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] z-20 flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
        </div>

        <table class="w-full text-left text-xs sm:text-sm text-slate-600 border-collapse">
          <thead class="bg-white border-b border-slate-200 sticky top-0 z-10 text-[9px] sm:text-xs text-slate-500 uppercase">
            <tr>
              <!-- MASTER CHECKBOX -->
              <th v-if="hasPermission('checklist.delete')" class="py-2 px-3 sm:py-3 sm:px-4 w-10">
                <input 
                  type="checkbox" 
                  class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 transition-all cursor-pointer"
                  :checked="isAllSelected"
                  :indeterminate="isPartiallySelected"
                  @change="toggleSelectAll"
                >
              </th>
              
              <th class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap">ID</th>
              
              <th @click="toggleSort('vehicle_unit')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Vehicle
                  <svg v-if="sortBy === 'vehicle_unit'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 -4 -4 -4"></path></svg>
                </div>
              </th>

              <th @click="toggleSort('type')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Type & Date
                  <svg v-if="sortBy === 'type'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 -4 -4 -4"></path></svg>
                </div>
              </th>

              <th class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap">Driver</th>
              
              <th class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap">Reviewed By</th>

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
          <TableSkeleton v-if="isLoading" :columns="8" :rows="5" />
          <tbody v-else class="divide-y divide-slate-100">
            <tr v-if="!checklistStore.checklists?.length && !checklistStore.loading">
              <td colspan="8" class="py-8 text-center text-slate-500 font-medium whitespace-nowrap text-xs sm:text-sm">No checklists found.</td>
            </tr>
            <tr v-for="chk in checklistStore.checklists" :key="chk.id" class="hover:bg-slate-50 transition-colors" :class="{'bg-teal-50/30': selectedItems.includes(chk.id)}">
              <td v-if="hasPermission('checklist.delete')" class="py-2 px-3 sm:py-3 sm:px-4">
                 <input 
                  type="checkbox" 
                  class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 transition-all cursor-pointer"
                  :value="chk.id"
                  v-model="selectedItems"
                >
              </td>
              
              <td class="py-2 px-3 sm:py-3 sm:px-4 font-bold text-slate-800 whitespace-nowrap">#{{ chk.id }}</td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap">
                <div class="font-bold text-slate-800">{{ chk.vehicle_unit }}</div>
                <div class="text-[10px] text-slate-500">{{ chk.vehicle_plate }}</div>
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap">
                <div class="font-bold text-slate-700">{{ chk.type.replace('_', ' ') }}</div>
                <div class="text-[10px] text-slate-500">{{ formatDateTime(chk.date) }}</div>
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 text-slate-700 whitespace-nowrap">{{ chk.outgoing_driver || 'System' }}</td>
              
              <td class="py-2 px-3 sm:py-3 sm:px-4 text-slate-700 whitespace-nowrap">
                  <span v-if="chk.dispatcher_name" class="font-medium">{{ chk.dispatcher_name }}</span>
                  <span v-else class="text-slate-400 italic text-[10px]">Pending</span>
              </td>

              <td class="py-2 px-3 sm:py-3 sm:px-4">
                <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 text-[9px] sm:text-[10px] font-bold rounded-full uppercase tracking-wider"
                      :class="{
                          'bg-amber-50 text-amber-700 border border-amber-200': chk.status === 'PENDING' || chk.status === 'NEEDS_REVIEW',
                          'bg-red-50 text-red-700 border border-red-200': chk.status === 'REJECTED',
                          'bg-teal-50 text-teal-700 border border-teal-200': chk.status === 'APPROVED' || chk.status === 'COMPLETED'
                      }">
                  {{ chk.status.replace('_', ' ') }}
                </span>
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 text-right space-x-1 sm:space-x-2 whitespace-nowrap">
                
                <button v-if="chk.status === 'PENDING' || chk.status === 'NEEDS_REVIEW'" @click="openReviewModal(chk)" class="px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white rounded-lg border border-indigo-100 transition-colors shadow-sm inline-flex items-center align-middle mr-1 sm:mr-2">
                  Review & Sign
                </button>

                <button @click="viewChecklist(chk)" class="p-1 sm:p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors align-middle" title="View Checklist Details">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </button>
                
                <button v-if="hasPermission('checklist.edit')" @click="editChecklist(chk)" class="p-1 sm:p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded transition-colors align-middle" title="Edit Checklist">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </button>

                <button v-if="hasPermission('checklist.delete')" @click="confirmDelete(chk)" class="p-1 sm:p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors align-middle" title="Delete Checklist">
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
        <span class="text-sm font-medium tracking-wide">checklists selected</span>
        <div class="h-5 w-px bg-slate-600 mx-1"></div>
        <button @click="confirmBatchDelete" class="text-sm font-bold text-red-400 hover:text-red-300 transition-colors flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
          Delete Selection
        </button>
      </div>
    </transition>

    <!-- MODAL COMPONENTS -->
    <ChecklistAdd 
      v-if="isAddModalOpen" 
      @close="isAddModalOpen = false" 
      @saved="handleInspectionAdded" 
    />
    <ChecklistIssue 
      v-if="isIssueModalOpen" 
      @close="isIssueModalOpen = false" 
      @reported="handleIssueReported"
      :isDispatcher="true" 
    />

    <ChecklistReviewModal 
      :show="isReviewModalOpen" 
      :checklist="targetChecklist || {}"
      @close="isReviewModalOpen = false"
      @approve="handleApprove"
      @reject="handleReject"
    />
    
    <ChecklistViewReport 
      :show="isReportModalOpen" 
      :checklistId="targetChecklist?.id"
      @close="isReportModalOpen = false"
    />

    <!-- MODULAR DELETE COMPONENT -->
    <ChecklistDelete 
      v-if="isDeleteModalOpen" 
      :checklist="checklistToDelete" 
      :batch-ids="selectedItems" 
      @close="isDeleteModalOpen = false" 
      @deleted="handleDeleted" 
    />

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import TableSkeleton from '../../components/ui/TableSkeleton.vue';
import { useChecklistStore } from '../../stores/checklist';
import { useToastStore } from '../../stores/toast';
import { useAuthStore } from '../../stores/auth';
import api from '../../axios';
import { useACL } from '../../composables/useACL';

import ChecklistReviewModal from './ChecklistReviewModal.vue';
import ChecklistAdd from './ChecklistAdd.vue';
import ChecklistIssue from './ChecklistIssue.vue';
import ChecklistViewReport from './ChecklistViewReport.vue';
import ChecklistDelete from './ChecklistDelete.vue'; // IMPORTED HERE

const checklistStore = useChecklistStore();
const isLoading = ref(false);
const toastStore = useToastStore();
const authStore = useAuthStore();

const { hasPermission } = useACL();

const searchQuery = ref('');
const perPage = ref(10);
const currentPage = ref(1);
const totalPages = ref(1);
const sortBy = ref('id');
const sortDir = ref('desc');

const selectedItems = ref([]);

const isAllSelected = computed(() => {
    return checklistStore.checklists?.length > 0 && selectedItems.value.length === checklistStore.checklists.length;
});

const isPartiallySelected = computed(() => {
    return selectedItems.value.length > 0 && selectedItems.value.length < checklistStore.checklists?.length;
});

const toggleSelectAll = (event) => {
    if (event.target.checked) {
        selectedItems.value = checklistStore.checklists.map(c => c.id);
    } else {
        selectedItems.value = [];
    }
};

const loadChecklists = async () => {
    isLoading.value = true;
    try {
        await checklistStore.fetchChecklists({
            search: searchQuery.value,
            per_page: perPage.value,
            page: currentPage.value,
            sort_by: sortBy.value,
            sort_dir: sortDir.value
        });
        currentPage.value = checklistStore.currentPage;
        totalPages.value = checklistStore.lastPage;
        selectedItems.value = []; 
    } catch (e) {
        toastStore.show('Failed to fetch masterlist data.', 'error');
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    loadChecklists();
});

const toggleSort = (column) => {
    if (sortBy.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDir.value = 'asc';
    }
    loadChecklists();
};

let searchTimeout = null;
const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { 
        currentPage.value = 1; 
        loadChecklists(); 
    }, 300);
};

watch([perPage, currentPage], () => {
    loadChecklists();
});

const isAddModalOpen = ref(false);
const isIssueModalOpen = ref(false);
const isReviewModalOpen = ref(false);
const isReportModalOpen = ref(false);
const targetChecklist = ref(null);

const openReviewModal = (chk) => {
  targetChecklist.value = chk;
  isReviewModalOpen.value = true;
};

const viewChecklist = (chk) => {
  targetChecklist.value = chk;
  isReportModalOpen.value = true;
};

const editChecklist = (chk) => {
    toastStore.show('Direct editing of submitted logs is locked for audit trails.', 'info');
};

const isDeleteModalOpen = ref(false);
const checklistToDelete = ref(null); 

const confirmDelete = (chk) => {
    checklistToDelete.value = chk;
    isDeleteModalOpen.value = true;
};

const confirmBatchDelete = () => {
    checklistToDelete.value = null; 
    isDeleteModalOpen.value = true;
};

// Handle cleanup after component completes deletion
const handleDeleted = () => {
    isDeleteModalOpen.value = false;
    selectedItems.value = []; 
    loadChecklists(); 
};

const handleApprove = async (data) => {
  try {
      await checklistStore.reviewChecklist(data.id, { action: 'APPROVE', signature: data.signature });
      isReviewModalOpen.value = false;
      toastStore.show(`Checklist #${data.id} approved successfully! Vehicle released.`, 'success');
      loadChecklists();
  } catch (e) {
      toastStore.show('Failed to approve checklist. Ensure you have the right permissions.', 'error');
  }
};

const handleReject = async (data) => {
  try {
      await checklistStore.reviewChecklist(data.id, { action: 'REJECT', reason: data.reason, signature: data.signature });
      isReviewModalOpen.value = false;
      toastStore.show(`Vehicle grounded. Checklist #${data.id} rejected.`, 'error');
      loadChecklists();
  } catch (e) {
      toastStore.show('Failed to reject checklist.', 'error');
  }
};

const handleInspectionAdded = async (payload) => {
  try {
      isAddModalOpen.value = false;
      await api.post('/api/checklists', payload);
      toastStore.show('Inspection submitted successfully.', 'success');
      loadChecklists();
  } catch (e) {
      toastStore.show('Failed to submit inspection. Ensure vehicle exists.', 'error');
  }
};

const handleIssueReported = async (payload) => {
  try {
      isIssueModalOpen.value = false;
      await api.post('/api/emergencies/report', payload);
      toastStore.show('Issue reported to dispatch immediately.', 'error');
      loadChecklists(); 
  } catch (e) {
      toastStore.show('Failed to report issue.', 'error');
  }
};

const formatDateTime = (dateString) => {
    if (!dateString) return '';
    const utcDate = dateString.endsWith('Z') ? dateString : dateString.replace(' ', 'T') + '+08:00';
    return new Date(utcDate).toLocaleString('en-US', { 
        timeZone: 'Asia/Manila',
        month: 'short', 
        day: 'numeric', 
        hour: 'numeric', 
        minute: '2-digit', 
        hour12: true 
    });
};
</script>