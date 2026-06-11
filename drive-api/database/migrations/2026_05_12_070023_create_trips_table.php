<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            
            // CONSOLIDATED: Uses shift_id instead of driver/vehicle to support the State Machine
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            
            // State Machine for Driver Console
            $table->tinyInteger('current_phase')->default(0); 
            $table->boolean('is_cleared_by_dispatch')->default(false);
            
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->string('final_signature_path')->nullable(); // Saved Canvas Image
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};