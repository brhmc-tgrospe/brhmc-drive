<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AmbulanceInspection;
use Illuminate\Http\Request;

class AmbulanceInspectionController extends Controller
{
    /**
     * Fetch all inspections with their related user and vehicle data.
     */
    public function index()
    {
        $inspections = AmbulanceInspection::with([
            'vehicle:id,unit_id,plate_number', 
            'dispatcher:id,first_name,last_name',
            'outgoingDriver:id,first_name,last_name',
            'incomingDriver:id,first_name,last_name'
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($inspections);
    }

    /**
     * Store a newly created ambulance inspection log.
     */
    public function store(Request $request)
    {
        // 1. Validate the critical structural and relational data
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'dispatcher_id' => 'nullable|exists:users,id',
            'outgoing_driver_id' => 'nullable|exists:users,id',
            'incoming_driver_id' => 'nullable|exists:users,id',
            
            'inspection_type' => 'required|in:PRE_TRIP,POST_TRIP,ROUTINE',
            'odometer' => 'required|integer|min:0',
            'fuel_level' => 'required|integer|min:0|max:100',
            'is_fit_for_use' => 'required|boolean',
            
            // Validate the damage pin JSON arrays
            'damage_findings_right' => 'nullable|array',
            'damage_findings_left' => 'nullable|array',
            'damage_findings_front' => 'nullable|array',
            'damage_findings_rear' => 'nullable|array',
        ]);

        // 2. Merge the validated core data with the rest of the boolean/integer payload
        // (Safe because the Model's $fillable array blocks any unauthorized columns)
        $payload = array_merge($request->all(), $validated);

        // 3. Save to database
        $inspection = AmbulanceInspection::create($payload);

        // 4. If the inspection updates the vehicle's odometer, update the parent Vehicle record
        $inspection->vehicle()->update([
            'odometer' => $validated['odometer']
        ]);

        return response()->json([
            'message' => 'Ambulance inspection logged successfully.',
            'inspection' => $inspection
        ], 201);
    }

    public function accept(Request $request, $id)
    {
        $inspection = AmbulanceInspection::findOrFail($id);
        $inspection->vehicle()->update(['status' => 'MAINTENANCE']);
        activity()->causedBy($request->user())->performedOn($inspection)->log('Vehicle Transferred to Maintenance Bay');
        return response()->json(['message' => 'Vehicle successfully transferred to maintenance bay.', 'inspection' => $inspection]);
    }

    public function release(Request $request, $id)
    {
        $inspection = AmbulanceInspection::findOrFail($id);
        $inspection->vehicle()->update(['status' => 'READY']);
        activity()->causedBy($request->user())->performedOn($inspection)->log('Vehicle Released from Maintenance Bay');
        return response()->json(['message' => 'Vehicle successfully released from maintenance bay.']);
    }
}
