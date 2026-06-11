<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PruneTelemetryLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'telemetry:prune {--days=30 : The number of days of history to retain}';

    /**
     * The console command description.
     */
    protected $description = 'Deletes stale, high-frequency GPS telemetry logs to optimize database performance.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Pruning telemetry logs older than {$cutoffDate->toDateString()}...");

        $deletedCount = DB::table('telemetry_logs')
            ->where('recorded_at', '<', $cutoffDate)
            ->delete();

        $this->info("Successfully deleted {$deletedCount} stale telemetry records.");
        return Command::SUCCESS;
    }
}