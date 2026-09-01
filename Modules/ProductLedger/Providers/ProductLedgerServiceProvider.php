<?php

namespace Modules\ProductLedger\Providers;

use Illuminate\Support\ServiceProvider;

class ProductLedgerServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
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
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('ProductLedger', 'Config/config.php') => config_path('productledger.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('ProductLedger', 'Config/config.php'), 'productledger'
        );
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/productledger');
        $sourcePath = module_path('ProductLedger', 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom([$sourcePath], 'productledger');
    }

    /**
     * Register translations.
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/productledger');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'productledger');
        } else {
            $this->loadTranslationsFrom(module_path('ProductLedger', 'Resources/lang'), 'productledger');
        }
    }
}
