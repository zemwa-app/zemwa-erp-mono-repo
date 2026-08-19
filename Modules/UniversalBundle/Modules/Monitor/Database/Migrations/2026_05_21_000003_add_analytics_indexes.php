<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agent_activity_windows')) {
            Schema::table('agent_activity_windows', function (Blueprint $table) {
                $table->index(['user_id', 'is_idle', 'window_start'], 'aaw_user_idle_start_idx');
            });
        }

        if (Schema::hasTable('agent_activity_logs')) {
            Schema::table('agent_activity_logs', function (Blueprint $table) {
                $table->index(['user_id', 'started_at'], 'aal_user_started_idx');
                $table->index(['user_id', 'category', 'started_at'], 'aal_user_category_started_idx');
            });
        }

        if (Schema::hasTable('project_time_logs')) {
            Schema::table('project_time_logs', function (Blueprint $table) {
                $table->index(['user_id', 'start_time'], 'ptl_user_start_idx');
                $table->index(['project_id', 'start_time'], 'ptl_project_start_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('agent_activity_windows')) {
            Schema::table('agent_activity_windows', function (Blueprint $table) {
                $table->dropIndex('aaw_user_idle_start_idx');
            });
        }

        if (Schema::hasTable('agent_activity_logs')) {
            Schema::table('agent_activity_logs', function (Blueprint $table) {
                $table->dropIndex('aal_user_started_idx');
                $table->dropIndex('aal_user_category_started_idx');
            });
        }

        if (Schema::hasTable('project_time_logs')) {
            Schema::table('project_time_logs', function (Blueprint $table) {
                $table->dropIndex('ptl_user_start_idx');
                $table->dropIndex('ptl_project_start_idx');
            });
        }
    }
};
