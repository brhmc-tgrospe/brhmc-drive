<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\EmergencyReported;
use App\Events\IncidentReported;
use Carbon\Carbon;

class EmergencyController extends Controller
{
    public function reportIssue(Request $request)
    {
        $validated = $request->validate([
            'vehicle_unit' => 'required|string|exists:vehicles,unit_id',
            'issue_type' => 'required|string',
            'remarks' => 'required|string'
        ]);

        $driver = $request->user();
        $vehicle = DB::table('vehicles')->where('unit_id', $validated['vehicle_unit'])->first();

        $trip = DB::table('trips')
            ->join('shifts', 'trips.shift_id', '=', 'shifts.id')
            ->where('shifts.vehicle_id', $vehicle->id)
            ->select('trips.id')
            ->orderBy('trips.id', 'desc')
            ->first();

        // 1. Log the Emergency safely
        $emergency = \App\Models\Emergency::create([
            'trip_id' => $trip ? $trip->id : null,
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'description' => "[{$validated['issue_type']}] - {$validated['remarks']}",
            'status' => 'UNRESOLVED',
        ]);
        $emergencyId = $emergency->id;

        activity()->causedBy($driver)->performedOn(\App\Models\Emergency::find($emergencyId))->log('Emergency Triggered');

        // 2. Automatically ground the vehicle
        \App\Models\Vehicle::findOrFail($vehicle->id)->update([
            'status' => 'BREAKDOWN',
        ]);

        // 3. Fire WebSocket Event to trigger the Bell Icon on the Dispatcher Header
        $emergencyData = [
            'id' => $emergencyId,
            'vehicle_unit' => $vehicle->unit_id,
            'driver_name' => "{$driver->first_name} {$driver->last_name}",
            'issue_type' => $validated['issue_type'],
            'time' => Carbon::now()->toDateTimeString()
        ];

try {
                // CRITICAL FIX: Changed $incidentId to $emergencyId to match your code!
                event(new IncidentReported($emergencyId, $validated['issue_type']));
            } catch (\Exception $broadcastError) {
                \Illuminate\Support\Facades\Log::warning('Broadcast failed but report was saved: ' . $broadcastError->getMessage());
            }
    }
}