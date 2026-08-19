<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Monitor\Entities\MonitorSetting;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        MonitorSetting::addModuleToPackages();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
