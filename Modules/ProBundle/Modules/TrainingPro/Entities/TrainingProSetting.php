<?php

namespace Modules\TrainingPro\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\ModuleCompanyScope;

class TrainingProSetting extends BaseModel
{
	protected $table = 'training_pro_settings';

	protected $guarded = ['id'];

	const MODULE_NAME = 'trainingpro';

	protected static function boot()
	{
		parent::boot();
		static::addGlobalScope(new ModuleCompanyScope());
	}

	public static function addModuleSetting($company)
	{
		// create admin, employee and client module settings
		$roles = ['admin', 'employee', 'client'];
		ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
	}

	public function scopeUserPermission($query, $permission, $userId)
	{
		if ($permission === 'owned') {
			$query->where('user_id', $userId);
		} else if ($permission === 'none') {
			// No results for 'none' permission - return empty array
			$query->selectRaw('')->whereRaw('1 = 0');
		}
		return $query;
	}
}
