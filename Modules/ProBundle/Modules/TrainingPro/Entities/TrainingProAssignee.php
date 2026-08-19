<?php

namespace Modules\TrainingPro\Entities;

use App\Models\BaseModel;
use App\Models\Designation;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Models\Team;
use App\Models\User;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Support\Facades\Schema;

class TrainingProAssignee extends BaseModel
{
	protected $table = 'training_pro_assignees';

	protected $guarded = ['id'];

	const MODULE_NAME = 'trainingpro';

	protected static function boot()
	{
		parent::boot();

		if (!user()->is_superadmin) {
			static::addGlobalScope(new ModuleCompanyScope());
		}
	}

	public function category()
	{
		return $this->hasOne(TrainingProCategory::class, 'id', 'category_id');
	}

	public function programme()
	{
		return $this->hasOne(TrainingProProgramme::class, 'id', 'programme_id');
	}

	public function programmes()
	{
		return $this->hasMany(TrainingProProgramme::class, 'id', 'programme_id');
	}

	public function department()
	{
		return $this->belongsTo(Team::class, 'department_id', 'id');
	}

	public function designation()
	{
		return $this->belongsTo(Designation::class, 'designation_id', 'id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public static function isAssignedUser(int $id): bool
	{
		return (new static())->withoutGlobalScope(ModuleCompanyScope::class)
			->where(function ($subquery) use ($id) {
				$subquery->where('user_id', user()->employeeDetail->user_id)
					->orWhere('designation_id', user()->employeeDetail->designation_id)
					->orWhere('department_id', user()->employeeDetail->department_id);
			})
			->where('programme_id', $id)
			->exists();
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
