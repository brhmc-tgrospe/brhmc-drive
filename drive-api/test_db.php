<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'admin')->first();
echo "Raw DB Value: " . $user->getRawOriginal('legacy_permissions') . "\n";
echo "Casted Value Type: " . gettype($user->legacy_permissions) . "\n";
