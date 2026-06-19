<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ActivityLogController;

// Import all required controllers matching the exact filenames
use App\Http\Controllers\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\VehicleController;
use App\Http\Controllers\API\VehicleShiftController; 
use App\Http\Controllers\API\ChecklistController;
use App\Http\Controllers\API\TripController;
use App\Http\Controllers\API\EmergencyController;
use App\Http\Controllers\API\TelemetryController;
use App\Http\Controllers\API\IncidentController;
use App\Http\Controllers\API\TripLogController;
use App\Http\Controllers\API\SystemHealthController;

// Public Auth Route
Route::post('/login', [AuthController::class, 'login']);

// Protected Application Routes
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-timeout', [AuthController::class, 'logoutTimeout']);
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);
    
    // ==========================================
    // USER MANAGEMENT ROUTES
    // ==========================================
    // ==========================================
    // USER MANAGEMENT ROUTES
    // ==========================================
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users/{id}/impersonate', [UserController::class, 'impersonate']);
    
    // ==========================================
    // FLEET & VEHICLE ROUTES
    // ==========================================
    // ==========================================
    // FLEET & VEHICLE ROUTES
    // ==========================================
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/vehicles/{id}', [VehicleController::class, 'show']);
    
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::put('/vehicles/{id}', [VehicleController::class, 'update']);
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy']);
    Route::post('/vehicles/{id}/image', [VehicleController::class, 'uploadImage']);
    
    // ==========================================
    // SHIFT & SCHEDULING ROUTES
    // ==========================================
    Route::get('/shifts/my-shifts', [VehicleShiftController::class, 'myShifts']);
    Route::post('/shifts/{id}/start', [VehicleShiftController::class, 'startShift']);

    Route::get('/shifts', [VehicleShiftController::class, 'index']);
    Route::get('/shifts/{id}', [VehicleShiftController::class, 'show']);

    Route::post('/shifts', [VehicleShiftController::class, 'store']);
    Route::put('/shifts/{shift}', [VehicleShiftController::class, 'update']);
    Route::delete('/shifts/{shift}', [VehicleShiftController::class, 'destroy']);

    // ==========================================
    // CHECKLIST & BLOWBAGETS ROUTES
    // ==========================================
    Route::get('/checklists', [ChecklistController::class, 'index']);
    Route::post('/checklists', [ChecklistController::class, 'store']);
    Route::get('/checklists/{id}', [ChecklistController::class, 'show']);
    Route::post('/checklists/{id}/review', [ChecklistController::class, 'review']);
    // CRITICAL FIX: The missing delete route has been added here!
    Route::delete('/checklists/{id}', [ChecklistController::class, 'destroy']); 
    
    // ==========================================
    // TRIP STATE MACHINE ROUTES
    // ==========================================
    Route::post('/trips', [TripController::class, 'store']);
    Route::post('/trips/{id}/advance', [TripController::class, 'advancePhase']);
    Route::post('/trips/{id}/clear', [TripController::class, 'clearForDeparture']);

    // ==========================================
    // TELEMETRY & EMERGENCY ROUTES
    // ==========================================
    Route::post('/telemetry/ping', [TelemetryController::class, 'ping']);
    Route::post('/telemetry', [TelemetryController::class, 'store']);
    Route::get('/telemetry/locations', [TelemetryController::class, 'activeLocations']);
    Route::post('/emergencies/report', [EmergencyController::class, 'reportIssue']); 

    // ==========================================
    // INCIDENT & EMERGENCY ROUTES
    // ==========================================
    Route::get('/emergencies/report', [IncidentController::class, 'index']);
    Route::post('/emergencies/report', [IncidentController::class, 'store']); // CRITICAL FIX: Points to the new IncidentController!
    Route::put('/emergencies/report/{id}', [IncidentController::class, 'update']);
    Route::post('/emergencies/report/{id}/acknowledge', [IncidentController::class, 'acknowledge']);
    Route::post('/emergencies/report/{id}/resolve', [IncidentController::class, 'resolve']);
    Route::delete('/emergencies/report/{id}', [IncidentController::class, 'destroy']);

    // TRIP LOGS ROUTES
    Route::get('/trips/logs', [TripLogController::class, 'index']);
    Route::get('/trips/logs/{id}', [TripLogController::class, 'show']);
    Route::delete('/trips/logs/{id}', [TripLogController::class, 'destroy']);

    // SYSTEM HEALTH ROUTES
    Route::get('/system-health', [SystemHealthController::class, 'getMetrics']);
    Route::post('/system-health/clear-log', [SystemHealthController::class, 'clearErrorLog']);
    Route::post('/system-health/backup', [SystemHealthController::class, 'triggerBackup']);
    Route::post('/system-health/backup-schedule', [SystemHealthController::class, 'updateSchedule']);

    // ==========================================
    // ARCHIVE ROUTES
    // ==========================================
    Route::get('/archive/{type}', [\App\Http\Controllers\ArchiveController::class, 'index']);
    Route::post('/archive/{type}/{id}/restore', [\App\Http\Controllers\ArchiveController::class, 'restore']);
    Route::delete('/archive/{type}/{id}/force', [\App\Http\Controllers\ArchiveController::class, 'forceDelete']);

    // ==========================================
    // NOTIFICATION ROUTES
    // ==========================================
    Route::get('/notifications', function (Request $request) {
        return $request->user()->notifications()->latest()->take(50)->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'type' => $n->data['type'] ?? 'info',
                'message' => $n->data['message'] ?? '',
                'vehicle_unit' => $n->data['vehicle_unit'] ?? null,
                'expiry_type' => $n->data['expiry_type'] ?? null,
                'read' => !is_null($n->read_at),
                'timestamp' => $n->created_at->toISOString(),
            ];
        });
    });
    Route::post('/notifications/mark-read', function (Request $request) {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read.']);
    });
    Route::delete('/notifications', function (Request $request) {
        $request->user()->notifications()->delete();
        return response()->json(['message' => 'All notifications cleared.']);
    });
});

