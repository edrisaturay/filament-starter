<?php

namespace EdrisaTuray\FilamentStarter\Commands;

use EdrisaTuray\FilamentStarter\Support\OptionSchemaMigrator;
use EdrisaTuray\FilamentStarter\Support\PanelSnapshotManager;
use EdrisaTuray\FilamentStarter\Support\PluginRegistry;
use Illuminate\Console\Command;
use function Laravel\Prompts\multiselect;

class StarterUpdateCommand extends Command
{
    protected $signature = 'starter:update';

    protected $description = 'Run update logic for the Starter Platform';

    public function handle(OptionSchemaMigrator $migrator): int
    {
        $this->info('Starting platform update...');

        // 1. Publish configs and migrations if desired
        $this->publishPluginAssets();

        // 1. Publish required migrations before migrating
        $this->publishRequiredMigrations();

        // 2. Run migrations
        $this->call('migrate', ['--force' => true]);

        // 3. Migrate option schemas
        $migrator->migrate();

        // 4. Refresh snapshots
        PanelSnapshotManager::snapshot();

        $this->info('Platform updated successfully.');

        return 0;
    }

    /**
     * Publish plugin configs and migrations on demand.
     */
    protected function publishPluginAssets(): void
    {
        if ($this->option('no-interaction')) {
            return;
        }

        $plugins = PluginRegistry::getPlugins();
        $options = collect($plugins)
            ->mapWithKeys(fn ($definition, $key) => [$key => $definition['label']])
            ->toArray();

        $toPublish = $this->multiSelect(
            'Which plugins should have configs/migrations published?',
            array_merge(['none' => 'none', 'all' => 'all'], $options),
            ['none']
        );

        if (in_array('none', $toPublish, true)) {
            return;
        }

        if (in_array('all', $toPublish, true)) {
            $toPublish = array_keys($plugins);
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
        );
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
}
