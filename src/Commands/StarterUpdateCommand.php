<?php

namespace EdrisaTuray\FilamentStarter\Commands;

use EdrisaTuray\FilamentStarter\Support\OptionSchemaMigrator;
use EdrisaTuray\FilamentStarter\Support\PanelSnapshotManager;
use Illuminate\Console\Command;

class StarterUpdateCommand extends Command
{
    protected $signature = 'starter:update';

    protected $description = 'Run update logic for the Starter Platform';

    public function handle(OptionSchemaMigrator $migrator): int
    {
        $this->info('Starting platform update...');

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
                'tag' => 'laravel-health-migrations',
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
