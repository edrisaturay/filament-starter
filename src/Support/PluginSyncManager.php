<?php

namespace Raison\FilamentStarter\Support;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Raison\FilamentStarter\Models\AuditLog;

/**
 * Class PluginSyncManager
 *
 * Handles synchronization between the PluginRegistry and the database overrides.
 */
class PluginSyncManager
{
    /**
     * Synchronize all registered plugins with the overrides table for all panels.
     */
    public static function sync(): void
    {
        $panels = Filament::getPanels();
        $registry = PluginRegistry::getPlugins();
        $actorId = auth()->id();
        $managedPanels = config('filament-starter.managed_panels', []);

        foreach ($panels as $panelId => $panel) {
            // Only sync plugins for managed panels
            if (! in_array($panelId, $managedPanels)) {
                continue;
            }

            foreach ($registry as $pluginKey => $definition) {
                // Check if override exists
                $existing = DB::table('starter_panel_plugin_overrides')
                    ->where('panel_id', $panelId)
                    ->where('plugin_key', $pluginKey)
                    ->whereNull('tenant_id')
                    ->first();

                if ($existing) {
                    // Just update the updated_at to show it was verified
                    DB::table('starter_panel_plugin_overrides')
                        ->where('id', $existing->id)
                        ->update(['updated_at' => now()]);
                } else {
                    // Create new override with registry defaults
                    DB::table('starter_panel_plugin_overrides')->insert([
                        'panel_id' => $panelId,
                        'plugin_key' => $pluginKey,
                        'enabled' => $definition['default_enabled'],
                        'options' => json_encode($definition['default_options']),
                        'options_version' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    AuditLog::create([
                        'actor_user_id' => $actorId,
                        'action' => 'sync_create_plugin_override',
                        'panel_id' => $panelId,
                        'plugin_key' => $pluginKey,
                        'after' => [
                            'enabled' => $definition['default_enabled'],
                            'options' => $definition['default_options'],
                        ],
                    ]);
                }
            }
        }

        // Cleanup: remove overrides for plugins that are no longer in the registry or panels no longer managed
        $registryKeys = array_keys($registry);
        $toDelete = DB::table('starter_panel_plugin_overrides')
            ->whereNull('tenant_id')
            ->where(function ($query) use ($registryKeys, $managedPanels) {
                $query->whereNotIn('plugin_key', $registryKeys)
                    ->orWhereNotIn('panel_id', $managedPanels);
            })
            ->get();

        foreach ($toDelete as $override) {
            AuditLog::create([
                'actor_user_id' => $actorId,
                'action' => 'sync_delete_plugin_override',
                'panel_id' => $override->panel_id,
                'plugin_key' => $override->plugin_key,
                'before' => (array) $override,
            ]);

            DB::table('starter_panel_plugin_overrides')
                ->where('id', $override->id)
                ->delete();
        }

        // Invalidate cache for all panels
        foreach ($panels as $panelId => $panel) {
            PluginStateResolver::invalidate($panelId);
        }
    }
}
