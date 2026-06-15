<template>
  <nav class="flex flex-col w-full space-y-2 pt-4">
    <ItemDashboard v-if="hasAnyPermission(['dashboard.status_grids', 'dashboard.live_map_tracker', 'dashboard.live_trip_tracking', 'execute_shifts'])" />
    <ItemUsers v-if="hasPermission('user.view')" />
    <ItemSchedule v-if="hasPermission('schedule.view')" />
    <ItemVehicles v-if="hasPermission('vehicle.view')" />
    <ItemChecklists v-if="hasPermission('checklist.view')" />   
    <ItemMaintenance v-if="hasPermission('maintenance.full')" />
    <ItemTripLogs v-if="hasPermission('trip.view')" />
    <ItemIncidents v-if="hasPermission('incident.view')" />
    <!--DEV TOOLS -->
    <ItemActivityLogs v-if="hasRole('Developer')" />
    <ItemArchived v-if="hasRole('Developer')" />
    <ItemSystemHealth v-if="hasRole('Developer')" />

  </nav>
</template>

<script setup>
import { useACL } from '../../composables/useACL';
const { hasPermission, hasRole, hasAnyPermission } = useACL();

import ItemDashboard from './items/ItemDashboard.vue';
import ItemUsers from './items/ItemUsers.vue';
import ItemSchedule from './items/ItemSchedule.vue';
import ItemVehicles from './items/ItemVehicles.vue';
import ItemChecklists from './items/ItemChecklists.vue';
import ItemMaintenance from './items/ItemMaintenance.vue';
import ItemTripLogs from './items/ItemTripLogs.vue'; 
import ItemIncidents from './items/ItemIncidents.vue';
import ItemActivityLogs from './items/ItemActivityLogs.vue';
import ItemSystemHealth from './items/ItemSystemHealth.vue';
import ItemArchived from './items/ItemArchived.vue';
</script>