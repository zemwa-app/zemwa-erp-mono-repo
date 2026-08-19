<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('prorated_monitor_seats');

        Schema::table('subscription_invoices', function (Blueprint $table) {
            foreach (['monitor_prorated_seat', 'monitor_prorate_amount'] as $column) {
                if (Schema::hasColumn('subscription_invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('prorated_monitor_seats')) {
            Schema::create('prorated_monitor_seats', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->unsignedInteger('user_id');
                $table->timestamp('enabled_on');
                $table->timestamp('disabled_on')->nullable();
                $table->boolean('is_billed')->default(false);
                $table->timestamps();

                $table->index(['company_id', 'is_billed']);
                $table->index(['user_id', 'enabled_on']);
            });
        }

        Schema::table('subscription_invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_invoices', 'monitor_prorated_seat')) {
                $table->unsignedInteger('monitor_prorated_seat')->default(0)->after('monitor_amount');
            }
            if (! Schema::hasColumn('subscription_invoices', 'monitor_prorate_amount')) {
                $table->double('monitor_prorate_amount', 16, 2)->default(0)->after('monitor_prorated_seat');
            }
        });
    }
};
