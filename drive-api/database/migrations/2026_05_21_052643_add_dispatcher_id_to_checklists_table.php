<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            // Adds the dispatcher_id to track who reviewed the checklist
            $table->unsignedBigInteger('dispatcher_id')->nullable()->after('status');
            
            // Standard indexing for faster joins
            $table->index('dispatcher_id');
        });
    }

    public function down(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            $table->dropColumn('dispatcher_id');
        });
    }
};