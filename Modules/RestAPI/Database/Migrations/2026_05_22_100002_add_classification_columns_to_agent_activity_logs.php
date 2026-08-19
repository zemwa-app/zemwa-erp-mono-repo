<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_activity_logs', 'subcategory')) {
                $table->string('subcategory', 64)->nullable()->after('category');
            }
            if (!Schema::hasColumn('agent_activity_logs', 'classified_at')) {
                $table->timestamp('classified_at')->nullable()->after('subcategory');
                $table->index(['company_id', 'classified_at'], 'aal_company_classified_idx');
            }
        });

    }

    public function down(): void
    {
        Schema::table('agent_activity_logs', function (Blueprint $table) {
            if (Schema::hasColumn('agent_activity_logs', 'classified_at')) {
                $table->dropIndex('aal_company_classified_idx');
                $table->dropColumn('classified_at');
            }
            if (Schema::hasColumn('agent_activity_logs', 'subcategory')) {
                $table->dropColumn('subcategory');
            }
        });
    }
};
