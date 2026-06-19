<template> 
  <div class="max-w-7xl mx-auto h-full flex flex-col gap-4 sm:gap-6 pt-2 sm:pt-4 relative">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center shrink-0 gap-3 sm:gap-0">
      <div>
        <h2 class="text-lg sm:text-2xl font-extrabold text-slate-800 tracking-tight">User Management</h2>
        <p class="text-[11px] sm:text-sm text-slate-500 font-medium mt-0.5 sm:mt-1">Manage personnel roles and system access.</p>
      </div>
      <button v-if="hasPermission('user.add')" @click="openCreateForm" class="flex items-center justify-center px-4 py-2 sm:py-2.5 bg-teal-600 text-white font-bold text-xs sm:text-sm rounded-lg hover:bg-teal-700 transition-colors shadow-md shadow-teal-200 w-full sm:w-auto">
        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Add New User
      </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm flex-1 overflow-hidden flex flex-col">
      <!-- SEARCH & FILTER BAR -->
      <div class="bg-slate-50 p-3 border-b border-slate-200 flex flex-col sm:flex-row gap-3 items-center justify-between shrink-0">
        <div class="flex items-center gap-3 w-full sm:w-auto">
          <!-- Real-Time Search Input -->
          <div class="relative w-full sm:w-64">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" v-model="searchQuery" @input="debounceSearch" placeholder="Search users..." class="pl-9 pr-3 py-1.5 w-full border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 outline-none transition-shadow" />
          </div>
          
          <select v-model="perPage" @change="loadUsers" class="py-1.5 px-2 border border-slate-200 rounded-lg text-xs bg-white font-bold text-slate-600 outline-none">
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
        </div>

        <div class="flex items-center space-x-2">
          <button @click="currentPage--; loadUsers()" :disabled="currentPage === 1" class="p-1.5 rounded border border-slate-200 disabled:opacity-30 bg-white hover:bg-slate-50 transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
          </button>
          <span class="text-xs font-bold text-slate-600">Page {{ currentPage }} of {{ totalPages || 1 }}</span>
          <button @click="currentPage++; loadUsers()" :disabled="currentPage >= totalPages" class="p-1.5 rounded border border-slate-200 disabled:opacity-30 bg-white hover:bg-slate-50 transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
          </button>
        </div>
      </div>

      <div class="overflow-x-auto flex-1 custom-scrollbar">
        <table class="w-full text-left text-xs sm:text-sm text-slate-600">
          <thead>
            <tr class="border-b border-slate-200 bg-white text-slate-500 font-bold uppercase text-[9px] sm:text-xs">
              
              <!-- MASTER CHECKBOX -->
              <th v-if="hasPermission('user.delete')" class="py-2 px-3 sm:py-3 sm:px-4 w-10">
                <input 
                  type="checkbox" 
                  class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 transition-all cursor-pointer"
                  :checked="isAllSelected"
                  :indeterminate="isPartiallySelected"
                  @change="toggleSelectAll"
                >
              </th>
              
              <th @click="toggleSort('first_name')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  First Name
                  <svg v-if="sortBy === 'first_name'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 4 -4 -4"></path></svg>
                </div>
              </th>
              
              <th @click="toggleSort('last_name')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Last Name
                  <svg v-if="sortBy === 'last_name'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 4 -4 -4"></path></svg>
                </div>
              </th>
              
              <th @click="toggleSort('username')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Username / Email
                  <svg v-if="sortBy === 'username'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 4 -4 -4"></path></svg>
                </div>
              </th>
              
              <th class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap select-none">
                Contact Number
              </th>

              <th @click="toggleSort('role')" class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap cursor-pointer hover:text-teal-600 transition-colors group select-none">
                <div class="flex items-center">
                  Role
                  <svg v-if="sortBy === 'role'" :class="{'rotate-180': sortDir === 'desc'}" class="w-3 h-3 ml-1 transition-transform text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                  <svg v-else class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 -4 4 4m0 6l-4 4 -4 -4"></path></svg>
                </div>
              </th>

              <th class="py-2 px-3 sm:py-3 sm:px-4 font-bold whitespace-nowrap">
                <div class="flex items-center justify-end">
                  <span class="w-[80px] sm:w-[100px] text-center">Actions</span>
                </div>
              </th>
            </tr>
          </thead>
          <TableSkeleton v-if="isLoading" :columns="7" :rows="5" />
          <tbody v-else class="divide-y divide-slate-100">
            <tr v-if="!users?.length">
              <td colspan="7" class="py-8 text-center text-slate-500 font-medium text-sm">No users found.</td>
            </tr>
            
            <!-- CRITICAL FIX: The loop is safely established here -->
            <tr v-for="u in users" :key="u.id" class="hover:bg-slate-50 transition-colors" :class="{'bg-teal-50/30': selectedItems.includes(u.id)}">
              <!-- ROW CHECKBOX -->
              <td v-if="hasPermission('user.delete')" class="py-2 px-3 sm:py-3 sm:px-4">
                 <input 
                  type="checkbox" 
                  class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 transition-all cursor-pointer"
                  :value="u.id"
                  v-model="selectedItems"
                >
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 font-bold text-slate-800 whitespace-nowrap">{{ u.first_name }}</td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 font-bold text-slate-800 whitespace-nowrap">{{ u.last_name }}</td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap">
                <div class="font-semibold">{{ u.username }}</div>
                <div class="text-[9px] sm:text-[11px] text-slate-400">{{ u.email }}</div>
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 text-[10px] sm:text-xs font-medium text-slate-500 whitespace-nowrap">
                {{ u.contact_number || 'N/A' }}
              </td>
              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap">
                <span class="px-2 py-1 text-[9px] sm:text-[10px] font-bold rounded-full uppercase tracking-wider bg-slate-100 text-slate-700">{{ u.role }}</span>
              </td>
              
              <!-- Safely scoping the buttons under 'u' -->
              <td class="py-2 px-3 sm:py-3 sm:px-4 whitespace-nowrap">
                <div class="flex items-center justify-end gap-2">
                  <button v-if="authStore.user?.role?.toLowerCase() === 'developer' && u.id !== authStore.user?.id && u.role?.toLowerCase() !== 'developer'" 
                          @click="triggerImpersonate(u)"
                          class="text-[10px] sm:text-xs font-bold text-purple-600 hover:text-purple-800 bg-purple-50 px-2 py-1 rounded border border-purple-200 transition-colors shrink-0"
                          title="Login as this user">
                    Login As
                  </button>
                  
                  <div class="flex items-center justify-center gap-1 sm:gap-2 w-[80px] sm:w-[100px] shrink-0">
                    <button @click="openViewModal(u)" class="p-1 sm:p-1.5 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded transition-colors" title="View Profile">
                      <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                    
                    <template v-if="hasPermission('user.edit') || hasPermission('user.delete')">
                        <button v-if="hasPermission('user.edit')" @click="openEditForm(u)" class="p-1 sm:p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors" title="Edit User">
                          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>                   
                        <button v-if="hasPermission('user.delete')" @click="deleteUser(u)" :disabled="u.id === authStore.user?.id" class="p-1 sm:p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors disabled:opacity-30 disabled:hover:bg-transparent disabled:cursor-not-allowed" title="Delete User">
                          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </template>
                  </div>
                </div>
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
        <span class="text-sm font-medium tracking-wide">users selected</span>
        <div class="h-5 w-px bg-slate-600 mx-1"></div>
        <button @click="confirmBatchDelete" class="text-sm font-bold text-red-400 hover:text-red-300 transition-colors flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
          Delete Selection
        </button>
      </div>
    </transition>

    <!-- MODAL COMPONENTS -->
    <UserModal v-if="isAddModalOpen || isEditModalOpen" :user="isEditModalOpen ? targetUser : null" @close="isAddModalOpen = false; isEditModalOpen = false;" @saved="handleSaveAction" />
    <UserView v-if="isViewModalOpen" :user="targetUser" @close="isViewModalOpen = false" />
    <ConfirmModal 
      :show="isDeleteModalOpen" 
      :title="selectedItems.length > 0 ? 'Delete Multiple Users?' : 'Delete User?'"
      :message="selectedItems.length > 0 ? `Are you absolutely sure you want to permanently remove ${selectedItems.length} selected users? This action cannot be undone.` : `Are you absolutely sure you want to permanently delete ${targetUser?.first_name} ${targetUser?.last_name}? This action cannot be undone.`"
      type="danger"
      confirmText="Yes, Delete"
      :isLoading="isDeleting"
      @confirm="executeDelete"
      @cancel="isDeleteModalOpen = false"
    />
    
    <!-- Confirm Impersonate Modal -->
    <div v-if="isConfirmModalOpen" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="isConfirmModalOpen = false"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-5 sm:p-6 text-center animate-fade-in-up border border-slate-200">
        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4 text-purple-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">Login as {{ targetUser?.first_name }}?</h3>
        <p class="text-sm text-slate-500 mb-6 whitespace-normal">You are about to impersonate this user. Everything you do will be logged under their account until you return to developer mode.</p>
        <div class="flex space-x-3">
          <button @click="isConfirmModalOpen = false" class="flex-1 py-2.5 text-sm bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
          <button @click="executeImpersonation" class="flex-1 py-2.5 text-sm bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 transition-colors shadow-md shadow-purple-200">Yes, Login</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import TableSkeleton from '../../components/ui/TableSkeleton.vue';
