<?php

namespace EdrisaTuray\FilamentStarter;

use EdrisaTuray\FilamentStarter\Commands\StarterDoctorCommand;
use EdrisaTuray\FilamentStarter\Commands\StarterInstallCommand;
use EdrisaTuray\FilamentStarter\Commands\StarterSafeModeCommand;
use EdrisaTuray\FilamentStarter\Commands\StarterUpdateCommand;
use EdrisaTuray\FilamentStarter\Http\Middleware\DeveloperGateMiddleware;
use EdrisaTuray\FilamentStarter\Support\PluginStateResolver;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use Illuminate\Support\ServiceProvider;

class StarterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-starter.php', 'filament-starter');

        $this->app['router']->aliasMiddleware('starter.developer-gate', DeveloperGateMiddleware::class);
    }

    public function boot(): void
    {
        $this->registerPanelSwitcher();
        $this->registerReviveLivewireComponents();

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

    protected function registerReviveLivewireComponents(): void
    {
        if (! class_exists(\Livewire\Livewire::class)) {
            return;
        }

        if (
            ! class_exists(\Promethys\Revive\Pages\RecycleBin::class)
            || ! class_exists(\Promethys\Revive\Tables\RecycleBin::class)
        ) {
            return;
        }

        \Livewire\Livewire::component('revive::pages.recycle-bin', \Promethys\Revive\Pages\RecycleBin::class);
        \Livewire\Livewire::component('revive::tables.recycle-bin', \Promethys\Revive\Tables\RecycleBin::class);
    }

    /**
     * Register and configure the Filament Panel Switcher.
     */
    protected function registerPanelSwitcher(): void
    {
        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch
                ->modalHeading(__('Available Panels'))
                ->modalWidth('md')
                ->icons([
                    'admin' => 'heroicon-o-shield-check',
                    'staff' => 'heroicon-o-identification',
                    'knowledge-base' => 'heroicon-o-book-open',
                ])
                ->simple()
                ->labels([
                    'admin' => __('Control Center'),
                    'staff' => __('Operations'),
                    'knowledge-base' => __('Resources'),
                ])
                ->visible(fn (): bool => auth()->check())
                ->canSwitchPanels(fn (): bool => auth()->check());

            // Only show panels that are actually enabled for the user
            $panelSwitch->panels(function () {
                $panels = \Filament\Facades\Filament::getPanels();
                $enabledPanels = [];

                foreach ($panels as $panelId => $panel) {
                    $states = PluginStateResolver::resolve($panelId);
                    if ($states['filament-panel-switch']['enabled'] ?? false) {
                        $enabledPanels[] = $panelId;
                    }
                }

                return $enabledPanels ?: array_keys($panels);
            });
        });
    }
}
