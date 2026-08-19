<?php

namespace Modules\PublicAssessmentPro\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Modules\PublicAssessmentPro\Console\ActivateModuleCommand;

class PublicAssessmentProServiceProvider extends ServiceProvider
{
    /**
	 * Boot the application events.
	 */
	public function boot()
	{
		$this->registerTranslations();
		$this->registerConfig();
		$this->registerViews();
		$this->loadMigrationsFrom(module_path('PublicAssessmentPro', 'Database/Migrations'));
		$this->registerCommands();
	}
    /**
	 * Register the service provider.
	 */
	public function register(): void
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
			module_path('PublicAssessmentPro', 'Config/config.php') => config_path('publicassessmentpro.php'),
		], 'config');
		$this->mergeConfigFrom(
			module_path('PublicAssessmentPro', 'Config/config.php'), 'publicassessmentpro'
		);

		$this->mergeConfigFrom(
			module_path('PublicAssessmentPro', 'Config/xss_ignore.php'),
			'publicassessmentpro::xss_ignore'
		);
	}

	/**
	 * Register views.
	 *
	 * @return void
	 */
	public function registerViews()
	{
		$viewPath = resource_path('views/modules/publicassessmentpro');

		$sourcePath = module_path('PublicAssessmentPro', 'Resources/views');

		$this->publishes([
			$sourcePath => $viewPath,
		], 'views');

		$this->loadViewsFrom([$sourcePath], 'publicassessmentpro');
	}

	/**
	 * Register translations.
	 *
	 * @return void
	 */
	public function registerTranslations()
	{
		$langPath = resource_path('lang/modules/publicassessmentpro');

		if (is_dir($langPath)) {
			$this->loadTranslationsFrom($langPath, 'publicassessmentpro');

		} else {
			$this->loadTranslationsFrom(module_path('PublicAssessmentPro', 'Resources/lang'), 'publicassessmentpro');
		}
	}

	/**
	 * Register an additional directory of factories.
	 *
	 * @return void
	 */
	public function registerFactories()
	{
		if (! app()->environment('production') && $this->app->runningInConsole()) {
			app(Factory::class)->load(module_path('PublicAssessmentPro', 'Database/factories'));
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

	/**
	 * Register artisan commands
	 */
	private function registerCommands()
	{
		$this->commands([
			ActivateModuleCommand::class,
		]);
	}

//	public function scheduleCommands()
//	{
//		// Do not run for development and demo
//		if (app()->environment(['development', 'demo'])) {
//			return true;
//		}
//
//		$schedule = $this->app->make(Schedule::class);
//
//		$schedule->command('send-zoom-meeting-reminder')->everyMinute();
//	}
}
