<?php

namespace Modules\LandingPagePro\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;
use Modules\LandingPagePro\Console\ActivateModuleCommand;

class LandingPageProServiceProvider extends ServiceProvider
{
	/**
	 * Boot the application events.
	 */
	public function boot()
	{
		$this->registerTranslations();
		$this->registerConfig();
		$this->mergeModuleLang('permissions');
		$this->registerViews();
		$this->loadMigrationsFrom(module_path('LandingPagePro', 'Database/Migrations'));
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
			module_path('LandingPagePro', 'Config/config.php') => config_path('landingpagepro.php'),
		], 'config');
		$this->mergeConfigFrom(
			module_path('LandingPagePro', 'Config/config.php'), 'landingpagepro'
		);

		$this->mergeConfigFrom(
			module_path('landingpagepro', 'Config/xss_ignore.php'),
			'landingpagepro::xss_ignore'
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
		$modulePath = module_path('LandingPagePro', 'Resources/Lang/eng/' . $key . '.php');

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
		$viewPath = resource_path('views/modules/landingpagepro');

		$sourcePath = module_path('LandingPagePro', 'Resources/views');

		$this->publishes([
			$sourcePath => $viewPath,
		], 'views');

		$this->loadViewsFrom([$sourcePath], 'landingpagepro');
	}

	/**
	 * Register translations.
	 *
	 * @return void
	 */
	public function registerTranslations()
	{
		$langPath = resource_path('lang/modules/landingpagepro');

		if (is_dir($langPath)) {
			$this->loadTranslationsFrom($langPath, 'landingpagepro');

		} else {
			$this->loadTranslationsFrom(module_path('LandingPagePro', 'Resources/lang'), 'landingpagepro');
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
			app(Factory::class)->load(module_path('LandingPagePro', 'Database/factories'));
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
}
