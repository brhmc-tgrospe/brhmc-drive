<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::create('vehicle_shifts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
        $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('dispatcher_id')->constrained('users')->onDelete('cascade'); // Who created the shift
        
        // The Scheduling Rules
        $table->dateTime('start_time'); // Set by Dispatcher
        $table->integer('shift_duration'); // MUST be 8 or 12
        $table->dateTime('end_time'); // Automatically calculated by the backend
        
        $table->enum('status', ['SCHEDULED', 'ACTIVE', 'COMPLETED', 'MISSED'])->default('SCHEDULED');
        $table->timestamps();
    });
}
};
