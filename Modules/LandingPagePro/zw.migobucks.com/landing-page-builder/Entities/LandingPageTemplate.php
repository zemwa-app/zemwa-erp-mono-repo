<?php

namespace Modules\LandingPagePro\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class LandingPageTemplate extends BaseModel
{
	protected $table = 'landing_page_templates';

	protected $guarded = ['id'];

	const MODULE_NAME = 'landingpagepro';

	/**
	 * Boot method to register the model's event listeners.
	 */
	protected static function boot()
	{
		parent::boot();

		static::deleting(function ($model) {
			$model->deleteFile();
		});
	}

	/**
	 * Delete the associated file.
	 */
	public function deleteFile()
	{
		$imagePath = $this->thumbnail;
		if ($imagePath && Storage::disk('public')->exists($imagePath)) {
			Storage::disk('public')->delete($imagePath);
		}
	}

	public function landingPage()
	{
		return $this->hasOne(LandingPage::class);
	}

}
