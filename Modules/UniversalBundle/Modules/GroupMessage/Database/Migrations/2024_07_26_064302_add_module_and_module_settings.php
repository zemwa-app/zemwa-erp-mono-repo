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

        // Packages are seeded before module:migrate, so add groupmessage here.
        GroupMessageGlobalSetting::addModuleToPackages();

        // Must include package_id so createRoleSettingEntry can resolve the company package.
        // Selecting only `id` leaves package_id null, package relation fails, and settings are never created.
        Company::select(['id', 'package_id'])->chunk(50, function ($companies) {
            foreach ($companies as $company) {
                GroupMessageGlobalSetting::addModuleSetting($company);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
