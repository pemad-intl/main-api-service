<?php

namespace Pemad\MainApi;

use Illuminate\Support\ServiceProvider;

class MainApiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mainapi.php', 'mainapi');

        $this->app->singleton(MainApiService::class, function ($app) {
            return new MainApiService();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/mainapi.php' => config_path('mainapi.php'),
            ], 'mainapi-config');

            $this->commands([
                Console\TestMainApi::class,
            ]);
        }
    }
}
