import axios from 'axios';
import { useAuthStore } from '../stores/useAuthStore'; // We will update this store next
import router from '../router';

// Creates a configured Axios instance
const apiClient = axios.create({
    baseURL: 'http://localhost:8000', // Point this to your Laravel backend URL
    withCredentials: true, // CRITICAL FOR SANCTUM: Allows sending cookies cross-origin
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// Global Error Interceptor
apiClient.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        // If the backend says we are unauthorized (session expired or not logged in)
        if (error.response && error.response.status === 401) {
            const authStore = useAuthStore();
            authStore.clearAuth(); // Wipe local state
            router.push('/login'); // Kick them back to login
        }
        
        return Promise.reject(error);
    }
);

export default apiClient;