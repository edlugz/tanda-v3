<?php

namespace Edlugz\Tanda;

use Illuminate\Support\ServiceProvider;

class TandaServiceProvider extends ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/tanda.php';
    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('tanda.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tanda.php', 'tanda');

        // Register the service the package provides.
        $this->app->singleton('tanda', function ($app) {
            return new Tanda();
        });
    }

    public function provides()
    {
        return ['tanda'];
    }
}
