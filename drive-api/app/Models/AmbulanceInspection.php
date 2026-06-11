<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class AmbulanceInspection extends Model
{
    use SoftDeletes;
    use HasFactory, LogsActivity;

    protected $fillable = [
        // Relational Links
        'vehicle_id', 
        'dispatcher_id', 
        'outgoing_driver_id', 
        'incoming_driver_id', 
        
        // Context
        'inspection_type', 
        'odometer',
        
        // Engine/Cabin
        'battery_ok', 'oil_ok', 'water_coolant_ok', 'brakes_ok', 'siren_ok', 'horn_ok', 
        'power_locks_ok', 'windshield_wipers_ok', 'windshield_washer_ok', 'front_ac_ok', 
        'rear_ac_ok', 'exhaust_vent_ok',
        
        // Lights
        'light_dashboard_ok', 'light_front_interior_ok', 'light_rear_interior_ok', 
        'light_headlights_high_ok', 'light_headlights_low_ok', 'light_foglamps_ok', 
        'light_park_ok', 'light_tail_ok', 'light_signal_front_ok', 'light_signal_rear_ok', 
        'light_hazard_ok', 'light_reverse_ok', 'light_brake_ok', 'light_overhead_warning_ok', 
        'light_front_scene_ok', 'light_rear_scene_ok',
        
        // Equipment & Medical
        'eq_vhf_radio_ok', 'eq_mech_vent_ok', 'eq_mech_vent_battery_ok', 'eq_cardiac_monitor_ok', 
        'eq_cardiac_monitor_battery_ok', 'eq_aed_ok', 'eq_aed_battery_ok', 'eq_suction_machine_ok', 
        'eq_ecg_machine_ok', 'eq_ac_inverter_ok', 'eq_o2_regulator_ok', 'eq_o2_pressure_gauge_reading',
        'eq_splints_adult_ok', 'eq_splints_child_ok', 'eq_scoop_stretcher_ok', 'eq_spine_board_ok', 
        'eq_traction_splint_ok', 'eq_kendricks_extrication_ok', 'eq_cpr_machine_ok',
        
        // Tires, Fuel & Cleanliness
        'tire_front_left_ok', 'tire_front_right_ok', 'tire_rear_left_ok', 'tire_rear_right_ok',
        'tire_psi_front_left', 'tire_psi_front_right', 'tire_psi_rear_left', 'tire_psi_rear_right',
        'fuel_level', 'driver_condition_ok', 'seats_condition_ok', 'dashboard_condition_ok', 
        'driver_compartment_clean', 'patient_compartment_clean',
        
        // Findings & Final
        'damage_findings_right', 'damage_findings_left', 'damage_findings_front', 'damage_findings_rear',
        'remarks', 'is_fit_for_use'
    ];

    // Tell Laravel to handle the JSON columns automatically
    protected $casts = [
        'damage_findings_right' => 'array',
        'damage_findings_left' => 'array',
        'damage_findings_front' => 'array',
        'damage_findings_rear' => 'array',
        'is_fit_for_use' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['inspection_type', 'is_fit_for_use', 'fuel_level', 'driver_condition_ok'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    // Relationships
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function dispatcher()
    {
        return $this->belongsTo(User::class, 'dispatcher_id');
    }

    public function outgoingDriver()
    {
        return $this->belongsTo(User::class, 'outgoing_driver_id');
    }

    public function incomingDriver()
    {
        return $this->belongsTo(User::class, 'incoming_driver_id');
    }
}