<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change the column to a standard string to prevent strict ENUM crashing
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE incidents MODIFY status VARCHAR(50) DEFAULT 'PENDING'");
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE incidents MODIFY status VARCHAR(50) DEFAULT 'PENDING'");
        }
    }
};