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

        // 1. Ensure Breezy migrations are available
        $this->ensureBreezyInstalled();

        // 2. Run migrations
        $this->call('migrate', ['--force' => true]);

        // 3. Migrate option schemas
        $migrator->migrate();

        // 4. Refresh snapshots
        PanelSnapshotManager::snapshot();

        $this->info('Platform updated successfully.');

        return 0;
    }

    protected function ensureBreezyInstalled(): void
    {
        if (! class_exists(\Jeffgreco13\FilamentBreezy\BreezyCore::class)) {
            return;
        }

        if ($this->hasBreezySessionsTable()) {
            return;
        }

        if (! $this->hasBreezyMigrations()) {
            $this->info('Publishing Filament Breezy migrations...');
            $this->call('vendor:publish', [
                '--tag' => 'filament-breezy-migrations',
            ]);
        }
    }

    protected function hasBreezyMigrations(): bool
    {
        $migrationPath = database_path('migrations');

        if (! is_dir($migrationPath)) {
            return false;
        }

        foreach (glob($migrationPath.'/*_create_breezy_sessions_table.php') as $file) {
            return true;
        }

        return false;
    }

    protected function hasBreezySessionsTable(): bool
    {
        return \Illuminate\Support\Facades\Schema::hasTable('breezy_sessions');
    }
}
