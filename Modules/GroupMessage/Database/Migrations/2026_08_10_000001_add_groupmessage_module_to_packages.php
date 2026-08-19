<?php

use Illuminate\Database\Migrations\Migration;
use Modules\GroupMessage\Entities\GroupMessageGlobalSetting;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        GroupMessageGlobalSetting::addModuleToPackages();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
