<?php

namespace Raison\FilamentStarter\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PluginStateResolver
{
    public static function resolve(string $panelId, ?string $tenantId = null): array
    {
        $cacheKey = "starter_plugins_{$panelId}".($tenantId ? "_{$tenantId}" : '');

        return Cache::rememberForever($cacheKey, function () use ($panelId, $tenantId) {
            $registry = PluginRegistry::getPlugins();
            $resolved = [];

            // 1. Registry defaults & config defaults
            $configDefaults = config("filament-starter.plugin_defaults.{$panelId}", []);

            foreach ($registry as $key => $definition) {
                $enabled = $configDefaults[$key]['enabled'] ?? $definition['default_enabled'];
                $options = $configDefaults[$key]['options'] ?? $definition['default_options'];

                $resolved[$key] = [
                    'enabled' => $enabled,
                    'options' => $options,
                ];
            }

            // 2. DB overrides
            $overrides = DB::table('starter_panel_plugin_overrides')
                ->where('panel_id', $panelId)
                ->where('tenant_id', $tenantId)
                ->get();

            foreach ($overrides as $override) {
                if (isset($resolved[$override->plugin_key])) {
                    if ($override->enabled !== null) {
                        $resolved[$override->plugin_key]['enabled'] = (bool) $override->enabled;
                    }
                    if ($override->options !== null) {
                        $resolved[$override->plugin_key]['options'] = json_decode($override->options, true);
                    }
                }
            }

            // 3. Safe Mode
            if (SafeMode::isActive()) {
                foreach ($registry as $key => $definition) {
                    if ($definition['dangerous_to_disable']) {
                        $resolved[$key]['enabled'] = true;
                    }
                }
            }

            return $resolved;
        });
    }

    public static function invalidate(string $panelId, ?string $tenantId = null): void
    {
        $cacheKey = "starter_plugins_{$panelId}".($tenantId ? "_{$tenantId}" : '');
        Cache::forget($cacheKey);
    }
}
