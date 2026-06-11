<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            
            $table->dateTime('scheduled_start');
            $table->dateTime('scheduled_end');
            $table->enum('status', ['PENDING', 'ACTIVE', 'COMPLETED', 'CANCELLED'])->default('PENDING');
            
            $table->timestamps();
            
            // Critical indexing for schedule fetching performance
            $table->index(['driver_id', 'scheduled_start']);
            $table->index(['vehicle_id', 'scheduled_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};