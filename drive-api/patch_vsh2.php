<?php
$file = "app/Http/Controllers/Api/VehicleShiftController.php";
$content = file_get_contents($file);

$content = str_replace(
    "return response()->json(['message' => 'Shift Started Successfully!']);",
    "activity()->causedBy(\$request->user())->performedOn(\\App\\Models\\Shift::find(\$id))->log('Shift Started');\n            return response()->json(['message' => 'Shift Started Successfully!']);",
    $content
);

file_put_contents($file, $content);
?>
