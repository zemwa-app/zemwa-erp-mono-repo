<?php

namespace Modules\LandingPagePro\Console;

use App\Models\Company;
use Illuminate\Console\Command;
use Modules\LandingPagePro\Entities\LandingPageProSetting;

class ActivateModuleCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $name = 'landingpagepro:activate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add all the module settings of landingpagepro module';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$companies = Company::all();

		foreach ($companies as $company) {
			LandingPageProSetting::addModuleSetting($company);
		}
	}
}
