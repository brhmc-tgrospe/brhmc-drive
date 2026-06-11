<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add Live GPS tracking to Vehicles
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('current_lat', 10, 7)->nullable()->after('status');
            $table->decimal('current_lng', 11, 7)->nullable()->after('current_lat');
            $table->timestamp('last_telemetry_at')->nullable()->after('current_lng');
        });

        // 2. Add Exact Coordinates to Incident Reports
        Schema::table('incidents', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('evidence_image');
            $table->decimal('longitude', 11, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['current_lat', 'current_lng', 'last_telemetry_at']);
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};