<?php

namespace Raison\FilamentStarter\Commands;

use Illuminate\Console\Command;
use Raison\FilamentStarter\Support\Doctor;

class StarterDoctorCommand extends Command
{
    protected $signature = 'starter:doctor';

    protected $description = 'Check the health of the Starter Platform';

    public function handle(Doctor $doctor): int
    {
        $this->info('Running platform health checks...');
        $results = $doctor->check();
        $exitCode = 0;

        foreach ($results as $result) {
            $statusLabel = strtoupper($result['status']);
            $color = match ($result['status']) {
                'ok' => 'info',
                'warning' => 'comment',
                'critical' => 'error',
                default => 'info',
            };

            $this->line("<{$color}>[{$statusLabel}]</{$color}> {$result['check']}: {$result['message']}");

            if ($result['status'] === 'critical') {
                $exitCode = 1;
            }
        }

        return $exitCode;
    }
}
