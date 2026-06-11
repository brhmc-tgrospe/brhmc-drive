<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damage_pins', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            // CRITICAL FIX: Links to our new checklists table instead of the deleted inspections table
            $table->foreignId('checklist_id')->constrained('checklists')->cascadeOnDelete(); 
            
            $table->enum('vehicle_view', ['front', 'rear', 'left', 'right']);
            
            // Decimal allows us to save exact percentages (e.g., x: 45.12, y: 88.50)
            $table->decimal('x_coordinate', 8, 2);
            $table->decimal('y_coordinate', 8, 2);
            
            $table->text('remarks')->nullable(); // The specific finding for this exact pin
            $table->enum('status', ['Active', 'Resolved'])->default('Active');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_pins');
    }
};