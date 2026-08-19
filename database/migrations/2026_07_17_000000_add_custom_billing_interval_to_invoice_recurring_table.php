<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoice_recurring MODIFY rotation ENUM('monthly', 'weekly', 'bi-weekly', 'quarterly', 'half-yearly', 'annually', 'daily', 'custom') NOT NULL");

        Schema::table('invoice_recurring', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_recurring', 'billing_interval')) {
                $table->unsignedInteger('billing_interval')->nullable()->after('rotation');
            }

            if (!Schema::hasColumn('invoice_recurring', 'billing_unit')) {
                $table->enum('billing_unit', ['days', 'weeks', 'months', 'years'])->nullable()->after('billing_interval');
            }
        });
    }

    public function down(): void
    {
        DB::table('invoice_recurring')
            ->where('rotation', 'custom')
            ->update(['rotation' => 'annually']);

        Schema::table('invoice_recurring', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_recurring', 'billing_unit')) {
                $table->dropColumn('billing_unit');
            }

            if (Schema::hasColumn('invoice_recurring', 'billing_interval')) {
                $table->dropColumn('billing_interval');
            }
        });

        DB::statement("ALTER TABLE invoice_recurring MODIFY rotation ENUM('monthly', 'weekly', 'bi-weekly', 'quarterly', 'half-yearly', 'annually', 'daily') NOT NULL");
    }
};
