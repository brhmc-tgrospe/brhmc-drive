export const formatSubjectType = (type) => {
    if (!type) return '';
    const parts = type.split('\\');
    return parts[parts.length - 1];
};

export const getActionBadgeClass = (event) => {
    switch (event?.toLowerCase()) {
        case 'created': return 'bg-emerald-500 text-white border-emerald-600';
        case 'updated': return 'bg-blue-50 text-blue-600 border-blue-200';
        case 'deleted': return 'bg-orange-50 text-orange-600 border-orange-200';
        case 'force_deleted': return 'bg-red-50 text-red-600 border-red-200';
        case 'restored': return 'bg-purple-50 text-purple-600 border-purple-200';
        case 'login': return 'bg-green-50 text-green-700 border-green-300';
        case 'logout': return 'bg-amber-50 text-amber-700 border-amber-300';
        default: return 'bg-slate-100 text-slate-600 border-slate-200';
    }
};

export const formatEventName = (event) => {
    if (event === 'deleted') return 'soft deleted';
    if (event === 'force_deleted') return 'force deleted';
    return event;
};

export const formatDetailedDescription = (log) => {
    const rawEvent = log.event || log.description;
    const event = formatEventName(rawEvent);
    let subjectType = formatSubjectType(log.subject_type);
    
    // Extract attributes from properties if subject is missing or deleted
    const attrs = log.properties?.attributes || log.properties?.old || {};

    // Explicit / Custom Log Messages
    if (rawEvent !== 'created' && rawEvent !== 'updated' && rawEvent !== 'deleted' && rawEvent !== 'restored' && rawEvent !== 'force_deleted') {
        let msg = log.description;
        
        if (msg.startsWith('Trip Phase Advanced to ')) {
            const phaseMap = {
                1: "Pre-Trip Verified",
                2: "Dispatched from Base",
                3: "Arrived at Scene",
                4: "Dispatched from Scene",
                5: "Arrived at Hospital",
                6: "Proceeding to Base",
                7: "Arrived at Base",
                8: "Post-Trip Submitted"
            };
            const phaseNum = parseInt(msg.replace('Trip Phase Advanced to ', ''), 10);
            const phaseName = phaseMap[phaseNum] || `Phase ${phaseNum}`;
            const tripId = log.subject?.id || log.subject_id || 'Unknown';
            return `Driver tapped to record location: ${phaseName} (Trip ID: ${tripId})`;
        }

        if (log.subject || Object.keys(attrs).length > 0) {
             const unit = log.subject?.plate_number || log.subject?.unit_id || attrs.plate_number || attrs.unit_id || log.subject?.id || attrs.id;
             const fname = log.subject?.first_name || attrs.first_name;
             const lname = log.subject?.last_name || attrs.last_name;
             
             if (subjectType === 'Vehicle') msg += ` (Vehicle: ${unit})`;
             else if (subjectType === 'User' && fname) msg += ` (User: ${fname} ${lname})`;
             else if (subjectType === 'Trip' || subjectType === 'Shift') {
                 const driverStr = log.subject?.shift?.driver || log.subject?.driver;
                 const dName = driverStr ? `${driverStr.first_name} ${driverStr.last_name}` : 'Unknown Driver';
                 const vInfo = log.subject?.shift?.vehicle || log.subject?.vehicle;
                 const vName = vInfo ? (vInfo.unit_id || vInfo.plate_number) : 'Unknown Vehicle';
                 msg += ` (Vehicle: ${vName}, Driver: ${dName})`;
             }
             else if (subjectType === 'AmbulanceInspection') {
                 const vInfo = log.subject?.vehicle;
                 const vName = vInfo ? (vInfo.unit_id || vInfo.plate_number) : 'Unknown Vehicle';
                 msg += ` (Vehicle: ${vName})`;
             }
        }
        return msg;
    }

    // Default CRUD actions translation
    let details = `${subjectType} ${event}`;
    
    if (log.subject || Object.keys(attrs).length > 0) {
        if (subjectType === 'Shift' || subjectType === 'VehicleShift') {
            const dName = log.subject?.driver ? `${log.subject.driver.first_name} ${log.subject.driver.last_name}` : attrs.driver_name;
            const dId = log.subject?.driver_id || attrs.driver_id;
            const driverName = dName ? dName : (dId ? `Driver ID ${dId}` : 'Unknown');
            
            const vName = log.subject?.vehicle ? (log.subject.vehicle.unit_id || log.subject.vehicle.plate_number) : (attrs.unit_id || attrs.plate_number);
            const vId = log.subject?.vehicle_id || attrs.vehicle_id;
            const vehicleInfo = vName ? vName : (vId ? `Vehicle ID ${vId}` : 'Unknown');
            
            details = `Schedule ${event} for ${driverName}, Vehicle ${vehicleInfo}`;
        } else if (subjectType === 'Vehicle') {
            const vInfo = log.subject?.unit_id || log.subject?.plate_number || attrs.unit_id || attrs.plate_number || log.subject?.id || attrs.id;
            details = `Vehicle ${vInfo} was ${event}`;
        } else if (subjectType === 'User') {
            const fname = log.subject?.first_name || attrs.first_name;
            const lname = log.subject?.last_name || attrs.last_name;
            const userName = fname ? `${fname} ${lname}` : (log.subject?.id || attrs.id);
            details = `User account for ${userName} was ${event}`;
        } else if (subjectType === 'Incident') {
            const vInfo = log.subject?.vehicle_id || attrs.vehicle_id;
            const issueType = log.subject?.issue_type || attrs.issue_type || 'Unknown';
            const id = log.subject?.id || attrs.id || log.subject_id;
            details = `Incident report #${id} (${issueType}) was ${event} for Vehicle ID ${vInfo}`;
        } else if (subjectType === 'Checklist') {
            const tripInfo = log.subject?.trip_id || attrs.trip_id;
            const typeInfo = log.subject?.type || attrs.type || 'Unknown';
            details = `Checklist (${typeInfo}) was ${event} for Trip ID ${tripInfo}`;
        } else if (subjectType === 'TripLog') {
            const phase = log.subject?.phase || attrs.phase;
            details = `Driver tapped to record location (Trip Phase ${phase})`;
        } else if (subjectType === 'AmbulanceInspection') {
            const vInfo = log.subject?.vehicle ? (log.subject.vehicle.unit_id || log.subject.vehicle.plate_number) : (attrs.vehicle_id || 'Unknown');
            const inspType = log.subject?.inspection_type || attrs.inspection_type || 'Routine';
            details = `Ambulance Inspection (${inspType}) was ${event} for Vehicle ${vInfo}`;
        } else {
            const id = log.subject?.id || attrs.id || log.subject_id;
            details = `${subjectType} (ID: ${id}) was ${event}`;
        }
    } else if (log.subject_type) {
         details = `${subjectType} (ID: ${log.subject_id}) was ${event}`;
    }

    if (log.event === 'force_deleted') {
        details += ' - Data is permanently wiped and is not recoverable.';
    }

    if (event === 'updated' && log.properties && log.properties.attributes && log.properties.old) {
        const changedKeys = Object.keys(log.properties.attributes).filter(k => k !== 'updated_at');
        if (changedKeys.length > 0) {
            const changes = changedKeys.map(k => {
                let oldVal = log.properties.old[k];
                let newVal = log.properties.attributes[k];
                if (oldVal === null) oldVal = 'empty';
                if (newVal === null) newVal = 'empty';
                if (oldVal === true) oldVal = 'Yes';
                if (oldVal === false) oldVal = 'No';
                if (newVal === true) newVal = 'Yes';
                if (newVal === false) newVal = 'No';
                return `[${k}] from '${oldVal}' to '${newVal}'`;
            }).join(', ');
            details += `. Modifications: ${changes}`;
        }
    }

    return details;
};

