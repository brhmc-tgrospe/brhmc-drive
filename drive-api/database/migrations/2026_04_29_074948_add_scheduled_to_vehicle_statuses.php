<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Modify the ENUM column to include 'SCHEDULED'
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE vehicles MODIFY COLUMN status ENUM('READY', 'SCHEDULED', 'IN_USE', 'MAINTENANCE', 'BREAKDOWN') DEFAULT 'READY'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Rollback to the original statuses
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE vehicles MODIFY COLUMN status ENUM('READY', 'IN_USE', 'MAINTENANCE', 'BREAKDOWN') DEFAULT 'READY'");
        }
    }
};