<?php

namespace Modules\PublicAssessmentPro\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;


class PublicAssessmentProAssessment extends BaseModel
{

	protected $table = 'public_assessment_pro_assessments';

	protected $guarded = ['id'];

	const MODULE_NAME = 'publicassessmentpro';

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
    public function questions()
    {
        return $this->hasMany(PublicAssessmentProQuestion::class, 'assessment_id', 'id');
    }
	public function pAssessments()
    {
        return $this->hasMany(PublicAssessment::class, 'assessment_id', 'id');
    }
	public function product()
    {
        return $this->hasOne(Product::class, 'product_id', 'id');
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
