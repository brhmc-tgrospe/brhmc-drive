<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Events\ShiftScheduled;
use Illuminate\Support\Facades\Log;

class VehicleShiftController extends Controller
{
    private function mapShiftToVue($item, $itemPins = null)
    {
        $formattedPins = ['right' => [], 'left' => [], 'front' => [], 'rear' => []];
        
        if ($itemPins) {
            foreach ($itemPins as $pin) {
                $formattedPins[$pin->vehicle_view][] = [
                    'x' => (float) $pin->x_coordinate,
                    'y' => (float) $pin->y_coordinate,
                    'remarks' => $pin->remarks
                ];
            }
        }

        return [
            'id' => $item->id,
            'driver_id' => $item->driver_id,
            'vehicle_id' => $item->vehicle_id,
            'start_time' => $item->start_time,
            'end_time' => $item->end_time,
            'shift_duration' => Carbon::parse($item->start_time)->diffInHours(Carbon::parse($item->end_time)),
            'status' => $item->status,
            'driver' => [
                'id' => $item->driver_id,
                'first_name' => $item->driver_first_name ?? 'Unknown',
                'last_name' => $item->driver_last_name ?? '',
            ],
            'vehicle' => [
                'id' => $item->vehicle_id,
                'unit_id' => $item->vehicle_unit ?? 'Unknown',
                'plate_number' => $item->vehicle_plate ?? '',
                
                'odometer' => (int) ($item->vehicle_odometer ?? 0),
                'fuel_level' => (int) ($item->vehicle_fuel_level ?? 100),
                'tire_psi_front_left' => (int) ($item->vehicle_tire_psi_front_left ?? 32),
                'tire_psi_front_right' => (int) ($item->vehicle_tire_psi_front_right ?? 32),
                'tire_psi_rear_left' => (int) ($item->vehicle_tire_psi_rear_left ?? 32),
                'tire_psi_rear_right' => (int) ($item->vehicle_tire_psi_rear_right ?? 32),
                'active_pins' => $formattedPins,

                'image_path' => $item->vehicle_image_path ?? null,
                'status' => $item->vehicle_status ?? 'READY'
            ],
            'trip_type' => $item->trip_type ?? 'EMERGENCY',
            'trip' => $item->trip_id ? [
                'id' => $item->trip_id,
                'current_phase' => (int) $item->trip_current_phase,
                'is_cleared_by_dispatch' => (bool) $item->trip_is_cleared,
                'type' => $item->trip_type_actual ?? ($item->trip_type ?? 'EMERGENCY'),
                'current_destination' => $item->trip_current_destination ?? null,
            ] : null
        ];
    }

    public function index(Request $request)
    {
        try {
            $query = DB::table('shifts')
                ->join('users as drivers', 'shifts.driver_id', '=', 'drivers.id')
                ->join('vehicles', 'shifts.vehicle_id', '=', 'vehicles.id')
                ->leftJoin('trips', function($join) {
                    $join->on('trips.shift_id', '=', 'shifts.id'); 
                })
                ->select(
                    'shifts.id', 'shifts.driver_id', 'shifts.vehicle_id', 'shifts.status',
                    'shifts.scheduled_start as start_time', 'shifts.scheduled_end as end_time', 'shifts.trip_type',
                    'drivers.first_name as driver_first_name', 'drivers.last_name as driver_last_name',
                    'vehicles.unit_id as vehicle_unit', 'vehicles.plate_number as vehicle_plate', 
                    
                    'vehicles.odometer as vehicle_odometer', 'vehicles.fuel_level as vehicle_fuel_level',
                    'vehicles.tire_psi_front_left as vehicle_tire_psi_front_left', 'vehicles.tire_psi_front_right as vehicle_tire_psi_front_right',
                    'vehicles.tire_psi_rear_left as vehicle_tire_psi_rear_left', 'vehicles.tire_psi_rear_right as vehicle_tire_psi_rear_right',
                    
                    'vehicles.image_path as vehicle_image_path', 'vehicles.status as vehicle_status',
                    'trips.id as trip_id', 'trips.current_phase as trip_current_phase', 'trips.is_cleared_by_dispatch as trip_is_cleared',
                    'trips.type as trip_type_actual', 'trips.current_destination as trip_current_destination'
                )
                ->orderBy('shifts.scheduled_start', 'desc');

            $user = $request->user();
            if (!$user->hasPermission('schedule.view') && strtolower($user->role) !== 'developer') {
                $query->where('shifts.driver_id', $user->id);
            }

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function($q) use ($s) {
                    $q->where('shifts.id', 'like', "%$s%")
                      ->orWhere('shifts.status', 'like', "%$s%")
                      ->orWhere('shifts.scheduled_start', 'like', "%$s%")
                      ->orWhere('shifts.scheduled_end', 'like', "%$s%")
                      ->orWhere('vehicles.unit_id', 'like', "%$s%")
                      ->orWhere('vehicles.plate_number', 'like', "%$s%")
                      ->orWhere(DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name)"), 'like', "%$s%");
                });
            }

