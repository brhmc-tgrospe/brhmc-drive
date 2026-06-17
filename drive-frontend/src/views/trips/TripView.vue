<template>
  <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-2xl flex flex-col border border-slate-200 overflow-hidden animate-fade-in-up max-h-[90vh]">
      <div class="px-4 py-3 sm:px-5 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
        <div>
          <h3 class="text-base font-bold text-slate-800">Trip Execution Log (TRP-{{ tripId }})</h3>
        </div>
        <button @click="$emit('close')" class="text-slate-400 hover:text-red-500 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      
      <div v-if="loading" class="p-10 flex justify-center">
         <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
      </div>

      <div v-else-if="tripDetails" class="p-4 sm:p-5 flex-1 overflow-y-auto custom-scrollbar space-y-5">
        
        <!-- SHIFT OVERVIEW -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
           <div>
               <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Driver</span>
               <span class="text-xs font-black text-slate-700">{{ tripDetails.driver_name }}</span>
           </div>
           <div>
               <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Vehicle</span>
               <span class="text-xs font-black text-slate-700">{{ tripDetails.vehicle_unit }}</span>
           </div>
           <div>
               <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</span>
               <span class="text-[10px] px-2 py-0.5 rounded font-bold uppercase" :class="tripDetails.current_phase >= 8 ? 'bg-teal-100 text-teal-700' : 'bg-blue-100 text-blue-700'">
                 {{ tripDetails.current_phase >= 8 ? 'COMPLETED' : `PHASE ${tripDetails.current_phase}` }}
               </span>
           </div>
           <div>
               <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Time Active</span>
               <span class="text-xs font-bold text-slate-700">{{ tripDuration }}</span>
           </div>
        </div>

        <!-- GPS TIMELINE -->
        <div>
            <div class="flex justify-between items-end border-b border-slate-200 pb-2 mb-3">
                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">GPS Execution Timeline</h4>
                <button @click="viewAllPins" class="text-[10px] font-bold text-teal-600 hover:text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-200 px-3 py-1 rounded-lg transition-colors flex items-center gap-1.5 shadow-sm active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    View Full Route
                </button>
            </div>
            
            <table class="w-full text-left text-xs border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 border-b border-slate-200 text-[10px] uppercase text-slate-500">
                    <tr>
                        <th class="p-2.5">Phase Action</th>
                        <th class="p-2.5">Timestamp</th>
                        <th class="p-2.5">GPS Location</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="!tripDetails.logs || tripDetails.logs.length === 0">
                        <td colspan="3" class="p-4 text-center text-slate-400 italic">No GPS coordinates recorded yet.</td>
                    </tr>
                    <tr v-for="log in tripDetails.logs" :key="log.id" class="hover:bg-slate-50">
                        <td class="p-2.5 font-bold text-slate-700">{{ getPhaseName(log.phase) }}</td>
                        <td class="p-2.5 text-slate-600">{{ formatTimeOnly(log.created_at) }}</td>
                        <td class="p-2.5">
                            <button v-if="log.latitude && log.longitude" 
                               @click="openMapModal([{ lat: log.latitude, lng: log.longitude, title: getPhaseName(log.phase), isIncident: false, phase: log.phase, time: formatTimeOnly(log.created_at) }], getPhaseName(log.phase))" 
                               class="text-blue-600 hover:text-blue-800 font-mono font-medium underline decoration-blue-300 underline-offset-2 flex items-center gap-1.5 transition-colors">
                               <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                               {{ log.latitude }}, {{ log.longitude }}
                            </button>
                            <span v-else class="text-slate-400 italic font-mono text-[10px]">GPS Offline</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MID-SHIFT INCIDENTS -->
        <div v-if="tripDetails.incidents && tripDetails.incidents.length > 0" class="mt-4">
            <h4 class="text-xs font-black text-red-600 uppercase tracking-widest border-b border-red-200 pb-2 mb-3">Mid-Shift Emergency Incidents</h4>
            <div v-for="inc in tripDetails.incidents" :key="inc.id" class="bg-red-50 border border-red-200 p-3 rounded-lg mb-2">
                <div class="flex justify-between items-start mb-1">
                    <span class="font-bold text-red-800 text-xs">{{ inc.issue_type }}</span>
                    <span class="text-[9px] font-bold text-red-600 uppercase">{{ inc.status }}</span>
                </div>
                <div class="flex justify-between items-center text-[10px] text-slate-600 mt-2">
                    <span>{{ formatTimeOnly(inc.created_at) }}</span>
                    <button v-if="inc.latitude && inc.longitude" 
                        @click="openMapModal([{ lat: inc.latitude, lng: inc.longitude, title: `🚨 ${inc.issue_type}`, isIncident: true, time: formatTimeOnly(inc.created_at) }], `Emergency: ${inc.issue_type}`)" 
                        class="text-blue-600 hover:text-blue-800 font-mono font-medium underline decoration-blue-300 underline-offset-2 flex items-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        {{ inc.latitude }}, {{ inc.longitude }}
                    </button>
                </div>
            </div>
        </div>

      </div>
    </div>
    
    <!-- ============================================== -->
    <!-- INNER MAP MODAL (Displays Leaflet Route/Pins)  -->
    <!-- ============================================== -->
    <div v-if="isMapModalOpen" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="closeMapModal"></div>
      
      <div class="relative w-full max-w-3xl bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden animate-fade-in-up border border-slate-400">
        <div class="px-4 py-3 bg-slate-800 text-white flex items-center justify-between shadow-md z-10">
          <div>
            <h3 class="text-sm font-bold tracking-wide uppercase">{{ activeMapTitle }}</h3>
            <!-- Dynamically changes header if multiple pins are shown -->
            <p class="text-[10px] text-slate-400 font-mono">
               <span v-if="activeMarkers.length === 1">Lat: {{ activeMarkers[0].lat }}, Lng: {{ activeMarkers[0].lng }}</span>
               <span v-else>{{ activeMarkers.length }} GPS Data Points Plotted</span>
            </p>
          </div>
          <button @click="closeMapModal" class="text-slate-400 hover:text-white transition-colors bg-slate-700 p-1.5 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        
        <!-- Leaflet Container -->
        <div ref="miniMapContainer" class="w-full h-[400px] sm:h-[500px] bg-slate-100 relative z-0"></div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, watch, computed, nextTick, shallowRef } from 'vue';
