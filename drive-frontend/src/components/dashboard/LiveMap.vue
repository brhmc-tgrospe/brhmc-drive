<template>
  <div class="flex flex-col w-full h-full min-h-[400px] bg-slate-50 border border-slate-200 rounded-xl shadow-sm overflow-hidden relative z-0">
    
    <div class="p-3 sm:p-4 xl:py-2 xl:px-4 bg-white/90 backdrop-blur-sm border-b border-slate-100 z-[1000] flex justify-between items-center shrink-0 shadow-sm">
      <div class="flex items-center justify-between w-full gap-2 sm:gap-3">
         <div>
            <h3 class="font-extrabold text-slate-800 text-sm sm:text-base leading-tight">Live Map Tracker</h3>
            <p class="hidden sm:block text-[10px] sm:text-xs text-slate-500 font-medium leading-tight">Active units and real-time GPS telemetry</p>
         </div>
         
         <div class="flex items-center gap-1.5 bg-green-500/10 border border-green-500/20 px-2 py-1 rounded text-green-600 text-[9px] uppercase font-black tracking-widest backdrop-blur-sm">
            <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span></span>
            Live Sync Active
         </div>
      </div>
    </div>
    
    <div ref="mapContainer" class="flex-1 w-full relative z-0"></div>

    <div class="absolute bottom-6 right-6 z-[1000] flex flex-col bg-white/95 backdrop-blur-md border border-slate-200 rounded-xl shadow-lg overflow-hidden">
       <button @click="zoomIn" class="p-3 hover:bg-slate-100 border-b border-slate-200 text-slate-700 transition-colors active:bg-slate-200" title="Zoom In">
         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
       </button>
       <button @click="zoomOut" class="p-3 hover:bg-slate-100 text-slate-700 transition-colors active:bg-slate-200" title="Zoom Out">
         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path></svg>
       </button>
    </div>

  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, shallowRef } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import api from '../../axios';
import echo from '../../echo';

const props = defineProps({
  selectedVehicles: { type: Array, default: () => [] }
});

const mapContainer = ref(null);
const map = shallowRef(null);
const vehicleMarkers = shallowRef({});
const vehicleTrails = shallowRef({}); 
const phaseMarkers = shallowRef({}); 
const incidentMarkers = shallowRef({}); 

const BASE_LAT = 13.146820; 
const BASE_LNG = 123.724505;

onMounted(() => {
  if (!mapContainer.value) return;

  map.value = L.map(mapContainer.value, {
    zoomControl: false,
    attributionControl: false 
  }).setView([BASE_LAT, BASE_LNG], 13);

  L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    maxZoom: 19,
  }).addTo(map.value);

  addLandmarks();

  fetchLiveTelemetry();
  
  echo.private('dispatch.fleet')
    .listen('.vehicle.moved', fetchLiveTelemetry);

  echo.channel('fleet-updates')
    .listen('TripPhaseAdvanced', fetchLiveTelemetry)
    .listen('IncidentReported', fetchLiveTelemetry)
    .listen('.vehicle.status.changed', fetchLiveTelemetry);

  echo.private('dispatch.alerts')
    .listen('.emergency.triggered', fetchLiveTelemetry);
});

onUnmounted(() => {
  echo.private('dispatch.fleet').stopListening('.vehicle.moved', fetchLiveTelemetry);
  echo.channel('fleet-updates')
    .stopListening('TripPhaseAdvanced', fetchLiveTelemetry)
    .stopListening('IncidentReported', fetchLiveTelemetry)
    .stopListening('.vehicle.status.changed', fetchLiveTelemetry);
  echo.private('dispatch.alerts').stopListening('.emergency.triggered', fetchLiveTelemetry);

  if (map.value) { map.value.remove(); map.value = null; }
});

const zoomIn = () => map.value?.zoomIn();
const zoomOut = () => map.value?.zoomOut();

const getStatusTextColor = (status) => {
    if (status === 'READY') return 'text-teal-500';
    if (status === 'SCHEDULED') return 'text-purple-500';
    if (status === 'IN_USE') return 'text-blue-500';
    if (status === 'MAINTENANCE' || status === 'BREAKDOWN') return 'text-red-500';
    return 'text-slate-500';
};

const getStatusHexColor = (status) => {
    if (status === 'READY') return '#14b8a6'; 
    if (status === 'SCHEDULED') return '#a855f7'; 
    if (status === 'IN_USE') return '#3b82f6'; 
    if (status === 'MAINTENANCE' || status === 'BREAKDOWN') return '#ef4444'; 
    return '#64748b'; 
};

