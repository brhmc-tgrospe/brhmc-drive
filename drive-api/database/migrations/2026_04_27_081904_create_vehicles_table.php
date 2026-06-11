<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('unit_id')->unique();
            $table->string('plate_number')->unique();
            $table->string('make_model');
            $table->string('vehicle_type');
            $table->string('base_location');
            $table->string('image_path')->nullable();
            
            // Retained State Data
            $table->unsignedInteger('odometer')->default(0);
            $table->unsignedInteger('fuel_level')->default(100);
            $table->unsignedInteger('tire_psi_front_left')->default(32);
            $table->unsignedInteger('tire_psi_front_right')->default(32);
            $table->unsignedInteger('tire_psi_rear_left')->default(32);
            $table->unsignedInteger('tire_psi_rear_right')->default(32);
            
            $table->enum('status', ['READY', 'SCHEDULED', 'IN_USE', 'MAINTENANCE', 'BREAKDOWN'])->default('READY');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};