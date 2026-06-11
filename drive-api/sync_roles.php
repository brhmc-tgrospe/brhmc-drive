<?php
use App\Models\User;

$users = User::all();
$count = 0;
foreach($users as $user) {
    if($user->role) {
        try {
            $roleStr = strtolower($user->role);
            $mappedRole = null;
            if (str_contains($roleStr, 'dev')) $mappedRole = 'Developer';
            elseif (str_contains($roleStr, 'admin')) $mappedRole = 'Admin';
            elseif (str_contains($roleStr, 'dispatch')) $mappedRole = 'Dispatcher';
            elseif (str_contains($roleStr, 'mainten')) $mappedRole = 'Maintenance';
            elseif (str_contains($roleStr, 'driver')) $mappedRole = 'Driver';
            
            if ($mappedRole) {
                $roleObj = \Spatie\Permission\Models\Role::findByName($mappedRole, 'api');
                $user->assignRole($roleObj);
                $count++;
            }
        } catch (\Exception $e) {
            echo "Warning: Could not assign role to user {$user->id}. " . $e->getMessage() . "\n";
        }
    }
}
echo "Successfully synced $count users to Spatie Roles.\n";
