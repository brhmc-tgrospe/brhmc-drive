<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Events\EmergencyReported;
use Carbon\Carbon;

class CheckStaleTelemetry extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'telemetry:check-stale {--minutes=10 : Minutes without a ping to trigger alert}';

    /**
     * The console command description.
     */
    protected $description = 'Checks for active vehicles that have dropped off the map and triggers a signal lost alert.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = (int) $this->option('minutes');
        $cutoff = Carbon::now()->subMinutes($minutes);

        $this->info("Checking for IN_USE vehicles without telemetry since {$cutoff->toDateTimeString()}...");

        // BULLETPROOF QUERY: Find vehicles IN_USE that have NO telemetry logs newer than the cutoff
        $staleVehicles = DB::table('vehicles')
            ->whereIn('status', ['IN_USE'])
            ->whereNotExists(function ($query) use ($cutoff) {
                $query->select(DB::raw(1))
                      ->from('telemetry_logs')
                      ->whereColumn('telemetry_logs.vehicle_id', 'vehicles.id')
                      ->where('recorded_at', '>=', $cutoff);
            })
            ->get();

        if ($staleVehicles->isEmpty()) {
            $this->info("All active vehicles have strong GPS signals.");
            return Command::SUCCESS;
        }

        foreach ($staleVehicles as $vehicle) {
            $this->warn("Signal Lost: Vehicle {$vehicle->unit_id}");
            
            // FIX: Safely retrieve the active trip and driver to satisfy strict DB foreign key constraints
            $trip = DB::table('trips')
                ->join('shifts', 'trips.shift_id', '=', 'shifts.id')
                ->where('shifts.vehicle_id', $vehicle->id)
                ->where('trips.current_phase', '<', 4)
                ->select('trips.id', 'shifts.driver_id')
                ->orderBy('trips.id', 'desc')
                ->first();

            if (!$trip) {
                // If the vehicle is IN_USE but has no trip, it's an orphaned state. Reset it safely.
                DB::table('vehicles')->where('id', $vehicle->id)->update(['status' => 'READY']);
                continue;
            }
            
            // 1. Log a System-Generated Emergency with valid foreign keys
            $emergencyId = DB::table('emergencies')->insertGetId([
                'vehicle_id' => $vehicle->id,
                'trip_id' => $trip->id, 
                'driver_id' => $trip->driver_id, 
                'description' => "[SYSTEM ALERT] - GPS Signal Lost for over {$minutes} minutes. Driver may be in a dead zone or device failed.",
                'status' => 'UNRESOLVED',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // 2. Broadcast the alert instantly to the Dispatcher UI
            $emergencyData = [
                'id' => $emergencyId,
                'vehicle_unit' => $vehicle->unit_id,
                'driver_name' => "System Watchdog",
                'issue_type' => "GPS Signal Lost",
                'time' => Carbon::now()->toDateTimeString()
            ];
            
            broadcast(new EmergencyReported($emergencyData));
        }

        return Command::SUCCESS;
    }
}