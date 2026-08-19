<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->text('allowed_work_from')->nullable()->after('auto_clock_in_location');
        });

        // Default: all three types allowed for existing companies
        DB::table('attendance_settings')->whereNull('allowed_work_from')->update([
            'allowed_work_from' => json_encode(['office', 'home', 'other']),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn('allowed_work_from');
        });
    }
};
