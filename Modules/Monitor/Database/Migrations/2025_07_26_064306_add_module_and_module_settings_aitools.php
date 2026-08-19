<?php

use App\Models\Company;
use App\Models\Module;
use Illuminate\Database\Migrations\Migration;
use Modules\Monitor\Entities\MonitorSetting;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Module::firstOrCreate(['module_name' => MonitorSetting::MODULE_NAME]);

        // Packages are seeded before module:migrate, so add monitor here.
        MonitorSetting::addModuleToPackages();

        Company::chunk(50, function ($companies) {
            foreach ($companies as $company) {
                MonitorSetting::addModuleSetting($company);
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
