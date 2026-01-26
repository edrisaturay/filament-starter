<?php

namespace EdrisaTuray\FilamentStarter\Filament;

use Filament\Panel;
use EdrisaTuray\FilamentStarter\Support\PluginRegistry;
use EdrisaTuray\FilamentStarter\Support\PluginStateResolver;

class PlatformPanelFactory
{
    public static function build(Panel $panel, string $panelId): Panel
    {
        $states = PluginStateResolver::resolve($panelId);
        $registry = PluginRegistry::getPlugins();

        foreach ($states as $key => $state) {
            if ($state['enabled'] && isset($registry[$key])) {
                $registry[$key]['installer']($panel, $state['options']);
            }
        }

        // Apply tenancy if enabled
        if (config('filament-starter.tenancy.enabled') && in_array($panelId, config('filament-starter.tenancy.scoped_panels', []))) {
            $panel->tenant(config('filament-starter.tenancy.tenant_model'));
            // Add tenancy middleware if needed
        }

        return $panel;
    }
}
