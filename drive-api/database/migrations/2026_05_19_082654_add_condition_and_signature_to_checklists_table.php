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
        Schema::table('checklists', function (Blueprint $table) {
            // Adds the condition fields and digital signature payload
            // We use 'condition' as per ChecklistController Line 145 requirements
            if (!Schema::hasColumn('checklists', 'condition')) {
                $table->string('condition')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('checklists', 'driver_condition')) {
                $table->string('driver_condition')->nullable()->after('condition');
            }
            
            if (!Schema::hasColumn('checklists', 'signature')) {
                $table->longText('signature')->nullable()->after('driver_condition');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            $table->dropColumn(['condition', 'driver_condition', 'signature']);
        });
    }
};