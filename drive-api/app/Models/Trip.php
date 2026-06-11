<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Trip extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = ['shift_id', 'current_phase', 'is_cleared_by_dispatch', 'started_at', 'ended_at'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

}