const getPhaseTitle = (phase) => {
    const titles = { 1: "Pre-Trip Verified", 
    2: "Dispatched from Base", 
    3: "Arrived at Scene", 
    4: "Dispatched from Scene", 
    5: "Arrived at Hospital", 
    6: "Proceeding to Base", 
    7: "Arrived at Base", 
    8: "Post-Trip Inspection Submitted" };
    return titles[phase] || `Phase ${phase}`;
};

const createCustomIcon = (vehicle) => {
    const textColorClass = getStatusTextColor(vehicle.status);
    const bounceClass = vehicle.status === 'READY' ? 'animate-bounce-short' : '';
    
    const html = `
      <div class="relative flex items-center justify-center ${bounceClass}">
         <svg class="w-10 h-10 ${textColorClass} drop-shadow-md" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z" stroke="white" stroke-width="1.5" stroke-linejoin="round"/>
         </svg>
         <div class="absolute top-[8px] text-white">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
         </div>
         <span class="absolute left-10 bg-slate-800/80 backdrop-blur-sm text-white px-2 py-1 rounded shadow-sm border border-slate-700 text-[10px] font-bold tracking-wider whitespace-nowrap z-50">
            ${vehicle.unit_id}
         </span>
      </div>
    `;
    return L.divIcon({ html: html, className: 'vehicle-marker', iconSize: [40, 40], iconAnchor: [20, 38] });
};

const addLandmarks = () => {
    if (!map.value) return;

    const brhmcHtml = `
      <div class="relative flex items-center justify-center opacity-90 hover:opacity-100 transition-opacity cursor-help">
         <svg class="w-8 h-8 text-green-600 drop-shadow-md" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z" stroke="white" stroke-width="1.5" stroke-linejoin="round"/></svg>
         <div class="absolute top-[6px] text-white"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>
         <span class="absolute left-8 bg-green-50 backdrop-blur-sm border border-green-200 px-2 py-0.5 rounded shadow-sm text-[10px] font-bold text-green-700 whitespace-nowrap z-50">BRHMC</span>
      </div>`;
    L.marker([13.146820, 123.724505], { icon: L.divIcon({ html: brhmcHtml, className: '', iconSize: [32, 32], iconAnchor: [16, 30] }) }).addTo(map.value);
};

