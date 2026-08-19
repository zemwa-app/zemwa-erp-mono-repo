<?php

namespace Modules\TrainingPro\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Support\Facades\Schema;

class TrainingProAssessment extends BaseModel
{
	protected $table = 'training_pro_assessments';

	protected $guarded = ['id'];

	const MODULE_NAME = 'trainingpro';

	protected static function boot()
	{
		parent::boot();

		if (!user()->is_superadmin) {
			static::addGlobalScope(new ModuleCompanyScope());
		}

		static::deleting(function ($assessment) {
			$assessment->questions()->delete();
		});
	}

	public function results()
	{
		return $this->hasMany(TrainingProResult::class, 'assessment_id');
	}

	public function logs()
	{
		return $this->hasMany(TrainingProAssessmentLog::class, 'assessment_id');
	}

	public function programmes()
	{
		return $this->belongsTo(TrainingProProgramme::class, 'programme_id');
	}

	public function questions()
	{
		return $this->hasMany(TrainingProQuestion::class, 'assessment_id', 'id');
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
