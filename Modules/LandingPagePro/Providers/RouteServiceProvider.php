<?php

namespace Modules\LandingPagePro\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
	/**
	 * The module namespace to assume when generating URLs to actions.
	 *
	 * @var string
	 */
	protected $moduleNamespace = '';

	/**
	 * Define the routes for the application.
	 *
	 * @return void
	 */
	public function map()
	{
		$this->mapApiRoutes();

		$this->mapWebRoutes();
		
		$this->mapPublicRoutes();
	}

	/**
	 * Define the "web" routes for the application.
	 *
	 * These routes all receive session state, CSRF protection, etc.
	 *
	 * @return void
	 */
	protected function mapWebRoutes()
	{
		Route::middleware('web')
			->namespace($this->moduleNamespace)
			->group(module_path('LandingPagePro', '/Routes/web.php'));
	}
	/**
	 * Define the "public" routes for the application.
	 *
	 * These routes are typically stateless.
	 *
	 * @return void
	 */
	protected function mapPublicRoutes()
	{
		Route::middleware('web')
			->namespace($this->namespace)
			->group(module_path('LandingPagePro', '/Routes/web-public.php'));
	}

	/**
	 * Define the "api" routes for the application.
	 *
	 * These routes are typically stateless.
	 *
	 * @return void
	 */
	protected function mapApiRoutes()
	{
		Route::prefix('api')
			->middleware('api')
			->namespace($this->moduleNamespace)
			->group(module_path('LandingPagePro', '/Routes/api.php'));
	}
}
