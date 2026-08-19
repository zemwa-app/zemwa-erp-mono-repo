<?php

namespace Modules\PublicAssessmentPro\Console;

use App\Models\Company;
use Illuminate\Console\Command;
use Modules\PublicAssessmentPro\Entities\PublicAssessmentProSetting;

class ActivateModuleCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $name = 'publicassessmentpro:activate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add all the module settings of PublicAssessmentPro module';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$companies = Company::all();

		foreach ($companies as $company) {
			PublicAssessmentProSetting::addModuleSetting($company);
		}
	}
}
