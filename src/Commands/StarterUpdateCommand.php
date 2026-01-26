<?php

namespace EdrisaTuray\FilamentStarter\Commands;

use Illuminate\Console\Command;
use EdrisaTuray\FilamentStarter\Support\OptionSchemaMigrator;
use EdrisaTuray\FilamentStarter\Support\PanelSnapshotManager;

class StarterUpdateCommand extends Command
{
    protected $signature = 'starter:update';

    protected $description = 'Run update logic for the Starter Platform';

    public function handle(OptionSchemaMigrator $migrator): int
    {
        $this->info('Starting platform update...');

        // 1. Run migrations
        $this->call('migrate', ['--force' => true]);

        // 2. Migrate option schemas
        $migrator->migrate();

        // 3. Refresh snapshots
        PanelSnapshotManager::snapshot();

        $this->info('Platform updated successfully.');

        return 0;
    }
}
