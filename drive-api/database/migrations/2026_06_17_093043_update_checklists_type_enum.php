<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE checklists MODIFY COLUMN type ENUM('PRE_TRIP', 'POST_TRIP', 'ROUTINE_MAINTENANCE_CHECK') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE checklists MODIFY COLUMN type ENUM('PRE_TRIP', 'POST_TRIP') NOT NULL");
    }
};
