<?php

namespace Modules\PublicAssessmentPro\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;

class PublicAssessmentProSetting extends BaseModel
{
   	protected $table = 'public_assessment_pro_settings';

	protected $guarded = ['id'];

	const MODULE_NAME = 'publicassessmentpro';

	public static function addModuleSetting($company)
	{
		// create admin, employee and client module settings
		$roles = ['admin', 'employee'];
		ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
	}
	public function getPackage()
	{
		return $this->belongsTo(Package::class, 'package_id');
	}

}
