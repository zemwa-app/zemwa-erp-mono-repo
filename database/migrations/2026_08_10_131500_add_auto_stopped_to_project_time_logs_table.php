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
        Schema::table('project_time_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('project_time_logs', 'auto_stopped')) {
                $table->boolean('auto_stopped')->default(false)->after('end_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_time_logs', function (Blueprint $table) {
            if (Schema::hasColumn('project_time_logs', 'auto_stopped')) {
                $table->dropColumn('auto_stopped');
            }
        });
    }
};
