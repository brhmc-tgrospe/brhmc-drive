<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->enum('type', ['EMERGENCY', 'REGULAR'])->default('EMERGENCY')->after('shift_id');
            $table->string('current_destination')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['type', 'current_destination']);
        });
    }
};
