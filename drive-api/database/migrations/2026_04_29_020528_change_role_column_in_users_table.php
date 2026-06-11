<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Converts the strict ENUM into a flexible string up to 100 characters
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY role VARCHAR(100) NOT NULL");
        }
    }

    public function down(): void
    {
        // Reverts back to ENUM if we ever rollback
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY role ENUM('developer', 'admin', 'dispatcher', 'driver') NOT NULL");
        }
    }
};
