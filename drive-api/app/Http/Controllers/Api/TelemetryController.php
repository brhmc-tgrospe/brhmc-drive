<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TelemetryController extends Controller
{
    public function ping(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        DB::table('vehicles')->where('id', $validated['vehicle_id'])->update([
            'current_lat' => $validated['latitude'],
            'current_lng' => $validated['longitude'],
            'last_telemetry_at' => Carbon::now(),
        ]);

        return response()->json(['status' => 'Telemetry logged']);
    }

    public function activeLocations(Request $request)
    {
        $user = $request->user();
        $isDriver = false;
        
        // If the user has explicitly only the Driver role, or lacks view-fleet-metrics (if you prefer)
        // Let's use the straightforward role check
        if ((strtolower($user->role) === 'driver' || $user->hasRole('Driver')) && !($user->hasPermission('view-fleet') || strtolower($user->role) === 'developer')) {
            $isDriver = true;
        }

        $query = DB::table('vehicles')
            ->select('id', 'status', 'current_lat', 'current_lng', 'last_telemetry_at')
            ->whereIn('status', ['IN_USE', 'READY', 'SCHEDULED', 'BREAKDOWN', 'MAINTENANCE']);

        if ($isDriver) {
            $assignedVehicleIds = DB::table('shifts')
                ->where('driver_id', $user->id)
                ->whereIn('status', ['PENDING', 'SCHEDULED', 'ACTIVE'])
                ->pluck('vehicle_id');
            $query->whereIn('id', $assignedVehicleIds);
        }

        // CRITICAL FIX: Fetch all relevant vehicles regardless of if their live GPS is currently active
        $locations = $query->get();

        foreach ($locations as $loc) {
            $loc->logs = [];
            $loc->incidents = [];

            $shift = DB::table('shifts')
                ->where('vehicle_id', $loc->id)
                ->orderBy('id', 'desc')
                ->first();

            if ($shift) {
                $trip = DB::table('trips')->where('shift_id', $shift->id)->first();
                if ($trip) {
                    $loc->logs = DB::table('trip_logs')
                        ->where('trip_id', $trip->id)
                        ->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->orderBy('phase', 'asc')
                        ->get();
                }

                $loc->incidents = DB::table('incidents')
                    ->where('shift_id', $shift->id)
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->get();
            }
        }

        return response()->json($locations);
    }
}