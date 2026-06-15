<template>
  <div class="h-full flex flex-col space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between bg-white p-4 rounded-xl border border-slate-200 shadow-sm shrink-0">
      <div class="flex items-center space-x-3 mb-4 sm:mb-0">
        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center text-red-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
        </div>
        <div>
          <h1 class="text-xl font-extrabold text-slate-800">Archived Records</h1>
          <p class="text-sm text-slate-500 font-medium">Manage and restore deleted data.</p>
        </div>
      </div>
      <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
        <select v-model="perPage" class="py-2 px-3 border border-slate-200 rounded-lg text-sm bg-slate-50 font-bold text-slate-600 outline-none focus:ring-2 focus:ring-red-500">
          <option :value="10">10 Rows</option>
          <option :value="25">25 Rows</option>
          <option :value="50">50 Rows</option>
          <option :value="100">100 Rows</option>
        </select>
        <div class="relative w-full sm:w-64">
          <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
          <input v-model="searchQuery" type="text" placeholder="Search..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex justify-between items-center bg-white rounded-xl border border-slate-200 shadow-sm shrink-0 overflow-x-auto custom-scrollbar p-1">
      <div class="flex space-x-1">
        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
          :class="[
            'px-4 py-2 text-sm font-bold rounded-lg transition-all whitespace-nowrap',
            activeTab === tab.id ? 'bg-red-50 text-red-700 shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'
          ]">
          {{ tab.name }}
        </button>
      </div>
    </div>

    <!-- Data Table -->
    <div class="flex-1 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col relative">
      <div v-if="archiveStore.loading" class="absolute inset-0 bg-white/50 backdrop-blur-sm z-10 flex items-center justify-center">
        <svg class="animate-spin w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
      </div>

      <div class="overflow-x-auto flex-1 custom-scrollbar">
        <table class="w-full text-left text-sm whitespace-nowrap">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 sticky top-0 z-0">
            <tr>
              <th class="px-6 py-3 w-12 text-center">
                <input type="checkbox" @change="toggleAll" :checked="allSelected" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
              </th>
              <th class="px-6 py-3 font-bold">ID</th>
              <th class="px-6 py-3 font-bold">Identifier / Name</th>
              <th class="px-6 py-3 font-bold">Deleted At</th>
              <th class="px-6 py-3 font-bold text-right">Actions</th>
            </tr>
          </thead>
          <TableSkeleton v-if="isLoading" :columns="5" :rows="5" />
          <tbody v-else class="divide-y divide-slate-100">
            <tr v-for="record in archiveStore.archivedRecords" :key="record.id" class="hover:bg-slate-50/50 transition-colors group">
              <td class="px-6 py-3 text-center">
                <input type="checkbox" v-model="selectedRecords" :value="record.id" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
              </td>
              <td class="px-6 py-3 font-medium text-slate-700">#{{ record.id }}</td>
              <td class="px-6 py-3 text-slate-600 font-medium">{{ getRecordName(record) }}</td>
              <td class="px-6 py-3 text-slate-500">
                {{ record.deleted_at ? new Date(record.deleted_at).toLocaleString() : 'Unknown' }}
              </td>
              <td class="px-6 py-3 text-right space-x-1 sm:space-x-2 whitespace-nowrap">
                <button @click="viewRecord(record)" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors align-middle" title="View Full Details">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </button>
                <button @click="confirmRestore(record)" class="p-1.5 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded transition-colors align-middle" title="Restore Record">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                </button>
                <button @click="confirmForceDelete(record)" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors align-middle" title="Permanently Delete">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
              </td>
            </tr>
            <tr v-if="!archiveStore.loading && archiveStore.archivedRecords.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                <div class="flex flex-col items-center">
                  <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  <p class="font-bold text-slate-600">No archived {{ activeTabName }} found.</p>
                  <p class="text-xs mt-1">Deleted items will appear here.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer (Mockup/Simple) -->
      <div v-if="archiveStore.pagination.total > 0" class="bg-slate-50 border-t border-slate-200 p-4 flex items-center justify-between shrink-0">
        <span class="text-xs font-bold text-slate-500">
          Showing page {{ archiveStore.pagination.currentPage }} of {{ archiveStore.pagination.lastPage }} ({{ archiveStore.pagination.total }} total)
        </span>
        <div class="flex space-x-2">
          <button @click="changePage(archiveStore.pagination.currentPage - 1)" :disabled="archiveStore.pagination.currentPage === 1" class="px-3 py-1 bg-white border border-slate-200 text-slate-600 rounded hover:bg-slate-50 disabled:opacity-50 font-bold text-xs transition-colors">Prev</button>
          <button @click="changePage(archiveStore.pagination.currentPage + 1)" :disabled="archiveStore.pagination.currentPage === archiveStore.pagination.lastPage" class="px-3 py-1 bg-white border border-slate-200 text-slate-600 rounded hover:bg-slate-50 disabled:opacity-50 font-bold text-xs transition-colors">Next</button>
        </div>
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
      <div v-if="selectedRecords.length > 0" class="absolute bottom-6 left-1/2 -translate-x-1/2 bg-slate-800 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-4 z-50">
        <span class="text-sm font-bold bg-white/20 px-2 py-0.5 rounded">{{ selectedRecords.length }}</span>
        <span class="text-sm font-medium tracking-wide">records selected</span>
        <div class="h-5 w-px bg-slate-600 mx-1"></div>
        <button @click="confirmBatchRestore" class="text-sm font-bold text-teal-400 hover:text-teal-300 transition-colors flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
          Restore
        </button>
        <button @click="confirmBatchForceDelete" class="text-sm font-bold text-red-400 hover:text-red-300 transition-colors flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
          Force Delete
        </button>
      </div>
    </transition>

    <!-- Delete Confirm Modal -->
    <ConfirmModal
      :show="showDeleteConfirm"
      title="Permanently Delete Record?"
      message="This action cannot be undone. The record will be permanently removed from the database."
      type="danger"
      confirm-text="Yes, Force Delete"
      :is-loading="isDeleting"
      @confirm="executeForceDelete"
      @cancel="showDeleteConfirm = false"
    />

    <!-- Restore Confirm Modal -->
    <ConfirmModal
      :show="showRestoreConfirm"
      title="Restore Record(s)?"
      message="Are you sure you want to restore the selected record(s)? They will become active again."
      type="success"
      confirm-text="Yes, Restore"
      :is-loading="isRestoring"
      @confirm="executeRestore"
      @cancel="showRestoreConfirm = false"
    />

    <!-- View Record Modal -->
    <div v-if="showViewModal" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showViewModal = false"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col animate-fade-in-up">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-2xl">
          <div>
            <h3 class="text-lg font-bold text-slate-800">Record Details</h3>
            <p class="text-xs text-slate-500 font-medium">Viewing full database object for deletion context</p>
          </div>
          <button @click="showViewModal = false" class="text-slate-400 hover:text-slate-600 bg-white p-1 rounded-full border border-slate-200 shadow-sm transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1 bg-white">
          <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
            <table class="w-full text-sm text-left">
              <tbody class="divide-y divide-slate-100">
                <tr v-for="(val, key) in recordToView" :key="key" class="hover:bg-slate-50/50">
                  <td class="px-4 py-2.5 font-bold text-slate-600 w-1/3 align-top bg-slate-50/50 border-r border-slate-100">{{ formatKey(key) }}</td>
                  <td class="px-4 py-2.5 text-slate-800 whitespace-pre-wrap break-all sm:break-words font-mono text-xs">
                    <span v-if="val === null || val === ''" class="text-slate-400 italic">null</span>
                    <span v-else>{{ val }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl flex justify-end space-x-2">
          <button @click="showViewModal = false" class="px-5 py-2.5 bg-slate-200 text-slate-700 font-bold rounded-lg hover:bg-slate-300 transition-colors shadow-sm">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useArchiveStore } from '../stores/archiveStore';
