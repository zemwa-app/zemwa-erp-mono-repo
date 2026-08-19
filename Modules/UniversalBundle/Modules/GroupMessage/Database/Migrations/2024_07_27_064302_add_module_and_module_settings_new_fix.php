<?php

use App\Models\Company;
use App\Models\Module;
use Illuminate\Database\Migrations\Migration;
use Modules\GroupMessage\Entities\GroupMessageGlobalSetting;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Module::firstOrCreate(['module_name' => GroupMessageGlobalSetting::MODULE_NAME]);

        $companies = Company::withoutGlobalScopes()->get();

        foreach ($companies as $company) {
            GroupMessageGlobalSetting::addModuleSetting($company);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