import api from '../../axios';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import { useRouter } from 'vue-router';

import UserModal from './UserModal.vue';
import UserView from './UserView.vue';
import ConfirmModal from '../../components/modals/ConfirmModal.vue';
import { useACL } from '../../composables/useACL';

const authStore = useAuthStore();
const toastStore = useToastStore();
const router = useRouter();
const { hasPermission } = useACL();

const users = ref([]);
const isLoading = ref(false);
const searchQuery = ref('');
const perPage = ref(10);
const currentPage = ref(1);
const totalPages = ref(1);
const sortBy = ref('id');
const sortDir = ref('desc');

const selectedItems = ref([]);

const isAllSelected = computed(() => {
    return users.value?.length > 0 && selectedItems.value.length === users.value.length;
});

const isPartiallySelected = computed(() => {
    return selectedItems.value.length > 0 && selectedItems.value.length < users.value?.length;
});

const toggleSelectAll = (event) => {
    if (event.target.checked) {
        selectedItems.value = users.value.map(u => u.id);
    } else {
        selectedItems.value = [];
    }
};

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isConfirmModalOpen = ref(false);
const targetUser = ref(null);
const isDeleting = ref(false);

const loadUsers = async () => {
    isLoading.value = true;
    try {
        const response = await api.get('/api/users', {
            params: {
                search: searchQuery.value,
                per_page: perPage.value,
                page: currentPage.value,
                sort_by: sortBy.value,
                sort_dir: sortDir.value
            }
        });
        users.value = response.data.data;
        totalPages.value = response.data.last_page;
        currentPage.value = response.data.current_page;
    } catch (error) {
        toastStore.show("Failed to load users", "error");
    } finally {
        isLoading.value = false;
    }
};

