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
    // Only Developers, Admins, and Dispatchers can view the live map
    return in_array($user->role, ['developer', 'admin', 'dispatcher']);
});

// Emergency Alerts Channel
Broadcast::channel('dispatch.alerts', function ($user) {
    // Only Developers, Admins, and Dispatchers receive the red emergency bell notifications
    return in_array($user->role, ['developer', 'admin', 'dispatcher']);
});

// Per-Driver Private Channel
Broadcast::channel('driver.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});