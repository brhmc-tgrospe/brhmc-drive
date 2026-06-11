import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import { createPinia } from 'pinia';
import './style.css';
import App from './App.vue';
import Login from './views/Login.vue';

import { useAuthStore } from './stores/auth'; 

// =====================================================
// LAZY-LOADED ROUTE COMPONENTS
// =====================================================
const AppLayout = () => import('./layouts/AppLayout.vue');
const Dashboard = () => import('./components/dashboard/Dashboard.vue');
const Forbidden = () => import('./views/errors/Forbidden.vue');
const Vehicles = () => import('./views/vehicles/VehicleIndex.vue');
const Users = () => import('./views/users/UserIndex.vue');
const Schedules = () => import('./views/schedules/ScheduleIndex.vue');
const Settings = () => import('./components/header/Settings.vue');
const Profile = () => import('./components/header/Profile.vue');
const Checklists = () => import('./views/checklists/ChecklistIndex.vue');
const Incidents = () => import('./views/incidents/IncidentIndex.vue');
const MaintenanceBay = () => import('./views/maintenance/MaintenanceBay.vue');
const Trips = () => import('./views/trips/TripIndex.vue');
const ActivityLogs = () => import('./views/activityLogs/ActivityLogIndex.vue');
const SystemHealth = () => import('./views/system/SystemHealth.vue');

const routes = [
    { path: '/', component: Login, name: 'RootLogin', meta: { guest: true } },
    { path: '/login', component: Login, name: 'Login', meta: { guest: true } },
    {
        path: '/',
        component: AppLayout,
        children: [
            { path: 'dashboard', component: Dashboard, name: 'Dashboard', meta: { requiresAuth: true } },
            { path: 'vehicles', component: Vehicles, name: 'Vehicles Masterlist', meta: { requiresAuth: true, requiresPermission: 'vehicle.view' } },
            { path: 'checklists', component: Checklists, name: 'Vehicle Checklists', meta: { requiresAuth: true, requiresPermission: 'checklist.view' } },           
            { path: 'users', component: Users, name: 'User Management', meta: { requiresAuth: true, requiresPermission: 'user.view' } },  
            { path: 'schedules', component: Schedules, name: 'Schedules', meta: { requiresAuth: true, requiresPermission: 'schedule.view' } },
            { path: 'trips', component: Trips, name: 'Trip Logs', meta: { requiresAuth: true, requiresPermission: 'trip.view' } },
            { path: 'incidents', component: Incidents, name: 'Incidents', meta: { requiresAuth: true, requiresPermission: 'incident.view' } },
            { path: 'maintenance', component: MaintenanceBay, name: 'Maintenance Bay', meta: { requiresAuth: true, requiresPermission: 'maintenance.full' } },
            { path: 'activity-logs', component: ActivityLogs, name: 'Activity Logs', meta: { requiresAuth: true, requiresRole: 'developer' } },
            { path: 'system-health', component: SystemHealth, name: 'System Health', meta: { requiresAuth: true, requiresRole: 'developer' } },
            { path: 'settings', component: Settings, name: 'Settings', meta: { requiresAuth: true } },
            { path: 'profile', component: Profile, name: 'Profile', meta: { requiresAuth: true } },
            { path: 'forbidden', component: Forbidden, name: 'Forbidden' },
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

const app = createApp(App);
const pinia = createPinia();

app.use(pinia); 

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    const hasToken = !!localStorage.getItem('token');

    if (hasToken && !authStore.isAuthenticated) {
        await authStore.fetchUser();
    }
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return next({ name: 'Login' });
    }
    if (to.meta.guest && authStore.isAuthenticated) {
        return next({ name: 'Dashboard' });
    }
    if (to.meta.requiresRole) {
        const { useACL } = await import('./composables/useACL');
        const acl = useACL();
        if (!acl.hasRole(to.meta.requiresRole)) {
            return next({ name: 'Forbidden' });
        }
    }
    
    // ACL Check
    if (to.meta.requiresPermission) {
        const { useACL } = await import('./composables/useACL');
        const acl = useACL();
        if (!acl.hasPermission(to.meta.requiresPermission)) {
            return next({ name: 'Forbidden' });
        }
    }

    next();
});

app.use(router);
app.mount('#app');

// TEMPORARY DIAGNOSTIC: Capture and log any unhandled promise rejections
// with full stack traces so we can identify the exact source.
window.addEventListener('unhandledrejection', (event) => {
    const reason = event.reason;
    const stack = reason?.stack || reason?.message || String(reason);
    console.error('[DRIVE DIAGNOSTIC] Unhandled Promise Rejection:', stack);
    console.error('[DRIVE DIAGNOSTIC] Full reason object:', reason);
});