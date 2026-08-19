<?php

namespace Modules\PublicAssessmentPro\Entities;

use App\Models\BaseModel;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;


class PublicAssessment extends BaseModel
{
   
	protected $table = 'public_assessments';

	protected $guarded = ['id'];

	/**
	 * The attributes that are mass assignable.
	 */
	protected $fillable = [];

	// protected static function boot()
	// {
	// 	parent::boot();
	// 	static::addGlobalScope(new ModuleCompanyScope());
	// }

	public function assessment()
	{
		return $this->belongsTo(PublicAssessmentProAssessment::class);
	}

	public function answers()
	{
		return $this->hasMany(PublicAssessmentAnswer::class);
	}

	public function scopeUserPermission($query, $permission, $userId)
	{
		$userIdColumn = Schema::hasColumn('public_assessments', 'added_by') ? 'added_by' : 'user_id';

		if ($permission === 'owned') {
			$query->where($userIdColumn, $userId);
		} else if ($permission === 'none') {
			$query->selectRaw('')->whereRaw('1 = 0');
		}
		return $query;
	}

    
}
