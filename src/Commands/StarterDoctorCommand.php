<?php

namespace EdrisaTuray\FilamentStarter\Commands;

use Illuminate\Console\Command;
use EdrisaTuray\FilamentStarter\Support\Doctor;

/**
 * Class StarterDoctorCommand
 *
 * Artisan command to check the health of the Starter Platform.
 */
class StarterDoctorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starter:doctor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the health of the Starter Platform';

    /**
     * Execute the console command.
     */
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
