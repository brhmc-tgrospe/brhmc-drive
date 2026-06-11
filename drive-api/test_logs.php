<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = \Spatie\Activitylog\Models\Activity::where('log_name', 'authentication')->orderBy('id', 'desc')->limit(10)->get();
print_r($logs->toArray());
