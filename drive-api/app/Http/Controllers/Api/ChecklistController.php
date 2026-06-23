<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Events\ChecklistSubmitted;

class ChecklistController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('checklists')
                ->whereNull('checklists.deleted_at')
                ->leftJoin('trips', 'checklists.trip_id', '=', 'trips.id')
                ->leftJoin('shifts', 'trips.shift_id', '=', 'shifts.id')
                ->leftJoin('vehicles', function($join) {
                    $join->on('shifts.vehicle_id', '=', 'vehicles.id')
                         ->orOn('checklists.vehicle_id', '=', 'vehicles.id');
                })
                ->leftJoin('users as drivers', 'shifts.driver_id', '=', 'drivers.id')
                ->leftJoin('users as dispatchers', 'checklists.dispatcher_id', '=', 'dispatchers.id')
                ->select(
                    'checklists.id',
                    'checklists.trip_id',
                    'checklists.type',
                    'checklists.status',
                    'checklists.created_at as date',
                    'vehicles.unit_id as vehicle_unit',
                    'vehicles.plate_number as vehicle_plate',
                    DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name) as outgoing_driver"),
                    DB::raw("CONCAT(dispatchers.first_name, ' ', dispatchers.last_name) as dispatcher_name")
                );

            $sortBy = $request->input('sort_by', 'checklists.id');
            $sortDir = $request->input('sort_dir', 'desc');
            
            if ($sortBy === 'vehicle_unit') $sortBy = 'vehicles.unit_id';
            elseif ($sortBy === 'type') $sortBy = 'checklists.type';
            elseif ($sortBy === 'status') $sortBy = 'checklists.status';
            else $sortBy = 'checklists.id'; // safe fallback

            $query->orderBy($sortBy, $sortDir);

            // 0. Driver Data Isolation — scope by role so drivers only see own data
            $user = $request->user();
            if (strtolower($user->role) === 'driver') {
                $query->where('shifts.driver_id', $user->id);
            }

            if ($request->filled('status')) $query->where('checklists.status', $request->status);
            if ($request->filled('trip_id')) $query->where('checklists.trip_id', $request->trip_id);
            if ($request->filled('type')) $query->where('checklists.type', $request->type);

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function($q) use ($s) {
                    $q->where('checklists.id', 'like', "%$s%")
                      ->orWhere('checklists.trip_id', 'like', "%$s%")
                      ->orWhere('checklists.type', 'like', "%$s%")
                      ->orWhere('checklists.status', 'like', "%$s%")
                      ->orWhere('checklists.created_at', 'like', "%$s%")
                      ->orWhere('vehicles.unit_id', 'like', "%$s%")
                      ->orWhere('vehicles.plate_number', 'like', "%$s%")
                      ->orWhere(DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name)"), 'like', "%$s%")
                      ->orWhere(DB::raw("CONCAT(dispatchers.first_name, ' ', dispatchers.last_name)"), 'like', "%$s%");
                });
            }

            $paginator = $query->paginate((int) $request->input('per_page', 50));
            $response = $paginator->toArray();

            $checklistIds = array_column($response['data'], 'id');

            if (!empty($checklistIds)) {
                $metaItems = DB::table('checklist_items')
                    ->whereIn('checklist_id', $checklistIds)
                    ->get();

                foreach ($response['data'] as &$item) {
                    $myMeta = $metaItems->where('checklist_id', $item->id);

                    $odo = $myMeta->firstWhere('category', 'MetaData::Odometer');
                    $item->odometer = $odo ? $odo->remarks : 'N/A';

                    $fuel = $myMeta->firstWhere('category', 'MetaData::FuelLevel');
                    $item->fuel_level = $fuel ? $fuel->remarks : 'N/A';

                    $cond = $myMeta->firstWhere('category', 'MetaData::Condition');
                    $item->condition = $cond ? $cond->remarks : 'N/A';

                    $rem = $myMeta->firstWhere('category', 'MetaData::Remarks');
                    $item->remarks = $rem ? $rem->remarks : '';
                    
                    $engRem = $myMeta->firstWhere('category', 'MetaData::EngineRemarks');
                    $item->engine_remarks = $engRem ? $engRem->remarks : '';
                }
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Error fetching checklists: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error while fetching checklists.', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission('checklist.add') && !in_array(strtolower($user->role), ['developer', 'driver'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'shift_id' => 'nullable|exists:shifts,id',
            'trip_id' => 'nullable|exists:trips,id', 
            'vehicle_unit' => 'required|string',
            'type' => 'required|string',
            'odometer' => 'required|numeric',
            'fuel_level' => 'required|numeric',
            'fuel_image' => 'nullable|string',
            'tire_psi_front_left' => 'nullable|numeric',
            'tire_psi_front_right' => 'nullable|numeric',
            'tire_psi_rear_left' => 'nullable|numeric',
            'tire_psi_rear_right' => 'nullable|numeric',
            'condition' => 'required|string',
            'driver_condition' => 'nullable|string', 
            'signature' => 'required|string',
            'remarks' => 'nullable|string',
            'engine_remarks' => 'nullable|string', 
            'engineCabin' => 'nullable|array',
            'lights' => 'nullable|array',
            'equipment' => 'nullable|array',
            'pins' => 'nullable|array',
            'damage_findings_right' => 'nullable|string',
            'damage_findings_left' => 'nullable|string',
            'damage_findings_front' => 'nullable|string',
            'damage_findings_rear' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $vehicle = DB::table('vehicles')->where('unit_id', $validated['vehicle_unit'])->first();
            if (!$vehicle) {
                DB::rollBack();
                return response()->json(['message' => 'Vehicle not found'], 404);
            }

            $typeEnum = strtoupper(str_replace(['-', ' '], '_', $validated['type']));
            if (!in_array($typeEnum, ['PRE_TRIP', 'POST_TRIP', 'ROUTINE_MAINTENANCE_CHECK'])) {
                $typeEnum = 'PRE_TRIP';
            }

            $trip = null;
            if (!empty($validated['shift_id'])) {
                $trip = DB::table('trips')
                    ->where('shift_id', $validated['shift_id'])
                    ->where('current_phase', '<', 8) 
                    ->orderBy('id', 'desc')
                    ->first();
            }

            $tripId = $validated['trip_id'] ?? null;

            if (!$tripId && $trip) {
                $tripId = $trip->id;
            } 

            if ($tripId) {
                $alreadySubmitted = DB::table('checklists')
                    ->where('trip_id', $tripId)
                    ->where('type', $typeEnum)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($alreadySubmitted) {
                    DB::rollBack();
                    return response()->json(['message' => 'A ' . str_replace('_', '-', $typeEnum) . ' checklist has already been submitted for this trip.'], 422);
                }
            } elseif (!$tripId && $typeEnum === 'PRE_TRIP') {
                if (!empty($validated['shift_id'])) {
                    $trip = \App\Models\Trip::create([
                        'shift_id' => $validated['shift_id'],
                        'current_phase' => 0,
                        'is_cleared_by_dispatch' => 0,
                    ]);
                    $tripId = $trip->id;
                }
            } elseif (!$tripId) {
                if (!empty($validated['shift_id'])) {
                    DB::rollBack();
                    return response()->json(['message' => 'No active trip sequence found for this shift.'], 404);
                }
            }

            $checklist = \App\Models\Checklist::create([
                'vehicle_id' => $vehicle->id,
                'trip_id' => $tripId,
                'type' => $typeEnum,
                'status' => 'PENDING',
                'condition' => $validated['condition'],
                'driver_condition' => $validated['driver_condition'] ?? null,
                'signature' => $validated['signature'],
                'dispatcher_id' => $request->user()->id,
                'submitted_at' => now(),
            ]);$checklistId = $checklist->id;

            $metaData = [
                ['checklist_id' => $checklistId, 'category' => 'MetaData::Odometer', 'is_passed' => 1, 'remarks' => $validated['odometer']],
                ['checklist_id' => $checklistId, 'category' => 'MetaData::FuelLevel', 'is_passed' => 1, 'remarks' => $validated['fuel_level']],
                ['checklist_id' => $checklistId, 'category' => 'MetaData::Condition', 'is_passed' => 1, 'remarks' => $validated['condition']],
                ['checklist_id' => $checklistId, 'category' => 'MetaData::Remarks', 'is_passed' => 1, 'remarks' => $validated['remarks'] ?? 'None'],
                ['checklist_id' => $checklistId, 'category' => 'MetaData::EngineRemarks', 'is_passed' => 1, 'remarks' => $validated['engine_remarks'] ?? 'None'],
                
                ['checklist_id' => $checklistId, 'category' => 'Tire::FrontLeft', 'is_passed' => 1, 'remarks' => $validated['tire_psi_front_left'] ?? 32],
                ['checklist_id' => $checklistId, 'category' => 'Tire::FrontRight', 'is_passed' => 1, 'remarks' => $validated['tire_psi_front_right'] ?? 32],
                ['checklist_id' => $checklistId, 'category' => 'Tire::RearLeft', 'is_passed' => 1, 'remarks' => $validated['tire_psi_rear_left'] ?? 32],
                ['checklist_id' => $checklistId, 'category' => 'Tire::RearRight', 'is_passed' => 1, 'remarks' => $validated['tire_psi_rear_right'] ?? 32],
                
                ['checklist_id' => $checklistId, 'category' => 'Damage::FindingsRight', 'is_passed' => 1, 'remarks' => $validated['damage_findings_right'] ?? ''],
                ['checklist_id' => $checklistId, 'category' => 'Damage::FindingsLeft', 'is_passed' => 1, 'remarks' => $validated['damage_findings_left'] ?? ''],
                ['checklist_id' => $checklistId, 'category' => 'Damage::FindingsFront', 'is_passed' => 1, 'remarks' => $validated['damage_findings_front'] ?? ''],
                ['checklist_id' => $checklistId, 'category' => 'Damage::FindingsRear', 'is_passed' => 1, 'remarks' => $validated['damage_findings_rear'] ?? ''],
            ];

            if (!empty($validated['fuel_image'])) {
                $metaData[] = [
                    'checklist_id' => $checklistId, 
                    'category' => 'MetaData::FuelImage', 
                    'is_passed' => 1, 
                    'remarks' => $validated['fuel_image']
                ];
            }

            if (!empty($validated['driver_condition'])) {
                $metaData[] = [
                    'checklist_id' => $checklistId, 
                    'category' => 'MetaData::DriverCondition', 
                    'is_passed' => $validated['driver_condition'] === 'Fit to Drive' ? 1 : 0, 
                    'remarks' => $validated['driver_condition']
                ];
            }

            $checkboxGroups = ['engineCabin' => 'Engine::', 'lights' => 'Light::', 'equipment' => 'Equip::'];
            foreach ($checkboxGroups as $groupKey => $prefix) {
                if (!empty($validated[$groupKey]) && is_array($validated[$groupKey])) {
                    foreach ($validated[$groupKey] as $itemKey => $isPassed) {
                        $metaData[] = [
                            'checklist_id' => $checklistId,
                            'category' => $prefix . $itemKey,
                            'is_passed' => (bool)$isPassed ? 1 : 0,
                            'remarks' => null
                        ];
                    }
                }
            }

            foreach ($metaData as $item) {
                \App\Models\ChecklistItem::create($item);
            }
            \App\Models\DamagePin::where('vehicle_id', $vehicle->id)->update(['status' => 'Resolved']);

            if (!empty($validated['pins']) && is_array($validated['pins'])) {
                $pinsToInsert = [];
                foreach (['right', 'left', 'front', 'rear'] as $view) {
                    if (isset($validated['pins'][$view]) && is_array($validated['pins'][$view])) {
                        $findingsText = $validated['damage_findings_'.$view] ?? '';
                        $lines = explode("\n", $findingsText);
                        
                        foreach ($validated['pins'][$view] as $idx => $pin) {
                            $pinNum = $idx + 1;
                            $pinRemark = "Damage reported."; 
                            
                            foreach($lines as $line) {
                                if (strpos($line, "[Pin {$pinNum}]:") === 0) {
                                    $pinRemark = trim(substr($line, strlen("[Pin {$pinNum}]:")));
                                    break;
                                }
                            }
                            
                            $pinsToInsert[] = [
                                'vehicle_id' => $vehicle->id,
                                'checklist_id' => $checklistId,
                                'vehicle_view' => $view,
                                'x_coordinate' => round($pin['x'], 2), 
                                'y_coordinate' => round($pin['y'], 2),
                                'remarks' => $pinRemark,
                                'status' => 'Active',
                            ];
                        }
                    }
                }
                if (!empty($pinsToInsert)) {
                    foreach ($pinsToInsert as $pinData) {
                        \App\Models\DamagePin::create($pinData);
                    }
                }
            }

            $isUnfitDriver = $typeEnum === 'PRE_TRIP' && ($validated['driver_condition'] ?? '') === 'Fatigued';
            $isBrokenVehicle = $validated['condition'] === 'Needs Maintenance';

            $newVehicleStatus = $vehicle->status;
            if ($isBrokenVehicle) {
                $newVehicleStatus = 'BREAKDOWN';
            } elseif ($isUnfitDriver) {
                $newVehicleStatus = 'READY';
            }

            \App\Models\Vehicle::where('id', $vehicle->id)->update([
                'odometer' => $validated['odometer'],
                'fuel_level' => $validated['fuel_level'],
                'status' => $newVehicleStatus,
            ]);

            if ($typeEnum === 'PRE_TRIP' && ($isBrokenVehicle || $isUnfitDriver)) {
                if (!empty($validated['shift_id'])) {
                    \App\Models\Shift::where('id', $validated['shift_id'])->update(['status' => 'CANCELLED']);
                }
                \App\Models\Checklist::where('id', $checklistId)->update(['status' => $isBrokenVehicle ? 'REJECTED' : 'CANCELLED']);
                
                DB::commit();
                return response()->json(['message' => 'Checklist logged. Shift cancelled.', 'checklist_id' => $checklistId, 'cancelled' => true], 201);
            }

            if ($typeEnum === 'PRE_TRIP' && $tripId) {
                \App\Models\Trip::where('id', $tripId)->update(['current_phase' => 1]);
            }

            DB::commit();
            activity()->causedBy($request->user())->performedOn(\App\Models\Checklist::find($checklistId))->log('Checklist Submitted');

            // ==========================================
            // CRITICAL FIX: SAFE BROADCASTING
            // Catches websocket errors so they don't break the return response
            // ==========================================
            try {
                if (!empty($validated['shift_id'])) {
                    $shift = DB::table('shifts')
                        ->leftJoin('users', 'shifts.driver_id', '=', 'users.id')
                        ->where('shifts.id', $validated['shift_id'])
                        ->select('shifts.driver_id', 'shifts.scheduled_start', DB::raw("CONCAT(users.first_name, ' ', users.last_name) as driver_name"))
                        ->first();
                }

                event(new ChecklistSubmitted(
                    $checklistId,
                    $shift->driver_name ?? null,
                    $validated['vehicle_unit'],
                    $typeEnum,
                    $shift->scheduled_start ?? null,
                    $shift->driver_id ?? null
                ));
            } catch (\Exception $broadcastError) {
                Log::warning('Broadcast failed but checklist was saved: ' . $broadcastError->getMessage());
            }

            return response()->json(['message' => 'Checklist submitted successfully.', 'checklist_id' => $checklistId], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing checklist: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to submit checklist', 'error' => $e->getMessage()], 500);
        }
    }
    public function show($id)
    {
        try {
            $checklist = DB::table('checklists')
                ->whereNull('checklists.deleted_at')
                ->leftJoin('trips', 'checklists.trip_id', '=', 'trips.id')
                ->leftJoin('shifts', 'trips.shift_id', '=', 'shifts.id')
                ->leftJoin('vehicles', function($join) {
                    $join->on('shifts.vehicle_id', '=', 'vehicles.id')
                         ->orOn('checklists.vehicle_id', '=', 'vehicles.id');
                })
                ->leftJoin('users as drivers', 'shifts.driver_id', '=', 'drivers.id')
                ->select(
                    'checklists.*',
                    'vehicles.unit_id as vehicle_unit',
                    'vehicles.plate_number as vehicle_plate',
                    'vehicles.vehicle_type',
                    'shifts.driver_id',
                    DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name) as driver_name")
                )
                ->where('checklists.id', $id)
                ->first();

            if (!$checklist) return response()->json(['message' => 'Checklist not found'], 404);

            $user = auth()->user();
            if (strtolower($user->role) === 'driver') {
                if ((int)$checklist->driver_id !== (int)$user->id) {
                    return response()->json(['message' => 'Unauthorized access to checklist data.'], 403);
                }
            }

            $items = DB::table('checklist_items')->where('checklist_id', $id)->get();
            $pins = DB::table('damage_pins')->where('checklist_id', $id)->get();

            $groupedItems = ['engineCabin' => [], 'lights' => [], 'equipment' => [], 'meta' => []];

            foreach ($items as $item) {
                if (strpos($item->category, 'Engine::') === 0) {
                    $groupedItems['engineCabin'][str_replace('Engine::', '', $item->category)] = $item->is_passed;
                } elseif (strpos($item->category, 'Light::') === 0) {
                    $groupedItems['lights'][str_replace('Light::', '', $item->category)] = $item->is_passed;
                } elseif (strpos($item->category, 'Equip::') === 0) {
                    $groupedItems['equipment'][str_replace('Equip::', '', $item->category)] = $item->is_passed;
                } elseif (strpos($item->category, 'MetaData::') === 0) {
                    $groupedItems['meta'][str_replace('MetaData::', '', $item->category)] = $item->remarks;
                } elseif (strpos($item->category, 'Tire::') === 0) {
                    $groupedItems['meta'][str_replace('Tire::', '', $item->category)] = $item->remarks;
                } elseif (strpos($item->category, 'Damage::') === 0) {
                    $groupedItems['meta'][str_replace('Damage::', '', $item->category)] = $item->remarks;
                }
            }

            $groupedPins = ['front' => [], 'rear' => [], 'left' => [], 'right' => []];
            foreach ($pins as $pin) {
                $groupedPins[$pin->vehicle_view][] = [
                    'x' => $pin->x_coordinate,
                    'y' => $pin->y_coordinate,
                    'remarks' => $pin->remarks
                ];
            }

            return response()->json([
                'checklist' => $checklist,
                'items' => $groupedItems,
                'pins' => $groupedPins
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching checklist details: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to load checklist details'], 500);
        }
    }

    public function review(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission('checklist.edit') && !$user->hasPermission('checklist.sign_turnovers') && strtolower($user->role) !== 'developer') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'action' => 'required|in:APPROVE,REJECT',
            'signature' => 'required|string',
            'reason' => 'nullable|string',
            'vehicle_status' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $checklist = DB::table('checklists')->where('id', $id)->whereNull('deleted_at')->lockForUpdate()->first();
            if (!$checklist) {
                DB::rollBack();
                return response()->json(['message' => 'Checklist not found'], 404);
            }

            $rawType = strtoupper($checklist->type ?? '');
            $status = $validated['action'] === 'APPROVE' ? 'APPROVED' : 'REJECTED';
            $frontendStatus = !empty($validated['vehicle_status']) ? strtoupper(trim(str_replace(' ', '_', $validated['vehicle_status']))) : null;
            $dispatcherId = $request->user()->id;

            // CRITICAL FIX: Verifying database actually updated
            $affectedRows = \App\Models\Checklist::where('id', $id)->update([
                'status' => $status,
                'dispatcher_id' => $dispatcherId,
            ]);

            if ($affectedRows === 0 && $checklist->status !== $status) {
                DB::rollBack();
                return response()->json(['message' => 'Database refused to update checklist status.'], 500);
            }

            \App\Models\ChecklistItem::create([
                'checklist_id' => $id,
                'category' => 'MetaData::DispatcherReason',
                'is_passed' => $status === 'APPROVED' ? 1 : 0,
                'remarks' => $status === 'REJECTED' ? ($validated['reason'] ?? 'Rejected') : 'Approved',
            ]);
            
            \App\Models\ChecklistItem::create([
                'checklist_id' => $id,
                'category' => 'MetaData::DispatcherSignature',
                'is_passed' => 1,
                'remarks' => $validated['signature'],
            ]);

            $tripToUpdate = null;
            $shiftToUpdate = null;

            if ($checklist->trip_id) {
                 $tripToUpdate = DB::table('trips')->where('id', $checklist->trip_id)->first();
                 if ($tripToUpdate) {
                     $shiftToUpdate = DB::table('shifts')->where('id', $tripToUpdate->shift_id)->first();
                 }
            }

            $isPreTrip = strpos($rawType, 'PRE') !== false;
            $isPostTrip = strpos($rawType, 'POST') !== false;

            if ($isPreTrip) {
                if ($status === 'REJECTED' || $frontendStatus === 'BREAKDOWN') {
                     if ($shiftToUpdate) \App\Models\Shift::where('id', $shiftToUpdate->id)->update(['status' => 'CANCELLED']);
                } elseif ($status === 'APPROVED') {
                     if ($tripToUpdate) \App\Models\Trip::where('id', $tripToUpdate->id)->update(['is_cleared_by_dispatch' => 1]);
                     if ($shiftToUpdate) \App\Models\Shift::where('id', $shiftToUpdate->id)->update(['status' => 'ACTIVE']);
                }
            } 
            elseif ($isPostTrip) {
                if ($tripToUpdate) \App\Models\Trip::where('id', $tripToUpdate->id)->update(['current_phase' => 8, 'ended_at' => Carbon::now()]);
                if ($shiftToUpdate) \App\Models\Shift::where('id', $shiftToUpdate->id)->update(['status' => 'COMPLETED']);
            }

            $targetVehicleId = $shiftToUpdate->vehicle_id ?? null;

            if ($targetVehicleId) {
                $finalVehicleStatus = 'SCHEDULED'; 
                if ($frontendStatus) {
                    $finalVehicleStatus = $frontendStatus;
                } else {
                    if ($status === 'REJECTED') {
                        $finalVehicleStatus = 'BREAKDOWN'; 
                    } elseif ($status === 'APPROVED') {
                        if ($isPreTrip) {
                            $finalVehicleStatus = 'IN_USE'; 
                        } elseif ($isPostTrip) {
                            $finalVehicleStatus = 'READY'; 
                        } else {
                            $finalVehicleStatus = 'READY'; 
                        }
                    }
                }
                \App\Models\Vehicle::where('id', $targetVehicleId)->update(['status' => $finalVehicleStatus]);
            }

            DB::commit();
            activity()->causedBy($request->user())->performedOn(\App\Models\Checklist::find($id))->log($status === 'APPROVED' ? 'Checklist Approved' : 'Checklist Rejected');
            return response()->json(['message' => 'Checklist reviewed successfully.']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reviewing checklist: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to review checklist', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission('checklist.delete') && strtolower($user->role) !== 'developer') {
            return response()->json(['message' => 'Unauthorized. You cannot delete checklists.'], 403);
        }

        try {
            // Support Batch Delete via Comma Separation
            if (strpos($id, ',') !== false) {
                $ids = explode(',', $id);
                \App\Models\Checklist::whereIn('id', $ids)->delete();
                return response()->json(['message' => count($ids) . ' checklists deleted successfully.']);
            }

            $checklist = DB::table('checklists')->where('id', $id)->whereNull('deleted_at')->first();
            if (!$checklist) return response()->json(['message' => 'Checklist not found'], 404);

            \App\Models\Checklist::where('id', $id)->delete();
            return response()->json(['message' => 'Checklist deleted successfully.']);

        } catch (\Exception $e) {
            Log::error('Error deleting checklist: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete checklist', 'error' => $e->getMessage()], 500);
        }
    }
}