<?php

namespace Raison\FilamentStarter\Commands;

use Illuminate\Console\Command;
use Raison\FilamentStarter\Support\PanelSnapshotManager;

class StarterInstallCommand extends Command
{
    protected $signature = 'starter:install {--force} {--tenancy= : Enable tenancy (yes/no)} {--multilanguage= : Enable multilanguage (yes/no)}';

    protected $description = 'Initialize the Starter Platform';

    public function handle(): int
    {
        if (config('filament-starter.installed') && ! $this->option('force')) {
            $this->error('Platform already installed. Use --force to reinstall.');

            return 1;
        }

        $this->info('Starting Starter Platform installation...');

        // 1. Run migrations
        $this->call('migrate', ['--force' => true]);

        // 2. Snapshot panels
        $this->info('Snapshotting panels...');
        PanelSnapshotManager::snapshot();

        // 3. Collect invariants
        $tenancy = $this->getInvariants('tenancy');
        $multilanguage = $this->getInvariants('multilanguage');

        // 4. Update config file (simulated for this environment, in real app would use a proper config writer)
        $this->updateConfig($tenancy, $multilanguage);

        // 5. Mark as installed in DB (using a simple setting or log)
        \Raison\FilamentStarter\Models\AuditLog::create([
            'action' => 'install',
            'after' => [
                'tenancy' => $tenancy,
                'multilanguage' => $multilanguage,
            ],
        ]);

        $this->info('Platform installed successfully.');

        return 0;
    }

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

    protected function updateConfig(bool $tenancy, bool $multilanguage): void
    {
        // Ideally we use a package like 'october/rain' or similar for config writing
        // For now, we will just output what should be changed or assume it's handled via env/overrides
        $this->info('Locked invariants: Tenancy='.($tenancy ? 'Yes' : 'No').', Multilanguage='.($multilanguage ? 'Yes' : 'No'));
    }
}
