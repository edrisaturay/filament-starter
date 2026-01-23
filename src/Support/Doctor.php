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

        // Tenancy Check
        if (config('filament-starter.tenancy.enabled')) {
            $tenantModel = config('filament-starter.tenancy.tenant_model');
            $results[] = [
                'check' => 'Tenancy Config',
                'status' => class_exists($tenantModel) ? 'ok' : 'critical',
                'message' => class_exists($tenantModel) ? "Tenant model {$tenantModel} found." : "Tenant model {$tenantModel} MISSING.",
            ];
        }

        return $results;
    }
}
