<?php
$emg = "app/Http/Controllers/Api/EmergencyController.php";
$content = file_get_contents($emg);

$content = str_replace(
    "'updated_at' => Carbon::now(),\n        ]);",
    "'updated_at' => Carbon::now(),\n        ]);\n\n        activity()->causedBy(\$driver)->performedOn(\\App\\Models\\Emergency::find(\$emergencyId))->log('Emergency Triggered');",
    $content
);

file_put_contents($emg, $content);
?>
