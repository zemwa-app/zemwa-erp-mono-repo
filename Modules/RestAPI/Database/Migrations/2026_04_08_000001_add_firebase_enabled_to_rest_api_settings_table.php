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
        Schema::table('rest_api_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('rest_api_settings', 'firebase_enabled')) {
                $table->boolean('firebase_enabled')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rest_api_settings', function (Blueprint $table) {
            if (Schema::hasColumn('rest_api_settings', 'firebase_enabled')) {
                $table->dropColumn('firebase_enabled');
            }
        });
    }

};
