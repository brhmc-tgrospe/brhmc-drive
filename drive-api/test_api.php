<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create('/api/activity-logs', 'GET');
$request->setUserResolver(function() {
    return \App\Models\User::first(); // Assuming first user is developer
});

$controller = new \App\Http\Controllers\Api\ActivityLogController();
$response = $controller->index($request);

echo $response->getContent();
