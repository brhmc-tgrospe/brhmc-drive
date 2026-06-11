<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telemetry_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            
            // High precision decimals for precise LiveMap tracking
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->unsignedSmallInteger('speed')->default(0); 
            
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            // Optimization: Indexing to quickly retrieve the latest points for drawing map polylines
            $table->index(['vehicle_id', 'recorded_at']);
            $table->index('trip_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telemetry_logs');
    }
};