<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add the new billing_type column first
        Schema::table('server_domains', function (Blueprint $table) {
            $table->integer('billing_cycle_count')->nullable()->after('billing_cycle');
            $table->enum('billing_type', ['day', 'week', 'month', 'year'])->nullable()->after('billing_cycle_count');
        });

        // Step 2: Update billing_cycle and billing_type values based on current data
        DB::table('server_domains')->where('billing_cycle', 'monthly')->update([
            'billing_cycle_count' => 1,
            'billing_type' => 'month'
        ]);

        DB::table('server_domains')->where('billing_cycle', 'quarterly')->update([
            'billing_cycle_count' => 3,
            'billing_type' => 'month'
        ]);

        DB::table('server_domains')->where('billing_cycle', 'semi-annually')->update([
            'billing_cycle_count' => 6,
            'billing_type' => 'month'
        ]);

        DB::table('server_domains')->where('billing_cycle', 'annually')->update([
            'billing_cycle_count' => 1,
            'billing_type' => 'year'
        ]);

        DB::table('server_domains')->where('billing_cycle', 'biennially')->update([
            'billing_cycle_count' => 2,
            'billing_type' => 'year'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('server_domains', function (Blueprint $table) {
            $table->dropColumn('billing_cycle_count');
            $table->dropColumn('billing_type');
        });
    }
};
