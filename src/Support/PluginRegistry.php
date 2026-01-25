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

/**
 * Class PluginRegistry
 *
 * Central registry for all managed Filament plugins in the starter kit.
 */
class PluginRegistry
{
    /**
     * Get the list of managed plugins and their configurations.
     *
     * @return array<string, array<string, mixed>>
     */
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
                'class' => FilamentShieldPlugin::class,
                'package' => 'bezhansalleh/filament-shield',
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
                'class' => BreezyCore::class,
                'package' => 'jeffgreco13/filament-breezy',
            ],
            'filament-jobs-monitor' => [
                'label' => 'Jobs Monitor',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentJobsMonitorPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
                'class' => FilamentJobsMonitorPlugin::class,
                'package' => 'croustibat/filament-jobs-monitor',
            ],
            'filament-log-viewer' => [
                'label' => 'Log Viewer',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentLogViewer::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => FilamentLogViewer::class,
                'package' => 'achyutn/filament-log-viewer',
            ],
            'filament-authentication-log' => [
                'label' => 'Authentication Log',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentAuthenticationLogPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
                'class' => FilamentAuthenticationLogPlugin::class,
                'package' => 'tapp/filament-authentication-log',
            ],
            'filament-email' => [
                'label' => 'Filament Email',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentEmail::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
                'class' => FilamentEmail::class,
                'package' => 'rickdbcn/filament-email',
            ],
            'filament-health' => [
                'label' => 'Spatie Health',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentSpatieLaravelHealthPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
                'class' => FilamentSpatieLaravelHealthPlugin::class,
                'package' => 'shuvroroy/filament-spatie-laravel-health',
            ],
            'filament-backup' => [
                'label' => 'Spatie Backup',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentSpatieLaravelBackupPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
                'class' => FilamentSpatieLaravelBackupPlugin::class,
                'package' => 'shuvroroy/filament-spatie-laravel-backup',
            ],
            'filament-environment-indicator' => [
                'label' => 'Environment Indicator',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(EnvironmentIndicatorPlugin::make()->showGitBranch()->showDebugModeWarningInProduction()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => EnvironmentIndicatorPlugin::class,
                'package' => 'pxlrbt/filament-environment-indicator',
            ],
            'global-search-modal' => [
                'label' => 'Global Search Modal',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(GlobalSearchModalPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => GlobalSearchModalPlugin::class,
                'package' => 'charrafimed/global-search-modal',
            ],
            'filter-sets' => [
                'label' => 'Advanced Tables (Filter Sets)',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(AdvancedTablesPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
                'class' => AdvancedTablesPlugin::class,
                'package' => 'archilex/filament-filter-sets',
            ],
            'sticky-table-header' => [
                'label' => 'Sticky Table Header',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(StickyTableHeaderPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => StickyTableHeaderPlugin::class,
                'package' => 'watheqalshowaiter/filament-sticky-table-header',
            ],
            'resized-column' => [
                'label' => 'Resized Column',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(ResizedColumnPlugin::make()->preserveOnDB()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
                'class' => ResizedColumnPlugin::class,
                'package' => 'asmit/resized-column',
            ],
            'phone-numbers' => [
                'label' => 'Phone Numbers',
                'installer' => fn (Panel $panel, array $options) => $panel, // It's often just field types, but registered for visibility
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => 'Cheesegrits\FilamentPhoneNumbers\FilamentPhoneNumbersServiceProvider',
                'package' => 'cheesegrits/filament-phone-numbers',
            ],
            'statefusion' => [
                'label' => 'Statefusion',
                'installer' => fn (Panel $panel, array $options) => $panel, // Registered as package in composer
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => 'A909m\FilamentStatefusion\FilamentStatefusionServiceProvider',
                'package' => 'a909m/filament-statefusion',
            ],
            'filament-activity-log' => [
                'label' => 'Activity Log',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(ActivityLogPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
                'class' => ActivityLogPlugin::class,
                'package' => 'alizharb/filament-activity-log',
            ],
            'filament-module-manager' => [
                'label' => 'Module Manager',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentModuleManagerPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => FilamentModuleManagerPlugin::class,
                'package' => 'alizharb/filament-module-manager',
            ],
            'filament-impersonate' => [
                'label' => 'Impersonate',
                'installer' => fn (Panel $panel, array $options) => $panel, // Often added via middleware or direct use, but let's keep it in registry
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => 'STS\FilamentImpersonate\FilamentImpersonateServiceProvider',
                'package' => 'stechstudio/filament-impersonate',
            ],
            'filament-progress-bar-column' => [
                'label' => 'Progress Bar Column',
                'installer' => fn (Panel $panel, array $options) => $panel, // Registered as package in composer
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => 'Tapp\FilamentProgressBarColumn\FilamentProgressBarColumnServiceProvider',
                'package' => 'tapp/filament-progress-bar-column',
            ],
        ];
    }

    /**
     * Determine if a plugin is dangerous to disable.
     */
    public static function isDangerous(?string $key): bool
    {
        if (! $key) {
            return false;
        }

        $plugins = static::getPlugins();

        return $plugins[$key]['dangerous_to_disable'] ?? false;
    }
}
