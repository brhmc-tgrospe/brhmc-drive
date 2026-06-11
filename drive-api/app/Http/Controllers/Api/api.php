<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Public Auth Route
Route::post('/login', [AuthController::class, 'login']);

// Protected Application Routes
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // ==========================================
    // USER MANAGEMENT ROUTES
    // ==========================================
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users/{id}/impersonate', [UserController::class, 'impersonate']);
    
    // ==========================================
    // FLEET & VEHICLE ROUTES
    // ==========================================
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::get('/vehicles/{id}', [VehicleController::class, 'show']);
    Route::put('/vehicles/{id}', [VehicleController::class, 'update']);
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy']);
    Route::post('/vehicles/{id}/image', [VehicleController::class, 'uploadImage']);
    
    // ==========================================
    // SHIFT & SCHEDULING ROUTES
    // ==========================================
    Route::get('/shifts/my-shifts', [VehicleShiftController::class, 'myShifts']);
    Route::post('/shifts/{id}/start', [VehicleShiftController::class, 'startShift']);
    Route::apiResource('shifts', VehicleShiftController::class);

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
    // INCIDENT REPORTING ROUTES
    // ==========================================
    Route::post('/incidents', [IncidentController::class, 'store']);
    Route::get('/emergencies/report', [IncidentController::class, 'index']);
    Route::put('/emergencies/report/{id}', [IncidentController::class, 'update']);
    Route::post('/emergencies/report/{id}/acknowledge', [IncidentController::class, 'acknowledge']);
    Route::post('/emergencies/report/{id}/resolve', [IncidentController::class, 'resolve']);
    Route::delete('/emergencies/report/{id}', [IncidentController::class, 'destroy']);

    // TRIP LOGS ROUTES
    Route::get('/trips/logs', [TripLogController::class, 'index']);
    Route::get('/trips/logs/{id}', [TripLogController::class, 'show']);
    Route::delete('/trips/logs/{id}', [TripLogController::class, 'destroy']);
});