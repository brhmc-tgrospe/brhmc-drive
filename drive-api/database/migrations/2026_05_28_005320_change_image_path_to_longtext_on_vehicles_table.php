<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use a raw DB statement to bypass Doctrine requirements
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE vehicles MODIFY image_path LONGTEXT');
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE vehicles MODIFY image_path VARCHAR(255)');
        }
    }
};