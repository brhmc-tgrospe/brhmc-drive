<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SystemSetting;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maintenanceSetting = SystemSetting::where('key', 'maintenance_mode')->first();
        $isMaintenanceMode = $maintenanceSetting ? filter_var($maintenanceSetting->value, FILTER_VALIDATE_BOOLEAN) : false;

        if ($isMaintenanceMode) {
            $user = $request->user();
            // If user is not authenticated or not a developer, block access.
            if (!$user || strtolower($user->role) !== 'developer') {
                return response()->json([
                    'message' => 'System is currently undergoing maintenance. Please try again later.',
                    'code' => 'MAINTENANCE_MODE'
                ], 503);
            }
        }

        return $next($request);
    }
}
