<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('employee_details', 'monitoring_enabled')) {
            Schema::table('employee_details', function (Blueprint $table) {
                $table->boolean('monitoring_enabled')->default(true)->after('employment_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employee_details', 'monitoring_enabled')) {
            Schema::table('employee_details', function (Blueprint $table) {
                $table->dropColumn('monitoring_enabled');
            });
        }
    }
};
