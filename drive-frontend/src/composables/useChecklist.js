import { ref } from 'vue';

export function useChecklist() {
    const displayNames = {
        engineCabin: { 
            battery: 'Battery', oil_level: 'Oil Level', water_coolant: 'Water Coolant', washer_fluid: 'Washer Fluid', brakes: 'Brakes', 
            siren: 'Siren', horn: 'Horn', power_locks: 'Power Locks', windshield_wipers: 'Windshield Wipers', windshield_washer: 'Windshield Washer',
            front_ac: 'Front A/C', rear_ac: 'Rear A/C', exhaust_vent: 'Exhaust Vent' 
        },
        lights: { 
            dashboard: 'Dashboard', front_interior: 'Front Interior', rear_interior: 'Rear Interior', 
            headlights_high: 'Headlights (High)', headlights_low: 'Headlights (Low)', 
            foglamps: 'Foglamps', park_light: 'Park Light', tail_light: 'Tail Light', 
            signal_front: 'Signal (Front)', signal_rear: 'Signal (Rear)', 
            hazard: 'Hazard Lights', reverse: 'Reverse Lights', brake_light: 'Brake Lights', 
            overhead_warning: 'Warning Light Bar', front_scene: 'Front Scene', rear_scene: 'Rear Scene' 
        },
        equipment: { 
            vhf_base_radio: 'VHF Base Radio', mech_vent: 'Mech Vent', mech_vent_battery: 'Mech Vent (Battery)', 
            cardiac_monitor: 'Cardiac Monitor (Defib)', cardiac_monitor_battery: 'Cardiac Monitor (Battery)', 
            aed: 'AED', aed_battery: 'AED (Battery)', suction_machine: 'Suction Machine', 
            ecg_machine: 'ECG Machine', ac_inverter: 'AC Inverter', o2_regulator: 'O2 Regulator/Humidifier', 
            o2_pressure_gauge: 'O2 Pressure Gauge', splint_adult: 'Splint Adult', splints_child: 'Splints Child', 
            scoop_stretcher: 'Scoop Stretcher', spine_board: 'Spine Board', traction_splint: 'Traction Splint', 
            kendricks_extrication: 'Kendricks Extrication', cpr_machine: 'CPR Machine', 
            seats_condition: 'Seats Condition', dashboard_condition: 'Dashboard Condition', 
            driver_compartment_clean: 'Driver Compartment Clean', back_compartment_clean: 'Back Compartment Clean' 
        }
    };

    const pins = ref({ right: [], left: [], front: [], rear: [] });
    const selectedPinToRemove = ref({ right: '', left: '', front: '', rear: '' });

    const dropPin = (event, view, formState) => {
        const rect = event.currentTarget.getBoundingClientRect();
        const x = ((event.clientX - rect.left) / rect.width) * 100;
        const y = ((event.clientY - rect.top) / rect.height) * 100;
        
        pins.value[view].push({ x, y });
        const pinNumber = pins.value[view].length;
        
        selectedPinToRemove.value[view] = pinNumber - 1;
        
        const prefix = `[Pin ${pinNumber}]: `;
        const currentText = formState[`damage_findings_${view}`] || '';
        
        if (currentText.trim() === '') {
            formState[`damage_findings_${view}`] = prefix;
        } else {
            formState[`damage_findings_${view}`] = currentText + `\n${prefix}`;
        }
    };

    const removeSelectedPin = (view, formState) => {
        const idx = selectedPinToRemove.value[view];
        if (idx === '' || idx === null || idx === undefined) return;
        
        const pinNumToRemove = idx + 1;
        pins.value[view].splice(idx, 1);
        
        let text = formState[`damage_findings_${view}`] || '';
        let lines = text.split('\n');
        
        let startIndex = lines.findIndex(l => l.startsWith(`[Pin ${pinNumToRemove}]:`));
        if (startIndex !== -1) {
            let endIndex = startIndex + 1;
            while(endIndex < lines.length && !lines[endIndex].match(/^\[Pin \d+\]:/)) {
                endIndex++;
            }
            lines.splice(startIndex, endIndex - startIndex);
        }
        
        lines = lines.map(line => {
            const match = line.match(/^\[Pin (\d+)\]:(.*)/);
            if (match) {
                const currentNum = parseInt(match[1], 10);
                if (currentNum > pinNumToRemove) {
                    return `[Pin ${currentNum - 1}]:${match[2]}`;
                }
            }
            return line;
        });
        
        formState[`damage_findings_${view}`] = lines.join('\n').trim();
        selectedPinToRemove.value[view] = pins.value[view].length > 0 ? pins.value[view].length - 1 : '';
    };

    const devToolClear = (formState) => { 
        ['engineCabin', 'lights', 'equipment'].forEach(s => {
            if(formState[s]) {
                Object.keys(formState[s]).forEach(k => formState[s][k] = false);
            }
        }); 
    };

    const devToolunClear = (formState) => { 
        ['engineCabin', 'lights', 'equipment'].forEach(s => {
            if(formState[s]) {
                Object.keys(formState[s]).forEach(k => formState[s][k] = true);
            }
        }); 
    };

    return {
        displayNames,
        pins,
        selectedPinToRemove,
        dropPin,
        removeSelectedPin,
        devToolClear,
        devToolunClear
    };
}
