<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncTripOdometer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tripId;
    protected $vehicleId;

    public function __construct($tripId, $vehicleId)
    {
        $this->tripId = $tripId;
        $this->vehicleId = $vehicleId;
    }

    public function handle()
    {
        Log::info("Starting async odometer calculation for Trip {$this->tripId}");

        // 1. Get all GPS points for this trip, ordered chronologically
        $points = DB::table('telemetry_logs')
            ->where('trip_id', $this->tripId)
            ->orderBy('recorded_at', 'asc')
            ->get(['latitude', 'longitude']);

        if ($points->count() < 2) {
            Log::info("Not enough telemetry data to calculate distance for Trip {$this->tripId}.");
            return;
        }

        // 2. Calculate total distance using the Haversine formula
        $totalDistanceKm = 0;
        $prevPoint = $points->first();

        foreach ($points->slice(1) as $point) {
            $totalDistanceKm += $this->calculateDistance(
                $prevPoint->latitude, $prevPoint->longitude,
                $point->latitude, $point->longitude
            );
            $prevPoint = $point;
        }

        // 3. Add the driven distance to the vehicle's master odometer securely
        $distanceRounded = (int) round($totalDistanceKm);
        
        if ($distanceRounded > 0) {
            DB::table('vehicles')->where('id', $this->vehicleId)->increment('odometer', $distanceRounded);
            Log::info("Added {$distanceRounded} km to Vehicle {$this->vehicleId} odometer.");
        }
    }

    /**
     * Haversine formula to calculate exact distance between two coordinates
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius of the earth in km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c; 
    }
}