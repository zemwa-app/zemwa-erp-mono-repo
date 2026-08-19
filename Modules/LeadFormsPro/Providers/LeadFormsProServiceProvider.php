<?php

namespace Modules\LeadFormsPro\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Modules\LeadFormsPro\Console\ActivateModuleCommand;

class LeadFormsProServiceProvider extends ServiceProvider
{
	/**
	 * Boot the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerTranslations();
		$this->registerConfig();
		$this->mergeModuleLang('permissions');
		$this->registerViews();
		$this->loadMigrationsFrom(module_path('LeadFormsPro', 'Database/Migrations'));
		$this->registerCommands();

//		$this->app->booted(function () {
//			$this->scheduleCommands();
//		});

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
			module_path('LeadFormsPro', 'Config/config.php') => config_path('leadformspro.php'),
		], 'config');
		$this->mergeConfigFrom(
			module_path('LeadFormsPro', 'Config/config.php'), 'leadformspro'
		);

		$this->mergeConfigFrom(
			module_path('leadformspro', 'Config/xss_ignore.php'),
			'leadformspro::xss_ignore'
		);
	}

	/**
	 * Register language file.
	 *
	 * @return void
	 */
	private function mergeModuleLang($key)
	{
		$rootPath = resource_path('lang/eng/' . $key . '.php');
		$modulePath = module_path('LeadFormsPro', 'Resources/Lang/eng/' . $key . '.php');

		if (file_exists($rootPath) && file_exists($modulePath)) {
			$rootTranslations = require $rootPath;
			$moduleTranslations = require $modulePath;

			// Avoid duplicate entries using array_diff_key
			$mergedTranslations = array_merge(
				$rootTranslations,
				array_diff_key($moduleTranslations, $rootTranslations)
			);

			//Lang::addLines($mergedTranslations, 'eng', $key);
		}
	}

	/**
	 * Register views.
	 *
	 * @return void
	 */
	public function registerViews()
	{
		$viewPath = resource_path('views/modules/leadformspro');

		$sourcePath = module_path('LeadFormsPro', 'Resources/views');

		$this->publishes([
			$sourcePath => $viewPath,
		], 'views');

		$this->loadViewsFrom([$sourcePath], 'leadformspro');
	}

	/**
	 * Register translations.
	 *
	 * @return void
	 */
	public function registerTranslations()
	{
		$langPath = resource_path('lang/modules/leadformspro');

		if (is_dir($langPath)) {
			$this->loadTranslationsFrom($langPath, 'leadformspro');

		} else {
			$this->loadTranslationsFrom(module_path('LeadFormsPro', 'Resources/lang'), 'leadformspro');
		}
	}

	/**
	 * Register an additional directory of factories.
	 *
	 * @return void
	 */
	public function registerFactories()
	{
		if (!app()->environment('production') && $this->app->runningInConsole()) {
			app(Factory::class)->load(module_path('LeadFormsPro', 'Database/factories'));
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
