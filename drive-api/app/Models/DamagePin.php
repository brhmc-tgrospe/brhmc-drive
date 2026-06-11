<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamagePin extends Model
{
    protected $fillable = [
        'vehicle_id',
        'checklist_id',
        'vehicle_view',
        'x_coordinate',
        'y_coordinate',
        'remarks',
        'status',
    ];
}
