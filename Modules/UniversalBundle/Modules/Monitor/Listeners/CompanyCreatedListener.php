<?php

namespace Modules\Monitor\Listeners;

use Modules\Monitor\Entities\MonitorSetting;

class CompanyCreatedListener
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $company = $event->company;
        MonitorSetting::addModuleSetting($company);
    }
}
