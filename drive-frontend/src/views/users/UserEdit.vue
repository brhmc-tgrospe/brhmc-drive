<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center p-2 sm:p-4 animate-fade-in-up">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="$emit('close')"></div>
    <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-2xl flex flex-col max-h-[95vh] sm:max-h-[90vh]">
      <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-slate-200 shrink-0">
        <h3 class="text-base sm:text-lg font-extrabold text-slate-800">Edit User</h3>
      </div>
      
      <div class="p-4 sm:p-6 overflow-y-auto flex-1">
        <UserForm :form="form" :is-edit="true" @submit="saveUser" id="userForm" />
      </div>
      
      <div class="px-4 py-3 sm:px-6 sm:py-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-3 shrink-0 rounded-b-lg">
        <button @click="$emit('close')" class="px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-bold text-slate-600 hover:text-slate-800 transition-colors">Cancel</button>
        <button type="submit" form="userForm" :disabled="isSaving" class="px-4 py-1.5 sm:px-6 sm:py-2 bg-teal-600 text-white text-xs sm:text-sm font-bold rounded-lg hover:bg-teal-700 transition-colors shadow-sm disabled:opacity-50">
          {{ isSaving ? 'Updating...' : 'Update User' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../axios';
import { useToastStore } from '../../stores/toast';
import UserForm from './UserForm.vue';

const props = defineProps({
    user: { type: Object, required: true }
});
const emit = defineEmits(['close', 'saved']);
const toastStore = useToastStore();

const isSaving = ref(false);

// 1. Safely parse permissions immediately (not waiting for onMounted)
let parsedPermissions = [];
try {
    parsedPermissions = typeof props.user.legacy_permissions === 'string' 
        ? JSON.parse(props.user.legacy_permissions) 
        : (Array.isArray(props.user.legacy_permissions) ? props.user.legacy_permissions : []);
} catch (e) {
    parsedPermissions = [];
}

// 2. Initialize the form with the exact schema right away
const form = ref({
    first_name: props.user.first_name || '',
    last_name: props.user.last_name || '',
    username: props.user.username || '',
    email: props.user.email || '',
    contact_number: props.user.contact_number || '', 
    password: '', // Kept empty initially
    role: props.user.role || '',
    permissions: parsedPermissions
});

const saveUser = async () => {
    isSaving.value = true;
    try {
        // Create a copy of the payload to modify
        const payload = { ...form.value };
        
        // Safety check: Prevent sending empty password if user didn't type one
        if (!payload.password || payload.password.trim() === '') {
            delete payload.password;
        }

        await api.put(`/api/users/${props.user.id}`, payload);
        toastStore.show('User updated successfully!', 'success');
        emit('saved');
        emit('close');
    } catch (error) {
        if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            const firstErrorMessage = Object.values(errors)[0][0];
            toastStore.show(`Validation Error: ${firstErrorMessage}`, 'error');
        } else {
            toastStore.show(error.response?.data?.message || 'Failed to update user.', 'error');
        }
    } finally {
        isSaving.value = false;
    }
};
</script>