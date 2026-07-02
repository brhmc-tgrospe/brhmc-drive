<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();
        if ($user && $user->role !== 'developer' && !$user->hasPermission('user.view') && !$user->hasPermission('schedule.add') && !$user->hasPermission('schedule.edit') && !$user->hasPermission('schedule.view')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = \App\Models\User::query();

        // Hide Developer accounts if the current user is NOT a Developer
        if (strtolower($user->role) !== 'developer') {
            $query->whereRaw('LOWER(role) != ?', ['developer']);
        }

        // Real-time Searching
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('id', 'like', "%$s%")
                  ->orWhere('first_name', 'like', "%$s%")
                  ->orWhere('last_name', 'like', "%$s%")
                  ->orWhere('username', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('contact_number', 'like', "%$s%")
                  ->orWhere('role', 'like', "%$s%")
                  ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%$s%");
            });
        }

        // Clickable Sorting
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'desc');

        $allowedSorts = ['first_name', 'username', 'role', 'id'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        // Return full list for dropdowns with dynamic Driver Availability
        if ($request->boolean('all')) {
            $users = $query->get();
            
            foreach ($users as $user) {
                $isDriverRole = strtolower($user->role) === 'driver';
                $hasExecuteShifts = false;
                
                if (!empty($user->legacy_permissions)) {
                    $perms = is_string($user->legacy_permissions) ? json_decode($user->legacy_permissions, true) : $user->legacy_permissions;
                    if (is_array($perms) && in_array('execute_shifts', $perms)) {
                        $hasExecuteShifts = true;
                    }
                }

                if ($isDriverRole || $hasExecuteShifts) {

                    // 1. Check if the driver is actively driving right now
                    $isActive = DB::table('shifts')
                        ->where('driver_id', $user->id)
                        ->where('status', 'ACTIVE')
                        ->exists();

                    if ($isActive) {
                        $user->driver_status = 'IN_USE';
                    } else {
                        // 2. CRITICAL FIX: Check for PENDING and SCHEDULED shifts
                        $isScheduled = DB::table('shifts')
                            ->where('driver_id', $user->id)
                            ->whereIn('status', ['PENDING', 'SCHEDULED'])
                            ->exists();
                        
                        $user->driver_status = $isScheduled ? 'SCHEDULED' : 'READY';
                    }
                }
            }
            return response()->json($users);
        }

        $perPage = $request->input('per_page', 10);
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        if ($user->role !== 'developer' && !$user->hasPermission('user.add')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        if ($request->has('permissions') && is_string($request->permissions)) {
            $request->merge(['permissions' => json_decode($request->permissions, true)]);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'nullable|string|max:20',
            'role' => 'required|string|max:100',
            'password' => 'required|string|min:6',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string'
        ]);

        // SECURITY LOOPHOLE FIX: Block "Developer" role creation at the API level
        if (strtolower(trim($validated['role'])) === 'developer' && strtolower($user->role) !== 'developer') {
            return response()->json(['message' => 'Action Denied: You cannot create an account with the Developer role.'], 403);
        }

        $validated['password'] = Hash::make($validated['password']);
        
        // Convert permissions array to JSON to ensure it saves correctly
        $validated['legacy_permissions'] = json_encode($request->input('permissions', []));
        unset($validated['permissions']);

        // Use forceFill to bypass $fillable protections on the User model
        $newUser = new User();
        $newUser->forceFill($validated)->save();

        return response()->json(['message' => 'User created', 'user' => $newUser]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        if ($user && $user->role !== 'developer' && !$user->hasPermission('user.view')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json(User::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = $request->user();
        
        if ($currentUser->role !== 'developer' && !$currentUser->hasPermission('user.edit')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $targetUser = User::findOrFail($id);

        if ($targetUser->role === 'developer' && $currentUser->role !== 'developer') {
             return response()->json(['message' => 'Cannot edit a developer account.'], 403);
        }
        if ($request->has('permissions') && is_string($request->permissions)) {
            $request->merge(['permissions' => json_decode($request->permissions, true)]);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username,'.$id,
            'email' => 'required|email|unique:users,email,'.$id,
            'contact_number' => 'nullable|string|max:20',
            'role' => 'required|string|max:100',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string'
        ]);

        // SECURITY LOOPHOLE FIX: Prevent escalating an existing account to Developer status
        if (strtolower(trim($validated['role'])) === 'developer' && strtolower($targetUser->role) !== 'developer' && strtolower($currentUser->role) !== 'developer') {
            return response()->json(['message' => 'Action Denied: Cannot escalate role to Developer.'], 403);
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $validated['password'] = Hash::make($request->password);
        }

        // Convert permissions array to JSON to ensure it saves correctly
        $validated['legacy_permissions'] = json_encode($request->input('permissions', []));
        unset($validated['permissions']);

        // Use forceFill to bypass $fillable protections on the User model
        $targetUser->forceFill($validated)->save();

        return response()->json(['message' => 'User updated', 'user' => $targetUser]);
    }

    public function destroy(Request $request, $id)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = $request->user();
        
        if ($currentUser->role !== 'developer' && !$currentUser->hasPermission('user.delete')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $targetUser = User::findOrFail($id);

        if ($currentUser->id === $targetUser->id) {
            return response()->json(['message' => 'Cannot delete your own account.'], 400);
        }
        if ($targetUser->role === 'developer' && $currentUser->role !== 'developer') {
            return response()->json(['message' => 'Cannot delete a developer account.'], 403);
        }

        $targetUser->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function impersonate(Request $request, int $id)
    {
        /** @var \App\Models\User $developer */
        $developer = $request->user();
        
        if ($developer->role !== 'developer') {
            return response()->json(['message' => 'Nice try.'], 403);
        }

        $targetUser = User::findOrFail($id);
        $token = $targetUser->createToken('impostor_token')->plainTextToken;

        $perms = $targetUser->legacy_permissions;
        if (is_string($perms)) $perms = json_decode($perms, true);
        if (!is_array($perms)) $perms = [];

        // Auto-grant driver view permissions when execute_shifts is active
        if (in_array('execute_shifts', $perms)) {
            foreach (['checklist.view', 'trip.view', 'incident.view'] as $driverPerm) {
                if (!in_array($driverPerm, $perms)) {
                    $perms[] = $driverPerm;
                }
            }
        }

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $targetUser->id,
                'first_name' => $targetUser->first_name,
                'last_name' => $targetUser->last_name,
                'email' => $targetUser->email,
                'username' => $targetUser->username,
                'role' => $targetUser->role,
                'permissions' => $perms,
            ]
        ]);
    }
}