<?php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$roles = Role::count();
$permissions = Permission::count();
echo "Seeded $roles roles and $permissions permissions.\n";

$admin = Role::findByName('Admin', 'api');
echo "Admin permissions count: " . $admin->permissions()->count() . "\n";
