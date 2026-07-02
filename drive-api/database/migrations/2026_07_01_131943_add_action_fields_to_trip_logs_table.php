<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_logs', function (Blueprint $table) {
            $table->string('action_label')->nullable()->after('phase');
            $table->string('destination')->nullable()->after('action_label');
        });
    }

    public function down(): void
    {
        Schema::table('trip_logs', function (Blueprint $table) {
            $table->dropColumn(['action_label', 'destination']);
        });
    }
};
