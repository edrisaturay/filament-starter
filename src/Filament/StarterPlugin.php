<?php

namespace Raison\FilamentStarter\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Raison\FilamentStarter\Filament\Resources\AuditLogResource;
use Raison\FilamentStarter\Filament\Resources\PanelPluginOverrideResource;
use Raison\FilamentStarter\Filament\Resources\PanelSnapshotResource;
use Raison\FilamentStarter\Filament\Resources\SystemStatusResource;

class StarterPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-starter';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                PanelPluginOverrideResource::class,
                PanelSnapshotResource::class,
                SystemStatusResource::class,
                AuditLogResource::class,
            ]);

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
