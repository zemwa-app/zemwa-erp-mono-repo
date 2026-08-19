<?php

namespace Modules\TrainingPro\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;

class TrainingProQuestion extends BaseModel
{
	protected $table = 'training_pro_questions';

	protected $guarded = ['id'];

	const MODULE_NAME = 'trainingpro';

	protected static function boot()
	{
		parent::boot();

		if (!user()->is_superadmin) {
			static::addGlobalScope(new ModuleCompanyScope());
		}

		static::deleting(function ($question) {
			$question->answers()->delete();
		});
	}

	public function assessment()
	{
		return $this->belongsTo(TrainingProAssessment::class, 'assessment_id');
	}

	public function answers()
	{
		return $this->hasMany(TrainingProAnswer::class, 'question_id', 'id');
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
