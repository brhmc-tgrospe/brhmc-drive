<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center p-2 sm:p-4 animate-fade-in-up">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="$emit('close')"></div>
    <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-2xl flex flex-col max-h-[95vh] sm:max-h-[90vh]">
      <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-slate-200 shrink-0">
        <h3 class="text-base sm:text-lg font-extrabold text-slate-800">Create New User</h3>
      </div>
      
      <div class="p-4 sm:p-6 overflow-y-auto flex-1">
        <UserForm :form="form" @submit="saveUser" id="userForm" />
      </div>
      
      <div class="px-4 py-3 sm:px-6 sm:py-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-3 shrink-0 rounded-b-lg">
        <button @click="$emit('close')" class="px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-bold text-slate-600 hover:text-slate-800 transition-colors">Cancel</button>
        <button type="submit" form="userForm" :disabled="isSaving" class="px-4 py-1.5 sm:px-6 sm:py-2 bg-teal-600 text-white text-xs sm:text-sm font-bold rounded-lg hover:bg-teal-700 transition-colors shadow-sm disabled:opacity-50">
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
import UserForm from './UserForm.vue';

const emit = defineEmits(['close', 'saved']);
const toastStore = useToastStore();
const { hasRole } = useACL();

const isSaving = ref(false);
const form = ref({
    first_name: '', last_name: '', username: '', email: '', 
    contact_number: '', password: '', role: '', permissions: [] // Array initialized properly
});

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