<?php

namespace EdrisaTuray\FilamentStarter\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PluginStateResolver
{
    public static function resolve(string $panelId, ?string $tenantId = null): array
    {
        $cacheKey = "starter_plugins_{$panelId}".($tenantId ? "_{$tenantId}" : '');

        $computeState = function () use ($panelId, $tenantId): array {
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

            // 2. DB overrides (Only if table exists)
            try {
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
                            $resolved[$override->plugin_key]['options'] = is_string($override->options) ? json_decode($override->options, true) : $override->options;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Table might not exist yet during migration/install
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
        };

        try {
            return Cache::rememberForever($cacheKey, $computeState);
        } catch (\Throwable $exception) {
            report($exception);

            return $computeState();
        }
    }

    public static function invalidate(string $panelId, ?string $tenantId = null): void
    {
        $cacheKey = "starter_plugins_{$panelId}".($tenantId ? "_{$tenantId}" : '');
        Cache::forget($cacheKey);
    }
}
