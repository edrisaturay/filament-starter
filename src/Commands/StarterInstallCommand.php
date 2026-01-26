<?php

namespace EdrisaTuray\FilamentStarter\Commands;

use EdrisaTuray\FilamentStarter\Models\PanelPluginOverride;
use EdrisaTuray\FilamentStarter\Support\Doctor;
use EdrisaTuray\FilamentStarter\Support\PanelSnapshotManager;
use EdrisaTuray\FilamentStarter\Support\PluginRegistry;
use Illuminate\Console\Command;

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

        // 1. Run migrations
        $this->call('migrate', ['--force' => true]);

        // 2. Snapshot panels and sync plugins
        $this->info('Snapshotting panels and syncing plugins...');
        PanelSnapshotManager::snapshot();

        // 3. Interactive Plugin Configuration
        $this->configurePlugins();

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
        $pluginOptions = collect($plugins)->map(fn ($p) => $p['label'])->toArray();

        // 1. Publish Configs
        $this->publishPluginConfigs($plugins, $pluginOptions);

        // 2. Enable/Disable per Panel
        $this->activatePluginsPerPanel($plugins, $pluginOptions);

        // 3. Mark Dangerous Plugins
        $this->markDangerousPlugins($plugins, $pluginOptions);
    }

    /**
     * Ask which plugin configs to publish.
     */
    protected function publishPluginConfigs(array $plugins, array $options): void
    {
        if ($this->option('publish-all')) {
            $toPublish = array_keys($options);
        } else {
            $toPublish = $this->choice(
                'Which plugin config files should be published? (comma separated indices)',
                array_merge(['none', 'all'], array_values($options)),
                0,
                null,
                true
            );

            if (in_array('none', $toPublish)) {
                return;
            }

            if (in_array('all', $toPublish)) {
                $toPublish = array_keys($options);
            } else {
                // Map labels back to keys
                $labelToKey = array_flip(collect($plugins)->map(fn ($p) => $p['label'])->toArray());
                $toPublish = collect($toPublish)->map(fn ($label) => $labelToKey[$label] ?? null)->filter()->toArray();
            }
        }

        foreach ($toPublish as $key) {
            $package = $plugins[$key]['package'] ?? null;
            if ($package) {
                $this->info("Publishing config for {$plugins[$key]['label']}...");
                // In a real scenario, we'd need the actual provider or tag.
                // We'll attempt a generic publish by provider if available in registry
                $class = $plugins[$key]['class'] ?? null;
                if ($class) {
                    $this->call('vendor:publish', [
                        '--provider' => $class,
                        '--tag' => 'config',
                    ]);
                }
            }
        }
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
                ->filter(fn ($p) => $p['default_enabled'])
                ->map(fn ($p) => $p['label'])
                ->values()
                ->toArray();

            $selected = $this->choice(
                "Which plugins should be ENABLED in the '{$panelId}' panel?",
                array_values($options),
                implode(',', $enabledInRegistry),
                null,
                true
            );

            $labelToKey = array_flip(collect($plugins)->map(fn ($p) => $p['label'])->toArray());
            $selectedKeys = collect($selected)->map(fn ($label) => $labelToKey[$label])->toArray();

            foreach ($plugins as $key => $definition) {
                $isEnabled = in_array($key, $selectedKeys);

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
            ->map(fn ($p) => $p['label'])
            ->values()
            ->toArray();

        $selected = $this->choice(
            'Which plugins should be marked as DANGEROUS to disable? (These will be forced to enabled)',
            array_values($options),
            implode(',', $dangerousInRegistry),
            null,
            true
        );

        $labelToKey = array_flip(collect($plugins)->map(fn ($p) => $p['label'])->toArray());
        $selectedKeys = collect($selected)->map(fn ($label) => $labelToKey[$label])->toArray();

        foreach ($selectedKeys as $key) {
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

        $kbPanelId = 'knowledge-base';
        $panels = \EdrisaTuray\FilamentStarter\Models\PanelSnapshot::pluck('panel_id')->toArray();

        if (! in_array($kbPanelId, $panels)) {
            $this->warn("Dedicated panel '{$kbPanelId}' not found.");
            $this->line("Please run: <comment>php artisan filament:panel {$kbPanelId}</comment>");
            $this->line('Then register the <info>KnowledgeBasePlugin</info> in its Provider.');
        } else {
            $this->info("Panel '{$kbPanelId}' found.");
        }

        $this->line('');
        $this->info('Knowledge Base requires custom Filament themes.');
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
        $panels = \EdrisaTuray\FilamentStarter\Models\PanelSnapshot::pluck('panel_id')->toArray();
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
            $this->call('revive:install', ['--no-interaction' => true]);

            return;
        }

        if (! $this->confirm('Do you want to set up the Revive Recycle Bin?')) {
            return;
        }

        // 1. Run the plugin's install command
        $this->call('revive:install');

        // 2. Ask for global config options
        $this->info('Configuring global Revive settings...');

        $userScoping = $this->confirm('Enable User Scoping by default? (Users see only their own deletions)', true);
        $tenantScoping = $this->confirm('Enable Tenant Scoping by default? (Users see deletions within their tenant)', true);

        $panels = \EdrisaTuray\FilamentStarter\Models\PanelSnapshot::pluck('panel_id')->toArray();
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
}
