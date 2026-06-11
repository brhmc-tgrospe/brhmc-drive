<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Incident extends Model
{
    use SoftDeletes;
    use LogsActivity;

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'reporter_id',
        'vehicle_id',
        'shift_id',
        'incident_target',
        'issue_type',
        'remarks',
        'evidence_image',
        'status',
        'dispatcher_id',
        'acknowledged_at',
        'dispatcher_signature',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'datetime',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * The driver who reported the incident.
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * The dispatcher who acknowledged the incident.
     */
    public function dispatcher()
    {
        return $this->belongsTo(User::class, 'dispatcher_id');
    }

    /**
     * The vehicle involved in the incident.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * The shift during which the incident occurred.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

}