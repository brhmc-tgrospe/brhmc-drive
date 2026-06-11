<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class User extends Authenticatable
{
    use SoftDeletes;
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;

    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'username',
        'contact_number',
        'password',
        'role',
        'is_active',
        'driver_status', // Required for scheduling dropdowns
        'legacy_permissions',   // Required to save custom access arrays
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'legacy_permissions' => 'array', // Forces Laravel to try and cast JSON to array
        ];
    }

    // ==========================================
    // CRITICAL FIX: BULLETPROOF SECURITY CHECK
    // Safely decodes stringified JSON to prevent in_array() crashes
    // ==========================================
    public function hasPermission(string $permission): bool
    {
        // "God Mode": Developers can do literally everything
        if (strtolower($this->role) === 'developer') {
            return true;
        }

        $userPermissions = $this->legacy_permissions;

        // Fail-safe: If the database returns a JSON string, decode it into an array
        if (is_string($userPermissions)) {
            $userPermissions = json_decode($userPermissions, true);
        }

        // If it's null or still not an array, default to empty to prevent crashes
        if (!is_array($userPermissions)) {
            $userPermissions = [];
        }

        return in_array($permission, $userPermissions);
    }

    // ==========================================
    // ACTIVITY LOG CONFIGURATION
    // ==========================================
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'role', 'is_active', 'driver_status']) 
            ->logOnlyDirty() 
            ->dontLogEmptyChanges();
    }

    // ==========================================
    // SYSTEM RELATIONSHIPS (Updated for DRIVE System)
    // ==========================================
    
    /**
     * Shifts assigned to this user (Driver).
     */
    public function shifts()
    {
        return $this->hasMany(Shift::class, 'driver_id');
    }

    /**
     * Checklists reviewed and approved by this user (Dispatcher).
     */
    public function reviewedChecklists()
    {
        return $this->hasMany(Checklist::class, 'dispatcher_id');
    }

    /**
     * Mid-Shift Emergencies reported by this user.
     */
    public function incidentsReported()
    {
        return $this->hasMany(Incident::class, 'reporter_id');
    }

    /**
     * Mid-Shift Emergencies acknowledged by this user (Dispatcher).
     */
    public function incidentsAcknowledged()
    {
        return $this->hasMany(Incident::class, 'dispatcher_id');
    }
}