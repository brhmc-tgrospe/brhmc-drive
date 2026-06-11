<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center p-2 sm:p-4 animate-fade-in-up">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="$emit('close')"></div>
    <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-2xl flex flex-col max-h-[95vh] sm:max-h-[90vh]">
      <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-slate-200 shrink-0">
        <h3 class="text-base sm:text-lg font-extrabold text-slate-800">Create New User</h3>
      </div>
      
      <div class="p-4 sm:p-6 overflow-y-auto flex-1">
        <form @submit.prevent="saveUser" class="space-y-3 sm:space-y-4">
          <div class="grid grid-cols-2 gap-3 sm:gap-4">
            <div>
              <label class="block text-[10px] sm:text-xs font-bold text-slate-600 mb-1">First Name</label>
              <input v-model="form.first_name" type="text" required class="w-full bg-slate-50 border border-slate-200 rounded px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 outline-none">
            </div>
            <div>
              <label class="block text-[10px] sm:text-xs font-bold text-slate-600 mb-1">Last Name</label>
              <input v-model="form.last_name" type="text" required class="w-full bg-slate-50 border border-slate-200 rounded px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 outline-none">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <div>
              <label class="block text-[10px] sm:text-xs font-bold text-slate-600 mb-1">Username</label>
              <input v-model="form.username" type="text" required class="w-full bg-slate-50 border border-slate-200 rounded px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3 sm:gap-4 sm:col-span-1">
               <div>
                <label class="block text-[10px] sm:text-xs font-bold text-slate-600 mb-1">Email</label>
                <input v-model="form.email" type="email" required class="w-full bg-slate-50 border border-slate-200 rounded px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 outline-none">
               </div>
               <div>
                <label class="block text-[10px] sm:text-xs font-bold text-slate-600 mb-1">Contact No.</label>
                <input v-model="form.contact_number" type="text" class="w-full bg-slate-50 border border-slate-200 rounded px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm outline-none" placeholder="0912...">
               </div>
            </div>
          </div>

          <div>
            <label class="block text-[10px] sm:text-xs font-bold text-slate-600 mb-1">Password</label>
            <input v-model="form.password" type="password" required minlength="6" class="w-full bg-slate-50 border border-slate-200 rounded px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 outline-none">
          </div>

          <div class="pt-2 border-t border-slate-100">
            <label class="block text-[10px] sm:text-xs font-bold text-slate-800 mb-1">Job Title (Role)</label>
            <input v-model="form.role" type="text" required placeholder="e.g., Head Dispatcher, EMT Driver, Maintenance Crew..." class="w-full bg-slate-50 border border-slate-200 rounded px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 outline-none">
          </div>

          <div class="pt-2">
            <label class="block text-[10px] sm:text-xs font-extrabold text-slate-800 mb-2 uppercase">System Access Capabilities (ACL)</label>
            <div class="space-y-3">
              <!-- Dashboard -->
              <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex justify-between items-center">
                  <span class="text-xs font-bold text-slate-800">Dashboard</span>
                  <label class="flex items-center space-x-1 cursor-pointer">
                    <input type="checkbox" @change="toggleAll('dashboard', $event)" :checked="hasAll('dashboard')" class="text-teal-600 rounded w-3 h-3">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Enable All</span>
                  </label>
                </div>
                <div class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="dashboard.status_grids" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Status Grids</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="dashboard.live_map_tracker" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Live Map Tracker</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="dashboard.live_trip_tracking" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Live Trip Tracking</span>
                  </label>
                </div>
              </div>
              
              <!-- User Management -->
              <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex justify-between items-center">
                  <span class="text-xs font-bold text-slate-800">User Management</span>
                  <label class="flex items-center space-x-1 cursor-pointer">
                    <input type="checkbox" @change="toggleAll('user', $event)" :checked="hasAll('user')" class="text-teal-600 rounded w-3 h-3">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Enable All</span>
                  </label>
                </div>
                <div class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="user.view" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">View</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="user.add" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Add</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="user.edit" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Edit</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="user.delete" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Delete</span>
                  </label>
                </div>
              </div>

              <!-- Shift Schedules -->
              <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex justify-between items-center">
                  <span class="text-xs font-bold text-slate-800">Shift Schedules</span>
                  <label class="flex items-center space-x-1 cursor-pointer">
                    <input type="checkbox" @change="toggleAll('schedule', $event)" :checked="hasAll('schedule')" class="text-teal-600 rounded w-3 h-3">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Enable All</span>
                  </label>
                </div>
                <div class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="schedule.view" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">View</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="schedule.add" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Add</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="schedule.edit" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Edit</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="schedule.delete" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Delete</span>
                  </label>
                </div>
              </div>

              <!-- Vehicle Fleet -->
              <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex justify-between items-center">
                  <span class="text-xs font-bold text-slate-800">Vehicle Fleet</span>
                  <label class="flex items-center space-x-1 cursor-pointer">
                    <input type="checkbox" @change="toggleAll('vehicle', $event)" :checked="hasAll('vehicle')" class="text-teal-600 rounded w-3 h-3">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Enable All</span>
                  </label>
                </div>
                <div class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="vehicle.view" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">View</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="vehicle.add" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Add</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="vehicle.edit" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Edit</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="vehicle.delete" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Delete</span>
                  </label>
                </div>
              </div>

              <!-- Vehicle Checklist -->
              <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex justify-between items-center">
                  <span class="text-xs font-bold text-slate-800">Vehicle Checklist</span>
                  <label class="flex items-center space-x-1 cursor-pointer">
                    <input type="checkbox" @change="toggleAll('checklist', $event)" :checked="hasAll('checklist')" class="text-teal-600 rounded w-3 h-3">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Enable All</span>
                  </label>
                </div>
                <div class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="checklist.view" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">View</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="checklist.add" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Add</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="checklist.edit" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Edit</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="checklist.delete" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Delete</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="checklist.sign_turnovers" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Sign Turnovers</span>
                  </label>
                </div>
              </div>

              <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-50 px-3 py-2">
                  <label class="flex items-center space-x-2 cursor-pointer w-full">
                    <input type="checkbox" v-model="form.permissions" value="maintenance.full" class="text-teal-600 rounded">
                    <span class="text-xs font-bold text-slate-800">Maintenance Bay</span>
                  </label>
                </div>
              </div>

              <!-- Trip Logs -->
              <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex justify-between items-center">
                  <span class="text-xs font-bold text-slate-800">Trip Logs</span>
                  <label class="flex items-center space-x-1 cursor-pointer">
                    <input type="checkbox" @change="toggleAll('trip', $event)" :checked="hasAll('trip')" class="text-teal-600 rounded w-3 h-3">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Enable All</span>
                  </label>
                </div>
                <div class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="trip.view" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">View</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="trip.delete" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Delete</span>
                  </label>
                </div>
              </div>

              <!-- Incident Reports -->
              <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex justify-between items-center">
                  <span class="text-xs font-bold text-slate-800">Incident Reports</span>
                  <label class="flex items-center space-x-1 cursor-pointer">
                    <input type="checkbox" @change="toggleAll('incident', $event)" :checked="hasAll('incident')" class="text-teal-600 rounded w-3 h-3">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Enable All</span>
                  </label>
                </div>
                <div class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="incident.view" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">View</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="incident.acknowledge" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Acknowledge</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="incident.edit" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Edit</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="incident.delete" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Delete</span>
                  </label>
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" v-model="form.permissions" value="incident.generate_report" class="text-teal-600 rounded">
                    <span class="text-xs text-slate-700">Generate Report</span>
                  </label>
                </div>
              </div>

              <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-50 px-3 py-2">
                  <label class="flex items-center space-x-2 cursor-pointer w-full">
                    <input type="checkbox" v-model="form.permissions" value="execute_shifts" class="text-teal-600 rounded">
                    <span class="text-xs font-bold text-slate-800">Driver Operations (Execute Shifts)</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      
      <div class="px-4 py-3 sm:px-6 sm:py-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-3 shrink-0 rounded-b-lg">
        <button @click="$emit('close')" class="px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-bold text-slate-600 hover:text-slate-800 transition-colors">Cancel</button>
        <button @click="saveUser" :disabled="isSaving" class="px-4 py-1.5 sm:px-6 sm:py-2 bg-teal-600 text-white text-xs sm:text-sm font-bold rounded-lg hover:bg-teal-700 transition-colors shadow-sm disabled:opacity-50">
          {{ isSaving ? 'Saving...' : 'Save User' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../axios';
import { useToastStore } from '../../stores/toast';
import { useACL } from '../../composables/useACL';

const emit = defineEmits(['close', 'saved']);
const toastStore = useToastStore();
const { hasRole } = useACL();

const isSaving = ref(false);
const form = ref({
    first_name: '', last_name: '', username: '', email: '', 
    contact_number: '', password: '', role: '', permissions: [] // Array initialized properly
});

const permissionGroups = {
  dashboard: ['dashboard.status_grids', 'dashboard.live_map_tracker', 'dashboard.live_trip_tracking'],
  user: ['user.view', 'user.add', 'user.edit', 'user.delete'],
  schedule: ['schedule.view', 'schedule.add', 'schedule.edit', 'schedule.delete'],
  vehicle: ['vehicle.view', 'vehicle.add', 'vehicle.edit', 'vehicle.delete'],
  checklist: ['checklist.view', 'checklist.add', 'checklist.edit', 'checklist.delete', 'checklist.sign_turnovers'],
  trip: ['trip.view', 'trip.delete'],
  incident: ['incident.view', 'incident.acknowledge', 'incident.edit', 'incident.delete', 'incident.generate_report']
};

const hasAll = (group) => {
  const perms = permissionGroups[group];
  return perms.every(p => form.value.permissions.includes(p));
};

const toggleAll = (group, event) => {
  const isChecked = event.target.checked;
  const perms = permissionGroups[group];
  if (isChecked) {
    perms.forEach(p => {
      if (!form.value.permissions.includes(p)) form.value.permissions.push(p);
    });
  } else {
    form.value.permissions = form.value.permissions.filter(p => !perms.includes(p));
  }
};

const saveUser = async () => {
    // SECURITY LOOPHOLE FIX: Prevent creation of Developer roles
    if (form.value.role && form.value.role.trim().toLowerCase() === 'developer' && !hasRole('Developer')) {
        toastStore.show('Action Denied: You cannot create an account with the Developer role.', 'error');
        return;
    }

    isSaving.value = true;
    try {
        await api.post('/api/users', form.value);
        toastStore.show('New user created successfully!', 'success');
        emit('saved');
        emit('close');
    } catch (error) {
        if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            const firstErrorMessage = Object.values(errors)[0][0];
            toastStore.show(`Validation Error: ${firstErrorMessage}`, 'error');
        } else {
            toastStore.show(error.response?.data?.message || 'Failed to create user.', 'error');
        }
    } finally {
        isSaving.value = false;
    }
};
</script>