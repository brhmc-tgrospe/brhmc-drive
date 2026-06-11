<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            // Change the column to LONGTEXT to accommodate Base64 image strings safely
            $table->longText('remarks')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->string('remarks', 255)->nullable()->change();
        });
    }
};