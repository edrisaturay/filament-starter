<?php

namespace Asmit\ResizedColumn;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ResizedColumnServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('asmit-resized-column')
            ->hasMigrations([
                'create_table_settings',
            ]);
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Js::make('resized-column', __DIR__.'/../resources/dist/js/resized-column.js'),
            Css::make('resized-column', __DIR__.'/../resources/css/resized-column.css'),
        ], 'asmit/resized-column');

        // Register publishable migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_table_settings.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_table_settings_table.php'),
            ], 'resized-column-migrations');
        }
    }
}
