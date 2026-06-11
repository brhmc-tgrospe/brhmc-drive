<?php
$file = "app/Http/Controllers/Api/TripController.php";
$content = file_get_contents($file);

$content = str_replace(
    "return response()->json([\n            'message' => 'Trip advanced to phase ' . \$newPhase,\n            'current_phase' => \$newPhase\n        ]);",
    "activity()->causedBy(\$request->user())->performedOn(\\App\\Models\\Trip::find(\$id))->log('Trip Phase Advanced to ' . \$newPhase);\n        return response()->json([\n            'message' => 'Trip advanced to phase ' . \$newPhase,\n            'current_phase' => \$newPhase\n        ]);",
    $content
);

$content = str_replace(
    "return response()->json(['message' => 'Vehicle successfully cleared for dispatch.']);",
    "activity()->causedBy(\$request->user())->performedOn(\\App\\Models\\Trip::find(\$id))->log('Trip Cleared for Departure');\n        return response()->json(['message' => 'Vehicle successfully cleared for dispatch.']);",
    $content
);

file_put_contents($file, $content);
?>
