<?php

namespace Raison\FilamentStarter\Support;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

class PanelSnapshotManager
{
    public static function snapshot(): void
    {
        $panels = Filament::getPanels();

        foreach ($panels as $panel) {
            $id = $panel->getId();

            DB::table('starter_panel_snapshots')->updateOrInsert(
                ['panel_id' => $id],
                [
                    'meta' => json_encode([
                        'path' => $panel->getPath(),
                        'domains' => $panel->getDomains(),
                        'middleware' => array_map(fn ($m) => is_string($m) ? $m : get_class($m), $panel->getMiddleware()),
                        'tenancy' => $panel->hasTenancy(),
                    ]),
                    'last_seen_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Also sync plugins to ensure overrides table is populated for UI
        PluginSyncManager::sync();
    }

    public static function getSnapshots(): array
    {
        return DB::table('starter_panel_snapshots')->get()->toArray();
    }
}
