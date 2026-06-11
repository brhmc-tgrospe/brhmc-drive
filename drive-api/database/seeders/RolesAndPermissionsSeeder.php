<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define ACLs (Permissions)
        $permissions = [
            'execute-shifts',
            'view-fleet-metrics',
            'view-schedules',
            'manage-schedules',
            'view-operational-maps',
            'manage-maintenance',
            'view-fleet',
            'manage-fleet',
            'view-users',
            'manage-users',
            'view-system-health',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        // 2. Create Roles & Assign Permissions
        
        // Driver
        $roleDriver = Role::firstOrCreate(['name' => 'Driver', 'guard_name' => 'api']);
        $roleDriver->syncPermissions(['execute-shifts', 'view-fleet-metrics', 'view-operational-maps', 'view-schedules']);

        // Dispatcher
        $roleDispatcher = Role::firstOrCreate(['name' => 'Dispatcher', 'guard_name' => 'api']);
        $roleDispatcher->syncPermissions(['view-schedules', 'manage-schedules', 'view-operational-maps', 'view-fleet-metrics', 'view-fleet', 'view-users']);

        // Maintenance
        $roleMaintenance = Role::firstOrCreate(['name' => 'Maintenance', 'guard_name' => 'api']);
        $roleMaintenance->syncPermissions(['manage-maintenance', 'view-operational-maps']);

        // Admin
        $roleAdmin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'api']);
        $roleAdmin->syncPermissions([
            'execute-shifts',
            'view-schedules',
            'manage-schedules',
            'view-operational-maps',
            'view-fleet-metrics',
            'manage-maintenance',
            'view-fleet',
            'manage-fleet',
            'view-users',
            'manage-users'
        ]);

        // Developer
        $roleDeveloper = Role::firstOrCreate(['name' => 'Developer', 'guard_name' => 'api']);
        // Developer gets all permissions implicitly via a Gate::before rule.
    }
}
