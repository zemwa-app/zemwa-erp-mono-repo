<?php

namespace Modules\LandingPagePro\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Support\Facades\Schema;
use Modules\LandingPagePro\Scopes\UserPermissionScope;

class LandingPage extends BaseModel
{

	protected $table = 'landing_pages';

	protected $guarded = ['id'];

	const MODULE_NAME = 'landingpagepro';

	protected $enumStatus = [
		0 => 'Deactive',
		1 => 'Active',
		2 => 'Draft',
		3 => 'Newly Created'
	];

	public function getStatusTextAttribute()
	{
		return $this->enumStatus[$this->attributes['status']] ?? 'Unknown';
	}

	protected static function boot()
	{
		parent::boot();
		static::addGlobalScope(new ModuleCompanyScope());
	}

	public function landingPageCategory()
	{
		return $this->belongsTo(LandingPageCategory::class, 'category_id');
	}

	public function landingPageTemplate()
	{
		return $this->belongsTo(LandingPageTemplate::class, 'template_id');
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
