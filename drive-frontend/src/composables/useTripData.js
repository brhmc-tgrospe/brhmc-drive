import { ref, watch, computed } from 'vue';
import { useTripLogStore } from '../stores/tripLog';

export function useTripData(props, onTripClosed = () => {}) {
    const tripLogStore = useTripLogStore();
    const loading = ref(false);
    const tripDetails = ref(null);

    watch(() => props.show, async (newVal) => {
        if (newVal && props.tripId) {
            loading.value = true;
            try {
                tripDetails.value = await tripLogStore.getTripDetails(props.tripId);
            } catch (e) {
                console.error(e);
            } finally {
                loading.value = false;
            }
        } else {
            tripDetails.value = null;
            onTripClosed();
        }
    });

    const getPhaseName = (logOrPhase) => {
        const tripType = tripDetails.value?.type || 'EMERGENCY';
        
        let phase = logOrPhase;
        let actionLabel = null;
        let destination = null;

        if (typeof logOrPhase === 'object' && logOrPhase !== null) {
            phase = logOrPhase.phase;
            actionLabel = logOrPhase.action_label;
            destination = logOrPhase.destination;
        }

        if (tripType === 'REGULAR') {
            if (actionLabel === 'dispatch_from_base') {
                return destination ? `Dispatched to ${destination}` : 'Dispatched from Base';
            }
            if (actionLabel === 'next_destination') {
                return destination ? `Proceeding to ${destination}` : 'Proceeding to Next Destination';
            }
            if (actionLabel === 'arrive_destination') {
                return destination ? `Arrived at ${destination}` : 'Arrived at Destination';
            }
            if (actionLabel === 'return_base') {
                return 'Returning to Base';
            }
            if (actionLabel === 'arrive_base') {
                return 'Arrived at Base';
            }
            
            // Fallback for regular
            if (phase === 1) return "Pre-Trip Verified";
            if (phase === 3) return destination ? `En route to ${destination}` : "En route to Destination";
            if (phase === 4) return destination ? `Arrived at ${destination}` : "Arrived at Destination";
            if (phase === 5) return "Returning to Base";
            if (phase === 6) return "Arrived at Base";
            if (phase === 7 || phase === 8) return "Post-Trip Submitted";
        }

        const emergencyTitles = { 
            1: "Pre-Trip Verified", 
            2: "Dispatched from Base", 
            3: "Arrived at Scene", 
            4: "Dispatched from Scene", 
            5: "Arrived at Hospital", 
            6: "Proceeding to Base", 
            7: "Arrived at Base", 
            8: "Post-Trip Submitted" 
        };
        return emergencyTitles[phase] || `Phase ${phase}`;
    };

    const tripDuration = computed(() => {
        if (!tripDetails.value?.started_at) return 'Not Started';
        const start = new Date(tripDetails.value.started_at);
        const end = tripDetails.value.ended_at ? new Date(tripDetails.value.ended_at) : new Date();
        const diffMins = Math.round((end - start) / 60000);
        const hrs = Math.floor(diffMins / 60);
        const mins = diffMins % 60;
        return `${hrs}h ${mins}m`;
    });

    const formatTimeOnly = (dateString) => {
        if (!dateString) return '';
        const utcDate = dateString.endsWith('Z') ? dateString : dateString.replace(' ', 'T') + '+08:00';
        return new Date(utcDate).toLocaleString('en-US', { 
            timeZone: 'Asia/Manila', 
            month: 'short', 
            day: 'numeric', 
            hour: 'numeric', 
            minute: '2-digit', 
            hour12: true 
        });
    };

    return {
        loading,
        tripDetails,
        getPhaseName,
        tripDuration,
        formatTimeOnly
    };
}
