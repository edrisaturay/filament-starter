<?php

namespace Raison\FilamentStarter\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;

class StarterPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-starter';
    }

    public function register(Panel $panel): void
    {
        if ($panel->getId() === 'platform') {
            return;
        }

        PlatformPanelFactory::build($panel, $panel->getId());
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
