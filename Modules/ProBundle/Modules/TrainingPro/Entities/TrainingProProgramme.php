<?php

namespace Modules\TrainingPro\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Support\Facades\Schema;

class TrainingProProgramme extends BaseModel
{
	protected $table = 'training_pro_programmes';

	protected $guarded = ['id'];

	const MODULE_NAME = 'trainingpro';

	protected static function boot()
	{
		parent::boot();

		if (!user()->is_superadmin) {
			static::addGlobalScope(new ModuleCompanyScope());
		}
	}

	public function assessment()
	{
		return $this->hasOne(TrainingProAssessment::class, 'programme_id', 'id');
	}

	public function assessments()
	{
		return $this->hasMany(TrainingProAssessment::class, 'programme_id', 'id');
	}

	public function topics()
	{
		return $this->hasMany(TrainingProTopic::class, 'programme_id');
	}

	public function category()
	{
		return $this->belongsTo(TrainingProCategory::class, 'category_id', 'id');
	}

	public function assignees()
	{
		return $this->belongsTo(TrainingProAssignee::class, 'category_id');
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
