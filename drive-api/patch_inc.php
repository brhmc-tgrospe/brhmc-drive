<?php
function pregPatchFile($file, $replacements) {
    $content = file_get_contents($file);
    foreach ($replacements as $pattern => $replace) {
        $content = preg_replace($pattern, $replace, $content);
    }
    file_put_contents($file, $content);
}

// 2. IncidentController
$inc = "app/Http/Controllers/Api/IncidentController.php";
pregPatchFile($inc, [
    "/DB::commit\(\);\s*try\s*\{\s*event\(new IncidentReported/" => 
    "DB::commit();\n            activity()->causedBy(\$request->user())->performedOn(\\App\\Models\\Incident::find(\$incidentId))->log('Incident Reported');\n\n            try {\n                event(new IncidentReported",
    
    "/DB::commit\(\);\s*return response\(\)->json\(\['message' => 'Incident Acknowledged\.'\]\);/" => 
    "DB::commit();\n            activity()->causedBy(\$request->user())->performedOn(\\App\\Models\\Incident::find(\$id))->log('Incident Acknowledged');\n            return response()->json(['message' => 'Incident Acknowledged.']);",
    
    "/\\\$incident->status = 'RESOLVED';\s*\\\$incident->save\(\);/" => 
    "\$incident->status = 'RESOLVED';\n        \$incident->save();\n        activity()->causedBy(\$request->user())->performedOn(\$incident)->log('Incident Resolved');"
]);
echo "Patched Incident\n";
?>
