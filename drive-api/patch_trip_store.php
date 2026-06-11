<?php
$file = "app/Http/Controllers/Api/TripController.php";
$content = file_get_contents($file);

$content = str_replace(
    "return response()->json([\n            'message' => 'Trip initialized.',\n            'trip_id' => \$tripId\n        ], 201);",
    "activity()->causedBy(\$request->user())->performedOn(\\App\\Models\\Trip::find(\$tripId))->log('Trip Started');\n        return response()->json([\n            'message' => 'Trip initialized.',\n            'trip_id' => \$tripId\n        ], 201);",
    $content
);
file_put_contents($file, $content);
?>
