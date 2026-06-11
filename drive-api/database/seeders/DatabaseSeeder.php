<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tell Laravel to execute our custom roles and users seeder
        $this->call([
            RolesAndUsersSeeder::class,
            VehicleSeeder::class,
        ]);
    }
}