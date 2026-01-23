<?php

namespace Raison\FilamentStarter\Support;

use AchyutN\FilamentLogViewer\FilamentLogViewer;
use AlizHarb\ActivityLog\ActivityLogPlugin;
use Alizharb\FilamentModuleManager\FilamentModuleManagerPlugin;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Croustibat\FilamentJobsMonitor\FilamentJobsMonitorPlugin;
use Filament\Panel;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;
use RickDBCN\FilamentEmail\FilamentEmail;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;
use Tapp\FilamentAuthenticationLog\FilamentAuthenticationLogPlugin;
use WatheqAlshowaiter\FilamentStickyTableHeader\StickyTableHeaderPlugin;

class PluginRegistry
{
    public static function getPlugins(): array
    {
        return [
            'filament-shield' => [
                'label' => 'Filament Shield',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentShieldPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => true,
                'requires_migrations' => true,
                'default_options' => [],
            ],
            'filament-breezy' => [
                'label' => 'Filament Breezy',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(
                    BreezyCore::make()
                        ->enableTwoFactorAuthentication()
                        ->enableSanctumTokens()
                        ->myProfile()
                ),
                'default_enabled' => true,
                'dangerous_to_disable' => true,
                'requires_migrations' => true,
                'default_options' => [],
            ],
            'filament-jobs-monitor' => [
                'label' => 'Jobs Monitor',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentJobsMonitorPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
            ],
            'filament-log-viewer' => [
                'label' => 'Log Viewer',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentLogViewer::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
            ],
            'filament-authentication-log' => [
                'label' => 'Authentication Log',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentAuthenticationLogPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
            ],
            'filament-email' => [
                'label' => 'Filament Email',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentEmail::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
            ],
            'filament-health' => [
                'label' => 'Spatie Health',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentSpatieLaravelHealthPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
            ],
            'filament-backup' => [
                'label' => 'Spatie Backup',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentSpatieLaravelBackupPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
            ],
            'filament-environment-indicator' => [
                'label' => 'Environment Indicator',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(EnvironmentIndicatorPlugin::make()->showGitBranch()->showDebugModeWarningInProduction()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
            ],
            'global-search-modal' => [
                'label' => 'Global Search Modal',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(GlobalSearchModalPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
            ],
            'filter-sets' => [
                'label' => 'Advanced Tables (Filter Sets)',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(AdvancedTablesPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
            ],
            'sticky-table-header' => [
                'label' => 'Sticky Table Header',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(StickyTableHeaderPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
            ],
            'resized-column' => [
                'label' => 'Resized Column',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(ResizedColumnPlugin::make()->preserveOnDB()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
            ],
            'phone-numbers' => [
                'label' => 'Phone Numbers',
                'installer' => fn (Panel $panel, array $options) => $panel, // It's often just field types, but registered for visibility
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
            ],
            'statefusion' => [
                'label' => 'Statefusion',
                'installer' => fn (Panel $panel, array $options) => $panel, // Registered as package in composer
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
            ],
            'filament-activity-log' => [
                'label' => 'Activity Log',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(ActivityLogPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
            ],
            'filament-module-manager' => [
                'label' => 'Module Manager',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentModuleManagerPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
            ],
            'filament-impersonate' => [
                'label' => 'Impersonate',
                'installer' => fn (Panel $panel, array $options) => $panel, // Often added via middleware or direct use, but let's keep it in registry
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
            ],
        ];
    }
}