import { useToastStore } from '../../stores/toast';
import { useTripData } from '../../composables/useTripData';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({ show: Boolean, tripId: Number });
const emit = defineEmits(['close']);

const toastStore = useToastStore();

// MAP MODAL STATE
const isMapModalOpen = ref(false);
const activeMarkers = ref([]);
const activeMapTitle = ref('');
const miniMapContainer = ref(null);
const mapInstance = shallowRef(null);

const closeMapModal = () => {
    isMapModalOpen.value = false;
    if (mapInstance.value) {
        mapInstance.value.remove();
        mapInstance.value = null;
    }
};

const { loading, tripDetails, getPhaseName, tripDuration, formatTimeOnly } = useTripData(props, closeMapModal);


// ========================================================
// LEAFLET MODAL LOGIC WITH OSRM ROAD ROUTING
// ========================================================
const viewAllPins = () => {
    const markers = [];
    
    // Add Phase Pins
    if (tripDetails.value?.logs) {
        tripDetails.value.logs.forEach(log => {
            if (log.latitude && log.longitude) {
                markers.push({ 
                    lat: log.latitude, 
                    lng: log.longitude, 
                    title: getPhaseName(log.phase), 
                    isIncident: false, 
                    phase: log.phase,
                    time: formatTimeOnly(log.created_at)
                });
            }
        });
    }
    
    // Add Incident Pins
    if (tripDetails.value?.incidents) {
        tripDetails.value.incidents.forEach(inc => {
            if (inc.latitude && inc.longitude) {
                markers.push({ 
                    lat: inc.latitude, 
                    lng: inc.longitude, 
                    title: `🚨 ${inc.issue_type}`, 
                    isIncident: true,
                    time: formatTimeOnly(inc.created_at)
                });
            }
        });
    }

    if (markers.length > 0) {
        openMapModal(markers, 'Complete Trip Route & History');
    } else {
        toastStore.show('No GPS data was recorded for this trip.', 'error');
    }
};

