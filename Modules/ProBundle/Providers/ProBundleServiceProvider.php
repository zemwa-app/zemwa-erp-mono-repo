<?php

namespace Modules\ProBundle\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ProBundleServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('probundle.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'probundle'
        );
    }

    public function registerViews()
    {
        $viewPath = base_path('resources/views/modules/probundle');
        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ]);

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/probundle';
        }, \Config::get('view.paths')), [$sourcePath]), 'probundle');
    }

    public function registerTranslations()
    {
        $langPath = base_path('resources/lang/modules/probundle');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'probundle');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'probundle');
        }
    }
}
