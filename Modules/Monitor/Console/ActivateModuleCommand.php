<?php

namespace Modules\Monitor\Console;

use App\Models\Company;
use Illuminate\Console\Command;
use Modules\Monitor\Entities\MonitorSetting;

class ActivateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'monitor:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add all the module settings of monitor module';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        MonitorSetting::addModuleToPackages();

        $companies = Company::all();

        foreach ($companies as $company) {
            MonitorSetting::addModuleSetting($company);
        }
    }

}
