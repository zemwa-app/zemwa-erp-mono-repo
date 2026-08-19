<?php

namespace Modules\RestAPI\Providers;

use App\Events\LeaveEvent;
use App\Events\NewCompanyCreatedEvent;
use App\Events\NewExpenseEvent;
use App\Events\NewNoticeEvent;
use App\Events\NewProjectMemberEvent;
use App\Events\ProjectReminderEvent;
use App\Events\TaskEvent;
use App\Events\TaskReminderEvent;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Modules\RestAPI\Console\ActivateModuleCommand;
use Modules\RestAPI\Entities\PersonalAccessToken;
use Modules\RestAPI\Http\Middleware\AgentVersionMiddleware;
use Modules\RestAPI\Http\Middleware\AuthMiddleware;
use Modules\RestAPI\Http\Middleware\OptionalSanctumAuth;
use Modules\RestAPI\Listeners\CompanyCreatedListener;
use Modules\RestAPI\Listeners\ExpensePushListener;
use Modules\RestAPI\Listeners\LeavePushListener;
use Modules\RestAPI\Listeners\NewNoticePushListener;
use Modules\RestAPI\Listeners\ProjectMemberPushListener;
use Modules\RestAPI\Listeners\ProjectReminderPushListener;
use Modules\RestAPI\Listeners\TaskPushListener;
use Modules\RestAPI\Listeners\TaskReminderPushListener;
use Modules\RestAPI\Listeners\BasePushNotification;

class RestAPIServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // Set your app config.
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path('RestAPI', 'Database/Migrations'));

        $router->aliasMiddleware('api.auth', AuthMiddleware::class);
        $router->aliasMiddleware('api.auth.optional', OptionalSanctumAuth::class);
        $router->aliasMiddleware('agent.version', AgentVersionMiddleware::class);
        $this->commands([
            ActivateModuleCommand::class,
        ]);

        Event::listen(TaskReminderEvent::class, TaskReminderPushListener::class);
        Event::listen(TaskEvent::class, TaskPushListener::class);
        Event::listen(NewProjectMemberEvent::class, ProjectMemberPushListener::class);
        Event::listen(ProjectReminderEvent::class, ProjectReminderPushListener::class);
        Event::listen(NewNoticeEvent::class, NewNoticePushListener::class);
        Event::listen(NewExpenseEvent::class, ExpensePushListener::class);
        Event::listen(LeaveEvent::class, LeavePushListener::class);
        Event::listen(NewCompanyCreatedEvent::class, CompanyCreatedListener::class);

        // Conditionally register Payroll salary slip push notifications if Payroll module is enabled
        if (class_exists(\Modules\Payroll\Entities\SalarySlip::class)) {
            // Salary slip generated
            Event::listen('eloquent.created: Modules\\Payroll\\Entities\\SalarySlip', function ($salarySlip) {
                try {
                    $pusher = new BasePushNotification();
                    $role = $pusher->getUserRole($salarySlip->user);
                    $type = 'salary-slip';
                    $message = [
                        'apn' => [
                            'notification' => [
                                'title' => 'Salary Slip Generated',
                                'body' => 'Your salary slip for ' . $salarySlip->salary_from->format('M Y') . ' has been generated and is ready for review.',
                                'sound' => 'default',
                                'badge' => 1,
                                'id' => $salarySlip->id,
                                'type' => $type,
                                'role' => $role,
                            ],
                        ],
                        'fcm' => [
                            'data' => [
                                'title' => 'Salary Slip Generated',
                                'body' => 'Your salary slip for ' . $salarySlip->salary_from->format('M Y') . ' has been generated and is ready for review.',
                                'sound' => 'default',
                                'badge' => 1,
                                'id' => $salarySlip->id,
                                'type' => $type,
                                'role' => $role,
                            ],
                        ],
                    ];
                    $pusher->sendNotification($salarySlip->user, $message);
                } catch (\Throwable $e) {
                    \Log::error('Payroll Push (created) failed: ' . $e->getMessage());
                }
            });

            // Salary slip status changes: review, locked, paid (and others)
            Event::listen('eloquent.updated: Modules\\Payroll\\Entities\\SalarySlip', function ($salarySlip) {
                try {
                    if (! $salarySlip->wasChanged('status')) {
                        return;
                    }

                    $status = $salarySlip->status; // review | locked | paid | generated
                    $pusher = new BasePushNotification();
                    $role = $pusher->getUserRole($salarySlip->user);

                    $messages = [
                        'review' => [
                            'title' => 'Salary Slip Under Review',
                            'body' => 'Your salary slip has been submitted for review for month of ' . $salarySlip->salary_from->format('M Y') . '.'
                        ],
                        'locked' => [
                            'title' => 'Salary Slip Locked',
                            'body' => 'Your salary slip has been locked for month of ' . $salarySlip->salary_from->format('M Y') . '.'
                        ],
                        'paid' => [
                            'title' => 'Salary Paid',
                            'body' => 'Great news! Your salary has been paid successfully for month of ' . $salarySlip->salary_from->format('M Y') . '.'
                        ],
                        'generated' => [
                            'title' => 'Salary Slip Generated',
                            'body' => 'Your salary slip has been generated for month of ' . $salarySlip->salary_from->format('M Y') . '.'
                        ],
                    ];

                    $type = 'salary-slip';
                    $messageData = $messages[$status] ?? [
                        'title' => 'Salary Slip Updated',
                        'body' => 'Your salary slip has been updated for month of ' . $salarySlip->salary_from->format('M Y') . '.'
                    ];

                    $message = [
                        'apn' => [
                            'notification' => [
                                'title' => $messageData['title'],
                                'body' => $messageData['body'],
                                'sound' => 'default',
                                'badge' => 1,
                                'id' => $salarySlip->id,
                                'type' => $type,
                                'role' => $role,
                            ],
                        ],
                        'fcm' => [
                            'data' => [
                                'title' => $messageData['title'],
                                'body' => $messageData['body'],
                                'sound' => 'default',
                                'badge' => 1,
                                'id' => $salarySlip->id,
                                'type' => $type,
                                'role' => $role,
                            ],
                        ],
                    ];

                    $pusher->sendNotification($salarySlip->user, $message);
                } catch (\Throwable $e) {
                    \Log::error('Payroll Push (updated) failed: ' . $e->getMessage());
                }
            });
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('restapi.php'),
        ], 'config');
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'restapi');

        $this->mergeConfigFrom(
            module_path('restapi', 'Config/xss_ignore.php'),
            'restapi::xss_ignore'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/restapi');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom([$sourcePath], 'restapi');

    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/restapi');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'restapi');

        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'restapi');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
