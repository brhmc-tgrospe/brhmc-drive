<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Checklist extends Model
{
    use SoftDeletes;
    use LogsActivity;

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'trip_id',
        'type',
        'status',
        'condition',
        'driver_condition',
        'signature',
        'dispatcher_id',
        'submitted_at',
    ];

    /**
     * Get the trip associated with the checklist.
     */
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
    
    /**
     * Get the items/metadata associated with the checklist.
     */
    public function items()
    {
        return $this->hasMany(ChecklistItem::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

}