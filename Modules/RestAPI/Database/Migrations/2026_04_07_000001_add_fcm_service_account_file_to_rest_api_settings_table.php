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
            $table->string('fcm_service_account_file')->nullable()->after('fcm_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rest_api_settings', function (Blueprint $table) {
            $table->dropColumn('fcm_service_account_file');
        });
    }

};
