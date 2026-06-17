<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VehicleController extends Controller
{
    /**
     * Helper Method: Injects the active driver and trip phase into the vehicle payload.
     * This is what powers the Dispatcher's "Live Trip Tracking" grid!
     */
    private function appendActiveShiftData($vehicles)
    {
        $vehicleIds = collect($vehicles)->pluck('id')->toArray();

        if (empty($vehicleIds)) return;

        // Fetch shifts for these vehicles, prioritizing ACTIVE shifts first
        $shifts = DB::table('shifts')
            ->leftJoin('users', 'shifts.driver_id', '=', 'users.id')
            ->leftJoin('trips', function($join) {
                // Link the trip, but only if it's not officially complete
                $join->on('trips.shift_id', '=', 'shifts.id')
                     ->where('trips.current_phase', '<', 8);
            })
            ->whereIn('shifts.vehicle_id', $vehicleIds)
            ->whereIn('shifts.status', ['ACTIVE', 'PENDING', 'SCHEDULED'])
            // Custom SQL sorting: Put 'ACTIVE' first, then sort the rest by nearest schedule
            ->orderByRaw("CASE WHEN shifts.status = 'ACTIVE' THEN 1 ELSE 2 END")
            ->orderBy('shifts.scheduled_start', 'asc')
            ->select(
                'shifts.vehicle_id',
                'shifts.id as shift_id',
                'trips.id as trip_id',
                'trips.current_phase',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as driver_name")
            )
            ->get()
            ->groupBy('vehicle_id');

        foreach ($vehicles as $v) {
            // Get the highest priority shift for this specific vehicle
            $shift = $shifts->get($v->id)?->first();
            
            if ($shift) {
                $v->current_driver = $shift->driver_name;
                $v->trip_id = $shift->trip_id;
                $v->current_phase = $shift->current_phase;
                $v->shift_id = $shift->shift_id;
            } else {
                $v->current_driver = null;
                $v->trip_id = null;
                $v->current_phase = null;
                $v->shift_id = null;
            }
        }
    }

    /**
     * Helper Method: Injects active damage pins into the vehicle payload.
     */
    private function appendActivePins($vehicles)
    {
        $vehicleIds = collect($vehicles)->pluck('id')->toArray();
        if (empty($vehicleIds)) return;

        $allPins = DB::table('damage_pins')
            ->whereIn('vehicle_id', $vehicleIds)
            ->where('status', 'Active')
            ->get();

        foreach ($vehicles as $v) {
            $formattedPins = ['right' => [], 'left' => [], 'front' => [], 'rear' => []];
            $itemPins = $allPins->where('vehicle_id', $v->id);
            
            foreach ($itemPins as $pin) {
                $formattedPins[$pin->vehicle_view][] = [
                    'x' => (float) $pin->x_coordinate,
                    'y' => (float) $pin->y_coordinate,
                    'remarks' => $pin->remarks
                ];
            }
            $v->active_pins = $formattedPins;
        }
    }

    /**
     * Fetch all vehicles (Paginated or All)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user && strtolower($user->role) !== 'developer' && !$user->hasPermission('vehicle.view') && !$user->hasPermission('schedule.add') && !$user->hasPermission('schedule.edit') && !$user->hasPermission('schedule.view') && !$user->hasPermission('maintenance.full')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // BULLETPROOF QUERY: Bypass Eloquent Resources
        $query = DB::table('vehicles')->whereNull('deleted_at');

        // Real-Time Searching
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('id', 'like', "%$s%")
                  ->orWhere('unit_id', 'like', "%$s%")
                  ->orWhere('plate_number', 'like', "%$s%")
                  ->orWhere('vehicle_type', 'like', "%$s%")
                  ->orWhere('make_model', 'like', "%$s%")
                  ->orWhere('status', 'like', "%$s%")
                  ->orWhere('odometer', 'like', "%$s%")
                  ->orWhere('base_location', 'like', "%$s%");
            });
        }

        // Clickable Sorting
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'desc');

        $allowedSorts = ['unit_id', 'plate_number', 'status', 'base_location', 'id'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        // Return full list for dropdown menus and Dashboard Map
        if ($request->boolean('all')) {
            $vehicles = $query->get();
            $this->appendActiveShiftData($vehicles); // Inject Live Data
            $this->appendActivePins($vehicles); // Inject Live Pins
            return response()->json($vehicles);
        }

        // Pagination for Masterlist
        $perPage = $request->input('per_page', 10);
        $paginator = $query->paginate($perPage);
        
        $collection = $paginator->getCollection();
        $this->appendActiveShiftData($collection); // Inject Live Data
        $this->appendActivePins($collection); // Inject Live Pins
        $paginator->setCollection($collection);

        return response()->json($paginator);
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user->hasPermission('vehicle.add') && strtolower($user->role) !== 'developer') {
            return response()->json(['message' => 'Unauthorized. You do not have permission to add vehicles.'], 403);
        }

        $validated = $request->validate([
            'unit_id' => 'required|string|unique:vehicles',
            'plate_number' => 'required|string|unique:vehicles',
            'vehicle_type' => 'required|string',
            'make_model' => 'required|string',
            'status' => 'required|in:READY,IN_USE,MAINTENANCE,BREAKDOWN,SCHEDULED',
            'odometer' => 'required|numeric|min:0',
            'base_location' => 'required|string',
        ]);

        $newVehicle = \App\Models\Vehicle::create($validated);
        $id = $newVehicle->id;

        $vehicle = DB::table('vehicles')->where('id', $id)->whereNull('deleted_at')->first();
        $this->appendActiveShiftData(collect([$vehicle]));

        return response()->json([
            'message' => 'Vehicle added successfully.',
            'vehicle' => $vehicle
        ], 201);
    }
    
    /**
     * Display a specific vehicle.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if ($user && strtolower($user->role) !== 'developer' && !$user->hasPermission('vehicle.view')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $vehicle = DB::table('vehicles')->where('id', $id)->whereNull('deleted_at')->first();
        
        if (!$vehicle) {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }
        
        $collection = collect([$vehicle]);
        $this->appendActiveShiftData($collection);
        $this->appendActivePins($collection);
        
        return response()->json($vehicle);
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, $id) 
    {
        $user = $request->user();
        
        if (!$user->hasPermission('vehicle.edit') && strtolower($user->role) !== 'developer') {
            return response()->json(['message' => 'Unauthorized. You do not have permission to edit vehicles.'], 403);
        }

        $vehicle = DB::table('vehicles')->where('id', $id)->whereNull('deleted_at')->first();
        
        if (!$vehicle) {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }

        $validated = $request->validate([
            'unit_id' => 'required|string|unique:vehicles,unit_id,' . $id,
            'plate_number' => 'required|string|unique:vehicles,plate_number,' . $id,
            'vehicle_type' => 'required|string',
            'make_model' => 'required|string',
            'status' => 'required|in:READY,IN_USE,MAINTENANCE,BREAKDOWN,SCHEDULED',
            'odometer' => 'required|numeric|min:0',
            'base_location' => 'required|string',
        ]);

        $previousStatus = $vehicle->status;

        \App\Models\Vehicle::findOrFail($id)->update($validated);

        $updatedVehicle = DB::table('vehicles')->where('id', $id)->whereNull('deleted_at')->first();
        $collection = collect([$updatedVehicle]);
        $this->appendActiveShiftData($collection);
        $this->appendActivePins($collection);

        if ($previousStatus !== $validated['status']) {
            try {
                event(new \App\Events\VehicleStatusChanged($updatedVehicle, $previousStatus, $validated['status']));
            } catch (\Exception $broadcastError) {
                \Illuminate\Support\Facades\Log::warning('Broadcast failed: ' . $broadcastError->getMessage());
            }
        }

        return response()->json([
            'message' => 'Vehicle updated successfully.',
            'vehicle' => $updatedVehicle
        ]);
    }

    /**
     * Remove the specified vehicle from the database.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        if (!$user->hasPermission('vehicle.delete') && strtolower($user->role) !== 'developer') {
            return response()->json(['message' => 'Unauthorized. You do not have permission to delete vehicles.'], 403);
        }

        $vehicle = DB::table('vehicles')->where('id', $id)->whereNull('deleted_at')->first();
        
        if (!$vehicle) {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }

        if ($vehicle->image_path && Storage::disk('public')->exists($vehicle->image_path)) {
            Storage::disk('public')->delete($vehicle->image_path);
        }
        
        \App\Models\Vehicle::findOrFail($id)->delete();

        return response()->json(['message' => 'Vehicle removed from fleet.']);
    }

    /**
     * Upload an image for the vehicle (Base64 Database Storage)
     */
/**
     * Upload an image for the vehicle (Base64 Database Storage)
     */
    public function uploadImage(Request $request, $id)
    {
        $user = $request->user();
        
        if (!$user->hasPermission('vehicle.edit') && strtolower($user->role) !== 'developer') {
            return response()->json(['message' => 'Unauthorized. You cannot upload vehicle images.'], 403);
        }

        $vehicle = DB::table('vehicles')->where('id', $id)->whereNull('deleted_at')->first();
        
        if (!$vehicle) {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }

        // CRITICAL FIX: Look for 'base64_image' to bypass Laravel's automatic file detection
        $validated = $request->validate([
            'base64_image' => 'required|string', 
        ]);

        // Clean up the old physical file if it exists and isn't a Base64 string yet
        if ($vehicle->image_path && !str_starts_with($vehicle->image_path, 'data:image') && Storage::disk('public')->exists($vehicle->image_path)) {
            Storage::disk('public')->delete($vehicle->image_path);
        }
        
        // Save the Base64 String directly to the database
        \App\Models\Vehicle::findOrFail($id)->update([
            'image_path' => $validated['base64_image']
        ]);

        $updatedVehicle = DB::table('vehicles')->where('id', $id)->whereNull('deleted_at')->first();
        $collection = collect([$updatedVehicle]);
        $this->appendActiveShiftData($collection);
        $this->appendActivePins($collection);

        return response()->json([
            'message' => 'Image saved to database successfully.',
            'vehicle' => $updatedVehicle
        ]);
    }
}