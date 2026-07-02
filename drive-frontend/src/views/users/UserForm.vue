<template>
  <form @submit.prevent="submit" class="space-y-3 sm:space-y-4">
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

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
      <div>
        <label class="block text-[10px] sm:text-xs font-bold text-slate-600 mb-1">Password <span v-if="isEdit" class="text-slate-400 font-normal">(Leave blank to keep current)</span></label>
        <div class="relative">
          <input v-model="form.password" :type="showPassword ? 'text' : 'password'" :required="!isEdit" minlength="6" class="w-full bg-slate-50 border border-slate-200 rounded px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 outline-none pr-10">
          <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600">
            <svg v-if="!showPassword" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            <svg v-else class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.978 9.978 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
          </button>
        </div>
      </div>
      <div v-if="!isEdit || form.password">
        <label class="block text-[10px] sm:text-xs font-bold text-slate-600 mb-1">Confirm Password</label>
        <div class="relative">
          <input v-model="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" :required="!isEdit || (isEdit && form.password.length > 0)" minlength="6" class="w-full bg-slate-50 border border-slate-200 rounded px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 outline-none pr-10">
          <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600">
            <svg v-if="!showConfirmPassword" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            <svg v-else class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.978 9.978 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
          </button>
        </div>
        <p v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation" class="text-red-500 text-[10px] sm:text-xs mt-1">Passwords do not match.</p>
      </div>
    </div>

    <div class="pt-2 border-t border-slate-100">
      <label class="block text-[10px] sm:text-xs font-bold text-slate-800 mb-1">Job Title (Role)</label>
      <input v-model="form.role" type="text" :required="!isEdit" :disabled="isEdit" :placeholder="!isEdit ? 'e.g., Head Dispatcher, EMT Driver, Maintenance Crew...' : ''" :class="isEdit ? 'w-full bg-slate-100 text-slate-500 border border-slate-200 rounded px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm outline-none cursor-not-allowed' : 'w-full bg-slate-50 border border-slate-200 rounded px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 outline-none'">
      <p v-if="isEdit" class="text-[10px] text-slate-400 mt-1 font-medium">
        Role cannot be modified after account creation.
      </p>
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

    <slot></slot>
  </form>
</template>

<script setup>
import { ref } from 'vue';
const props = defineProps({
  form: { type: Object, required: true },
  isEdit: { type: Boolean, default: false }
});

const emit = defineEmits(['submit']);

const showPassword = ref(false);
const showConfirmPassword = ref(false);

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
  return perms.every(p => props.form.permissions.includes(p));
};

const toggleAll = (group, event) => {
  const isChecked = event.target.checked;
  const perms = permissionGroups[group];
  if (isChecked) {
    perms.forEach(p => {
      if (!props.form.permissions.includes(p)) props.form.permissions.push(p);
    });
  } else {
    props.form.permissions = props.form.permissions.filter(p => !perms.includes(p));
  }
};

const submit = () => {
  emit('submit');
};
</script>
