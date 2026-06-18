<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // Changed from 'email' to 'login'
            'password' => 'required|string',
        ]);

        // Find the user by either username OR email
        // @phpstan-ignore-next-line
        $user = User::where('email', $request->login)
                    ->orWhere('username', $request->login)
                    ->first();

        // Check if user exists and password is correct
        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid login credentials provided.'
            ], 401);
        }

        // Generate the Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Log the login event
        activity('authentication')
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->event('login')
            ->log("{$user->first_name} {$user->last_name} logged in");

        // Load roles using Spatie (don't load permissions because we use legacy_permissions)
        $user->load('roles');

        $perms = $user->legacy_permissions;
        if (is_string($perms)) $perms = json_decode($perms, true);
        if (!is_array($perms)) $perms = [];

        return response()->json([
            'token' => $token,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role, // legacy if frontend still uses it
                'roles' => $user->getRoleNames(),
                'permissions' => $perms,
            ]
        ]);
    }

/**
     * Log the user out (Revoke the active token).
     */
/**
     * Log the user out (Revoke the active token).
     */
    public function logout(\Illuminate\Http\Request $request)
    {
        // Tell Intelephense exactly what type of object this is so it stops complaining
        $user = $request->user();

        // Log the logout event before revoking token
        activity('authentication')
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->event('logout')
            ->log("{$user->first_name} {$user->last_name} logged out");

        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $user->currentAccessToken();
        
        // Delete the token
        $token->delete();

        return response()->json([
            'message' => 'User logged out successfully. Token has been revoked.'
        ]);
    }

    /**
     * Log the user out due to inactivity timeout.
     */
    public function logoutTimeout(\Illuminate\Http\Request $request)
    {
        $user = $request->user();

        // Log the timeout event
        activity('authentication')
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->event('logout')
            ->log("{$user->first_name} {$user->last_name} session timed out due to inactivity");

        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $user->currentAccessToken();
        
        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'User logged out due to inactivity.'
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        $perms = $user->legacy_permissions;
        if (is_string($perms)) $perms = json_decode($perms, true);
        if (!is_array($perms)) $perms = [];

        return response()->json([
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'roles' => $user->getRoleNames(),
                'permissions' => $perms,
            ],
            'role' => $user->roles->first()->name ?? 'None'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'contact_number' => 'nullable|string|max:20',
        ]);

        $user->forceFill($validated)->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
                'errors' => ['current_password' => ['Current password is incorrect.']]
            ], 422);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }
}