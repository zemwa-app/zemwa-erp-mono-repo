<?php

namespace Modules\GroupMessage\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\PackageSetting;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupMessageGlobalSetting extends BaseModel
{

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    const MODULE_NAME = 'groupmessage';

    protected $table = 'group_message_global_settings';

    public static function addModuleSetting($company)
    {
        $roles = ['employee', 'admin', 'client'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }

    /**
     * Ensure groupmessage is present in every package and trial package settings.
     * Packages are seeded before module:migrate, so this must run after install.
     */
    public static function addModuleToPackages(): void
    {
        Package::query()->each(function (Package $package) {
            $modules = json_decode($package->module_in_package ?? '[]', true) ?: [];

            if (in_array(self::MODULE_NAME, $modules, true)) {
                return;
            }

            $modules[] = self::MODULE_NAME;
            $package->module_in_package = json_encode(array_values($modules));
            $package->save();
        });

        PackageSetting::query()->each(function (PackageSetting $packageSetting) {
            $modules = json_decode($packageSetting->modules ?? '[]', true) ?: [];

            if (in_array(self::MODULE_NAME, $modules, true)) {
                return;
            }

            $modules[] = self::MODULE_NAME;
            $packageSetting->modules = json_encode(array_values($modules));
            $packageSetting->save();
        });
    }
}
