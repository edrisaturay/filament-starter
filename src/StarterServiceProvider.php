<?php

namespace Raison\FilamentStarter;

use Illuminate\Support\ServiceProvider;
use Raison\FilamentStarter\Commands\StarterDoctorCommand;
use Raison\FilamentStarter\Commands\StarterInstallCommand;
use Raison\FilamentStarter\Commands\StarterSafeModeCommand;
use Raison\FilamentStarter\Commands\StarterUpdateCommand;
use Raison\FilamentStarter\Filament\Panels\PlatformPanelProvider;

class StarterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-starter.php', 'filament-starter');

        $this->app->register(PlatformPanelProvider::class);
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
