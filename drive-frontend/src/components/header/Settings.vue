<template>
  <div class="max-w-7xl mx-auto h-full flex flex-col p-4 sm:p-6 overflow-y-auto animate-fade-in-up">
    
    <!-- PAGE HEADER -->
    <div class="mb-6 shrink-0">
      <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Settings</h1>
      <p class="text-sm text-slate-500 mt-0.5">Configure your application preferences and security.</p>
    </div>

    <div class="flex flex-col md:flex-row gap-6 lg:gap-8">
      
      <!-- SIDEBAR NAVIGATION -->
      <div class="w-full md:w-64 shrink-0">
        <nav class="flex md:flex-col gap-1 overflow-x-auto md:overflow-visible pb-2 md:pb-0 hide-scrollbar">
          <button 
            v-for="tab in availableTabs" 
            :key="tab.id"
            @click="activeTab = tab.id"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all whitespace-nowrap"
            :class="activeTab === tab.id ? 'bg-teal-50 text-teal-700 shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
          >
            <div v-html="tab.icon" class="w-5 h-5" :class="activeTab === tab.id ? 'text-teal-600' : 'text-slate-400'"></div>
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <!-- CONTENT AREA -->
      <div class="flex-1">
        
        <!-- GENERAL SETTINGS -->
        <div v-if="activeTab === 'general'" class="bg-white border border-slate-200 rounded-2xl shadow-sm animate-fade-in-up">
          <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-800">General Preferences</h2>
            <p class="text-sm text-slate-500">Manage display and localization settings.</p>
          </div>
          <div class="p-6 space-y-6">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 border-b border-slate-100 pb-6">
              <div>
                <h3 class="font-bold text-slate-800 text-sm">Theme Appearance</h3>
                <p class="text-xs text-slate-500 mt-0.5">Select how the interface looks.</p>
              </div>
              <select v-model="settings.theme" class="p-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 w-full sm:w-48">
                <option value="light">Light Mode (Default)</option>
                <option value="dark">Dark Mode</option>
                <option value="system">System Default</option>
              </select>
            </div>
            
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
              <div>
                <h3 class="font-bold text-slate-800 text-sm">Language</h3>
                <p class="text-xs text-slate-500 mt-0.5">Set the system language.</p>
              </div>
              <select v-model="settings.language" class="p-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 w-full sm:w-48">
                <option value="en">English (US)</option>
                <option value="tl">Tagalog</option>
              </select>
            </div>
          </div>
          <div class="p-5 bg-slate-50 border-t border-slate-100 text-right rounded-b-2xl">
            <button @click="saveSettings" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-lg transition-colors">Save Preferences</button>
          </div>
        </div>

        <!-- NOTIFICATIONS -->
        <div v-if="activeTab === 'notifications'" class="bg-white border border-slate-200 rounded-2xl shadow-sm animate-fade-in-up">
          <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-800">Notification Rules</h2>
            <p class="text-sm text-slate-500">Choose what alerts you want to receive.</p>
          </div>
          <div class="p-6 space-y-6">
            <!-- Toggle 1 -->
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-bold text-slate-800 text-sm">In-App Push Alerts</h3>
                <p class="text-xs text-slate-500 mt-0.5">Receive browser notifications for dispatch updates.</p>
              </div>
              <button @click="settings.push = !settings.push" :class="settings.push ? 'bg-teal-600' : 'bg-slate-200'" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none">
                <span :class="settings.push ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
              </button>
            </div>

          </div>
        </div>

        <!-- SECURITY -->
        <div v-if="activeTab === 'security'" class="bg-white border border-slate-200 rounded-2xl shadow-sm animate-fade-in-up">
          <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-800">Account Security</h2>
            <p class="text-sm text-slate-500">Update your password and secure your account.</p>
          </div>
          <form @submit.prevent="savePassword" class="p-6 space-y-4">
            <div class="space-y-1">
              <label class="block text-sm font-bold text-slate-700">Current Password</label>
              <div class="relative w-full md:w-2/3">
                <input v-model="passwordForm.current_password" :type="showCurrentPassword ? 'text' : 'password'" required class="w-full p-2.5 pr-10 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
                <button type="button" @click="showCurrentPassword = !showCurrentPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                  <svg v-if="!showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.978 9.978 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                </button>
              </div>
            </div>
            <div class="space-y-1 pt-2">
              <label class="block text-sm font-bold text-slate-700">New Password</label>
              <div class="relative w-full md:w-2/3">
                <input v-model="passwordForm.new_password" :type="showNewPassword ? 'text' : 'password'" required minlength="6" class="w-full p-2.5 pr-10 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
                <button type="button" @click="showNewPassword = !showNewPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                  <svg v-if="!showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.978 9.978 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                </button>
              </div>
            </div>
            <div class="space-y-1">
              <label class="block text-sm font-bold text-slate-700">Confirm New Password</label>
              <div class="relative w-full md:w-2/3">
                <input v-model="passwordForm.new_password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" required minlength="6" class="w-full p-2.5 pr-10 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                  <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.978 9.978 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                </button>
              </div>
            </div>
            <div class="pt-4 flex items-center gap-4">
              <button type="submit" :disabled="isSavingPassword" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white text-sm font-bold rounded-lg shadow-sm transition-colors flex items-center gap-2">
                <svg v-if="isSavingPassword" class="animate-spin -ml-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Update Password
              </button>
            </div>
          </form>
        </div>

        <!-- SYSTEM ADMIN -->
        <div v-if="activeTab === 'system'" class="bg-white border border-red-200 rounded-2xl shadow-sm animate-fade-in-up">
          <div class="p-6 border-b border-red-100 bg-red-50/50 rounded-t-2xl">
            <h2 class="text-lg font-bold text-red-800">System Administration</h2>
            <p class="text-sm text-red-600">Danger Zone ---></p>
          </div>
          <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-bold text-slate-800 text-sm">Maintenance Mode</h3>
                <p class="text-xs text-slate-500 mt-0.5">Locks out drivers and dispatchers. Only developers can login.</p>
              </div>
              <button @click="settings.maintenance = !settings.maintenance" :class="settings.maintenance ? 'bg-red-600' : 'bg-slate-200'" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none">
                <span :class="settings.maintenance ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
              </button>
            </div>
            
            <div class="pt-6 border-t border-slate-100">
              <button @click="clearCache" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-lg transition-colors">
                Clear Application Cache
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import axios from '../../axios';

