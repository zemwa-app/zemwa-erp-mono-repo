<?php

namespace Modules\TrainingPro\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Modules\TrainingPro\Console\ActivateModuleCommand;

class TrainingProServiceProvider extends ServiceProvider
{
	/**
	 * Boot the application events.
	 */
	public function boot()
	{
		$this->registerTranslations();
		$this->registerConfig();
		$this->registerViews();
		$this->loadMigrationsFrom(module_path('TrainingPro', 'Database/Migrations'));
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
			module_path('TrainingPro', 'Config/config.php') => config_path('trainingpro.php'),
		], 'config');
		$this->mergeConfigFrom(
			module_path('TrainingPro', 'Config/config.php'), 'trainingpro'
		);

		$this->mergeConfigFrom(
			module_path('TrainingPro', 'Config/xss_ignore.php'),
			'trainingpro::xss_ignore'
		);
	}

	/**
	 * Register views.
	 *
	 * @return void
	 */
	public function registerViews()
	{
		$viewPath = resource_path('views/modules/trainingpro');

		$sourcePath = module_path('TrainingPro', 'Resources/views');

		$this->publishes([
			$sourcePath => $viewPath,
		], 'views');

		$this->loadViewsFrom([$sourcePath], 'trainingpro');
	}

	/**
	 * Register translations.
	 *
	 * @return void
	 */
	public function registerTranslations()
	{
		$langPath = resource_path('lang/modules/trainingpro');

		if (is_dir($langPath)) {
			$this->loadTranslationsFrom($langPath, 'trainingpro');

		} else {
			$this->loadTranslationsFrom(module_path('TrainingPro', 'Resources/lang'), 'trainingpro');
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
			app(Factory::class)->load(module_path('TrainingPro', 'Database/factories'));
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
