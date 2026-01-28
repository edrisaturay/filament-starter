<?php

namespace EdrisaTuray\FilamentStarter\Commands;

use EdrisaTuray\FilamentStarter\Models\PanelPluginOverride;
use EdrisaTuray\FilamentStarter\Support\Doctor;
use EdrisaTuray\FilamentStarter\Support\PanelSnapshotManager;
use EdrisaTuray\FilamentStarter\Support\PluginRegistry;
use Illuminate\Console\Command;
use function Laravel\Prompts\multiselect;

/**
 * Class StarterInstallCommand
 *
 * Artisan command to initialize the Starter Platform.
 */
class StarterInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starter:install {--force} {--tenancy= : Enable tenancy (yes/no)} {--multilanguage= : Enable multilanguage (yes/no)} {--publish-all : Publish all plugin configs without asking}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize the Starter Platform';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (config('filament-starter.installed') && ! $this->option('force')) {
            $this->error('Platform already installed. Use --force to reinstall.');

            return 1;
        }

        $this->info('Starting Starter Platform installation...');

        // 1. Ensure Sanctum is installed
        $this->ensureSanctumInstalled();

        // 1.1 Publish required migrations before migrating
        $this->publishRequiredMigrations();

        // 2. Run migrations
        $this->call('migrate', ['--force' => true]);

        // 2. Snapshot panels and sync plugins
        $this->info('Snapshotting panels and syncing plugins...');
        PanelSnapshotManager::snapshot();

        // 3. Interactive Plugin Configuration
        $this->configurePlugins();

        // 3.2. Shield Setup
        $this->setupShield();

        // 3.5. Knowledge Base Setup
        $this->setupKnowledgeBase();

        // 3.6. Backgrounds Setup
        $this->setupBackgrounds();

        // 3.7. Revive Setup
        $this->setupRevive();

        // 4. Collect invariants
        $tenancy = $this->getInvariants('tenancy');
        $multilanguage = $this->getInvariants('multilanguage');

        // 4. Update config file (simulated for this environment, in real app would use a proper config writer)
        $this->updateConfig($tenancy, $multilanguage);

        // 5. Mark as installed in DB (using a simple setting or log)
        \EdrisaTuray\FilamentStarter\Models\AuditLog::create([
            'action' => 'install',
            'after' => [
                'tenancy' => $tenancy,
                'multilanguage' => $multilanguage,
            ],
        ]);

        // 6. Final Health Check
        $this->checkHealth();

        $this->info('Platform installed successfully.');

        return 0;
    }

    /**
     * Run interactive plugin configuration.
     */
    protected function configurePlugins(): void
    {
        if ($this->option('no-interaction')) {
            return;
        }

        $plugins = PluginRegistry::getPlugins();
        $pluginOptions = collect($plugins)
            ->mapWithKeys(fn ($definition, $key) => [$key => $definition['label']])
            ->toArray();

        // 1. Publish configs and migrations
        $this->publishPluginAssets($plugins, $pluginOptions);

        // 2. Enable/Disable per Panel
        $this->activatePluginsPerPanel($plugins, $pluginOptions);

        // 3. Mark Dangerous Plugins
        $this->markDangerousPlugins($plugins, $pluginOptions);
    }

    /**
     * Ask which plugin configs to publish.
     */
    protected function publishPluginAssets(array $plugins, array $options): void
    {
        if ($this->option('publish-all')) {
            $toPublish = array_keys($plugins);
        } else {
            $toPublish = $this->multiSelect(
                'Which plugins should have configs/migrations published?',
                array_merge(['none' => 'none', 'all' => 'all'], $options),
                ['none']
            );

            if (in_array('none', $toPublish)) {
                return;
            }

            if (in_array('all', $toPublish)) {
                $toPublish = array_keys($plugins);
            }
        }

        foreach ($toPublish as $key) {
            $definition = $plugins[$key] ?? null;
            if (! $definition) {
                continue;
            }

            $this->publishPluginConfig($definition);
            $this->publishPluginMigrations($key);
        }
    }

    /**
     * Publish plugin config if possible.
     *
     * @param  array<string, mixed>  $definition
     */
    protected function publishPluginConfig(array $definition): void
    {
        $package = $definition['package'] ?? null;
        if (! $package) {
            return;
        }

        $this->info("Publishing config for {$definition['label']}...");
        $tag = $this->configPublishTags()[$definition['package']] ?? 'config';
        $class = $definition['class'] ?? null;
        if ($class) {
            $this->call('vendor:publish', [
                '--provider' => $class,
                '--tag' => $tag,
            ]);
        }
    }

    /**
     * Publish plugin migrations if missing.
     */
    protected function publishPluginMigrations(string $key): void
    {
        $publishers = $this->pluginMigrationPublishers();
        $publisher = $publishers[$key] ?? null;

        if (! $publisher) {
            return;
        }

        if ($this->hasTableForMigration($publisher['table'])) {
            return;
        }

        if ($this->hasMigrationPublished($publisher['migration_glob'])) {
            return;
        }

        $this->info("Publishing {$publisher['label']} migrations...");
        $arguments = ['--tag' => $publisher['tag']];
        if (! empty($publisher['provider'])) {
            $arguments['--provider'] = $publisher['provider'];
        }

        $this->call('vendor:publish', $arguments);
    }

    /**
     * Ask which plugins to activate per panel.
     */
    protected function activatePluginsPerPanel(array $plugins, array $options): void
    {
        $managedPanels = config('filament-starter.managed_panels', []);

        foreach ($managedPanels as $panelId) {
            $this->info("Configuring plugins for panel: {$panelId}");

            $enabledInRegistry = collect($plugins)
                ->keys()
                ->values()
                ->toArray();

            $selected = $this->multiSelect(
                "Which plugins should be ENABLED in the '{$panelId}' panel?",
                $options,
                $enabledInRegistry
            );

            foreach ($plugins as $key => $definition) {
                $isEnabled = in_array($key, $selected);

                PanelPluginOverride::updateOrCreate(
                    ['panel_id' => $panelId, 'plugin_key' => $key, 'tenant_id' => null],
                    ['enabled' => $isEnabled]
                );
            }
        }
    }

    /**
     * Ask which plugins to mark as dangerous.
     */
    protected function markDangerousPlugins(array $plugins, array $options): void
    {
        $dangerousInRegistry = collect($plugins)
            ->filter(fn ($p) => $p['dangerous_to_disable'])
            ->keys()
            ->values()
            ->toArray();

        $selected = $this->multiSelect(
            'Which plugins should be marked as DANGEROUS to disable? (These will be forced to enabled)',
            $options,
            $dangerousInRegistry
        );

        foreach ($selected as $key) {
            // Apply to all managed panels
            $managedPanels = config('filament-starter.managed_panels', []);
            foreach ($managedPanels as $panelId) {
                PanelPluginOverride::updateOrCreate(
                    ['panel_id' => $panelId, 'plugin_key' => $key, 'tenant_id' => null],
                    ['is_dangerous' => true, 'enabled' => true]
                );
            }
        }
    }

    /**
     * Run health checks and alert about missing dependencies.
     */
    protected function checkHealth(): void
    {
        $doctor = app(Doctor::class);
        $results = $doctor->check();

        foreach ($results as $result) {
            if ($result['status'] === 'critical') {
                $this->error("[CRITICAL] {$result['check']}: {$result['message']}");
            } elseif ($result['status'] === 'warning') {
                $this->warn("[WARNING] {$result['check']}: {$result['message']}");
            }
        }
    }

    /**
     * Interactive setup for Knowledge Base.
     */
    protected function setupKnowledgeBase(): void
    {
        if ($this->option('no-interaction')) {
            return;
        }

        $this->newLine();
        $this->info('--- Knowledge Base Setup ---');

        if (! $this->confirm('Do you want to set up the Knowledge Base plugin?')) {
            return;
        }

        $kbPluginKey = 'filament-knowledge-base';
        $kbCompanionKey = 'filament-knowledge-base-companion';
        $kbPanelId = 'knowledge-base';
        $panels = $this->getPanelIds();

        // 1. Check for dedicated KB panel
        if (! in_array($kbPanelId, $panels)) {
            $this->warn("Dedicated panel '{$kbPanelId}' not found.");
            if ($this->confirm("Do you want to create the '{$kbPanelId}' panel now?", true)) {
                $this->call('filament:panel', ['id' => $kbPanelId]);
                // Refresh snapshots
                \EdrisaTuray\FilamentStarter\Support\PanelSnapshotManager::snapshot();
                $panels = \EdrisaTuray\FilamentStarter\Models\PanelSnapshot::pluck('panel_id')->toArray();
            }
        } else {
            $this->info("Panel '{$kbPanelId}' found.");
        }

        // 2. Check for NPM dependency
        $packageJsonPath = base_path('package.json');
        if (file_exists($packageJsonPath)) {
            $packageJson = json_decode(file_get_contents($packageJsonPath), true);
            $installedNpm = array_merge($packageJson['dependencies'] ?? [], $packageJson['devDependencies'] ?? []);
            if (! isset($installedNpm['@tailwindcss/typography'])) {
                if ($this->confirm("NPM dependency '@tailwindcss/typography' is missing. Do you want to install it now?", true)) {
                    $this->info('Installing @tailwindcss/typography...');
                    passthru('npm install -D @tailwindcss/typography');
                }
            }
        }

        // 3. Check for custom themes in panels where KB is enabled
        foreach ($panels as $panelId) {
            $states = \EdrisaTuray\FilamentStarter\Support\PluginStateResolver::resolve($panelId);
            if (($states[$kbPluginKey]['enabled'] ?? false) || ($states[$kbCompanionKey]['enabled'] ?? false)) {
                $themePath = resource_path("css/filament/{$panelId}/theme.css");
                if (! file_exists($themePath)) {
                    if ($this->confirm("Custom theme for panel '{$panelId}' not found. KB requires a custom theme. Create it now?", true)) {
                        $this->call('make:filament-theme', ['panel' => $panelId]);
                    }
                }
            }
        }

        $this->line('');
        $this->info('Knowledge Base requires specific Tailwind directives in your custom themes.');
        $this->line('Ensure your theme CSS files (e.g., resources/css/filament/admin/theme.css) include:');
        $this->line('<comment>@plugin "@tailwindcss/typography";</comment>');
        $this->line("<comment>@source '../../../../vendor/guava/filament-knowledge-base/src/**/*';</comment>");
        $this->line("<comment>@source '../../../../vendor/guava/filament-knowledge-base/resources/views/**/*';</comment>");

        if ($this->confirm('Do you want to enable Knowledge Base Companion in any panel now?')) {
            $choices = $this->choice(
                'Select panels for Knowledge Base Companion (comma separated)',
                array_merge(['none'], $panels),
                0,
                null,
                true
            );

            if (! in_array('none', $choices)) {
                foreach ($choices as $panelId) {
                    \EdrisaTuray\FilamentStarter\Models\PanelPluginOverride::updateOrCreate(
                        ['panel_id' => $panelId, 'plugin_key' => 'filament-knowledge-base-companion', 'tenant_id' => null],
                        ['enabled' => true]
                    );
                    $this->info("Enabled KB Companion for {$panelId}.");
                }
            }
        }
    }

    /**
     * Interactive setup for Filament Shield.
     */
    protected function setupShield(): void
    {
        if ($this->option('no-interaction')) {
            return;
        }

        $this->newLine();
        $this->info('--- Filament Shield Setup ---');
        $this->call('shield:setup');

        if ($this->confirm('Create a Shield super admin role now?', true)) {
            $this->call('shield:super_admin');
        }
    }

    /**
     * Ensure Laravel Sanctum is installed for API tokens.
     */
    protected function ensureSanctumInstalled(): void
    {
        if ($this->hasSanctumConfig()) {
            return;
        }

        $this->info('Installing Laravel Sanctum (install:api)...');
        $this->call('install:api', ['--no-interaction' => true]);
    }

    protected function hasSanctumConfig(): bool
    {
        return file_exists(config_path('sanctum.php'));
    }

    /**
     * Publish all required migrations for enabled plugins and core dependencies.
     */
    protected function publishRequiredMigrations(): void
    {
        foreach ($this->requiredMigrationPublishers() as $publisher) {
            if ($this->hasTableForMigration($publisher['table'])) {
                continue;
            }

            if ($this->hasMigrationPublished($publisher['migration_glob'])) {
                continue;
            }

            $this->info("Publishing {$publisher['label']} migrations...");
            $arguments = ['--tag' => $publisher['tag']];
            if (! empty($publisher['provider'])) {
                $arguments['--provider'] = $publisher['provider'];
            }

            $this->call('vendor:publish', $arguments);
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function requiredMigrationPublishers(): array
    {
        return [
            [
                'label' => 'Spatie Activitylog',
                'table' => config('activitylog.table_name', 'activity_log'),
                'tag' => 'activitylog-migrations',
                'provider' => \Spatie\Activitylog\ActivitylogServiceProvider::class,
                'migration_glob' => '*_create_activity_log_table.php',
            ],
            [
                'label' => 'Authentication Log',
                'table' => config('authentication-log.table_name', 'authentication_log'),
                'tag' => 'authentication-log-migrations',
                'provider' => \Rappasoft\LaravelAuthenticationLog\LaravelAuthenticationLogServiceProvider::class,
                'migration_glob' => '*_create_authentication_log_table.php',
            ],
            [
                'label' => 'Filament Breezy',
                'table' => 'breezy_sessions',
                'tag' => 'filament-breezy-migrations',
                'provider' => \Jeffgreco13\FilamentBreezy\FilamentBreezyServiceProvider::class,
                'migration_glob' => '*_create_breezy_sessions_table.php',
            ],
            [
                'label' => 'Filament Email',
                'table' => 'filament_email_log',
                'tag' => 'filament-email-migrations',
                'provider' => \RickDBCN\FilamentEmail\FilamentEmailServiceProvider::class,
                'migration_glob' => '*_create_filament_email_table.php',
            ],
            [
                'label' => 'Advanced Tables (Filter Sets)',
                'table' => 'filament_filter_sets',
                'tag' => 'advanced-tables-migrations',
                'provider' => \Archilex\AdvancedTables\AdvancedTablesServiceProvider::class,
                'migration_glob' => '*_create_filament_filter_sets_table.php',
            ],
            [
                'label' => 'Spatie Health',
                'table' => 'health_check_result_history_items',
                'tag' => 'health-migrations',
                'provider' => \Spatie\Health\HealthServiceProvider::class,
                'migration_glob' => '*_create_health_tables.php',
            ],
            [
                'label' => 'Filament Jobs Monitor',
                'table' => 'queue_monitors',
                'tag' => 'filament-jobs-monitor-migrations',
                'provider' => \Croustibat\FilamentJobsMonitor\FilamentJobsMonitorServiceProvider::class,
                'migration_glob' => '*_create_filament-jobs-monitor_table.php',
            ],
            [
                'label' => 'Promethys Revive',
                'table' => 'recycle_bin_items',
                'tag' => 'revive-migrations',
                'provider' => \Promethys\Revive\ReviveServiceProvider::class,
                'migration_glob' => '*_create_recycle_bin_items_table.php',
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function pluginMigrationPublishers(): array
    {
        return [
            'filament-activity-log' => [
                'label' => 'Spatie Activitylog',
                'table' => config('activitylog.table_name', 'activity_log'),
                'tag' => 'activitylog-migrations',
                'provider' => \Spatie\Activitylog\ActivitylogServiceProvider::class,
                'migration_glob' => '*_create_activity_log_table.php',
            ],
            'filament-authentication-log' => [
                'label' => 'Authentication Log',
                'table' => config('authentication-log.table_name', 'authentication_log'),
                'tag' => 'authentication-log-migrations',
                'provider' => \Rappasoft\LaravelAuthenticationLog\LaravelAuthenticationLogServiceProvider::class,
                'migration_glob' => '*_create_authentication_log_table.php',
            ],
            'filament-breezy' => [
                'label' => 'Filament Breezy',
                'table' => 'breezy_sessions',
                'tag' => 'filament-breezy-migrations',
                'provider' => \Jeffgreco13\FilamentBreezy\FilamentBreezyServiceProvider::class,
                'migration_glob' => '*_create_breezy_sessions_table.php',
            ],
            'filament-email' => [
                'label' => 'Filament Email',
                'table' => 'filament_email_log',
                'tag' => 'filament-email-migrations',
                'provider' => \RickDBCN\FilamentEmail\FilamentEmailServiceProvider::class,
                'migration_glob' => '*_create_filament_email_table.php',
            ],
            'filter-sets' => [
                'label' => 'Advanced Tables (Filter Sets)',
                'table' => 'filament_filter_sets',
                'tag' => 'advanced-tables-migrations',
                'provider' => \Archilex\AdvancedTables\AdvancedTablesServiceProvider::class,
                'migration_glob' => '*_create_filament_filter_sets_table.php',
            ],
            'filament-health' => [
                'label' => 'Spatie Health',
                'table' => 'health_check_result_history_items',
                'tag' => 'health-migrations',
                'provider' => \Spatie\Health\HealthServiceProvider::class,
                'migration_glob' => '*_create_health_tables.php',
            ],
            'filament-jobs-monitor' => [
                'label' => 'Filament Jobs Monitor',
                'table' => 'queue_monitors',
                'tag' => 'filament-jobs-monitor-migrations',
                'provider' => \Croustibat\FilamentJobsMonitor\FilamentJobsMonitorServiceProvider::class,
                'migration_glob' => '*_create_filament-jobs-monitor_table.php',
            ],
            'filament-revive' => [
                'label' => 'Promethys Revive',
                'table' => 'recycle_bin_items',
                'tag' => 'revive-migrations',
                'provider' => \Promethys\Revive\ReviveServiceProvider::class,
                'migration_glob' => '*_create_recycle_bin_items_table.php',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function configPublishTags(): array
    {
        return [
            'tomatophp/filament-users' => 'filament-users-config',
        ];
    }

    protected function hasMigrationPublished(string $pattern): bool
    {
        $migrationPath = database_path('migrations');

        if (! is_dir($migrationPath)) {
            return false;
        }

        return (bool) glob($migrationPath.'/'.$pattern);
    }

    protected function hasTableForMigration(string $table): bool
    {
        return \Illuminate\Support\Facades\Schema::hasTable($table);
    }

    /**
     * Prompt for multi-select choices with a spacebar-friendly UI.
     *
     * @param  array<int|string, string>  $options
     * @param  array<int|string>  $default
     * @return array<int|string>
     */
    protected function multiSelect(string $label, array $options, array $default = []): array
    {
        return multiselect(
            label: $label,
            options: $options,
            default: $default,
            scroll: 12,
        );
    }

    /**
     * Interactive setup for Filament Backgrounds.
     */
    protected function setupBackgrounds(): void
    {
        $this->newLine();
        $this->info('--- Filament Backgrounds Setup ---');

        if ($this->option('no-interaction')) {
            // In non-interactive mode, we still run the install command
            $this->call('filament-backgrounds:install', ['--no-interaction' => true]);

            return;
        }

        if (! $this->confirm('Do you want to set up the Filament Backgrounds plugin?')) {
            return;
        }

        // 1. Run the plugin's install command
        $this->call('filament-backgrounds:install');

        // 2. Ask for global config options
        $this->info('Configuring global background settings...');

        $attribution = $this->confirm('Show photographer attribution?', true);
        $remember = $this->ask('Cache time for images (in seconds)?', 900);
        $provider = $this->choice(
            'Select default image provider:',
            ['curated', 'my-images', 'triangles'],
            0
        );

        $directory = 'images/backgrounds';
        if ($provider === 'my-images') {
            $directory = $this->ask('Directory for your custom images (inside public/)?', 'images/backgrounds');
        }

        $this->info('Updating background configuration...');
        $this->warn('Please ensure the following values are set in config/filament-starter.php:');
        $this->line("- 'plugins.backgrounds.show_attribution' => ".($attribution ? 'true' : 'false'));
        $this->line("- 'plugins.backgrounds.remember' => {$remember}");
        $this->line("- 'plugins.backgrounds.image_provider' => '{$provider}'");
        $this->line("- 'plugins.backgrounds.my_images_directory' => '{$directory}'");

        // 3. Ask which panels should have it enabled
        $panels = $this->getPanelIds();
        $enabledPanels = $this->choice(
            'In which panels should Filament Backgrounds be ENABLED?',
            array_merge(['none', 'all'], $panels),
            0,
            null,
            true
        );

        if (! in_array('none', $enabledPanels)) {
            $panelsToEnable = in_array('all', $enabledPanels) ? $panels : $enabledPanels;

            foreach ($panelsToEnable as $panelId) {
                \EdrisaTuray\FilamentStarter\Models\PanelPluginOverride::updateOrCreate(
                    ['panel_id' => $panelId, 'plugin_key' => 'filament-backgrounds', 'tenant_id' => null],
                    ['enabled' => true]
                );
            }
            $this->info('Filament Backgrounds activated for selected panels.');
        }
    }

    /**
     * Interactive setup for Promethys Revive.
     */
    protected function setupRevive(): void
    {
        $this->newLine();
        $this->info('--- Promethys Revive (Recycle Bin) Setup ---');

        if ($this->option('no-interaction')) {
            $this->publishReviveMigrationsIfNeeded();

            return;
        }

        if (! $this->confirm('Do you want to set up the Revive Recycle Bin?')) {
            return;
        }

        // 1. Publish required migrations when missing
        $this->publishReviveMigrationsIfNeeded();

        if (! $this->hasRecycleBinItemsTable() && $this->confirm('Would you like to run the migrations now?', true)) {
            $this->call('migrate', ['--force' => true]);
        }

        // 2. Ask for global config options
        $this->info('Configuring global Revive settings...');

        $userScoping = $this->confirm('Enable User Scoping by default? (Users see only their own deletions)', true);
        $tenantScoping = $this->confirm('Enable Tenant Scoping by default? (Users see deletions within their tenant)', true);

        $panels = $this->getPanelIds();
        $adminPanels = $this->choice(
            'Which panels should be "Global Admin Panels"? (See all records regardless of user/tenant)',
            array_merge(['none'], $panels),
            0,
            null,
            true
        );

        $adminPanels = in_array('none', $adminPanels) ? [] : $adminPanels;

        $this->info('Updating Revive configuration...');
        $this->warn('Please ensure the following values are set in config/filament-starter.php:');
        $this->line("- 'plugins.revive.user_scoping' => ".($userScoping ? 'true' : 'false'));
        $this->line("- 'plugins.revive.tenant_scoping' => ".($tenantScoping ? 'true' : 'false'));
        $this->line("- 'plugins.revive.global_admin_panels' => ['".implode("', '", $adminPanels)."']");

        // 3. Ask which panels should have it enabled
        $enabledPanels = $this->choice(
            'In which panels should Revive be ENABLED?',
            array_merge(['none', 'all'], $panels),
            0,
            null,
            true
        );

        if (! in_array('none', $enabledPanels)) {
            $panelsToEnable = in_array('all', $enabledPanels) ? $panels : $enabledPanels;

            foreach ($panelsToEnable as $panelId) {
                \EdrisaTuray\FilamentStarter\Models\PanelPluginOverride::updateOrCreate(
                    ['panel_id' => $panelId, 'plugin_key' => 'filament-revive', 'tenant_id' => null],
                    ['enabled' => true]
                );
            }
            $this->info('Revive Recycle Bin activated for selected panels.');
        }
    }

    /**
     * Get invariant options from command or interaction.
     */
    protected function getInvariants(string $key): bool
    {
        $option = $this->option($key);

        if ($option !== null) {
            return in_array(strtolower($option), ['yes', 'true', '1']);
        }

        if ($this->option('no-interaction')) {
            return false;
        }

        return $this->confirm("Enable {$key}?", false);
    }

    /**
     * Update platform configuration.
     */
    protected function updateConfig(bool $tenancy, bool $multilanguage): void
    {
        // Ideally we use a package like 'october/rain' or similar for config writing
        // For now, we will just output what should be changed or assume it's handled via env/overrides
        $this->info('Locked invariants: Tenancy='.($tenancy ? 'Yes' : 'No').', Multilanguage='.($multilanguage ? 'Yes' : 'No'));
    }

    /**
     * @return array<int, string>
     */
    protected function getPanelIds(): array
    {
        return \EdrisaTuray\FilamentStarter\Models\PanelSnapshot::pluck('panel_id')->toArray();
    }

    protected function publishReviveMigrationsIfNeeded(): void
    {
        if ($this->hasRecycleBinItemsTable()) {
            return;
        }

        if ($this->hasMigrationPublished('*_create_recycle_bin_items_table.php')) {
            return;
        }

        $this->info('Publishing Promethys Revive migrations...');
        $this->call('vendor:publish', [
            '--tag' => 'revive-migrations',
            '--provider' => \Promethys\Revive\ReviveServiceProvider::class,
        ]);
    }

    protected function hasRecycleBinItemsTable(): bool
    {
        return \Illuminate\Support\Facades\Schema::hasTable('recycle_bin_items');
    }
}
