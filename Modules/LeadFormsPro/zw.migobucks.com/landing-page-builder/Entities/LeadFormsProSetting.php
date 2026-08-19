<?php

namespace Modules\LeadFormsPro\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\Schema;

class LeadFormsProSetting extends BaseModel
{
	protected $table = 'lead_forms_pro_settings';

	protected $guarded = ['id'];

	const MODULE_NAME = 'leadformspro';

	public static function addModuleSetting($company)
	{
		// create admin, employee and client module settings
		$roles = ['admin', 'employee', 'client'];
		ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
	}

	public function getPackage()
	{
		return $this->belongsTo(Package::class, 'package_id');
	}
}
