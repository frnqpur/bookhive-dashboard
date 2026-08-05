<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BookHiveDemoResetAlias extends Command
{
    protected $signature = 'bookhive:demo-reset
        {--trigger=scheduled : Trigger source, for example scheduled or manual}
        {--user-id= : User id that triggered a manual reset}
        {--skip-storage-cleanup : Keep uploaded public book cover files even if they are no longer referenced}';

    protected $description = 'Backward-compatible alias for demo:reset.';

    public function handle(): int
    {
        $this->warn('bookhive:demo-reset is deprecated. Use php artisan demo:reset instead.');

        return $this->call('demo:reset', [
            '--trigger' => $this->option('trigger'),
            '--user-id' => $this->option('user-id'),
            '--skip-storage-cleanup' => (bool) $this->option('skip-storage-cleanup'),
        ]);
    }
}