const openMapModal = async (markers, title) => {
    activeMarkers.value = markers;
    activeMapTitle.value = title;
    isMapModalOpen.value = true;

    await nextTick();

    if (mapInstance.value) {
        mapInstance.value.remove();
        mapInstance.value = null;
    }

    if (miniMapContainer.value) {
        mapInstance.value = L.map(miniMapContainer.value, {
            zoomControl: true,
            attributionControl: false
        });

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
        }).addTo(mapInstance.value);

        const bounds = L.latLngBounds();
        const routePoints = [];

        markers.forEach(m => {
            let html = '';
            
            if (m.isIncident) {
                html = `<div class="relative flex flex-col items-center justify-center animate-pulse cursor-pointer group hover:scale-110 transition-transform">
                           <div class="w-6 h-6 bg-red-600 text-white rounded-md flex items-center justify-center font-black text-sm border-2 border-white shadow-[0_0_10px_rgba(220,38,38,0.8)] z-50">!</div>
                           <div class="absolute left-8 top-0 bg-slate-800/90 backdrop-blur-sm text-white px-2 py-1 rounded shadow-sm border border-slate-700 z-50 flex flex-col pointer-events-none transition-all">
                              <span class="text-[10px] font-bold tracking-wider whitespace-nowrap">${m.title}</span>
                              <span class="text-[9px] text-red-300 font-mono whitespace-nowrap hidden group-hover:block mt-0.5">${m.time}</span>
                           </div>
                        </div>`;
            } else {
                html = `<div class="relative flex flex-col items-center justify-center -mt-4 cursor-pointer group hover:scale-110 transition-transform">
                           <div class="w-5 h-5 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-[10px] border-2 border-white shadow-md z-40">
                              ${m.phase}
                           </div>
                           <div class="w-1 h-3 bg-indigo-600 -mt-1 z-30 rounded-b shadow"></div>
                           <div class="absolute left-6 top-0 bg-slate-800/90 backdrop-blur-sm text-white px-2 py-1 rounded shadow-sm border border-slate-700 z-50 flex flex-col pointer-events-none transition-all">
                              <span class="text-[10px] font-bold tracking-wider whitespace-nowrap">${m.title}</span>
                              <span class="text-[9px] text-teal-300 font-mono whitespace-nowrap hidden group-hover:block mt-0.5">Recorded: ${m.time}</span>
                           </div>
                        </div>`;
                if (!m.isIncident) routePoints.push([m.lat, m.lng]); 
            }

            const customIcon = L.divIcon({ html: html, className: '', iconSize: [20, 28], iconAnchor: [10, 28] });
            L.marker([m.lat, m.lng], { icon: customIcon }).addTo(mapInstance.value);
            bounds.extend([m.lat, m.lng]);
        });

        // ========================================================
        // CRITICAL FIX: OSRM ROAD-SNAPPED ROUTING API
        // ========================================================
        if (routePoints.length > 1) {
            try {
                // OSRM Format requires Lng,Lat order
                const coordinates = routePoints.map(p => `${p[1]},${p[0]}`).join(';');
                const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${coordinates}?overview=full&geometries=geojson`;
                
                const res = await fetch(osrmUrl);
                const data = await res.json();
                
                if (data.routes && data.routes.length > 0) {
                    L.geoJSON(data.routes[0].geometry, {
                        style: { color: '#4f46e5', weight: 4, opacity: 0.8 }
                    }).addTo(mapInstance.value);
                } else {
                    // Fallback to straight dashed lines if OSRM is busy
                    L.polyline(routePoints, { color: '#4f46e5', weight: 4, opacity: 0.6, dashArray: '8, 8', lineJoin: 'round' }).addTo(mapInstance.value);
                }
            } catch (e) {
                // Fallback on network error
                L.polyline(routePoints, { color: '#4f46e5', weight: 4, opacity: 0.6, dashArray: '8, 8', lineJoin: 'round' }).addTo(mapInstance.value);
            }
        }

        // Auto-center the camera
        if (markers.length > 1) {
            mapInstance.value.fitBounds(bounds, { padding: [50, 50] });
        } else if (markers.length === 1) {
            mapInstance.value.setView([markers[0].lat, markers[0].lng], 16);
        }

        setTimeout(() => {
            if (mapInstance.value) {
                mapInstance.value.invalidateSize();
                if (markers.length > 1) mapInstance.value.fitBounds(bounds, { padding: [50, 50] });
            }
        }, 150);
    }
};

</script>