<template>
  <div class="max-w-7xl mx-auto h-full flex flex-col p-4 sm:p-6 overflow-y-auto animate-fade-in-up">
    
    <!-- PAGE HEADER -->
    <div class="mb-6 shrink-0">
      <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">My Profile</h1>
      <p class="text-sm text-slate-500 mt-0.5">Manage your personal information and view recent activity.</p>
    </div>

    <!-- MAIN CONTENT GRID -->
    <div class="flex flex-col lg:flex-row gap-6">
      
      <!-- LEFT COLUMN: AVATAR & QUICK STATS -->
      <div class="w-full lg:w-5/12 flex flex-col gap-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 flex flex-col items-center text-center relative overflow-hidden">
          <div class="absolute top-0 left-0 w-full h-24 bg-teal-600"></div>
          
          <div class="relative w-24 h-24 rounded-full bg-white border-4 border-white shadow-md flex items-center justify-center text-3xl font-extrabold text-teal-600 mt-8 mb-3">
            {{ userInitials }}
          </div>
          
          <h2 class="text-xl font-bold text-slate-800">{{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</h2>
          <span class="px-3 py-1 mt-2 bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-wider rounded-full">
            {{ authStore.user?.role || 'Guest User' }}
          </span>
          <p class="text-sm text-slate-500 mt-3">{{ authStore.user?.email || 'No email provided' }}</p>

          <div class="w-full border-t border-slate-100 mt-6 pt-6 flex justify-between text-sm">
            <div class="text-center w-1/2 border-r border-slate-100">
              <p class="text-slate-500 text-xs">Joined</p>
              <p class="font-bold text-slate-800 mt-0.5">May 2026</p>
            </div>
            <div class="text-center w-1/2">
              <p class="text-slate-500 text-xs">Account Status</p>
              <p class="font-bold text-teal-600 mt-0.5 flex items-center justify-center gap-1">
                <span class="w-2 h-2 rounded-full bg-teal-500"></span> Active
              </p>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 relative">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Recent Activity</h3>
            <button @click="showActivityModal = true" class="text-xs font-bold text-teal-600 hover:text-teal-700 transition-colors">
              View All
            </button>
          </div>
          <div class="space-y-4">
            <div v-if="isLoadingActivities" class="text-sm text-slate-500">Loading activities...</div>
            <div v-else-if="activities.length === 0" class="text-sm text-slate-500">No recent activity found.</div>
            <div v-for="activity in activities" :key="activity.id" class="flex gap-3 items-start">
              <div class="w-2 h-2 rounded-full bg-teal-500 mt-1.5 shrink-0"></div>
              <div>
                <p class="text-sm text-slate-700 leading-snug">{{ formatDetailedDescription(activity) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ new Date(activity.created_at).toLocaleString() }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT COLUMN: EDIT FORM -->
      <div class="w-full lg:w-7/12 flex flex-col gap-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-800">Personal Information</h2>
            <p class="text-sm text-slate-500">Update your account details here.</p>
          </div>
          
          <form @submit.prevent="saveProfile" class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <div class="space-y-1">
                <label class="block text-sm font-bold text-slate-700">First Name</label>
                <input v-model="form.first_name" type="text" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>
              <div class="space-y-1">
                <label class="block text-sm font-bold text-slate-700">Last Name</label>
                <input v-model="form.last_name" type="text" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <div class="space-y-1">
                <label class="block text-sm font-bold text-slate-700">Email Address</label>
                <input v-model="form.email" type="email" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>
              <div class="space-y-1">
                <label class="block text-sm font-bold text-slate-700">Phone Number</label>
                <input v-model="form.contact_number" type="text" placeholder="+63 900 000 0000" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>
            </div>

            <!-- Role Specific Fields -->
            <div v-if="authStore.user?.role === 'driver'" class="space-y-1 pt-4 border-t border-slate-100">
              <label class="block text-sm font-bold text-slate-700">Driver's License Number</label>
              <input v-model="form.license_number" type="text" class="w-full sm:w-1/2 p-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="A01-23-456789" />
              <p class="text-xs text-slate-500 mt-1">Leave blank if unchanged. Updating this requires admin verification.</p>
            </div>

            <div class="pt-4 flex justify-end">
              <button type="submit" :disabled="isSaving" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm hover:shadow transition-all disabled:opacity-50 flex items-center gap-2">
                <svg v-if="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                {{ isSaving ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>
      </div>
      
    </div>

    <!-- Modals -->
    <UserActivityModal :show="showActivityModal" @close="showActivityModal = false" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import axios from '../../axios';
import UserActivityModal from '../modals/UserActivityModal.vue';
import { formatDetailedDescription } from '../../utils/activityFormatter';

const authStore = useAuthStore();
const toastStore = useToastStore();

const userInitials = computed(() => {
  const first = authStore.user?.first_name?.charAt(0) || '';
  const last = authStore.user?.last_name?.charAt(0) || '';
  return (first + last).toUpperCase() || 'U';
});

const form = ref({
  first_name: authStore.user?.first_name || '',
  last_name: authStore.user?.last_name || '',
  email: authStore.user?.email || '',
  contact_number: authStore.user?.contact_number || '', 
  license_number: authStore.user?.license_number || '' 
});

const isSaving = ref(false);

const saveProfile = async () => {
  isSaving.value = true;
  try {
    const response = await axios.put('/api/user/profile', form.value);
    authStore.user = response.data.user;
    toastStore.show('Profile updated successfully!', 'success');
    fetchActivities(); // Refresh activities after update
    setTimeout(() => {
      window.location.reload();
    }, 1000);
  } catch (error) {
    console.error(error);
    toastStore.show('Failed to update profile.', 'error');
    isSaving.value = false;
  }
};

const activities = ref([]);
const isLoadingActivities = ref(false);

const fetchActivities = async () => {
  if (!authStore.user?.id) return;
  isLoadingActivities.value = true;
  try {
    const response = await axios.get('/api/activity-logs', {
      params: {
        causer_id: authStore.user.id,
        per_page: 3
      }
    });
    activities.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch activities:', error);
  } finally {
    isLoadingActivities.value = false;
  }
};

const showActivityModal = ref(false);

onMounted(() => {
  fetchActivities();
});
</script>