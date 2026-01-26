<?php

namespace EdrisaTuray\FilamentStarter;

use Illuminate\Support\ServiceProvider;
use EdrisaTuray\FilamentStarter\Commands\StarterDoctorCommand;
use EdrisaTuray\FilamentStarter\Commands\StarterInstallCommand;
use EdrisaTuray\FilamentStarter\Commands\StarterSafeModeCommand;
use EdrisaTuray\FilamentStarter\Commands\StarterUpdateCommand;
use EdrisaTuray\FilamentStarter\Http\Middleware\DeveloperGateMiddleware;

class StarterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-starter.php', 'filament-starter');

        $this->app['router']->aliasMiddleware('starter.developer-gate', DeveloperGateMiddleware::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-starter');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-starter.php' => config_path('filament-starter.php'),
            ], 'filament-starter-config');

            $this->commands([
                StarterInstallCommand::class,
                StarterUpdateCommand::class,
                StarterDoctorCommand::class,
                StarterSafeModeCommand::class,
            ]);
        }
    }
}
