<?php

namespace EdrisaTuray\FilamentStarter\Commands;

use EdrisaTuray\FilamentStarter\Support\SafeMode;
use Illuminate\Console\Command;

class StarterSafeModeCommand extends Command
{
    protected $signature = 'starter:safe-mode {status : on or off}';

    protected $description = 'Toggle Safe Mode for the Starter Platform';

    public function handle(): int
    {
        $status = $this->argument('status');

        if ($status === 'on') {
            SafeMode::activate();
            $this->info('Safe Mode activated.');
        } elseif ($status === 'off') {
            SafeMode::deactivate();
            $this->info('Safe Mode deactivated.');
        } else {
            $this->error('Invalid status. Use "on" or "off".');

            return 1;
        }

        return 0;
    }
}
