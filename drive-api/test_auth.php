<?php
use App\Models\User;

$user = User::first();
if (!$user) {
    echo "No user found.\n";
    exit;
}

// Emulate AuthController
try {
    $roles = $user->getRoleNames();
    $perms = $user->getAllPermissions()->pluck('name');
    echo "Success! Roles: " . count($roles) . " Perms: " . count($perms) . "\n";
    echo "Roles array: " . json_encode($roles) . "\n";
    echo "Perms array: " . json_encode($perms) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
