<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
// 1. Developer
        User::firstOrCreate(
            ['email' => 'admin@drive.local'], 
            [
                'first_name' => 'Tyrone Ace',
                'last_name' => 'Grospe',
                'username' => 'developer',
                'contact_number' => '0917-123-4567',
                'password' => Hash::make('password123'), 
                'is_active' => true,
                'role' => 'developer',
            ]
        );

        // 2. Admin
        User::firstOrCreate(
            ['email' => 'hospital.admin@drive.local'], 
            [
                'first_name' => 'Hospital',
                'last_name' => 'Administrator',
                'username' => 'h_admin',
                'contact_number' => '0918-987-6543',
                'password' => Hash::make('password123'), 
                'is_active' => true,
                'role' => 'admin',
            ]
        );

        // 3. Dispatcher
        User::firstOrCreate(
            ['email' => 'dispatcher@drive.local'], 
            [
                'first_name' => 'Desk',
                'last_name' => 'Dispatcher',
                'username' => 'disp_1',
                'contact_number' => '0919-555-8888',
                'password' => Hash::make('password123'), 
                'is_active' => true,
                'role' => 'dispatcher',
            ]
        );

        // 4. Driver 1
        User::firstOrCreate(
            ['email' => 'driver1@drive.local'], 
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'username' => 'd_juan',
                'contact_number' => '0920-111-2222',
                'password' => Hash::make('password123'), 
                'is_active' => true,
                'role' => 'driver',
            ]
        );

        // 5. Driver 2
        User::firstOrCreate(
            ['email' => 'driver2@drive.local'], 
            [
                'first_name' => 'Mario',
                'last_name' => 'Santos',
                'username' => 'd_mario',
                'contact_number' => '0921-333-4444',
                'password' => Hash::make('password123'), 
                'is_active' => true,
                'role' => 'driver',
            ]
        );
    }
}