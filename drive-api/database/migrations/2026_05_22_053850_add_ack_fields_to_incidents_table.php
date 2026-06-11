<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->timestamp('acknowledged_at')->nullable()->after('status');
            $table->longText('dispatcher_signature')->nullable()->after('acknowledged_at');
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn(['acknowledged_at', 'dispatcher_signature']);
        });
    }
};