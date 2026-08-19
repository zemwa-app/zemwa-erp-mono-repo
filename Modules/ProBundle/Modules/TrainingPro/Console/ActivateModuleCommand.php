<?php

namespace Modules\TrainingPro\Console;

use App\Models\Company;
use Illuminate\Console\Command;
use Modules\TrainingPro\Entities\TrainingProSetting;

class ActivateModuleCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $name = 'trainingpro:activate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add all the module settings of TrainingPro module';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$companies = Company::all();

		foreach ($companies as $company) {
			TrainingProSetting::addModuleSetting($company);
		}
	}
}