// ==========================================
// SCOPE FIX: fetchLiveTelemetry at Root Level
// ==========================================
const fetchLiveTelemetry = async () => {
    if (!map.value || props.selectedVehicles.length === 0) return;

    try {
        const response = await api.get('/api/telemetry/locations');
        const dbLocations = response.data; 

        props.selectedVehicles.forEach(vehicle => {
            const liveData = dbLocations.find(loc => loc.id === vehicle.id);
            
            if (liveData) {
                // Plot Phase Tap Pins
                if (liveData.logs && liveData.logs.length > 0) {
                    if (!phaseMarkers.value[vehicle.id]) phaseMarkers.value[vehicle.id] = {};
                    
                    liveData.logs.forEach(log => {
                        if (!phaseMarkers.value[vehicle.id][log.phase]) {
                            const html = `
                              <div class="relative flex flex-col items-center justify-center -mt-4 cursor-pointer hover:scale-110 transition-transform">
                                 <div class="w-5 h-5 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-[10px] border-2 border-white shadow-md z-40">
                                    ${log.phase}
                                 </div>
                                 <div class="w-1 h-3 bg-indigo-600 -mt-1 z-30 rounded-b shadow"></div>
                              </div>`;
                            
                            const marker = L.marker([log.latitude, log.longitude], {
                                icon: L.divIcon({ html: html, className: '', iconSize: [20, 28], iconAnchor: [10, 28] })
                            });

                            const time = new Date(log.created_at.endsWith('Z') ? log.created_at : log.created_at.replace(' ', 'T') + '+08:00').toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour: 'numeric', minute: '2-digit', hour12: true });
                            marker.bindPopup(`<div class="text-center p-1"><b class="text-indigo-700 uppercase tracking-wider text-[10px]">${getPhaseTitle(log.phase)}</b><br/><span class="text-xs font-medium text-slate-500">Recorded: ${time}</span></div>`);
                            marker.addTo(map.value);
                            phaseMarkers.value[vehicle.id][log.phase] = marker;
                        }
                    });
                }

                // Plot Emergency/Incident Pins
                if (liveData.incidents && liveData.incidents.length > 0) {
                    if (!incidentMarkers.value[vehicle.id]) incidentMarkers.value[vehicle.id] = {};

                    liveData.incidents.forEach(inc => {
                        if (!incidentMarkers.value[vehicle.id][inc.id]) {
                            const html = `
                              <div class="relative flex items-center justify-center animate-pulse cursor-pointer hover:scale-110 transition-transform">
                                 <div class="w-6 h-6 bg-red-600 text-white rounded-md flex items-center justify-center font-black text-sm border-2 border-white shadow-[0_0_10px_rgba(220,38,38,0.8)] z-50">!</div>
                              </div>`;
                              
                            const marker = L.marker([inc.latitude, inc.longitude], {
                                icon: L.divIcon({ html: html, className: '', iconSize: [24, 24], iconAnchor: [12, 12] })
                            });

                            const time = new Date(inc.created_at.endsWith('Z') ? inc.created_at : inc.created_at.replace(' ', 'T') + '+08:00').toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour: 'numeric', minute: '2-digit', hour12: true });
                            marker.bindPopup(`<div class="text-center p-1"><b class="text-red-600 uppercase text-[10px] font-black tracking-widest">🚨 ${inc.issue_type}</b><br/><span class="text-[10px] text-slate-700 font-bold uppercase">Target: ${inc.incident_target}</span><br/><span class="text-xs font-medium text-slate-500 block mt-1">Reported: ${time}</span></div>`);
                            marker.addTo(map.value);
                            incidentMarkers.value[vehicle.id][inc.id] = marker;
                        }
                    });
                }

                // Plot Live Vehicle Location
                if (liveData.current_lat && liveData.current_lng) {
                    const lat = parseFloat(liveData.current_lat);
                    const lng = parseFloat(liveData.current_lng);

                    if (!vehicleMarkers.value[vehicle.id]) {
                        vehicleMarkers.value[vehicle.id] = L.marker([lat, lng], { icon: createCustomIcon(vehicle) }).addTo(map.value);
                    } else {
                        vehicleMarkers.value[vehicle.id].setLatLng([lat, lng]);
                        vehicleMarkers.value[vehicle.id].setIcon(createCustomIcon(vehicle));
                    }

                    if (vehicle.status === 'IN_USE') {
                        if (!vehicleTrails.value[vehicle.id]) {
                            vehicleTrails.value[vehicle.id] = L.polyline([[lat, lng]], {
                                color: getStatusHexColor(vehicle.status), weight: 4, opacity: 0.7, dashArray: '8, 8', lineJoin: 'round'
                            }).addTo(map.value);
                        } else {
                            vehicleTrails.value[vehicle.id].addLatLng([lat, lng]);
                        }
                    } else if (vehicleTrails.value[vehicle.id]) {
                        map.value.removeLayer(vehicleTrails.value[vehicle.id]);
                        delete vehicleTrails.value[vehicle.id];
                    }
                } else {
                    const offsetLat = (vehicle.id % 5) * 0.0001; 
                    const offsetLng = (vehicle.id % 5) * 0.0001;
                    const parkLat = BASE_LAT + offsetLat;
                    const parkLng = BASE_LNG + offsetLng;

                    if (!vehicleMarkers.value[vehicle.id]) {
                        vehicleMarkers.value[vehicle.id] = L.marker([parkLat, parkLng], { icon: createCustomIcon(vehicle) }).addTo(map.value);
                    } else {
                        vehicleMarkers.value[vehicle.id].setLatLng([parkLat, parkLng]);
                        vehicleMarkers.value[vehicle.id].setIcon(createCustomIcon(vehicle));
                    }
                }
            }
        });

        // Cleanup unselected vehicles
        Object.keys(vehicleMarkers.value).forEach(id => {
            if (!props.selectedVehicles.find(v => String(v.id) === String(id))) {
                map.value.removeLayer(vehicleMarkers.value[id]);
                delete vehicleMarkers.value[id];
                if (vehicleTrails.value[id]) { map.value.removeLayer(vehicleTrails.value[id]); delete vehicleTrails.value[id]; }
                if (phaseMarkers.value[id]) { Object.values(phaseMarkers.value[id]).forEach(m => map.value.removeLayer(m)); delete phaseMarkers.value[id]; }
                if (incidentMarkers.value[id]) { Object.values(incidentMarkers.value[id]).forEach(m => map.value.removeLayer(m)); delete incidentMarkers.value[id]; }
            }
        });

    } catch (e) {}
};

watch(() => props.selectedVehicles, (newSelection) => {
    fetchLiveTelemetry(); 

    if (newSelection.length === 1 && map.value) {
        const selectedId = newSelection[0].id;
        const marker = vehicleMarkers.value[selectedId];
        if (marker) {
            map.value.flyTo(marker.getLatLng(), 15, { duration: 1.5 });
        }
    }
}, { deep: true });

</script>

<style>
.vehicle-marker {
  transition: transform 3s linear;
}

@keyframes bounce-short {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}
.animate-bounce-short {
  animation: bounce-short 1.5s ease-in-out infinite;
}
</style>