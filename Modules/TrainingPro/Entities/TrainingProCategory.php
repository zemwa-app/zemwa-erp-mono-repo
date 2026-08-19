<?php

namespace Modules\TrainingPro\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Support\Facades\Schema;

class TrainingProCategory extends BaseModel
{
	protected $table = 'training_pro_categories';

	protected $guarded = ['id'];

	const MODULE_NAME = 'trainingpro';

	protected static function boot()
	{
		parent::boot();

		if (!user()->is_superadmin) {
			static::addGlobalScope(new ModuleCompanyScope());
		}
	}

	public function programmes()
	{
		return $this->hasMany(TrainingProProgramme::class, 'category_id');
	}

	public function assignees()
	{
		return $this->belongsTo(TrainingProAssignee::class, 'category_id');
	}

	public static function allCategories()
	{
		if (user()->permission('view_category') == 'all' || user()->permission('view_category') == 'none') {
			return TrainingProCategory::all();
		}

		return TrainingProCategory::where('added_by', auth()->user()->id)->get();
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