import { useToastStore } from '../stores/toast';
import ConfirmModal from '../components/modals/ConfirmModal.vue';
import TableSkeleton from '../components/ui/TableSkeleton.vue';

const archiveStore = useArchiveStore();
const toast = useToastStore();

const tabs = [
  { id: 'users', name: 'Users' },
  { id: 'vehicles', name: 'Vehicles' },
  { id: 'trips', name: 'Trips' },
  { id: 'incidents', name: 'Incidents' },
  { id: 'checklists', name: 'Checklists' }
];

const activeTab = ref(tabs[0].id);
const searchQuery = ref('');
const perPage = ref(10);

const activeTabName = computed(() => {
  const tab = tabs.find(t => t.id === activeTab.value);
  return tab ? tab.name.toLowerCase() : 'records';
});

const selectedRecords = ref([]);

const allSelected = computed(() => {
  if (archiveStore.archivedRecords.length === 0) return false;
  return selectedRecords.value.length === archiveStore.archivedRecords.length;
});

const toggleAll = (e) => {
  if (e.target.checked) {
    selectedRecords.value = archiveStore.archivedRecords.map(r => r.id);
  } else {
    selectedRecords.value = [];
  }
};

// Modal States
const showDeleteConfirm = ref(false);
const isDeleting = ref(false);
const recordToDelete = ref(null);

