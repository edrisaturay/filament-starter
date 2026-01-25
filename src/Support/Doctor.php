<?php

namespace Raison\FilamentStarter\Support;

class Doctor
{
    public function check(): array
    {
        $results = [];

        // Check if platform is installed
        $results[] = [
            'check' => 'Platform Installed',
            'status' => config('filament-starter.installed') ? 'ok' : 'critical',
            'message' => config('filament-starter.installed') ? 'Platform is initialized.' : 'Platform needs to run starter:install.',
        ];

        // Check for snapshots
        $snapshotCount = \Illuminate\Support\Facades\DB::table('starter_panel_snapshots')->count();
        $results[] = [
            'check' => 'Panel Snapshots',
            'status' => $snapshotCount > 0 ? 'ok' : 'warning',
            'message' => "Found {$snapshotCount} panel snapshots.",
        ];

        // Check for Filament v5
        $filamentInstalled = class_exists(\Filament\Panel::class);
        $results[] = [
            'check' => 'Filament v5 Installed',
            'status' => $filamentInstalled ? 'ok' : 'critical',
            'message' => $filamentInstalled ? 'Filament v5 is present.' : 'Filament v5 is MISSING.',
        ];

        // Tenancy Check
        if (config('filament-starter.tenancy.enabled')) {
            $tenantModel = config('filament-starter.tenancy.tenant_model');
            $results[] = [
                'check' => 'Tenancy Config',
                'status' => class_exists($tenantModel) ? 'ok' : 'critical',
                'message' => class_exists($tenantModel) ? "Tenant model {$tenantModel} found." : "Tenant model {$tenantModel} MISSING.",
            ];
        }

        // Plugin Dependencies Check
        $registry = PluginRegistry::getPlugins();
        foreach ($registry as $key => $definition) {
            $class = $definition['class'] ?? null;
            if ($class && ! class_exists($class)) {
                $status = 'warning';
                $message = "Package {$definition['package']} might be missing (Class {$class} not found).";

                // If enabled for any panel, mark as critical
                $panels = \Raison\FilamentStarter\Models\PanelSnapshot::pluck('panel_id')->toArray();
                foreach ($panels as $panelId) {
                    $states = PluginStateResolver::resolve($panelId);
                    if (($states[$key]['enabled'] ?? false)) {
                        $status = 'critical';
                        $message = "Plugin {$key} is ENABLED but its package {$definition['package']} is MISSING.";
                        if ($key === 'filter-sets') {
                            $message .= " Ensure 'https://filament-filter-sets.composer.sh' repository is added to root composer.json.";
                        }
                        break;
                    }
                }

                $results[] = [
                    'check' => "Plugin dependency: {$key}",
                    'status' => $status,
                    'message' => $message,
                ];
            }
        }

        return $results;
    }
}
