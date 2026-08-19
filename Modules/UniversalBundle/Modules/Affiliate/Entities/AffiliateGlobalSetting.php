<?php

namespace Modules\Affiliate\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;

class AffiliateGlobalSetting extends BaseModel
{

    protected $guarded = ['id'];

    const MODULE_NAME = 'affiliate';

    public static function addModuleSetting($company)
    {
        $roles = ['employee', 'admin'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }
}
