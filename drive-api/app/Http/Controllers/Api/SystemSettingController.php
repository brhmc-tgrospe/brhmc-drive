<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;

class SystemSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = SystemSetting::all()->pluck('value', 'key');
        
        // Ensure defaults exist if not in DB
        if (!isset($settings['maintenance_mode'])) {
            $settings['maintenance_mode'] = false;
        } else {
            $settings['maintenance_mode'] = filter_var($settings['maintenance_mode'], FILTER_VALIDATE_BOOLEAN);
        }

        return response()->json($settings);
    }

    /**
     * Update the specified setting in storage.
     */
    public function update(Request $request)
    {
        // Only developers should be able to update settings
        if (strtolower($request->user()->role) !== 'developer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            // For boolean settings like maintenance_mode, store as "true"/"false" or 1/0
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value]
            );
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }
}
