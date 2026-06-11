<?php
function patchFile($file, $replacements) {
    $content = file_get_contents($file);
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    file_put_contents($file, $content);
}

// 1. ChecklistController
$check = "app/Http/Controllers/Api/ChecklistController.php";
patchFile($check, [
    "DB::commit();\n            return response()->json(['message' => 'Checklist reviewed successfully.']);" => 
    "DB::commit();\n            activity()->causedBy(\$request->user())->performedOn(\\App\\Models\\Checklist::find(\$id))->log(\$status === 'APPROVED' ? 'Checklist Approved' : 'Checklist Rejected');\n            return response()->json(['message' => 'Checklist reviewed successfully.']);",
    
    "DB::commit();\n\n            // ==========================================" => 
    "DB::commit();\n            activity()->causedBy(\$request->user())->performedOn(\\App\\Models\\Checklist::find(\$checklistId))->log('Checklist Submitted');\n\n            // =========================================="
]);

// 2. IncidentController
$inc = "app/Http/Controllers/Api/IncidentController.php";
patchFile($inc, [
    "DB::commit();\n\n            // Broadcast Event" => 
    "DB::commit();\n            activity()->causedBy(\$request->user())->performedOn(\\App\\Models\\Incident::find(\$incidentId))->log('Incident Reported');\n\n            // Broadcast Event",
    
    "DB::commit();\n\n            return response()->json(['message' => 'Incident acknowledged successfully.', 'incident' => \$incident]);" => 
    "DB::commit();\n            activity()->causedBy(\$request->user())->performedOn(\\App\\Models\\Incident::find(\$id))->log('Incident Acknowledged');\n\n            return response()->json(['message' => 'Incident acknowledged successfully.', 'incident' => \$incident]);",
    
    "\$incident->save();\n\n        return response()->json(['message' => 'Incident resolved successfully.', 'incident' => \$incident]);" => 
    "\$incident->save();\n        activity()->causedBy(\$request->user())->performedOn(\$incident)->log('Incident Resolved');\n\n        return response()->json(['message' => 'Incident resolved successfully.', 'incident' => \$incident]);"
]);

// 3. EmergencyController
$emg = "app/Http/Controllers/Api/EmergencyController.php";
patchFile($emg, [
    "\$emergency->save();\n\n            DB::commit();\n\n            return response()->json" => 
    "\$emergency->save();\n\n            DB::commit();\n            activity()->causedBy(\$request->user())->performedOn(\$emergency)->log('Emergency Triggered');\n\n            return response()->json",
    
    "DB::commit();\n\n        return response()->json" => 
    "DB::commit();\n        activity()->causedBy(\$request->user())->performedOn(\$emergency)->log('Emergency Resolved');\n\n        return response()->json"
]);

// 4. AmbulanceInspectionController
$amb = "app/Http/Controllers/Api/AmbulanceInspectionController.php";
patchFile($amb, [
    "DB::commit();\n            return response()->json(['message' => 'Vehicle successfully transferred to maintenance bay.', 'inspection' => \$inspection]);" => 
    "DB::commit();\n            activity()->causedBy(\$request->user())->performedOn(\$inspection)->log('Vehicle Transferred to Maintenance Bay');\n            return response()->json(['message' => 'Vehicle successfully transferred to maintenance bay.', 'inspection' => \$inspection]);",
    
    "DB::commit();\n\n            return response()->json(['message' => 'Vehicle successfully released from maintenance bay.']);" => 
    "DB::commit();\n            activity()->causedBy(\$request->user())->performedOn(\$inspection)->log('Vehicle Released from Maintenance Bay');\n\n            return response()->json(['message' => 'Vehicle successfully released from maintenance bay.']);"
]);

// 5. VehicleShiftController
$vsh = "app/Http/Controllers/Api/VehicleShiftController.php";
patchFile($vsh, [
    "DB::commit();\n\n            return response()->json(['message' => 'Shift started successfully', 'shift' => \$shift], 201);" => 
    "DB::commit();\n            activity()->causedBy(\$request->user())->performedOn(\$shift)->log('Shift Started');\n\n            return response()->json(['message' => 'Shift started successfully', 'shift' => \$shift], 201);",
    
    "DB::commit();\n\n        return response()->json(['message' => 'Shift ended successfully']);" => 
    "DB::commit();\n        activity()->causedBy(\$request->user())->performedOn(\$shift)->log('Shift Ended');\n\n        return response()->json(['message' => 'Shift ended successfully']);"
]);

// 6. TripController
$trp = "app/Http/Controllers/Api/TripController.php";
patchFile($trp, [
    "DB::commit();\n\n            return response()->json(['message' => 'Trip started successfully', 'trip' => \$trip], 201);" => 
    "DB::commit();\n            activity()->causedBy(\$request->user())->performedOn(\$trip)->log('Trip Started');\n\n            return response()->json(['message' => 'Trip started successfully', 'trip' => \$trip], 201);",
    
    "DB::commit();\n\n        return response()->json(['message' => 'Trip ended successfully']);" => 
    "DB::commit();\n        activity()->causedBy(\$request->user())->performedOn(\$trip)->log('Trip Ended');\n\n        return response()->json(['message' => 'Trip ended successfully']);",
    
    "\$trip->save();\n\n        return response()->json(['message' => 'Trip phase updated', 'current_phase' => \$trip->current_phase]);" => 
    "\$trip->save();\n        activity()->causedBy(\$request->user())->performedOn(\$trip)->log('Trip Phase Advanced to ' . \$trip->current_phase);\n\n        return response()->json(['message' => 'Trip phase updated', 'current_phase' => \$trip->current_phase]);"
]);

echo "Done patching.\n";
?>
