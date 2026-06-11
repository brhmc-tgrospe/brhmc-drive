import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useToastStore = defineStore('toast', () => {
    const toasts = ref([]);

    // Trigger a toast (type can be 'success', 'error', or 'info')
    const show = (message, type = 'success') => {
        const id = Date.now();
        toasts.value.push({ id, message, type });
        
        // Auto-remove after 3.5 seconds
        setTimeout(() => {
            remove(id);
        }, 3500);
    };

    const remove = (id) => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    };

    return { toasts, show, remove };
});