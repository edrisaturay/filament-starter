<?php

namespace Raison\FilamentStarter\Support;

/**
 * Class Doctor
 *
 * Provides health checks for the Starter Platform.
 */
class Doctor
{
    /**
     * Run all health checks and return the results.
     *
     * @return array<int, array<string, mixed>>
     */
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

        // Check for NPM dependencies
        $results = array_merge($results, $this->checkNpmDependencies());

        // Check for Knowledge Base setup
        $results = array_merge($results, $this->checkKnowledgeBaseSetup());

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

    /**
     * Check for Knowledge Base specific setup requirements.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function checkKnowledgeBaseSetup(): array
    {
        $results = [];
        $kbPluginKey = 'filament-knowledge-base';
        $kbCompanionKey = 'filament-knowledge-base-companion';
        $kbPanelId = 'knowledge-base';

        // Check if either KB plugin is enabled in any panel
        $isKbUsed = false;
        $panels = \Illuminate\Support\Facades\DB::table('starter_panel_snapshots')->pluck('panel_id')->toArray();

        foreach ($panels as $panelId) {
            $states = PluginStateResolver::resolve($panelId);
            if (($states[$kbPluginKey]['enabled'] ?? false) || ($states[$kbCompanionKey]['enabled'] ?? false)) {
                $isKbUsed = true;
                break;
            }
        }

        if (! $isKbUsed) {
            return $results;
        }

        // 1. Check for dedicated KB panel
        $kbPanelExists = \Illuminate\Support\Facades\DB::table('starter_panel_snapshots')
            ->where('panel_id', $kbPanelId)
            ->exists();

        if (! $kbPanelExists) {
            $results[] = [
                'check' => 'KB: Dedicated Panel',
                'status' => 'critical',
                'message' => "Dedicated panel '{$kbPanelId}' is MISSING. Run 'php artisan filament:panel {$kbPanelId}' to create it.",
            ];
        }

        // 2. Check for theme directives in all panels
        foreach ($panels as $panelId) {
            $states = PluginStateResolver::resolve($panelId);
            if (! ($states[$kbPluginKey]['enabled'] ?? false) && ! ($states[$kbCompanionKey]['enabled'] ?? false)) {
                continue;
            }

            // Get panel snapshot for theme path if we had it, but snapshots only have meta.
            // We'll try to find the theme file by convention or check common locations.
            $themePath = resource_path("css/filament/{$panelId}/theme.css");

            if (! file_exists($themePath)) {
                $results[] = [
                    'check' => "KB: Theme for {$panelId}",
                    'status' => 'warning',
                    'message' => "Custom theme for panel '{$panelId}' not found at 'resources/css/filament/{$panelId}/theme.css'. KB requires a custom theme.",
                ];

                continue;
            }

            $themeContent = file_get_contents($themePath);
            $requiredDirectives = [
                '@plugin "@tailwindcss/typography"',
                'vendor/guava/filament-knowledge-base/src',
                'vendor/guava/filament-knowledge-base/resources/views',
            ];

            foreach ($requiredDirectives as $directive) {
                if (! str_contains($themeContent, $directive)) {
                    $results[] = [
                        'check' => "KB: Theme Directives ({$panelId})",
                        'status' => 'critical',
                        'message' => "Theme file for '{$panelId}' is missing KB directive: '{$directive}'.",
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Check for missing NPM dependencies required by plugins.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function checkNpmDependencies(): array
    {
        $results = [];
        $registry = PluginRegistry::getPlugins();
        $packageJsonPath = base_path('package.json');

        if (! file_exists($packageJsonPath)) {
            return [
                [
                    'check' => 'NPM package.json',
                    'status' => 'warning',
                    'message' => 'package.json not found in root. Cannot verify NPM dependencies.',
                ],
            ];
        }

        $packageJson = json_decode(file_get_contents($packageJsonPath), true);
        $installedNpm = array_merge(
            $packageJson['dependencies'] ?? [],
            $packageJson['devDependencies'] ?? []
        );

        foreach ($registry as $key => $definition) {
            $npmDeps = $definition['npm_dependencies'] ?? [];

            foreach ($npmDeps as $dep) {
                if (! isset($installedNpm[$dep])) {
                    // Check if the plugin is actually enabled
                    $status = 'warning';
                    $message = "NPM dependency '{$dep}' is missing (required by {$key}).";

                    // Use Model::query() to avoid direct DB if possible, but snapshots are in starter_panel_snapshots
                    $panels = \Illuminate\Support\Facades\DB::table('starter_panel_snapshots')->pluck('panel_id')->toArray();
                    foreach ($panels as $panelId) {
                        $states = PluginStateResolver::resolve($panelId);
                        if (($states[$key]['enabled'] ?? false)) {
                            $status = 'critical';
                            $message = "Plugin '{$key}' is ENABLED but its NPM dependency '{$dep}' is MISSING. Run 'npm install -D {$dep}'";
                            break;
                        }
                    }

                    $results[] = [
                        'check' => "NPM dependency: {$dep}",
                        'status' => $status,
                        'message' => $message,
                    ];
                }
            }
        }

        return $results;
    }
}
