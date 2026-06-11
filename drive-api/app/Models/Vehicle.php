<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Vehicle extends Model
{
    use SoftDeletes;
    use HasFactory, LogsActivity;

    protected $fillable = [
        'unit_id',
        'plate_number',
        'vehicle_type',
        'make_model',
        'status',
        'odometer',
        'base_location',
        'assigned_driver_id',
        'image_path'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    public function inspections()
    {
        return $this->hasMany(AmbulanceInspection::class)->orderBy('created_at', 'desc');
    }
}
