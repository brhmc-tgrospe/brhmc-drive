<?php

use Illuminate\Support\Facades\Broadcast;

// BULLETPROOF FIX: Force Laravel to use Sanctum for WebSocket Auth
// By default, Laravel uses standard web sessions for broadcasting. We must override this.
Broadcast::routes(['middleware' => ['auth:sanctum']]);

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Dispatcher Live Map Channel
Broadcast::channel('dispatch.fleet', function ($user) {
    // Only users with map tracker permissions can view the live map
    return $user->hasPermission('dashboard.live_map_tracker');
});

// Emergency Alerts Channel
Broadcast::channel('dispatch.alerts', function ($user) {
    // Only users with incident view permissions receive the red emergency bell notifications
    return $user->hasPermission('incident.view');
});

// Per-Driver Private Channel
Broadcast::channel('driver.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Laravel Notification Channel (for VehicleGrounded/VehicleRestored broadcast notifications)
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});