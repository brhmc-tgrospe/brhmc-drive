<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Safely alter the ENUM column to accept REJECTED and CANCELLED
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE checklists MODIFY COLUMN status ENUM('PENDING', 'NEEDS_REVIEW', 'APPROVED', 'REJECTED', 'CANCELLED') DEFAULT 'PENDING'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original ENUM states
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE checklists MODIFY COLUMN status ENUM('PENDING', 'NEEDS_REVIEW', 'APPROVED') DEFAULT 'PENDING'");
        }
    }
};