<?php
$models = ["Checklist", "ChecklistItem", "ChecklistTemplate", "Emergency", "Incident", "Inspection", "InspectionResult", "AmbulanceInspection", "TelemetryLog", "Trip", "TripLog", "TripPhase", "User", "Vehicle", "VehicleShift"];

foreach ($models as $model) {
    $file = "c:/Users/Programmer/Desktop/DRIVE/api/app/Models/{$model}.php";
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    if (strpos($content, 'Illuminate\Database\Eloquent\SoftDeletes') === false) {
        $content = preg_replace('/(namespace App\\\\Models;)/', "$1\n\nuse Illuminate\Database\Eloquent\SoftDeletes;", $content);
    }
    
    if (strpos($content, 'use SoftDeletes;') === false) {
        $content = preg_replace('/(class ' . $model . ' extends .*?\{)/s', "$1\n    use SoftDeletes;", $content);
    }
    
    file_put_contents($file, $content);
}
echo "Done.\n";
