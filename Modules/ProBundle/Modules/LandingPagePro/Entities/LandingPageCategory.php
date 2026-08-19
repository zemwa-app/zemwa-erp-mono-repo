<?php

namespace Modules\LandingPagePro\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\ModuleCompanyScope;
use Illuminate\Support\Facades\Schema;

class LandingPageCategory extends BaseModel
{
	protected $table = 'landing_page_categories';

	protected $guarded = ['id'];

	const MODULE_NAME = 'landingpagepro';

	protected static function boot()
	{
		parent::boot();
		static::addGlobalScope(new ModuleCompanyScope());
	}

	public function landingPage()
	{
		return $this->hasOne(LandingPage::class);
	}
}
