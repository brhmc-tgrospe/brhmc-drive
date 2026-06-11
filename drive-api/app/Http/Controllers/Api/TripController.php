<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // CRITICAL FIX: Added Log Import
use App\Events\TripPhaseAdvanced; 

class TripController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
        ]);

        $existing = DB::table('trips')
            ->whereNull('deleted_at')
            ->where('shift_id', $validated['shift_id'])
            ->where('current_phase', '<', 8) 
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'An active trip already exists for this shift.'], 422);
        }

        $trip = \App\Models\Trip::create([
            'shift_id' => $validated['shift_id'],
            'current_phase' => 0,
            'is_cleared_by_dispatch' => 0,
        ]);
        $tripId = $trip->id;

        activity()->causedBy($request->user())->performedOn(\App\Models\Trip::find($tripId))->log('Trip Started');
        return response()->json([
            'message' => 'Trip initialized.',
            'trip_id' => $tripId
        ], 201);
    }

    public function show($id)
    {
        $trip = DB::table('trips')
            ->whereNull('trips.deleted_at')
            ->leftJoin('shifts', 'trips.shift_id', '=', 'shifts.id')
            ->leftJoin('vehicles', 'shifts.vehicle_id', '=', 'vehicles.id')
            ->leftJoin('users as drivers', 'shifts.driver_id', '=', 'drivers.id')
            ->select(
                'trips.*',
                'shifts.scheduled_start',
                'shifts.scheduled_end',
                'vehicles.unit_id',
                'vehicles.plate_number',
                DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name) as driver_name")
            )
            ->where('trips.id', $id)
            ->first();

        if (!$trip) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        return response()->json($trip);
    }

    public function advancePhase(Request $request, $id) 
    {
        $validated = $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        $trip = DB::table('trips')->where('id', $id)->whereNull('deleted_at')->first();
        if (!$trip) return response()->json(['message' => 'Trip not found'], 404);

        $shift = DB::table('shifts')->where('id', $trip->shift_id)->first();
        $user = $request->user();

        if ($shift && (int)$shift->driver_id !== (int)$user->id && !$user->hasPermission('manage_schedules') && $user->role !== 'developer') {
            return response()->json(['message' => 'Unauthorized. You are not assigned to this trip.'], 403);
        }

        $currentPhase = (int) $trip->current_phase;

        if ($currentPhase === 1 && !(bool)$trip->is_cleared_by_dispatch) {
            return response()->json(['message' => 'Cannot start trip. Awaiting dispatcher clearance.'], 403);
        }

        if ($currentPhase === 7) {
            return response()->json(['message' => 'Cannot advance phase. Post-Trip Inspection is strictly required to end the shift.'], 422);
        }

        if ($currentPhase >= 8) { 
            return response()->json(['message' => 'Trip is already complete.'], 422);
        }

        $newPhase = $currentPhase + 1;
        $updates = [
            'current_phase' => $newPhase,
        ];
        
        if ($newPhase === 2) {
            $updates['started_at'] = Carbon::now();
            if ($shift) \App\Models\Vehicle::where('id', $shift->vehicle_id)->update(['status' => 'IN_USE']);
        }

        \App\Models\Trip::where('id', $id)->update($updates);

        if (isset($validated['latitude']) && isset($validated['longitude'])) {
            \App\Models\TripLog::create([
                'trip_id' => $trip->id,
                'phase' => $newPhase,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);
        }

        // CRITICAL FIX: Safe Broadcasting wrapper
        try {
            $driverName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            event(new TripPhaseAdvanced($id, $newPhase, $driverName ?: null, $user->id));
        } catch (\Exception $broadcastError) {
            Log::warning('Broadcast failed but phase advanced: ' . $broadcastError->getMessage());
        }

        activity()->causedBy($request->user())->performedOn(\App\Models\Trip::find($id))->log('Trip Phase Advanced to ' . $newPhase);
        return response()->json([
            'message' => 'Trip advanced to phase ' . $newPhase,
            'current_phase' => $newPhase
        ]);
    }

    public function clearForDeparture(Request $request, $id)
    {
        $trip = DB::table('trips')->where('id', $id)->whereNull('deleted_at')->first();
        if (!$trip) return response()->json(['message' => 'Trip not found'], 404);

        $user = $request->user();
        if (!$user->hasPermission('manage_schedules') && !$user->hasPermission('execute_shifts') && $user->role !== 'developer') {
            return response()->json(['message' => 'Unauthorized. Only dispatchers can clear trips.'], 403);
        }

        if ((int) $trip->current_phase !== 1) {
            return response()->json(['message' => 'Trip is not currently waiting for dispatcher clearance.'], 422);
        }

        \App\Models\Trip::where('id', $id)->update([
            'is_cleared_by_dispatch' => 1,
        ]);

        activity()->causedBy($request->user())->performedOn(\App\Models\Trip::find($id))->log('Trip Cleared for Departure');
        return response()->json(['message' => 'Vehicle successfully cleared for dispatch.']);
    }
}