<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ambulance_inspections', function (Blueprint $table) {
            $table->id();
            
            // Relational Links - Dual Driver & Dispatcher Sign-offs
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dispatcher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('outgoing_driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('incoming_driver_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Context
            $table->enum('inspection_type', ['PRE_TRIP', 'POST_TRIP', 'ROUTINE']);
            $table->integer('odometer');
            
            // ENGINE & CABIN (Booleans)
            $table->boolean('battery_ok')->default(true);
            $table->boolean('oil_ok')->default(true);
            $table->boolean('water_coolant_ok')->default(true);
            $table->boolean('brakes_ok')->default(true);
            $table->boolean('siren_ok')->default(true);
            $table->boolean('horn_ok')->default(true);
            $table->boolean('power_locks_ok')->default(true);
            $table->boolean('windshield_wipers_ok')->default(true);
            $table->boolean('windshield_washer_ok')->default(true);
            $table->boolean('front_ac_ok')->default(true);
            $table->boolean('rear_ac_ok')->default(true);
            $table->boolean('exhaust_vent_ok')->default(true);

            // DETAILED LIGHTS CHECKLIST (Booleans)
            $table->boolean('light_dashboard_ok')->default(true);
            $table->boolean('light_front_interior_ok')->default(true);
            $table->boolean('light_rear_interior_ok')->default(true);
            $table->boolean('light_headlights_high_ok')->default(true);
            $table->boolean('light_headlights_low_ok')->default(true);
            $table->boolean('light_foglamps_ok')->default(true);
            $table->boolean('light_park_ok')->default(true);
            $table->boolean('light_tail_ok')->default(true);
            $table->boolean('light_signal_front_ok')->default(true);
            $table->boolean('light_signal_rear_ok')->default(true);
            $table->boolean('light_hazard_ok')->default(true);
            $table->boolean('light_reverse_ok')->default(true);
            $table->boolean('light_brake_ok')->default(true);
            $table->boolean('light_overhead_warning_ok')->default(true);
            $table->boolean('light_front_scene_ok')->default(true);
            $table->boolean('light_rear_scene_ok')->default(true);

            // RESPONSE EQUIPMENT CHECKLIST
            $table->boolean('eq_vhf_radio_ok')->default(true);
            $table->boolean('eq_mech_vent_ok')->default(true);
            $table->boolean('eq_mech_vent_battery_ok')->default(true);
            $table->boolean('eq_cardiac_monitor_ok')->default(true);
            $table->boolean('eq_cardiac_monitor_battery_ok')->default(true);
            $table->boolean('eq_aed_ok')->default(true);
            $table->boolean('eq_aed_battery_ok')->default(true);
            $table->boolean('eq_suction_machine_ok')->default(true);
            $table->boolean('eq_ecg_machine_ok')->default(true);
            $table->boolean('eq_ac_inverter_ok')->default(true);
            $table->boolean('eq_o2_regulator_ok')->default(true);
            $table->boolean('eq_o2_pressure_gauge_reading')->default(true);
            
            // Immobilization & Rescue Equipment
            $table->boolean('eq_splints_adult_ok')->default(true); 
            $table->boolean('eq_splints_child_ok')->default(true);
            $table->boolean('eq_scoop_stretcher_ok')->default(true);
            $table->boolean('eq_spine_board_ok')->default(true);
            $table->boolean('eq_traction_splint_ok')->default(true);
            $table->boolean('eq_kendricks_extrication_ok')->default(true);
            $table->boolean('eq_cpr_machine_ok')->default(true);

            // TIRES & FUEL
            $table->boolean('tire_front_left_ok')->default(true);
            $table->boolean('tire_front_right_ok')->default(true);
            $table->boolean('tire_rear_left_ok')->default(true);
            $table->boolean('tire_rear_right_ok')->default(true);
            $table->integer('tire_psi_front_left')->nullable();
            $table->integer('tire_psi_front_right')->nullable();
            $table->integer('tire_psi_rear_left')->nullable();
            $table->integer('tire_psi_rear_right')->nullable();
            $table->integer('fuel_level')->default(100);
            
            // CONDITIONS, CLEANLINESS & FINDINGS
            $table->boolean('driver_condition_ok')->default(true);
            $table->boolean('seats_condition_ok')->default(true);
            $table->boolean('dashboard_condition_ok')->default(true);
            $table->boolean('driver_compartment_clean')->default(true);
            $table->boolean('patient_compartment_clean')->default(true);
            
            // Damage Pin Arrays (JSON) mapped to Legends
            $table->json('damage_findings_right')->nullable();
            $table->json('damage_findings_left')->nullable();
            $table->json('damage_findings_front')->nullable();
            $table->json('damage_findings_rear')->nullable();
            
            $table->text('remarks')->nullable();
            $table->boolean('is_fit_for_use');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ambulance_inspections');
    }
};