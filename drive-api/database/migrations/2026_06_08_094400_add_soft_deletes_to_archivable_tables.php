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
        $tables = [
            'checklists',
            'checklist_items',
            'checklist_templates',
            'emergencies',
            'incidents',
            'inspections',
            'inspection_results',
            'ambulance_inspections',
            'telemetry_logs',
            'trips',
            'trip_logs',
            'trip_phases',
            'users',
            'vehicles',
            'vehicle_shifts',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $tableBlueprint) {
                    $tableBlueprint->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'checklists',
            'checklist_items',
            'checklist_templates',
            'emergencies',
            'incidents',
            'inspections',
            'inspection_results',
            'ambulance_inspections',
            'telemetry_logs',
            'trips',
            'trip_logs',
            'trip_phases',
            'users',
            'vehicles',
            'vehicle_shifts',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                if ($table !== 'users') {
                    Schema::table($table, function (Blueprint $tableBlueprint) {
                        $tableBlueprint->dropSoftDeletes();
                    });
                }
            }
        }
    }
};
