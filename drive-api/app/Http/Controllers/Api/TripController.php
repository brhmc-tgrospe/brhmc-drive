<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
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

        // Inherit trip type from the shift
        $shift = DB::table('shifts')->where('id', $validated['shift_id'])->first();
        $tripType = $shift->trip_type ?? 'EMERGENCY';

        $trip = \App\Models\Trip::create([
            'shift_id' => $validated['shift_id'],
            'type' => $tripType,
            'current_phase' => 0,
            'is_cleared_by_dispatch' => 0,
        ]);
        $tripId = $trip->id;

        activity()->causedBy($request->user())->performedOn(\App\Models\Trip::find($tripId))->log('Trip Started (' . $tripType . ')');
        return response()->json([
            'message' => 'Trip initialized.',
            'trip_id' => $tripId,
            'type' => $tripType,
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

    /**
     * Advance trip phase.
     * 
     * For EMERGENCY trips: linear phase progression 0 -> 1 -> 2 -> ... -> 7
     * For REGULAR trips: uses 'action' parameter for flexible state machine:
     *   Phase 0: Pre-trip (same as emergency)
     *   Phase 1: Awaiting dispatch clearance (same as emergency)
     *   Phase 2: Log Dispatch from Base -> prompts destination modal
     *   Phase 3: En Route to Destination
     *   Phase 4: Arrived at Destination -> choose Next Destination or Return to Base
     *   Phase 5: Returning to Base
     *   Phase 6: Arrived at Base (triggers Post-Trip)
     *   Phase 7: Post-Trip Inspection
     */
    public function advancePhase(Request $request, $id) 
    {
        $validated = $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'action' => 'nullable|string|in:dispatch_from_base,arrive_destination,next_destination,return_base,arrive_base,standby_next_destination,end_shift',
            'destination' => 'nullable|string|max:255',
            'intent' => 'nullable|string|in:standby,end_shift',
        ]);

        $trip = DB::table('trips')->where('id', $id)->whereNull('deleted_at')->first();
        if (!$trip) return response()->json(['message' => 'Trip not found'], 404);

        $shift = DB::table('shifts')->where('id', $trip->shift_id)->first();
        $user = $request->user();

        if ($shift && (int)$shift->driver_id !== (int)$user->id && !$user->hasPermission('manage_schedules') && $user->role !== 'developer') {
            return response()->json(['message' => 'Unauthorized. You are not assigned to this trip.'], 403);
        }

        $currentPhase = (int) $trip->current_phase;
        $tripType = $trip->type ?? 'EMERGENCY';

        // ========================================
        // EMERGENCY TRIP: Linear phase progression
        // ========================================
        if ($tripType === 'EMERGENCY') {
            return $this->advanceEmergencyPhase($request, $trip, $currentPhase, $validated, $user, $shift);
        }

        // ========================================
        // REGULAR TRIP: Action-based state machine
        // ========================================
        return $this->advanceRegularPhase($request, $trip, $currentPhase, $validated, $user, $shift);
    }

    /**
     * Emergency trip: simple linear phase increment (original behavior).
     */
    private function advanceEmergencyPhase(Request $request, $trip, int $currentPhase, array $validated, $user, $shift)
    {
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

        \App\Models\Trip::where('id', $trip->id)->update($updates);

        $this->logPhaseEntry($trip->id, $newPhase, $validated, null, null);
        $this->broadcastPhaseAdvance($trip->id, $newPhase, $user);

        activity()->causedBy($request->user())->performedOn(\App\Models\Trip::find($trip->id))->log('Trip Phase Advanced to ' . $newPhase);
        return response()->json([
            'message' => 'Trip advanced to phase ' . $newPhase,
            'current_phase' => $newPhase
        ]);
    }

    /**
     * Regular trip: action-based state machine with destination looping.
     * 
     * State transitions:
     *   Phase 0 -> 1 (Pre-trip submitted, awaiting clearance)
     *   Phase 1 -> 2 (Cleared, driver taps "Log Dispatch from Base" -> destination modal)
     *   Phase 2 -> 3 (dispatch_from_base: En Route with destination)
     *   Phase 3 -> 4 (arrive_destination: Arrived at destination)
     *   Phase 4 -> 3 (next_destination: Loop back En Route with NEW destination)
     *   Phase 4 -> 5 (return_base: Returning to base — with intent: standby or end_shift)
     *   Phase 5 -> 6 (arrive_base: Arrived at base)
     *   Phase 6 -> 3 (standby_next_destination: New destination from standby — loops back)
     *   Phase 6 -> 7 (end_shift: Post-Trip inspection required)
     */
    private function advanceRegularPhase(Request $request, $trip, int $currentPhase, array $validated, $user, $shift)
    {
        $action = $validated['action'] ?? null;
        $destination = $validated['destination'] ?? null;

        // Phase 0 and 1 work the same as Emergency (pre-trip + clearance)
        if ($currentPhase <= 1) {
            if ($currentPhase === 1 && !(bool)$trip->is_cleared_by_dispatch) {
                return response()->json(['message' => 'Cannot start trip. Awaiting dispatcher clearance.'], 403);
            }

            // Phase 1 (cleared) + dispatch_from_base action: jump directly to Phase 3 (En Route)
            if ($currentPhase === 1 && $action === 'dispatch_from_base') {
                if (!$destination) {
                    return response()->json(['message' => 'Destination is required when dispatching.'], 422);
                }

                $updates = [
                    'current_phase' => 3,
                    'current_destination' => $destination,
                    'started_at' => Carbon::now(),
                ];
                if ($shift) \App\Models\Vehicle::where('id', $shift->vehicle_id)->update(['status' => 'IN_USE']);

                \App\Models\Trip::where('id', $trip->id)->update($updates);
                $this->logPhaseEntry($trip->id, 2, $validated, 'dispatch_from_base_start', null);
                $this->logPhaseEntry($trip->id, 3, $validated, 'dispatch_from_base', $destination);
                $this->broadcastPhaseAdvance($trip->id, 3, $user);

                activity()->causedBy($request->user())->performedOn(\App\Models\Trip::find($trip->id))->log('Dispatched to: ' . $destination);
                return response()->json([
                    'message' => 'En route to ' . $destination,
                    'current_phase' => 3,
                    'current_destination' => $destination,
                ]);
            }

            $newPhase = $currentPhase + 1;
            $updates = ['current_phase' => $newPhase];

            if ($newPhase === 2) {
                $updates['started_at'] = Carbon::now();
                if ($shift) \App\Models\Vehicle::where('id', $shift->vehicle_id)->update(['status' => 'IN_USE']);
            }

            \App\Models\Trip::where('id', $trip->id)->update($updates);
            $this->logPhaseEntry($trip->id, $newPhase, $validated, 'phase_advance', null);
            $this->broadcastPhaseAdvance($trip->id, $newPhase, $user);

            activity()->causedBy($request->user())->performedOn(\App\Models\Trip::find($trip->id))->log('Regular Trip Phase Advanced to ' . $newPhase);
            return response()->json([
                'message' => 'Trip advanced to phase ' . $newPhase,
                'current_phase' => $newPhase,
            ]);
        }

        // Phase 2: Log Dispatch from Base -> goes to En Route (Phase 3)
        if ($currentPhase === 2 && $action === 'dispatch_from_base') {
            if (!$destination) {
                return response()->json(['message' => 'Destination is required when dispatching.'], 422);
            }

            \App\Models\Trip::where('id', $trip->id)->update([
                'current_phase' => 3,
                'current_destination' => $destination,
            ]);

            $this->logPhaseEntry($trip->id, 3, $validated, 'dispatch_from_base', $destination);
            $this->broadcastPhaseAdvance($trip->id, 3, $user);

            activity()->causedBy($request->user())->performedOn(\App\Models\Trip::find($trip->id))->log('Dispatched to: ' . $destination);
            return response()->json([
                'message' => 'En route to ' . $destination,
                'current_phase' => 3,
                'current_destination' => $destination,
            ]);
        }

        // Phase 3: Arrive at Destination -> goes to Arrived (Phase 4)
        if ($currentPhase === 3 && $action === 'arrive_destination') {
            \App\Models\Trip::where('id', $trip->id)->update([
                'current_phase' => 4,
            ]);

            $this->logPhaseEntry($trip->id, 4, $validated, 'arrive_destination', $trip->current_destination);
            $this->broadcastPhaseAdvance($trip->id, 4, $user);

            activity()->causedBy($request->user())->performedOn(\App\Models\Trip::find($trip->id))->log('Arrived at: ' . $trip->current_destination);
            return response()->json([
                'message' => 'Arrived at ' . $trip->current_destination,
                'current_phase' => 4,
            ]);
        }

        // Phase 4 -> Next Destination: Loop back to En Route (Phase 3)
        if ($currentPhase === 4 && $action === 'next_destination') {
            if (!$destination) {
                return response()->json(['message' => 'Next destination is required.'], 422);
            }

            \App\Models\Trip::where('id', $trip->id)->update([
                'current_phase' => 3,
                'current_destination' => $destination,
            ]);

            $this->logPhaseEntry($trip->id, 3, $validated, 'next_destination', $destination);
            $this->broadcastPhaseAdvance($trip->id, 3, $user);

            activity()->causedBy($request->user())->performedOn(\App\Models\Trip::find($trip->id))->log('Proceeding to next destination: ' . $destination);
            return response()->json([
                'message' => 'En route to ' . $destination,
                'current_phase' => 3,
                'current_destination' => $destination,
            ]);
        }

        // Phase 4 -> Return to Base (Phase 5) — with intent tracking
        if ($currentPhase === 4 && $action === 'return_base') {
            $intent = $validated['intent'] ?? 'end_shift';
            $intentLabel = $intent === 'standby' ? 'Base (Standby)' : 'Base (End Shift)';
            $actionLabel = $intent === 'standby' ? 'return_base_standby' : 'return_base_end_shift';

            \App\Models\Trip::where('id', $trip->id)->update([
                'current_phase' => 5,
                'current_destination' => $intentLabel,
            ]);

            $this->logPhaseEntry($trip->id, 5, $validated, $actionLabel, $intentLabel);
            $this->broadcastPhaseAdvance($trip->id, 5, $user);

            $logMessage = $intent === 'standby' ? 'Returning to Base (Standby)' : 'Returning to Base (End Shift)';
            activity()->causedBy($request->user())->performedOn(\App\Models\Trip::find($trip->id))->log($logMessage);
            return response()->json([
                'message' => $logMessage,
                'current_phase' => 5,
                'current_destination' => $intentLabel,
            ]);
        }

        // Phase 5 -> Arrive at Base (Phase 6)
        if ($currentPhase === 5 && $action === 'arrive_base') {
            $isStandby = str_contains($trip->current_destination ?? '', 'Standby');
            $actionLabel = $isStandby ? 'arrive_base_standby' : 'arrive_base_end_shift';

            \App\Models\Trip::where('id', $trip->id)->update([
                'current_phase' => 6,
            ]);

            $this->logPhaseEntry($trip->id, 6, $validated, $actionLabel, null);
            $this->broadcastPhaseAdvance($trip->id, 6, $user);

            $logMessage = $isStandby ? 'Arrived at Base (Standby)' : 'Arrived at Base (End Shift)';
            activity()->causedBy($request->user())->performedOn(\App\Models\Trip::find($trip->id))->log($logMessage);
            return response()->json([
                'message' => $logMessage,
                'current_phase' => 6,
            ]);
        }

        // Phase 6: Standby at Base — driver can dispatch again or end shift
        if ($currentPhase === 6 && $action === 'standby_next_destination') {
            if (!$destination) {
                return response()->json(['message' => 'Next destination is required.'], 422);
            }

            \App\Models\Trip::where('id', $trip->id)->update([
                'current_phase' => 3,
                'current_destination' => $destination,
            ]);

            $this->logPhaseEntry($trip->id, 3, $validated, 'standby_next_destination', $destination);
            $this->broadcastPhaseAdvance($trip->id, 3, $user);

            activity()->causedBy($request->user())->performedOn(\App\Models\Trip::find($trip->id))->log('Dispatched from Standby to: ' . $destination);
            return response()->json([
                'message' => 'En route to ' . $destination,
                'current_phase' => 3,
                'current_destination' => $destination,
            ]);
        }

        // Phase 6: End Shift — triggers Post-Trip requirement
        if ($currentPhase === 6 && $action === 'end_shift') {
            $this->logPhaseEntry($trip->id, 6, $validated, 'standby_end_shift', null);
            return response()->json(['message' => 'Post-Trip Inspection is required. Submit checklist to advance.'], 422);
        }

        // Phase 6: No action — prompt for Post-Trip (direct checklist submission path)
        if ($currentPhase === 6) {
            return response()->json(['message' => 'Post-Trip Inspection is required. Submit checklist to advance.'], 422);
        }

        if ($currentPhase >= 7) {
            return response()->json(['message' => 'Trip is already complete or awaiting final clearance.'], 422);
        }

        return response()->json(['message' => 'Invalid action for current phase.'], 422);
    }

    /**
     * Log a phase entry into trip_logs with optional action label and destination.
     */
    private function logPhaseEntry(int $tripId, int $phase, array $validated, ?string $actionLabel, ?string $destination): void
    {
        $data = [
            'trip_id' => $tripId,
            'phase' => $phase,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ];

        if ($actionLabel) $data['action_label'] = $actionLabel;
        if ($destination) $data['destination'] = $destination;

        \App\Models\TripLog::create($data);
    }

    /**
     * Broadcast phase advancement via WebSocket (safe wrapper).
     */
    private function broadcastPhaseAdvance(int $tripId, int $newPhase, $user): void
    {
        try {
            $driverName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            event(new TripPhaseAdvanced($tripId, $newPhase, $driverName ?: null, $user->id));
        } catch (\Exception $broadcastError) {
            Log::warning('Broadcast failed but phase advanced: ' . $broadcastError->getMessage());
        }
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