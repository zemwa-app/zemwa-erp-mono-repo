<?php

namespace Modules\Monitor\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Monitor\Listeners\CompanyCreatedListener;
use App\Events\NewCompanyCreatedEvent;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
    ];
}