const handleSaveAction = async () => {
    await loadUsers();
    selectedItems.value = [];
};

let searchTimeout = null;
const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage.value = 1; 
        loadUsers();
    }, 300);
};

const toggleSort = (column) => {
    if (sortBy.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDir.value = 'asc';
    }
    loadUsers();
};

onMounted(loadUsers);

const openCreateForm = () => isAddModalOpen.value = true;
const openEditForm = (user) => { targetUser.value = user; isEditModalOpen.value = true; };
const openViewModal = (user) => { targetUser.value = user; isViewModalOpen.value = true; };
const deleteUser = (user) => { targetUser.value = user; selectedItems.value = []; isDeleteModalOpen.value = true; };
const confirmBatchDelete = () => { targetUser.value = null; isDeleteModalOpen.value = true; };

const executeDelete = async () => {
    isDeleting.value = true;
    try {
        const isBatch = selectedItems.value.length > 0;
        if (isBatch) {
            await Promise.all(selectedItems.value.map(id => api.delete(`/api/users/${id}`)));
            toastStore.show(`${selectedItems.value.length} users removed.`, 'success');
        } else {
            await api.delete(`/api/users/${targetUser.value.id}`);
            toastStore.show('User deleted permanently.', 'success');
        }
        isDeleteModalOpen.value = false;
        handleSaveAction();
    } catch (error) {
        toastStore.show(error.response?.data?.message || "Failed to delete user(s).", 'error');
        isDeleteModalOpen.value = false;
    } finally {
        isDeleting.value = false;
    }
};

const triggerImpersonate = (user) => {
    targetUser.value = user;
    isConfirmModalOpen.value = true;
};

const executeImpersonation = async () => {
    if (!targetUser.value) return;
    try {
        await authStore.impersonate(targetUser.value.id);
        isConfirmModalOpen.value = false;
        router.push('/dashboard'); 
    } catch (error) {
        toastStore.show("Impersonation failed.", "error");
    }
};
</script>