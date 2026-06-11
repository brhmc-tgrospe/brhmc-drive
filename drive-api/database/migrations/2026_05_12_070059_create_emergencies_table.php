<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            
            $table->text('description');
            $table->enum('status', ['UNRESOLVED', 'RESOLVED'])->default('UNRESOLVED');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergencies');
    }
};