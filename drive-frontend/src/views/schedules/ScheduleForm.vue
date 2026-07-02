<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <!-- DRIVER DROPDOWN -->
      <div>
        <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Assign Driver</label>
        <select v-model="form.driver_id" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 bg-white">
          <option value="" disabled>Select Driver...</option>
          <option v-for="driver in availableDrivers" 
                  :key="driver.id" 
                  :value="driver.id" 
                  :disabled="isDriverDisabled(driver)"
                  :class="{'text-slate-400 bg-slate-50': isDriverDisabled(driver), 'font-medium text-slate-800': !isDriverDisabled(driver)}">
            {{ driver.first_name }} {{ driver.last_name }} {{ getDriverLabel(driver) }}
          </option>
        </select>
      </div>
      <!-- VEHICLE DROPDOWN -->
      <div>
        <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Assign Vehicle</label>
        <select v-model="form.vehicle_id" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 bg-white">
          <option value="" disabled>Select Vehicle...</option>
          <option v-for="vehicle in availableVehicles" 
                  :key="vehicle.id" 
                  :value="vehicle.id" 
                  :disabled="isVehicleDisabled(vehicle)"
                  :class="{'text-slate-400 bg-slate-50': isVehicleDisabled(vehicle), 'font-medium text-slate-800': !isVehicleDisabled(vehicle)}">
            {{ vehicle.unit_id }} {{ getVehicleLabel(vehicle) }}
          </option>
        </select>
      </div>
    </div>

    <div class="pt-2">
      <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-1">Shift Start Time</label>
      <input type="datetime-local" v-model="form.start_time" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-slate-200 rounded-md text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500" />
    </div>

    <div class="pt-2">
      <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-2">Shift Duration</label>
      <div class="flex space-x-3">
        <button type="button" @click="form.shift_duration = 8" :class="form.shift_duration === 8 ? 'bg-teal-600 text-white border-teal-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" class="flex-1 py-2 border rounded-lg font-bold text-sm transition-colors shadow-sm">
          8-Hour Shift
        </button>
        <button type="button" @click="form.shift_duration = 12" :class="form.shift_duration === 12 ? 'bg-teal-600 text-white border-teal-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" class="flex-1 py-2 border rounded-lg font-bold text-sm transition-colors shadow-sm">
          12-Hour Shift
        </button>
      </div>
    </div>
    
    <div class="pt-2">
      <label class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase mb-2">Trip Type</label>
      <div class="flex space-x-3">
        <button type="button" @click="form.trip_type = 'EMERGENCY'" :class="form.trip_type === 'EMERGENCY' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" class="flex-1 py-2 border rounded-lg font-bold text-sm transition-colors shadow-sm flex items-center justify-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          Emergency Response
        </button>
        <button type="button" @click="form.trip_type = 'REGULAR'" :class="form.trip_type === 'REGULAR' ? 'bg-teal-600 text-white border-teal-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" class="flex-1 py-2 border rounded-lg font-bold text-sm transition-colors shadow-sm flex items-center justify-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
          Regular Trip
        </button>
      </div>
    </div>
    
    <slot></slot>
  </form>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  form: { type: Object, required: true },
  drivers: { type: Array, required: true },
  vehicles: { type: Array, required: true },
  isEdit: { type: Boolean, default: false },
  originalDriverId: { type: [String, Number], default: null },
  originalVehicleId: { type: [String, Number], default: null }
});

const emit = defineEmits(['submit']);

const availableDrivers = computed(() => {
  const drvs = props.drivers.filter(u => {
    // Check if role is Driver
    const isDriver = u.role && u.role.toLowerCase() === 'driver';
    
    // Check if user has execute_shifts permission
    let hasPermission = false;
    if (u.legacy_permissions) {
      if (Array.isArray(u.legacy_permissions)) {
        hasPermission = u.legacy_permissions.includes('execute_shifts');
      } else if (typeof u.legacy_permissions === 'string') {
        try {
          const parsed = JSON.parse(u.legacy_permissions);
          if (Array.isArray(parsed)) {
            hasPermission = parsed.includes('execute_shifts');
          }
        } catch (e) {
          hasPermission = false;
        }
      }
    }
    
    return isDriver || hasPermission;
  });
  
  return drvs.sort((a, b) => {
      const statusA = a.driver_status === 'READY' ? 1 : 2;
      const statusB = b.driver_status === 'READY' ? 1 : 2;
      if (statusA !== statusB) return statusA - statusB;
      return (a.first_name || '').localeCompare(b.first_name || '');
  });
});

const availableVehicles = computed(() => {
  const statusOrder = { 'READY': 1, 'SCHEDULED': 2, 'IN_USE': 3, 'MAINTENANCE': 4, 'BREAKDOWN': 5 };
  return [...props.vehicles].sort((a, b) => {
      const orderA = statusOrder[a.status?.toUpperCase()] || 99;
      const orderB = statusOrder[b.status?.toUpperCase()] || 99;
      if (orderA !== orderB) return orderA - orderB;
      return (a.unit_id || '').localeCompare(b.unit_id || '');
  });
});

const isDriverDisabled = (driver) => {
  if (props.isEdit && driver.id === props.originalDriverId) return false;
  return !['READY', 'SCHEDULED'].includes(driver.driver_status);
};

const getDriverLabel = (driver) => {
  if (props.isEdit && driver.id === props.originalDriverId) return '(CURRENT)';
  return `(${driver.driver_status})`;
};

const isVehicleDisabled = (vehicle) => {
  if (props.isEdit && vehicle.id === props.originalVehicleId) return false;
  return !['READY', 'SCHEDULED'].includes(vehicle.status);
};

const getVehicleLabel = (vehicle) => {
  if (props.isEdit && vehicle.id === props.originalVehicleId) return '(CURRENT)';
  return `(${vehicle.status.replace('_', ' ')})`;
};

const submit = () => {
  emit('submit');
};
</script>
