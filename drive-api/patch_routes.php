<?php
$file = "routes/api.php";
$content = file_get_contents($file);

$content = str_replace(
    "use Illuminate\\Support\\Facades\\Route;",
    "use Illuminate\\Support\\Facades\\Route;\nuse App\\Http\\Controllers\\Api\\ActivityLogController;",
    $content
);

$content = str_replace(
    "Route::post('/logout', [AuthController::class, 'logout']);",
    "Route::post('/logout', [AuthController::class, 'logout']);\n    Route::get('/activity-logs', [ActivityLogController::class, 'index']);",
    $content
);

file_put_contents($file, $content);
?>