const showRestoreConfirm = ref(false);
const isRestoring = ref(false);
const recordToRestore = ref(null);

const showViewModal = ref(false);
const recordToView = ref(null);
const isLoading = ref(false);

const fetchRecords = async () => {
  isLoading.value = true;
  try {
    await archiveStore.fetchArchivedRecords(activeTab.value, {
      page: archiveStore.pagination.currentPage,
      search: searchQuery.value,
      per_page: perPage.value
    });
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchRecords();
});

watch(activeTab, () => {
  archiveStore.pagination.currentPage = 1;
  selectedRecords.value = [];
  fetchRecords();
});

watch(() => archiveStore.archivedRecords, () => {
  selectedRecords.value = [];
});

// Simple debounce for search
let searchTimeout;
watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    archiveStore.pagination.currentPage = 1;
    fetchRecords();
  }, 300);
});

watch(perPage, () => {
  archiveStore.pagination.currentPage = 1;
  fetchRecords();
});

const changePage = (page) => {
  if (page >= 1 && page <= archiveStore.pagination.lastPage) {
    archiveStore.pagination.currentPage = page;
    fetchRecords();
  }
};

const getRecordName = (record) => {
  if (activeTab.value === 'trips') return `Trip for Shift #${record.shift_id}`;
  if (activeTab.value === 'checklists') return `${record.type ? record.type.replace('_', ' ') : 'Checklist'} (Trip #${record.trip_id || 'Unknown'})`;
  if (activeTab.value === 'incidents') return `${record.type || 'Incident'} (${record.severity || 'Unknown Severity'})`;
  
  if (record.unit_id) return `${record.unit_id} - ${record.plate_number || 'No Plate'}`;
  if (record.first_name) return `${record.first_name} ${record.last_name || ''}`;
  
  return record.name || record.title || record.email || record.license_plate || record.reference_number || `Record #${record.id}`;
};

const viewRecord = (record) => {
  recordToView.value = record;
  showViewModal.value = true;
};

const formatKey = (key) => {
  return String(key).replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const confirmRestore = (record) => {
  recordToRestore.value = record;
  showRestoreConfirm.value = true;
};

const confirmBatchRestore = () => {
  recordToRestore.value = null;
  showRestoreConfirm.value = true;
};

const executeRestore = async () => {
  isRestoring.value = true;
  if (recordToRestore.value) {
    const success = await archiveStore.restoreRecord(activeTab.value, recordToRestore.value.id);
    if (success) {
      toast.show(`Record #${recordToRestore.value.id} has been restored successfully.`, 'success');
      recordToRestore.value = null;
    } else {
      toast.show(archiveStore.error || 'Failed to restore record.', 'error');
    }
  } else if (selectedRecords.value.length > 0) {
    const count = selectedRecords.value.length;
    const success = await archiveStore.batchRestore(activeTab.value, selectedRecords.value);
    if (success) {
      toast.show(`Successfully restored ${count} records.`, 'success');
      selectedRecords.value = [];
    } else {
      toast.show(archiveStore.error || 'Failed to restore records.', 'error');
    }
  }
  isRestoring.value = false;
  showRestoreConfirm.value = false;
};

const confirmForceDelete = (record) => {
  recordToDelete.value = record;
  showDeleteConfirm.value = true;
};

const confirmBatchForceDelete = () => {
  recordToDelete.value = null;
  showDeleteConfirm.value = true;
};

const executeForceDelete = async () => {
  isDeleting.value = true;
  if (recordToDelete.value) {
    const success = await archiveStore.forceDeleteRecord(activeTab.value, recordToDelete.value.id);
    if (success) {
      toast.show(`Record #${recordToDelete.value.id} was permanently deleted.`, 'success');
      recordToDelete.value = null;
    } else {
      toast.show(archiveStore.error || 'Failed to delete record.', 'error');
    }
  } else if (selectedRecords.value.length > 0) {
    const count = selectedRecords.value.length;
    const success = await archiveStore.batchForceDelete(activeTab.value, selectedRecords.value);
    if (success) {
      toast.show(`${count} records permanently deleted.`, 'success');
      selectedRecords.value = [];
    } else {
      toast.show(archiveStore.error || 'Failed to delete records.', 'error');
    }
  }
  isDeleting.value = false;
  showDeleteConfirm.value = false;
};
</script>
