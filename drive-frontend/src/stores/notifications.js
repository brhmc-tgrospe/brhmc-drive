import { defineStore } from 'pinia';
import { useAuthStore } from './auth';
import echo from '../echo';
import api from '../axios';

// Function to generate a pleasant beep using Web Audio API
const playBeep = () => {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();

        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
        oscillator.frequency.exponentialRampToValueAtTime(1760, audioCtx.currentTime + 0.1);

        gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
        gainNode.gain.linearRampToValueAtTime(0.5, audioCtx.currentTime + 0.05);
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);

        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        oscillator.start(audioCtx.currentTime);
        oscillator.stop(audioCtx.currentTime + 0.5);
    } catch (e) {
        console.warn('Audio Context not supported or allowed by browser', e);
    }
};

export const useNotificationStore = defineStore('notifications', {
    state: () => ({
        items: [],
        isListening: false,
        _shiftCheckInterval: null,
        _notifiedShiftIds: new Set()
    }),
    getters: {
        unreadCount: (state) => state.items.filter(n => !n.read).length
    },
    actions: {
        addNotification(type, title, message, color) {
            this.items.unshift({
                id: Date.now() + Math.random().toString(36).substring(2, 9),
                type,
                title,
                message,
                color,
                read: false,
                timestamp: new Date().toISOString()
            });
            if (this.items.length > 50) {
                this.items.pop();
            }
            playBeep();
        },
        markAllAsRead() {
            this.items.forEach(n => n.read = true);
        },

        listenForAlerts() {
            if (this.isListening) return;
            this.isListening = true;

            const authStore = useAuthStore();
            const user = authStore.user;
            const role = user?.role;
            const userId = user?.id;

            console.log(`[DRIVE Notifications] Initializing for role: ${role}, userId: ${userId}`);

            // =====================================================
            // DISPATCHER / ADMIN / DEVELOPER — fleet-wide view
            // =====================================================
            if (['dispatcher', 'admin', 'developer'].includes(role)) {
                const fleetChannel = echo.channel('fleet-updates');

                fleetChannel.listen('.vehicle.status.changed', (e) => {
                    console.log('[DRIVE Notifications] Vehicle status changed:', e);
                    const unit = e.vehicle?.unit_id || 'A Vehicle';
                    let color = 'blue';
                    if (e.newStatus === 'BREAKDOWN') color = 'red';
                    if (e.newStatus === 'READY') color = 'teal';
                    if (e.newStatus === 'MAINTENANCE') color = 'orange';

                    this.addNotification(
                        'status',
                        'Vehicle Status Updated',
                        `<span class="font-bold">${unit}</span> changed from ${e.oldStatus} to ${e.newStatus}.`,
                        color
                    );
                });

                fleetChannel.listen('.App\\Events\\ChecklistSubmitted', (e) => {
                    console.log('[DRIVE Notifications] Checklist submitted:', e);
                    const driver = e.driver_name || 'A driver';
                    const vehicle = e.vehicle_unit || '—';
                    const type = e.type || 'Checklist';
                    const time = e.scheduled_start ? new Date(e.scheduled_start).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
                    const timeStr = time ? ` · ${time}` : '';

                    this.addNotification(
                        'checklist',
                        `${type} Inspection`,
                        `<span class="font-bold">${driver}</span> submitted a <span class="font-bold">${type}</span> for <span class="font-bold">${vehicle}</span>${timeStr}`,
                        'yellow'
                    );
                });

                fleetChannel.listen('.App\\Events\\IncidentReported', (e) => {
                    console.log('[DRIVE Notifications] Incident reported:', e);
                    this.addNotification(
                        'incident',
                        'Incident Reported',
                        e.message || 'A driver reported an incident.',
                        'red'
                    );
                });

                fleetChannel.listen('.App\\Events\\TripPhaseAdvanced', (e) => {
                    console.log('[DRIVE Notifications] Trip phase advanced:', e);
                    const driver = e.driver_name || 'A driver';
                    let phaseText = '';
                    if (e.phase === 4) phaseText = 'has arrived at patient location';
                    else if (e.phase === 6) phaseText = 'has arrived at the hospital';
                    else if (e.phase === 8) phaseText = 'has completed the run';

                    if (phaseText !== '') {
                        this.addNotification(
                            'trip',
                            'Trip Update',
                            `Trip ${e.trip_id}: <span class="font-bold">${driver}</span> ${phaseText}.`,
                            'indigo'
                        );
                    }
                });

                // Private dispatch.alerts channel for emergencies
                try {
                    echo.private('dispatch.alerts')
                        .listen('.emergency.triggered', (e) => {
                            console.log('[DRIVE Notifications] Emergency:', e);
                            const plate = e.emergency?.plate_number || 'A Vehicle';
                            this.addNotification(
                                'emergency',
                                'Emergency Reported',
                                `<span class="font-bold">${plate}</span> reported a mechanical issue or emergency.`,
                                'red'
                            );
                        });
                } catch (err) {
                    console.warn('[DRIVE Notifications] Private channel auth failed (non-critical):', err);
                }

                console.log('[DRIVE Notifications] Dispatcher listeners active.');
            }

            // =====================================================
            // DRIVER — personal channel only + shift reminders
            // =====================================================
            if (role === 'driver' && userId) {
                try {
                    const driverChannel = echo.private(`driver.${userId}`);

                    // Checklist confirmation (your own submission was received)
                    driverChannel.listen('.App\\Events\\ChecklistSubmitted', (e) => {
                        console.log('[DRIVE Notifications] Your checklist submitted:', e);
                        const type = e.type || 'Checklist';
                        const vehicle = e.vehicle_unit || '—';

                        this.addNotification(
                            'checklist',
                            `${type} Submitted`,
                            `Your <span class="font-bold">${type}</span> inspection for <span class="font-bold">${vehicle}</span> has been received.`,
                            'teal'
                        );
                    });

                    // Trip milestone updates for your own trip
                    driverChannel.listen('.App\\Events\\TripPhaseAdvanced', (e) => {
                        console.log('[DRIVE Notifications] Your trip phase advanced:', e);
                        let phaseText = '';
                        if (e.phase === 2) phaseText = 'Trip started — en route to patient.';
                        else if (e.phase === 4) phaseText = 'Arrived at patient location.';
                        else if (e.phase === 6) phaseText = 'Arrived at the hospital.';
                        else if (e.phase === 8) phaseText = 'Trip completed. Great work!';

                        if (phaseText !== '') {
                            this.addNotification(
                                'trip',
                                `Trip ${e.trip_id} Update`,
                                phaseText,
                                'indigo'
                            );
                        }
                    });

                    console.log('[DRIVE Notifications] Driver private channel active.');
                } catch (err) {
                    console.warn('[DRIVE Notifications] Driver private channel failed:', err);
                }

                // Start checking for upcoming shift reminders
                this._startShiftReminders();
            }

            console.log('[DRIVE Notifications] All listeners initialized.');
        },

        // =====================================================
        // DRIVER SHIFT REMINDERS (client-side polling)
        // Checks every 5 minutes if a shift starts within 1 hour
        // =====================================================
        async _startShiftReminders() {
            // Run immediately on first load, then every 5 minutes
            await this._checkUpcomingShifts();
            this._shiftCheckInterval = setInterval(() => {
                this._checkUpcomingShifts();
            }, 5 * 60 * 1000);
        },

        async _checkUpcomingShifts() {
            try {
                const response = await api.get('/api/shifts/my-shifts');
                const shifts = response.data || [];
                const now = new Date();

                for (const shift of shifts) {
                    const startTime = new Date(shift.start_time);
                    const diffMs = startTime - now;
                    const diffMinutes = diffMs / (1000 * 60);

                    // Notify if shift starts within 60 minutes and hasn't been notified yet
                    if (diffMinutes > 0 && diffMinutes <= 60 && !this._notifiedShiftIds.has(shift.id)) {
                        this._notifiedShiftIds.add(shift.id);
                        const vehicle = shift.vehicle?.unit_id || 'your assigned vehicle';
                        const timeStr = startTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        const minsLeft = Math.round(diffMinutes);

                        this.addNotification(
                            'reminder',
                            'Upcoming Shift',
                            `You have a shift in <span class="font-bold">${minsLeft} min</span> at <span class="font-bold">${timeStr}</span> with <span class="font-bold">${vehicle}</span>.`,
                            'blue'
                        );
                    }
                }
            } catch (err) {
                console.warn('[DRIVE Notifications] Failed to check upcoming shifts:', err);
            }
        }
    }
});
