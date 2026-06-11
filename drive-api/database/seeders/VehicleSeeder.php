<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            [
                'unit_id' => 'BRHMC-A101',
                'plate_number' => 'BGC-1234',
                'make_model' => 'Mercedes Sprinter',
                'vehicle_type' => 'Ambulance - Type 1',
                'odometer' => 45210,
                'status' => 'READY',
                'base_location' => 'BRHMC Main Base',
            ],
            [
                'unit_id' => 'BRHMC-A102',
                'plate_number' => 'XYZ-9876',
                'make_model' => 'Nissan NV350',
                'vehicle_type' => 'Ambulance - Type 2',
                'odometer' => 28500,
                'status' => 'IN_USE',
                'base_location' => 'BRHMC Main Base',
            ],
            [
                'unit_id' => 'BRHMC-A103',
                'plate_number' => 'AWZ-8776',
                'make_model' => 'Toyota Fortuner',
                'vehicle_type' => 'Service Vehicle',
                'odometer' => 54500,
                'status' => 'BREAKDOWN',
                'base_location' => 'BRHMC Main Base',
            ],
            [
                'unit_id' => 'SJA-5555',
                'plate_number' => 'SJA-5555',
                'make_model' => 'Toyota Hiace',
                'vehicle_type' => 'Service Vehicle',
                'odometer' => 89000,
                'status' => 'MAINTENANCE',
                'base_location' => 'Legazpi Auto Shop',
            ]
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }
    }
}