            $paginator = $query->paginate((int) $request->input('per_page', 10));
            
            $vehicleIds = collect($paginator->items())->pluck('vehicle_id')->unique()->toArray();
            $allPins = DB::table('damage_pins')->whereIn('vehicle_id', $vehicleIds)->where('status', 'Active')->get();

            $paginator->getCollection()->transform(function ($item) use ($allPins) {
                $itemPins = $allPins->where('vehicle_id', $item->vehicle_id);
                return $this->mapShiftToVue($item, $itemPins);
            });

            return response()->json($paginator);

        } catch (\Exception $e) {
            Log::error('Error fetching all shifts: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error while fetching shifts.', 'error' => $e->getMessage()], 500);
        }
    }

    public function myShifts(Request $request)
    {
        try {
            $query = DB::table('shifts')
                ->join('users as drivers', 'shifts.driver_id', '=', 'drivers.id')
                ->join('vehicles', 'shifts.vehicle_id', '=', 'vehicles.id')
                ->leftJoin('trips', function($join) {
                    $join->on('trips.shift_id', '=', 'shifts.id'); 
                })
                ->select(
                    'shifts.id', 'shifts.driver_id', 'shifts.vehicle_id', 'shifts.status',
                    'shifts.scheduled_start as start_time', 'shifts.scheduled_end as end_time', 'shifts.trip_type',
                    'drivers.first_name as driver_first_name', 'drivers.last_name as driver_last_name',
                    'vehicles.unit_id as vehicle_unit', 'vehicles.plate_number as vehicle_plate', 
                    
                    'vehicles.odometer as vehicle_odometer', 'vehicles.fuel_level as vehicle_fuel_level',
                    'vehicles.tire_psi_front_left as vehicle_tire_psi_front_left', 'vehicles.tire_psi_front_right as vehicle_tire_psi_front_right',
                    'vehicles.tire_psi_rear_left as vehicle_tire_psi_rear_left', 'vehicles.tire_psi_rear_right as vehicle_tire_psi_rear_right',
                    
                    'vehicles.image_path as vehicle_image_path', 'vehicles.status as vehicle_status',
                    'trips.id as trip_id', 'trips.current_phase as trip_current_phase', 'trips.is_cleared_by_dispatch as trip_is_cleared',
                    'trips.type as trip_type_actual', 'trips.current_destination as trip_current_destination'
                )
                ->where('shifts.driver_id', $request->user()->id)
                ->whereIn('shifts.status', ['PENDING', 'ACTIVE']) 
                ->orderBy('shifts.scheduled_start', 'asc');

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function($q) use ($s) {
                    $q->where('shifts.id', 'like', "%$s%")
                      ->orWhere('shifts.status', 'like', "%$s%")
                      ->orWhere('shifts.scheduled_start', 'like', "%$s%")
                      ->orWhere('shifts.scheduled_end', 'like', "%$s%")
                      ->orWhere('vehicles.unit_id', 'like', "%$s%")
                      ->orWhere('vehicles.plate_number', 'like', "%$s%")
                      ->orWhere(DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name)"), 'like', "%$s%");
                });
            }

            $shifts = $query->get();

            $vehicleIds = $shifts->pluck('vehicle_id')->unique()->toArray();
            $shiftIds = $shifts->pluck('id')->unique()->toArray();

            $allPins = DB::table('damage_pins')->whereIn('vehicle_id', $vehicleIds)->where('status', 'Active')->get();
            
            // CRITICAL FIX: Fetch PENDING emergency incidents tied to this exact shift
            $pendingIncidents = DB::table('incidents')
                ->whereIn('shift_id', $shiftIds)
                ->where('status', 'PENDING')
                ->get();

            $mappedData = $shifts->map(function($item) use ($allPins, $pendingIncidents) {
                $itemPins = $allPins->where('vehicle_id', $item->vehicle_id);
                $vueData = $this->mapShiftToVue($item, $itemPins);
                
                // Attach the active emergency to the payload so the frontend can react!
                $vueData['active_emergency'] = $pendingIncidents->where('shift_id', $item->id)->first();
                
                return $vueData;
            });

            return response()->json($mappedData);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching my-shifts: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error while fetching shifts.', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user->hasPermission('schedule.add') && strtolower($user->role) !== 'developer') {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $validated = $request->validate([
                'driver_id' => 'required|exists:users,id',
                'vehicle_id' => 'required|exists:vehicles,id',
                'start_time' => 'required|date',
                'shift_duration' => 'required|integer',
                'trip_type' => 'nullable|in:EMERGENCY,REGULAR',
            ]);

            $startTime = Carbon::parse($validated['start_time']);
            $endTime = $startTime->copy()->addHours($validated['shift_duration']);
            
            $startTimeStr = $startTime->toDateTimeString();
            $endTimeStr = $endTime->toDateTimeString();

            $driverOverlap = DB::table('shifts')
                ->where('driver_id', $validated['driver_id'])
                ->whereIn('status', ['PENDING', 'SCHEDULED', 'ACTIVE'])
                ->where('scheduled_start', '<', $endTimeStr)
                ->where('scheduled_end', '>', $startTimeStr)
                ->exists();

            if ($driverOverlap) {
                return response()->json(['message' => 'Double Booking Detected: This driver is already scheduled during this exact time window.'], 422);
            }

            $vehicleOverlap = DB::table('shifts')
                ->where('vehicle_id', $validated['vehicle_id'])
                ->whereIn('status', ['PENDING', 'SCHEDULED', 'ACTIVE'])
                ->where('scheduled_start', '<', $endTimeStr)
                ->where('scheduled_end', '>', $startTimeStr)
                ->exists();

            if ($vehicleOverlap) {
                return response()->json(['message' => 'Double Booking Detected: This vehicle is already scheduled during this exact time window.'], 422);
            }

            $newShift = \App\Models\Shift::create([
                'driver_id' => $validated['driver_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'scheduled_start' => $startTimeStr,
                'scheduled_end' => $endTimeStr,
                'status' => 'PENDING',
                'trip_type' => $validated['trip_type'] ?? 'EMERGENCY',
            ]);
            $shiftId = $newShift->id;

            // ONLY MANAGE VEHICLE STATUS
            \App\Models\Vehicle::findOrFail($validated['vehicle_id'])->update(['status' => 'SCHEDULED']);

            // Notify driver via WebSocket that they have been scheduled
            try {
                event(new ShiftScheduled($newShift));
            } catch (\Exception $broadcastError) {
                Log::warning('ShiftScheduled broadcast failed: ' . $broadcastError->getMessage());
            }

            return response()->json(['message' => 'Shift scheduled.', 'shift_id' => $shiftId], 201);
            
        } catch (\Exception $e) {
            Log::error('Error scheduling shift: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to schedule shift', 'error' => $e->getMessage()], 500);
        }
    }

    public function startShift(Request $request, $id)
    {
        try {
            $shift = DB::table('shifts')->where('id', $id)->first();
            if (!$shift) return response()->json(['message' => 'Shift not found'], 404);

            $user = $request->user();
            $isAdminExecuting = false;

            if ((int)$shift->driver_id !== (int)$user->id) {
                if ($user->hasPermission('schedule.sign_turnovers') || strtolower($user->role) === 'developer') {
                    $request->validate(['remark' => 'required|string|min:5'], [
                        'remark.required' => 'A remark is required when executing a shift on behalf of a driver.'
                    ]);
                    $isAdminExecuting = true;
                } else {
                    return response()->json(['message' => 'Unauthorized.'], 403);
                }
            }

            \App\Models\Shift::findOrFail($id)->update([
                'status' => 'ACTIVE'
            ]);
            
            // ONLY MANAGE VEHICLE STATUS
            \App\Models\Vehicle::findOrFail($shift->vehicle_id)->update([
                'status' => 'IN_USE'
            ]);

            $logMsg = $isAdminExecuting ? "Shift Started by Admin ({$user->first_name}): {$request->remark}" : 'Shift Started';
            activity()->causedBy($user)->performedOn(\App\Models\Shift::find($id))->log($logMsg);
            return response()->json(['message' => 'Shift Started Successfully!']);
            
        } catch (\Exception $e) {
            Log::error('Error starting shift: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to start shift', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id) { /* Handled in Index */ }
    
public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            if (!$user->hasPermission('schedule.edit') && strtolower($user->role) !== 'developer') {
                DB::rollBack();
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $shift = DB::table('shifts')->where('id', $id)->lockForUpdate()->first();
            if (!$shift) {
                DB::rollBack();
                return response()->json(['message' => 'Shift not found'], 404);
            }

            // ========================================================
            // CRITICAL FIX: VAULT DOOR TO PREVENT ACTIVE SHIFT EDITS
            // ========================================================
            if ($shift->status === 'ACTIVE') {
                DB::rollBack();
                return response()->json(['message' => 'Action Denied: Active shifts cannot be edited. If there is a driver emergency, use the Mid-Shift Turnover feature.'], 403);
            }

            $validated = $request->validate([
                'driver_id' => 'required|exists:users,id',
                'vehicle_id' => 'required|exists:vehicles,id',
                'start_time' => 'required|date',
                'end_time' => 'required|date',
                'shift_duration' => 'required|integer', 
                'trip_type' => 'nullable|in:EMERGENCY,REGULAR',
            ]);
            
            $startTimeStr = Carbon::parse($validated['start_time'])->toDateTimeString();
            $endTimeStr = Carbon::parse($validated['end_time'])->toDateTimeString();

            $driverOverlap = DB::table('shifts')
                ->where('id', '!=', $id)
                ->where('driver_id', $validated['driver_id'])
                ->whereIn('status', ['PENDING', 'SCHEDULED', 'ACTIVE'])
                ->where('scheduled_start', '<', $endTimeStr)
                ->where('scheduled_end', '>', $startTimeStr)
                ->exists();

            if ($driverOverlap) {
                DB::rollBack();
                return response()->json(['message' => 'Double Booking Detected: This driver is already scheduled during this exact time window.'], 422);
            }

            $vehicleOverlap = DB::table('shifts')
                ->where('id', '!=', $id)
                ->where('vehicle_id', $validated['vehicle_id'])
                ->whereIn('status', ['PENDING', 'SCHEDULED', 'ACTIVE'])
                ->where('scheduled_start', '<', $endTimeStr)
                ->where('scheduled_end', '>', $startTimeStr)
                ->exists();

            if ($vehicleOverlap) {
                DB::rollBack();
                return response()->json(['message' => 'Double Booking Detected: This vehicle is already scheduled during this exact time window.'], 422);
            }

            if ((int)$shift->vehicle_id !== (int)$validated['vehicle_id']) {
                if (in_array($shift->status, ['PENDING', 'SCHEDULED'])) {
                    $oldVehicleStillScheduled = DB::table('shifts')->where('vehicle_id', $shift->vehicle_id)->where('id', '!=', $id)->whereIn('status', ['PENDING', 'SCHEDULED'])->exists();
                    \App\Models\Vehicle::findOrFail($shift->vehicle_id)->update(['status' => $oldVehicleStillScheduled ? 'SCHEDULED' : 'READY']);
                }
                \App\Models\Vehicle::findOrFail($validated['vehicle_id'])->update(['status' => 'SCHEDULED']);
            }

            if ((int)$shift->driver_id !== (int)$validated['driver_id']) {
                // The users table does not have a driver_status column, so we do not attempt to update it.
            }

            \App\Models\Shift::findOrFail($id)->update([
                'driver_id' => $validated['driver_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'scheduled_start' => $startTimeStr,
                'scheduled_end' => $endTimeStr,
                'trip_type' => $validated['trip_type'] ?? $shift->trip_type ?? 'EMERGENCY',
            ]);

            DB::commit();
            return response()->json(['message' => 'Shift updated successfully.']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating shift: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update shift', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        if (!$user->hasPermission('schedule.delete') && strtolower($user->role) !== 'developer') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $shift = DB::table('shifts')->where('id', $id)->first();
        if (!$shift) return response()->json(['message' => 'Shift not found'], 404);

        // ========================================================
        // CRITICAL FIX: VAULT DOOR TO PREVENT ACTIVE SHIFT DELETION
        // ========================================================
        if ($shift->status === 'ACTIVE') {
            return response()->json(['message' => 'Action Denied: You cannot delete an active, ongoing shift.'], 403);
        }

        if (in_array($shift->status, ['PENDING', 'SCHEDULED'])) {
            $vehicleStillScheduled = DB::table('shifts')->where('vehicle_id', $shift->vehicle_id)->where('id', '!=', $id)->whereIn('status', ['PENDING', 'SCHEDULED'])->exists();
            \App\Models\Vehicle::findOrFail($shift->vehicle_id)->update(['status' => $vehicleStillScheduled ? 'SCHEDULED' : 'READY']);
        }
        
        \App\Models\Shift::findOrFail($id)->delete();
        
        return response()->json(['message' => 'Shift deleted successfully. Resources Released.']);
    }
}