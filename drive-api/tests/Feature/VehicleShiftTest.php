<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Shift;
use Carbon\Carbon;

class VehicleShiftTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_schedule_valid_shift()
    {
        $dispatcher = User::factory()->create(['role' => 'developer']);
        $driver = User::factory()->create(['role' => 'driver']);
        $vehicle = Vehicle::create([
            'unit_id' => 'AMB-001',
            'plate_number' => 'ABC-1234',
            'vehicle_type' => 'Ambulance',
            'make_model' => 'Ford Transit',
            'status' => 'READY',
            'base_location' => 'Base',
            'odometer' => 1000,
            'fuel_level' => 100,
        ]);

        $response = $this->actingAs($dispatcher)->postJson('/api/shifts', [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => Carbon::now()->addDay()->setHour(8)->setMinute(0)->toDateTimeString(),
            'shift_duration' => 8,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('shifts', [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
        ]);
    }

    public function test_prevents_driver_double_booking()
    {
        $dispatcher = User::factory()->create(['role' => 'developer']);
        $driver = User::factory()->create(['role' => 'driver']);
        $vehicle1 = Vehicle::create([
            'unit_id' => 'AMB-001',
            'plate_number' => 'ABC-1234',
            'vehicle_type' => 'Ambulance',
            'make_model' => 'Ford Transit',
            'status' => 'READY',
            'base_location' => 'Base',
            'odometer' => 1000,
            'fuel_level' => 100,
        ]);
        $vehicle2 = Vehicle::create([
            'unit_id' => 'AMB-002',
            'plate_number' => 'XYZ-9876',
            'vehicle_type' => 'Ambulance',
            'make_model' => 'Ford Transit',
            'status' => 'READY',
            'base_location' => 'Base',
            'odometer' => 2000,
            'fuel_level' => 100,
        ]);

        $startTime = Carbon::now()->addDay()->setHour(8)->setMinute(0);

        Shift::create([
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle1->id,
            'scheduled_start' => $startTime->toDateTimeString(),
            'scheduled_end' => $startTime->copy()->addHours(8)->toDateTimeString(),
            'status' => 'PENDING'
        ]);

        $response = $this->actingAs($dispatcher)->postJson('/api/shifts', [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle2->id,
            'start_time' => $startTime->copy()->addHours(4)->toDateTimeString(),
            'shift_duration' => 8,
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'Double Booking Detected: This driver is already scheduled during this exact time window.']);
    }

    public function test_prevents_vehicle_double_booking()
    {
        $dispatcher = User::factory()->create(['role' => 'developer']);
        $driver1 = User::factory()->create(['role' => 'driver']);
        $driver2 = User::factory()->create(['role' => 'driver']);
        $vehicle = Vehicle::create([
            'unit_id' => 'AMB-001',
            'plate_number' => 'ABC-1234',
            'vehicle_type' => 'Ambulance',
            'make_model' => 'Ford Transit',
            'status' => 'READY',
            'base_location' => 'Base',
            'odometer' => 1000,
            'fuel_level' => 100,
        ]);

        $startTime = Carbon::now()->addDay()->setHour(8)->setMinute(0);

        Shift::create([
            'driver_id' => $driver1->id,
            'vehicle_id' => $vehicle->id,
            'scheduled_start' => $startTime->toDateTimeString(),
            'scheduled_end' => $startTime->copy()->addHours(8)->toDateTimeString(),
            'status' => 'PENDING'
        ]);

        $response = $this->actingAs($dispatcher)->postJson('/api/shifts', [
            'driver_id' => $driver2->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => $startTime->copy()->addHours(4)->toDateTimeString(),
            'shift_duration' => 8,
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'Double Booking Detected: This vehicle is already scheduled during this exact time window.']);
    }
}
