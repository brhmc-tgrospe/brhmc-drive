<?php
$file = "app/Http/Controllers/Api/AmbulanceInspectionController.php";
$content = file_get_contents($file);

$methods = "
    public function accept(Request \$request, \$id)
    {
        \$inspection = AmbulanceInspection::findOrFail(\$id);
        \$inspection->vehicle()->update(['status' => 'MAINTENANCE']);
        activity()->causedBy(\$request->user())->performedOn(\$inspection)->log('Vehicle Transferred to Maintenance Bay');
        return response()->json(['message' => 'Vehicle successfully transferred to maintenance bay.', 'inspection' => \$inspection]);
    }

    public function release(Request \$request, \$id)
    {
        \$inspection = AmbulanceInspection::findOrFail(\$id);
        \$inspection->vehicle()->update(['status' => 'READY']);
        activity()->causedBy(\$request->user())->performedOn(\$inspection)->log('Vehicle Released from Maintenance Bay');
        return response()->json(['message' => 'Vehicle successfully released from maintenance bay.']);
    }
}
";

$content = preg_replace('/\}\s*$/', $methods, $content);
file_put_contents($file, $content);
?>
