<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TripLogController extends Controller
{
    /**
     * Fetch paginated list of trips for the index table
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('trips')
                ->join('shifts', 'trips.shift_id', '=', 'shifts.id')
                ->join('vehicles', 'shifts.vehicle_id', '=', 'vehicles.id')
                ->join('users as drivers', 'shifts.driver_id', '=', 'drivers.id')
                ->select(
                    'trips.id',
                    'trips.current_phase',
                    'trips.created_at',
                    'vehicles.unit_id as vehicle_unit',
                    DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name) as driver_name")
                )
                ->whereNull('trips.deleted_at');

            // 0. Driver Data Isolation
            $user = $request->user();
            if (!$user->hasPermission('trip.view') && strtolower($user->role) !== 'developer') {
                $query->where('shifts.driver_id', $user->id);
            }

            // 1. Date Range Filter
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end = Carbon::parse($request->end_date)->endOfDay();
                $query->whereBetween('trips.created_at', [$start, $end]);
            }

            // 2. Global Search Filter
            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function($q) use ($s) {
                    $q->where('trips.id', 'like', "%$s%")
                      ->orWhere('trips.current_phase', 'like', "%$s%")
                      ->orWhere('trips.created_at', 'like', "%$s%")
                      ->orWhere('vehicles.unit_id', 'like', "%$s%")
                      ->orWhere(DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name)"), 'like', "%$s%");
                });
            }

            // 3. Sorting
            $sortBy = $request->input('sort_by', 'trips.id');
            $sortDir = $request->input('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);

            // 4. Pagination (Supports 10, 25, 50, 100)
            $perPage = $request->input('per_page', 10);
            
            return response()->json($query->paginate((int) $perPage));

        } catch (\Exception $e) {
            Log::error('Error fetching trip logs: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to load trip logs', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Fetch a specific trip's execution details (GPS logs & Incidents)
     */
    public function show(Request $request, $id)
    {
        try {
            // 1. Get Base Trip Details
            $trip = DB::table('trips')
                ->join('shifts', 'trips.shift_id', '=', 'shifts.id')
                ->join('vehicles', 'shifts.vehicle_id', '=', 'vehicles.id')
                ->join('users as drivers', 'shifts.driver_id', '=', 'drivers.id')
                ->select(
                    'trips.id',
                    'trips.current_phase',
                    'trips.started_at',
                    'trips.ended_at',
                    'trips.created_at',
                    'shifts.id as shift_id',
                    'shifts.driver_id',
                    'vehicles.unit_id as vehicle_unit',
                    DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name) as driver_name")
                )
                ->where('trips.id', $id)
                ->whereNull('trips.deleted_at')
                ->first();

            if (!$trip) {
                return response()->json(['message' => 'Trip not found'], 404);
            }

            $user = $request->user();
            if (!$user->hasPermission('trip.view') && strtolower($user->role) !== 'developer') {
                if ((int)$trip->driver_id !== (int)$user->id) {
                    return response()->json(['message' => 'Unauthorized access to trip data.'], 403);
                }
            }

            // 2. Fetch the GPS Timeline logs
            $trip->logs = DB::table('trip_logs')
                ->where('trip_id', $id)
                ->orderBy('phase', 'asc')
                ->get();

            // 3. Fetch any Emergency Incidents reported during this exact shift
            $trip->incidents = DB::table('incidents')
                ->where('shift_id', $trip->shift_id)
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json($trip);

        } catch (\Exception $e) {
            Log::error('Error fetching trip details: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to load trip details', 'error' => $e->getMessage()], 500);
        }
    }


/**
     * Permanently delete a trip log and its GPS history (Single)
     */
public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Only allow dispatchers/admins/developers to delete logs
            if (!$user->hasPermission('trip.delete') && strtolower($user->role) !== 'developer') {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            // CRITICAL FIX: Mirrors the ChecklistController comma-separated logic
            if (strpos($id, ',') !== false) {
                $ids = explode(',', $id);
                \App\Models\Trip::whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => count($ids) . ' trip logs deleted successfully.']);
            }

            \App\Models\Trip::where('id', $id)->forceDelete();

            return response()->json(['message' => 'Trip log deleted successfully.']);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error deleting trip log: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete trip log', 'error' => $e->getMessage()], 500);
        }
    }
    
}