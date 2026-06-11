<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'admin')->first();
$perms = $user->legacy_permissions;
echo "Type of legacy_permissions: " . gettype($perms) . "\n";
echo "Value: " . print_r($perms, true) . "\n";

if (is_string($perms)) $perms = json_decode($perms, true);
echo "Type after decode: " . gettype($perms) . "\n";
echo "Value after decode: " . print_r($perms, true) . "\n";
