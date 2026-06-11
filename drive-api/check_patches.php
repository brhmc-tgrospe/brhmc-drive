<?php
$files = [
    "app/Http/Controllers/Api/ChecklistController.php",
    "app/Http/Controllers/Api/IncidentController.php",
    "app/Http/Controllers/Api/EmergencyController.php",
    "app/Http/Controllers/Api/AmbulanceInspectionController.php",
    "app/Http/Controllers/Api/VehicleShiftController.php",
    "app/Http/Controllers/Api/TripController.php"
];
foreach($files as $file) {
    if (strpos(file_get_contents($file), 'activity()->causedBy') !== false) {
        echo "$file - SUCCESS\n";
    } else {
        echo "$file - FAILED\n";
    }
}
?>
