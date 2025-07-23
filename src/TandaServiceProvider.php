<?php

namespace EdLugz\Tanda;

use Illuminate\Support\ServiceProvider;

class TandaServiceProvider extends ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/tanda.php';
    public function boot(): void
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('tanda.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tanda.php', 'tanda');

        // Register the service the package provides.
        $this->app->singleton('tanda', function ($app) {
            return new Tanda();
        });
    }

    public function provides(): array
    {
        return ['tanda'];
    }
}
