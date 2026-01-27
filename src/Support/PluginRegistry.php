<?php

namespace EdrisaTuray\FilamentStarter\Support;

use AchyutN\FilamentLogViewer\FilamentLogViewer;
use AlizHarb\ActivityLog\ActivityLogPlugin;
use Alizharb\FilamentModuleManager\FilamentModuleManagerPlugin;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Croustibat\FilamentJobsMonitor\FilamentJobsMonitorPlugin;
use EdrisaTuray\FilamentAiChatAgent\AiChatAgentPlugin;
use EdrisaTuray\FilamentApiDocsBuilder\FilamentApiDocsBuilderPlugin;
use Filament\Panel;
use Guava\FilamentKnowledgeBase\Plugins\KnowledgeBaseCompanionPlugin;
use Guava\FilamentKnowledgeBase\Plugins\KnowledgeBasePlugin;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Promethys\Revive\RevivePlugin;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;
use RickDBCN\FilamentEmail\FilamentEmail;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;
use Swis\Filament\Backgrounds\ImageProviders\CuratedBySwis;
use Swis\Filament\Backgrounds\ImageProviders\MyImages;
use Swis\Filament\Backgrounds\ImageProviders\Triangles;
use Tapp\FilamentAuthenticationLog\FilamentAuthenticationLogPlugin;
use TomatoPHP\FilamentUsers\FilamentUsersPlugin;
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
            'filament-knowledge-base' => [
                'label' => 'Knowledge Base',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(KnowledgeBasePlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => KnowledgeBasePlugin::class,
                'package' => 'guava/filament-knowledge-base',
                'npm_dependencies' => [
                    '@tailwindcss/typography',
                ],
            ],
            'filament-knowledge-base-companion' => [
                'label' => 'Knowledge Base Companion',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(
                    KnowledgeBaseCompanionPlugin::make()
                        ->knowledgeBasePanelId('knowledge-base')
                ),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => KnowledgeBaseCompanionPlugin::class,
                'package' => 'guava/filament-knowledge-base',
            ],
            'filament-backgrounds' => [
                'label' => 'Filament Backgrounds',
                'installer' => function (Panel $panel, array $options) {
                    $plugin = FilamentBackgroundsPlugin::make()
                        ->showAttribution(config('filament-starter.plugins.backgrounds.show_attribution', true))
                        ->remember(config('filament-starter.plugins.backgrounds.remember', 900));

                    $imageProvider = config('filament-starter.plugins.backgrounds.image_provider', 'curated');

                    if ($imageProvider === 'my-images') {
                        $plugin->imageProvider(
                            MyImages::make()
                                ->directory(config('filament-starter.plugins.backgrounds.my_images_directory', 'images/backgrounds'))
                        );
                    } elseif ($imageProvider === 'triangles') {
                        $plugin->imageProvider(Triangles::make());
                    } else {
                        $plugin->imageProvider(CuratedBySwis::make());
                    }

                    return $panel->plugin($plugin);
                },
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => FilamentBackgroundsPlugin::class,
                'package' => 'swisnl/filament-backgrounds',
            ],
            'filament-revive' => [
                'label' => 'Revive (Recycle Bin)',
                'installer' => function (Panel $panel, array $options) {
                    $plugin = RevivePlugin::make();
                    $panelId = $panel->getId();

                    $globalAdminPanels = config('filament-starter.plugins.revive.global_admin_panels', ['admin']);

                    if (in_array($panelId, $globalAdminPanels)) {
                        $plugin->showAllRecords();
                    } else {
                        $userScoping = config('filament-starter.plugins.revive.user_scoping', true);
                        $tenantScoping = config('filament-starter.plugins.revive.tenant_scoping', true);

                        $plugin->enableUserScoping($userScoping)
                            ->enableTenantScoping($panel->hasTenancy() && $tenantScoping);
                    }

                    return $panel->plugin($plugin);
                },
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
                'class' => RevivePlugin::class,
                'package' => 'promethys/revive',
            ],
            'filament-quick-add-select' => [
                'label' => 'Quick Add Select',
                'installer' => fn (Panel $panel, array $options) => $panel, // Component enhancement
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => \Cocosmos\FilamentQuickAddSelect\FilamentQuickAddServiceProvider::class,
                'package' => 'cocosmos/filament-quick-add-select',
            ],
            'filament-ai-chat-agent' => [
                'label' => 'AI Chat Agent',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(AiChatAgentPlugin::make()),
                'default_enabled' => false,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => AiChatAgentPlugin::class,
                'package' => 'edrisaturay/filament-ai-chat-agent',
            ],
            'filament-api-docs-builder' => [
                'label' => 'API Docs Builder',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentApiDocsBuilderPlugin::make()),
                'default_enabled' => false,
                'dangerous_to_disable' => false,
                'requires_migrations' => true,
                'default_options' => [],
                'class' => FilamentApiDocsBuilderPlugin::class,
                'package' => 'edrisaturay/filament-api-docs-builder',
            ],
            'filament-natural-language-filter' => [
                'label' => 'Natural Language Filter',
                'installer' => fn (Panel $panel, array $options) => $panel, // Component enhancement
                'default_enabled' => false,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => 'EdrisaTuray\FilamentNaturalLanguageFilter\FilamentNaturalLanguageFilterServiceProvider',
                'package' => 'edrisaturay/filament-natural-language-filter',
            ],
            'filament-users' => [
                'label' => 'Filament Users',
                'installer' => fn (Panel $panel, array $options) => $panel->plugin(FilamentUsersPlugin::make()),
                'default_enabled' => true,
                'dangerous_to_disable' => false,
                'requires_migrations' => false,
                'default_options' => [],
                'class' => FilamentUsersPlugin::class,
                'package' => 'tomatophp/filament-users',
            ],
        ];
    }

    /**
     * Determine if a plugin is dangerous to disable.
     */
    public static function isDangerous(?string $key, ?string $panelId = null): bool
    {
        if (! $key) {
            return false;
        }

        // 1. Check DB overrides first if panelId is provided
        if ($panelId) {
            try {
                $override = \Illuminate\Support\Facades\DB::table('starter_panel_plugin_overrides')
                    ->where('panel_id', $panelId)
                    ->where('plugin_key', $key)
                    ->whereNull('tenant_id')
                    ->first();

                if ($override && $override->is_dangerous) {
                    return true;
                }
            } catch (\Exception $e) {
                // Table might not exist
            }
        }

        // 2. Fallback to registry
        $plugins = static::getPlugins();

        return $plugins[$key]['dangerous_to_disable'] ?? false;
    }
}
