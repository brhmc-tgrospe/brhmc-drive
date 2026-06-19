<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
    login: '', // Changed from email
    password: ''
});

const isLoading = ref(false);
const showPassword = ref(false);

const isSessionExpired = ref(false);

// Optional: Clear errors when the user starts typing again
const clearErrors = () => {
    authStore.authErrors = {};
    isSessionExpired.value = false;
};

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('expired') === '1') {
        isSessionExpired.value = true;
        
        // Clean up URL so it doesn't stay there if they refresh
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

const handleLogin = async () => {
    isLoading.value = true;
    try {
        const success = await authStore.login(form.value);
        if (success) {
            router.push('/dashboard').catch((err)=>{ console.warn('Router push cancelled', err) });
        }
    } catch (error) {
        console.error('Unhandled login error:', error);
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
  <div class="min-h-screen w-full flex bg-slate-50 relative overflow-hidden font-sans">
    
    <!-- COMPACTED MOBILE BACKGROUND ACCENT (Max ~35% height) -->
    <div class="absolute top-0 left-0 w-full h-[35vh] sm:h-[40vh] bg-gradient-to-br from-slate-900 via-teal-950 to-teal-900 lg:hidden rounded-b-[2.5rem] shadow-2xl z-0">
        <!-- Ambient glowing orb for mobile -->
        <div class="absolute top-5 left-1/2 -translate-x-1/2 w-48 h-48 bg-teal-500/20 rounded-full blur-[50px] pointer-events-none"></div>
    </div>

    <div class="w-full min-h-screen flex flex-col lg:flex-row relative z-10">

      <!-- ========================================== -->
      <!-- LEFT PANEL: BRANDING (Visible on Desktop)  -->
      <!-- ========================================== -->
      <div class="hidden lg:flex lg:w-[45%] xl:w-1/2 relative bg-slate-900 text-white flex-col justify-between overflow-hidden shadow-2xl z-20">
        
        <!-- Deep Medical-Tech Gradient & Glows -->
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-teal-950 to-teal-900 z-0"></div>
        
        <!-- Abstract Tech Grid Background -->
        <div class="absolute inset-0 opacity-[0.03] z-0 pointer-events-none" 
             style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 32px 32px;">
        </div>
        
        <!-- Glowing Ambient Orbs -->
        <div class="absolute -top-32 -left-32 w-[30rem] h-[30rem] bg-teal-500/20 rounded-full blur-[100px] z-0 pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-[30rem] h-[30rem] bg-blue-500/10 rounded-full blur-[100px] z-0 pointer-events-none"></div>

        <!-- Content -->
        <div class="relative z-10 p-12 xl:p-20 flex flex-col h-full justify-between">
          
          <!-- Top: Hospital Title -->
          <div class="flex items-center gap-3 fade-in">
            <!-- BRHMC Logo Badge (Solid White Background) -->
            <div class="bg-white p-1 rounded-full shrink-0 shadow-lg ring-2 ring-white/10">
              <img src="/images/brhmc-logo.png" alt="BRHMC Logo" class="w-8 h-8 sm:w-10 sm:h-10 object-contain rounded-full" />
            </div>
            <h2 class="text-sm tracking-widest uppercase font-bold text-teal-100/80 leading-tight">Bicol Regional Hospital<br>& Medical Center</h2>
          </div>

          <!-- Middle: System Identity -->
          <div class="max-w-lg fade-in-up">
            
            <!-- REFACTORED PREMIUM LOGO BADGE -->
            <div class="relative inline-flex items-center justify-center p-6 bg-white rounded-[2rem] mb-10 shadow-[0_20px_50px_-10px_rgba(0,0,0,0.5)] ring-4 ring-white/10 group">
              <!-- Animated Glow effect behind the logo card -->
              <div class="absolute inset-0 bg-teal-400/30 blur-2xl rounded-full opacity-50 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>
              <img src="/images/icon3.png" alt="DRIVE Logo" class="relative z-10 h-24 sm:h-28 w-auto object-contain transition-transform duration-500 group-hover:scale-105 drop-shadow-md" />
            </div>

            <h1 class="text-6xl xl:text-7xl font-black tracking-tight mb-6 leading-[1.1]">
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-400 to-blue-400 drop-shadow-sm">DRIVE</span>
            </h1>
            <p class="text-lg text-teal-50/70 font-medium leading-relaxed max-w-md">
              Dispatch & Routing Information for Vehicle Efficiency. Securely managing the hospital's vital fleet operations.
            </p>
          </div>

          <!-- Bottom: Footer -->
          <div class="text-slate-500 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-teal-500/50" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            Authorized Personnel Only
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- RIGHT PANEL: LOGIN FORM                    -->
      <!-- ========================================== -->
      <!-- 'justify-start lg:justify-center' pulls everything to the top on mobile, fixing the "too low" issue -->
      <div class="w-full lg:w-[55%] xl:w-1/2 flex flex-col items-center justify-start lg:justify-center p-4 sm:p-8 pt-[6vh] sm:pt-[8vh] lg:pt-8 relative z-10 flex-1">
        
        <!-- ========================================== -->
        <!-- MOBILE HERO BRANDING (Hidden on Desktop)   -->
        <!-- ========================================== -->
        <div class="lg:hidden flex flex-col items-center text-center w-full max-w-md mb-6 shrink-0 fade-in">
          
          <!-- Top: Hospital Title (Mobile) -->
          <div class="flex items-center gap-2 sm:gap-3 mb-4">
            <div class="bg-white p-1 rounded-full shrink-0 shadow-md ring-2 ring-white/10">
              <img src="/images/brhmc-logo.png" alt="BRHMC Logo" class="w-6 h-6 sm:w-8 sm:h-8 object-contain rounded-full" />
            </div>
            <h2 class="text-[10px] sm:text-xs tracking-widest uppercase font-bold text-teal-100/90 leading-tight text-left">Bicol Regional Hospital<br>& Medical Center</h2>
          </div>

          <!-- Middle: Compact DRIVE Logo Badge (Mobile) -->
          <div class="relative inline-flex items-center justify-center p-3 sm:p-4 bg-white rounded-[1.25rem] sm:rounded-2xl mb-3 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.4)] ring-4 ring-white/10 group">
            <div class="absolute inset-0 bg-teal-400/30 blur-xl rounded-full opacity-50 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            <img src="/images/icon3.png" alt="DRIVE Logo" class="relative z-10 h-14 sm:h-16 w-auto object-contain transition-transform duration-500 group-hover:scale-105 drop-shadow-md" />
          </div>
          
          <!-- Bottom: Gradient DRIVE Text (Mobile) -->
          <h1 class="text-3xl sm:text-4xl font-black tracking-tight leading-[1.1] mb-0.5">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-400 to-blue-400 drop-shadow-sm">DRIVE</span>
          </h1>
          <p class="text-[9px] sm:text-[10px] font-bold text-teal-200/80 uppercase tracking-widest mt-1">BRHMC Portal</p>
        </div>

        <!-- ========================================== -->
        <!-- WHITE LOGIN CARD                           -->
        <!-- ========================================== -->
        <!-- 'w-full' lets it fill horizontal space perfectly in mobile -->
        <div class="w-full max-w-md bg-white rounded-[2rem] shadow-[0_8px_40px_-12px_rgba(0,0,0,0.15)] p-6 sm:p-12 border border-slate-100 relative overflow-hidden">
          
          <!-- Subtle top accent line -->
          <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-teal-500 to-blue-500"></div>

          <!-- Form Header -->
          <div class="mb-8 lg:mb-10 text-center lg:text-left">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Welcome back</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-2 font-medium">Please enter your credentials to access the dashboard.</p>
          </div>

          <!-- Persistent Session Expired Alert -->
          <div v-if="isSessionExpired" 
               class="mb-8 p-4 bg-orange-50 border border-orange-200/60 rounded-xl flex items-start gap-3 animate-fade-in shadow-sm">
            <div class="bg-orange-100 p-1.5 rounded-lg shrink-0 mt-0.5">
              <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
              <h3 class="text-sm font-bold text-orange-800">Session Expired</h3>
              <p class="mt-1 text-xs font-medium text-orange-700">
                You have been automatically logged out due to inactivity. Please log in again to continue.
              </p>
            </div>
          </div>

          <!-- Elegant Error Alert -->
          <div v-if="authStore.authErrors && Object.keys(authStore.authErrors).length > 0" 
               class="mb-8 p-4 bg-rose-50 border border-rose-200/60 rounded-xl flex items-start gap-3 animate-fade-in shadow-sm">
            <div class="bg-rose-100 p-1.5 rounded-lg shrink-0 mt-0.5">
              <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
              <h3 class="text-sm font-bold text-rose-800">Authentication Failed</h3>
              <ul class="mt-1 text-xs font-medium text-rose-600 space-y-1">
                <li v-for="(errors, field) in authStore.authErrors" :key="field">
                  {{ Array.isArray(errors) ? errors[0] : errors }}
                </li>
              </ul>
            </div>
          </div>

          <!-- The Form -->
          <form @submit.prevent="handleLogin" class="space-y-6">
            
            <!-- Username/Email Field -->
            <div class="space-y-1.5">
              <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider ml-1">Username or Email</label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-teal-500 text-slate-400">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <input 
                  type="text" 
                  v-model="form.login" 
                  @input="clearErrors"
                  required 
                  class="block w-full pl-11 pr-4 py-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100/50 text-slate-800 focus:bg-white focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none font-semibold text-sm shadow-sm placeholder:text-slate-400 placeholder:font-medium" 
                  placeholder="Enter your credential"
                >
              </div>
            </div>

            <!-- Password Field -->
            <div class="space-y-1.5">
              <div class="flex justify-between items-center ml-1">
                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider">Password</label>
              </div>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-teal-500 text-slate-400">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  v-model="form.password" 
                  @input="clearErrors" 
                  required 
                  class="block w-full pl-11 pr-12 py-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100/50 text-slate-800 focus:bg-white focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none font-semibold text-sm shadow-sm placeholder:text-slate-400 placeholder:font-medium"
                  placeholder="••••••••"
                />
                <button 
                  type="button" 
                  @click="showPassword = !showPassword" 
                  class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-teal-600 transition-colors focus:outline-none"
                >
                  <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.978 9.978 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                </button>
              </div>
            </div>

            <!-- Submit Button -->
            <button 
              type="submit" 
              :disabled="isLoading"
              class="group relative w-full flex justify-center items-center py-4 px-4 rounded-xl font-bold text-white bg-teal-600 hover:bg-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-500/30 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-teal-600/20 overflow-hidden disabled:opacity-70 disabled:cursor-wait mt-8"
            >
              <!-- Loading State -->
              <span v-if="isLoading" class="flex items-center gap-2 relative z-10">
                <svg class="animate-spin h-5 w-5 text-white/90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Authenticating...
              </span>
              
              <!-- Ready State -->
              <span v-else class="flex items-center gap-2 relative z-10">
                Access System
                <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
              </span>
            </button>
          </form>

        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Smooth entrance animations for the left panel */
.fade-in {
  animation: fadeIn 1s ease-out forwards;
}
.fade-in-up {
  animation: fadeInUp 1s ease-out forwards;
}
.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>