const authStore = useAuthStore();
const toastStore = useToastStore();

const activeTab = ref('general');

const tabs = [
  { id: 'general', label: 'General', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>' },
  { id: 'notifications', label: 'Notifications', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>' },
  { id: 'security', label: 'Security', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>' }
];

const availableTabs = computed(() => {
  if (authStore.user?.username === 'developer') {
    return [...tabs, { id: 'system', label: 'System Admin', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>' }];
  }
  return tabs;
});

const settings = ref({ theme: 'light', language: 'en', push: true, maintenance: false });

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const isSavingPassword = ref(false);

const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

const saveSettings = () => toastStore.show('Settings successfully updated!', 'success');
const clearCache = () => toastStore.show('Application cache cleared.', 'info');

const savePassword = async () => {
  if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
    toastStore.show('New passwords do not match!', 'error');
    return;
  }
  isSavingPassword.value = true;
  try {
    await axios.put('/api/user/password', passwordForm.value);
    toastStore.show('Password successfully updated!', 'success');
    passwordForm.value.current_password = '';
    passwordForm.value.new_password = '';
    passwordForm.value.new_password_confirmation = '';
    showCurrentPassword.value = false;
    showNewPassword.value = false;
    showConfirmPassword.value = false;
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Failed to update password.';
    toastStore.show(errorMsg, 'error');
  } finally {
    isSavingPassword.value = false;
  }
};
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>