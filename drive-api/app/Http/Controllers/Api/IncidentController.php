<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Events\IncidentReported; 

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('incidents')
                ->whereNull('incidents.deleted_at')
                ->leftJoin('users as reporters', 'incidents.reporter_id', '=', 'reporters.id')
                ->leftJoin('users as dispatchers', 'incidents.dispatcher_id', '=', 'dispatchers.id')
                ->leftJoin('vehicles', 'incidents.vehicle_id', '=', 'vehicles.id')
                ->select(
                    'incidents.*',
                    'vehicles.unit_id as vehicle_unit',
                    'vehicles.plate_number as vehicle_plate',
                    DB::raw("CONCAT(reporters.first_name, ' ', reporters.last_name) as reporter_name"),
                    DB::raw("CONCAT(dispatchers.first_name, ' ', dispatchers.last_name) as dispatcher_name")
                );

            $sortBy = $request->input('sort_by', 'incidents.id');
            $sortDir = $request->input('sort_dir', 'desc');

            if ($sortBy === 'id') $sortBy = 'incidents.id';
            elseif ($sortBy === 'created_at') $sortBy = 'incidents.created_at';
            elseif ($sortBy === 'vehicle_unit') $sortBy = 'vehicles.unit_id';
            elseif ($sortBy === 'issue_type') $sortBy = 'incidents.issue_type';
            elseif ($sortBy === 'acknowledged_at') $sortBy = 'incidents.acknowledged_at';
            elseif ($sortBy === 'status') $sortBy = 'incidents.status';
            else $sortBy = 'incidents.id';

            $query->orderBy($sortBy, $sortDir);

            // 0. Driver Data Isolation
            $user = $request->user();
            if (!$user->hasPermission('incident.view') && strtolower($user->role) !== 'developer') {
                $query->where('incidents.reporter_id', $user->id);
            }

            if ($request->filled('status')) $query->where('incidents.status', $request->status);
            if ($request->filled('incident_target')) $query->where('incidents.incident_target', $request->incident_target);
            
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end = Carbon::parse($request->end_date)->endOfDay();
                $query->whereBetween('incidents.created_at', [$start, $end]);
            }

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function($q) use ($s) {
                    $q->where('incidents.id', 'like', "%$s%")
                      ->orWhere('incidents.issue_type', 'like', "%$s%")
                      ->orWhere('incidents.status', 'like', "%$s%")
                      ->orWhere('incidents.incident_target', 'like', "%$s%")
                      ->orWhere('incidents.remarks', 'like', "%$s%")
                      ->orWhere('incidents.created_at', 'like', "%$s%")
                      ->orWhere('incidents.acknowledged_at', 'like', "%$s%")
                      ->orWhere('vehicles.unit_id', 'like', "%$s%")
                      ->orWhere('vehicles.plate_number', 'like', "%$s%")
                      ->orWhere(DB::raw("CONCAT(reporters.first_name, ' ', reporters.last_name)"), 'like', "%$s%")
                      ->orWhere(DB::raw("CONCAT(dispatchers.first_name, ' ', dispatchers.last_name)"), 'like', "%$s%");
                });
            }

            if ($request->boolean('all')) {
                return response()->json($query->get()); 
            }

            return response()->json($query->paginate((int) $request->input('per_page', 10)));

        } catch (\Exception $e) {
            Log::error('Error fetching incidents: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_unit' => 'required|string',
            'incident_target' => 'required|in:Vehicle,Driver',
            'issue_type' => 'required|string',
            'remarks' => 'required|string',
            'evidence_image' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        DB::beginTransaction();
        try {
            $user = $request->user();
            $isDispatcher = $user->hasPermission('incident.acknowledge') || strtolower($user->role) === 'developer';

            $vehicle = DB::table('vehicles')->where('unit_id', $validated['vehicle_unit'])->first();
            if (!$vehicle) return response()->json(['message' => 'Vehicle not found.'], 404);

            $activeShift = DB::table('shifts')->where('vehicle_id', $vehicle->id)->whereIn('status', ['ACTIVE', 'SCHEDULED'])->first();

            $incident = \App\Models\Incident::create([
                'reporter_id' => $user->id,
                'vehicle_id' => $vehicle->id,
                'shift_id' => $activeShift ? $activeShift->id : null,
                'incident_target' => $validated['incident_target'],
                'issue_type' => $validated['issue_type'],
                'remarks' => $validated['remarks'],
                'evidence_image' => $validated['evidence_image'] ?? '', 
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'status' => $isDispatcher ? 'ACKNOWLEDGED' : 'PENDING',
                'dispatcher_id' => $isDispatcher ? $user->id : null,
                'acknowledged_at' => $isDispatcher ? Carbon::now() : null,
                'dispatcher_signature' => $isDispatcher ? 'Reported internally by Dispatcher' : null,
            ]);
            $incidentId = $incident->id;

            // If the vehicle broke, ground it immediately.
            if ($validated['incident_target'] === 'Vehicle') {
                \App\Models\Vehicle::findOrFail($vehicle->id)->update(['status' => 'BREAKDOWN']);
            }

            DB::commit();
            activity()->causedBy($request->user())->performedOn(\App\Models\Incident::find($incidentId))->log('Incident Reported');

            try {
                event(new IncidentReported($incidentId, $validated['issue_type']));
            } catch (\Exception $broadcastError) {
                Log::warning('Broadcast failed but incident was saved: ' . $broadcastError->getMessage());
            }

            return response()->json(['message' => 'Incident reported successfully. Dispatch notified.', 'incident_id' => $incidentId], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error filing incident: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to file incident report.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission('incident.edit') && strtolower($user->role) !== 'developer') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'issue_type' => 'required|string',
            'remarks' => 'required|string'
        ]);

        \App\Models\Incident::findOrFail($id)->update([
            'issue_type' => $validated['issue_type'],
            'remarks' => $validated['remarks'],
        ]);

        return response()->json(['message' => 'Incident details updated successfully.']);
    }

    public function acknowledge(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission('incident.acknowledge') && strtolower($user->role) !== 'developer') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'signature' => 'required|string',
            'replacement_driver_id' => 'nullable|exists:users,id'
        ]);

        DB::beginTransaction();
        try {
            $incident = DB::table('incidents')->where('id', $id)->whereNull('deleted_at')->first();
            if (!$incident) {
                DB::rollBack();
                return response()->json(['message' => 'Incident not found'], 404);
            }

            // CRITICAL FIX: If it was a Vehicle Breakdown, NOW we cancel the shift. 
            // We DO NOT update the users table because cancelling the shift automatically frees the driver.
            if ($incident->incident_target === 'Vehicle' && $incident->shift_id) {
                $shift = DB::table('shifts')->where('id', $incident->shift_id)->first();
                if ($shift && $shift->status === 'ACTIVE') {
                    \App\Models\Shift::findOrFail($shift->id)->update([
                        'status' => 'CANCELLED',
                    ]);
                }
            }
            // CRITICAL FIX: If it was a Driver issue, we clone the shift for the handover.
            // We DO NOT update the users table because changing the driver_id automatically frees the old driver!
            elseif ($incident->incident_target === 'Driver' && $incident->shift_id && !empty($validated['replacement_driver_id'])) {
                $oldShift = DB::table('shifts')->where('id', $incident->shift_id)->first();
                
                if ($oldShift && $oldShift->status === 'ACTIVE') {
                    $oldTrip = DB::table('trips')->where('shift_id', $oldShift->id)->orderBy('id', 'desc')->first();
                    if ($oldTrip) {
                        \App\Models\Trip::findOrFail($oldTrip->id)->update(['ended_at' => Carbon::now()]);
                    }

                    // Cancel old shift
                    \App\Models\Shift::findOrFail($oldShift->id)->update([
                        'status' => 'CANCELLED',
                    ]);

                    // Create NEW shift for replacement driver
                    $newShift = \App\Models\Shift::create([
                        'driver_id' => $validated['replacement_driver_id'],
                        'vehicle_id' => $oldShift->vehicle_id,
                        'scheduled_start' => Carbon::now()->toDateTimeString(),
                        'scheduled_end' => $oldShift->scheduled_end,
                        'status' => 'ACTIVE', 
                    ]);
                    $newShiftId = $newShift->id;

                    // Clone the trip phase
                    if ($oldTrip) {
                        \App\Models\Trip::create([
                            'shift_id' => $newShiftId,
                            'current_phase' => $oldTrip->current_phase,
                            'is_cleared_by_dispatch' => 1,
                            'started_at' => Carbon::now(),
                        ]);
                    }
                }
            }

            \App\Models\Incident::findOrFail($id)->update([
                'status' => 'ACKNOWLEDGED',
                'dispatcher_id' => $request->user()->id,
                'acknowledged_at' => Carbon::now(),
                'dispatcher_signature' => $validated['signature'],
            ]);

            DB::commit();
            activity()->causedBy($request->user())->performedOn(\App\Models\Incident::find($id))->log('Incident Acknowledged');
            return response()->json(['message' => 'Incident Acknowledged.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error acknowledging incident: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to acknowledge incident', 'error' => $e->getMessage()], 500);
        }
    }

    public function resolve(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission('incident.edit') && strtolower($user->role) !== 'developer') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        \App\Models\Incident::findOrFail($id)->update([
            'status' => 'RESOLVED',
        ]);

        return response()->json(['message' => 'Incident Resolved.']);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission('incident.delete') && strtolower($user->role) !== 'developer') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (strpos($id, ',') !== false) {
            $ids = explode(',', $id);
            \App\Models\Incident::whereIn('id', $ids)->delete();
            return response()->json(['message' => count($ids) . ' incidents deleted successfully.']);
        }
        
        \App\Models\Incident::findOrFail($id)->delete();
        return response()->json(['message' => 'Incident record deleted.']);
    }
}