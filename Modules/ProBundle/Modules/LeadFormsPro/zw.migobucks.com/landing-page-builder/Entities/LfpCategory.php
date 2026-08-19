<?php

namespace Modules\LeadFormsPro\Entities;

use App\Models\BaseModel;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;

class LfpCategory extends BaseModel
{
	protected $table = 'lfp_categories';

	protected $guarded = ['id'];

	/**
	 * The attributes that are mass assignable.
	 */
	protected $fillable = [];

	protected static function boot()
	{
		parent::boot();
		static::addGlobalScope(new ModuleCompanyScope());
	}

	public function leadForms()
	{
		return $this->hasMany(LfpLeadForm::class, 'category_id', 'id');
	}

	public function scopeUserPermission($query, $permission, $userId)
	{
		$userIdColumn = Schema::hasColumn('lfp_lead_forms', 'added_by') ? 'added_by' : 'user_id';

		if ($permission === 'owned') {
			$query->where($userIdColumn, $userId);
		} else if ($permission === 'none') {
			$query->selectRaw('')->whereRaw('1 = 0');
		}
		return $query;
	}

}
